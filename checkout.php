<?php
include './include/connection.php';
require_once './vendor/autoload.php';
require_once './functions/razorpay_config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

if (!$user_id) {
    die("Please login to continue checkout");
}

// Get user's saved addresses
$addresses_query = "SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC";
$stmt = $conn->prepare($addresses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses_result = $stmt->get_result();
$saved_addresses = [];
while ($address = mysqli_fetch_assoc($addresses_result)) {
    $saved_addresses[] = $address;
}

// Get cart items for the user
$products_query = "
    SELECT 
        c.id AS cart_id,
        c.qty,
        c.price AS cart_price,
        p.id AS product_id,
        p.name AS product_name,
        p.slug AS product_slug,
        p.image AS product_images,
        p.price AS product_price,
        p.discount_price AS product_discount,
        v.id AS variant_id,
        v.variant_name,
        v.price AS variant_price,
        v.discount_price AS variant_discount,
        v.image AS variant_image,
        cat.id AS category_id,
        cat.name AS category_name,
        cat.slug AS category_slug,
        cat.image AS category_image
    FROM cart c
    LEFT JOIN products p ON c.product_id = p.id
    LEFT JOIN product_variants v ON c.variant_id = v.id
    LEFT JOIN categories cat ON p.category_id = cat.id
    WHERE c.user_id = ?
";

$stmt = $conn->prepare($products_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$carts = $stmt->get_result();

// Calculate totals
$subtotal = 0;
$cart_items = [];
while ($item = mysqli_fetch_assoc($carts)) {
    $cart_items[] = $item;
    $item_price = $item['variant_id'] ?
        ($item['variant_discount'] > 0 ? $item['variant_discount'] : $item['variant_price']) : ($item['product_discount'] > 0 ? $item['product_discount'] : $item['product_price']);
    $subtotal += $item_price * $item['qty'];
}

mysqli_data_seek($carts, 0);

$tax_rate = 0.18; // 18% GST
$tax_amount = $subtotal * $tax_rate;
$total_amount = $subtotal + $tax_amount;

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    switch ($_POST['action']) {
        case 'save_address':
            echo json_encode(saveAddress($_POST, $user_id, $conn));
            exit;

        case 'apply_coupon':
            echo json_encode(applyCoupon($_POST['coupon_code'], $subtotal, $user_id, $conn));
            exit;

        case 'create_order':
            echo json_encode(createOrder($_POST, $cart_items, $user_id, $conn));
            exit;

        case 'process_payment':
            echo json_encode(processPayment($_POST, $conn));
            exit;
    }
}

function saveAddress($data, $user_id, $conn)
{
    try {
        $required_fields = ['full_name', 'phone', 'address_line1', 'city', 'state', 'postal_code'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => 'Please fill all required fields'];
            }
        }

        if (isset($data['is_default']) && $data['is_default'] == '1') {
            $stmt = $conn->prepare("UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }

        $stmt = $conn->prepare("INSERT INTO customer_addresses (user_id, full_name, phone, address_line1, address_line2, city, state, country, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $is_default = isset($data['is_default']) ? 1 : 0;
        $stmt->bind_param(
            "issssssssi",
            $user_id,
            $data['full_name'],
            $data['phone'],
            $data['address_line1'],
            $data['address_line2'] ?? '',
            $data['city'],
            $data['state'],
            $data['country'] ?? 'India',
            $data['postal_code'],
            $is_default
        );

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Address saved successfully', 'address_id' => $conn->insert_id];
        } else {
            return ['status' => 'error', 'message' => 'Failed to save address'];
        }
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Error saving address: ' . $e->getMessage()];
    }
}

function applyCoupon($coupon_code, $subtotal, $user_id, $conn)
{
    if (empty($coupon_code)) {
        return ['status' => 'error', 'message' => 'Please enter a coupon code'];
    }

    $coupon_query = "SELECT * FROM coupons WHERE code = ? AND status = 'active' 
                     AND valid_from <= NOW() AND valid_to >= NOW() 
                     AND min_order_value <= ?";

    $stmt = $conn->prepare($coupon_query);
    $stmt->bind_param("sd", $coupon_code, $subtotal);
    $stmt->execute();
    $coupon = $stmt->get_result()->fetch_assoc();

    if (!$coupon) {
        return ['status' => 'error', 'message' => 'Invalid or expired coupon'];
    }

    // Calculate discount
    if ($coupon['discount_type'] === 'percentage') {
        $discount = ($subtotal * $coupon['amount']) / 100;
        if (isset($coupon['max_discount']) && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }
    } else {
        $discount = $coupon['amount'];
    }

    $discount = min($discount, $subtotal);

    return [
        'status' => 'success',
        'discount' => $discount,
        'coupon_id' => $coupon['id'],
        'message' => 'Coupon applied successfully'
    ];
}
function createOrder($data, $cart_items, $user_id, $conn)
{
    // Ensure SDK loaded and config available
    if (!defined('RAZORPAY_KEY_ID') || !defined('RAZORPAY_KEY_SECRET')) {
        return ['status' => 'error', 'message' => 'Razorpay not configured'];
    }

    // Basic validations
    if (empty($cart_items)) {
        return ['status' => 'error', 'message' => 'Your cart is empty'];
    }

    // Get shipping address (reuse your helper)
    $shipping_address = getShippingAddress($data, $user_id, $conn);
    if ($shipping_address['status'] === 'error') {
        return $shipping_address;
    }

    try {
        $conn->begin_transaction();

        // Calculate subtotal
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $item_price = $item['variant_id'] ?
                ($item['variant_discount'] > 0 ? $item['variant_discount'] : $item['variant_price']) : ($item['product_discount'] > 0 ? $item['product_discount'] : $item['product_price']);
            $subtotal += $item_price * $item['qty'];
        }

        $discount = 0;
        $coupon_id = null;
        if (!empty($data['coupon_code'])) {
            $coupon_result = applyCoupon($data['coupon_code'], $subtotal, $user_id, $conn);
            if ($coupon_result['status'] === 'success') {
                $discount = $coupon_result['discount'];
                $coupon_id = $coupon_result['coupon_id'];
            }
        }

        $tax_amount = ($subtotal - $discount) * 0.18;
        $total_amount = $subtotal - $discount + $tax_amount;

        // Insert order into DB (payment_method will be updated below)
        $order_query = "INSERT INTO orders (user_id, total_amount, status, payment_status, shipping_address, created_at) 
                        VALUES (?, ?, 'pending', 'pending', ?, NOW())";
        $shipping_address_json = json_encode($shipping_address['data']);
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("ids", $user_id, $total_amount, $shipping_address_json);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Insert order items (unchanged)
        $item_query = "INSERT INTO order_items (order_id, product_id, variant_id, qty, price) VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $conn->prepare($item_query);
        foreach ($cart_items as $item) {
            $item_price = $item['variant_id'] ?
                ($item['variant_discount'] > 0 ? $item['variant_discount'] : $item['variant_price']) : ($item['product_discount'] > 0 ? $item['product_discount'] : $item['product_price']);
            $variant_id = $item['variant_id'] ?: null;
            $stmt_item->bind_param("iiiid", $order_id, $item['product_id'], $variant_id, $item['qty'], $item_price);
            $stmt_item->execute();
        }

        // If coupon applied, record usage (unchanged)
        if ($coupon_id) {
            $usage_query = "INSERT INTO coupon_usage (coupon_id, user_id, order_id, used_at) VALUES (?, ?, ?, NOW())";
            $stmt_usage = $conn->prepare($usage_query);
            $stmt_usage->bind_param("iii", $coupon_id, $user_id, $order_id);
            $stmt_usage->execute();
        }

        // If user wants online payment, create Razorpay order now
        $razorpay_order_id = null;
        $payment_method = $data['payment_method'] ?? 'cod';

        if ($payment_method === 'online') {
            // Use SDK to create order on Razorpay
            $api = new \Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

            $razorpayOrder = $api->order->create([
                'receipt' => "rcpt_{$order_id}",
                'amount' => intval(round($total_amount * 100)), // amount in paise
                'currency' => 'INR',
                'payment_capture' => 1
            ]);

            $razorpay_order_id = $razorpayOrder['id'] ?? null;

            // Store razorpay_order_id and payment_method in DB
            $update_order = "UPDATE orders SET razorpay_order_id = ?, payment_method = ? WHERE id = ?";
            $stmt2 = $conn->prepare($update_order);
            $stmt2->bind_param("ssi", $razorpay_order_id, $payment_method, $order_id);
            $stmt2->execute();
        } else {
            // store payment_method (cod)
            $update_order = "UPDATE orders SET payment_method = ? WHERE id = ?";
            $stmt2 = $conn->prepare($update_order);
            $stmt2->bind_param("si", $payment_method, $order_id);
            $stmt2->execute();
        }

        $conn->commit();

        // Return razorpay_order_id and public key when online, so client opens checkout
        return [
            'status' => 'success',
            'order_id' => $order_id,
            'amount' => $total_amount,
            'razorpay_order_id' => $razorpay_order_id,
            'razorpay_key' => RAZORPAY_KEY_ID,
            'message' => 'Order created successfully'
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return ['status' => 'error', 'message' => 'Failed to create order: ' . $e->getMessage()];
    }
}

/*
function createOrder($data, $cart_items, $user_id, $conn)
{
    if (empty($cart_items)) {
        return ['status' => 'error', 'message' => 'Your cart is empty'];
    }

    // Get shipping address
    $shipping_address = getShippingAddress($data, $user_id, $conn);
    if ($shipping_address['status'] === 'error') {
        return $shipping_address;
    }

    try {
        $conn->begin_transaction();

        // Calculate totals
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $item_price = $item['variant_id'] ?
                ($item['variant_discount'] > 0 ? $item['variant_discount'] : $item['variant_price']) : ($item['product_discount'] > 0 ? $item['product_discount'] : $item['product_price']);
            $subtotal += $item_price * $item['qty'];
        }

        $discount = 0;
        $coupon_id = null;

        // Apply coupon if provided
        if (!empty($data['coupon_code'])) {
            $coupon_result = applyCoupon($data['coupon_code'], $subtotal, $user_id, $conn);
            if ($coupon_result['status'] === 'success') {
                $discount = $coupon_result['discount'];
                $coupon_id = $coupon_result['coupon_id'];
            }
        }

        $tax_amount = ($subtotal - $discount) * 0.18;
        $total_amount = $subtotal - $discount + $tax_amount;

        // Create order
        $order_query = "INSERT INTO orders (user_id, total_amount, status, payment_status, shipping_address, created_at) 
                        VALUES (?, ?, 'pending', 'pending', ?, NOW())";

        $shipping_address_json = json_encode($shipping_address['data']);
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("ids", $user_id, $total_amount, $shipping_address_json);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Add order items
        $item_query = "INSERT INTO order_items (order_id, product_id, variant_id, qty, price) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($item_query);

        foreach ($cart_items as $item) {
            $item_price = $item['variant_id'] ?
                ($item['variant_discount'] > 0 ? $item['variant_discount'] : $item['variant_price']) : ($item['product_discount'] > 0 ? $item['product_discount'] : $item['product_price']);
            $variant_id = $item['variant_id'] ?: null;

            $stmt->bind_param("iiiid", $order_id, $item['product_id'], $variant_id, $item['qty'], $item_price);
            $stmt->execute();
        }

        // Record coupon usage if applied
        if ($coupon_id) {
            $usage_query = "INSERT INTO coupon_usage (coupon_id, user_id, order_id, used_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($usage_query);
            $stmt->bind_param("iii", $coupon_id, $user_id, $order_id);
            $stmt->execute();
        }

        $conn->commit();

        return [
            'status' => 'success',
            'order_id' => $order_id,
            'amount' => $total_amount,
            'message' => 'Order created successfully'
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return ['status' => 'error', 'message' => 'Failed to create order: ' . $e->getMessage()];
    }
}
*/
function getShippingAddress($data, $user_id, $conn)
{
    if (isset($data['selected_address']) && !empty($data['selected_address'])) {
        // Using saved address
        $address_query = "SELECT * FROM customer_addresses WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($address_query);
        $stmt->bind_param("ii", $data['selected_address'], $user_id);
        $stmt->execute();
        $address_result = $stmt->get_result()->fetch_assoc();

        if (!$address_result) {
            return ['status' => 'error', 'message' => 'Invalid address selected'];
        }

        return [
            'status' => 'success',
            'data' => [
                'full_name' => $address_result['full_name'],
                'phone' => $address_result['phone'],
                'address_line1' => $address_result['address_line1'],
                'address_line2' => $address_result['address_line2'],
                'city' => $address_result['city'],
                'state' => $address_result['state'],
                'country' => $address_result['country'],
                'postal_code' => $address_result['postal_code']
            ]
        ];
    } else {
        // Using form data for new address
        $required_fields = ['full_name', 'phone', 'address_line1', 'city', 'state', 'postal_code'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => 'Please provide complete address information'];
            }
        }

        return [
            'status' => 'success',
            'data' => [
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'address_line1' => $data['address_line1'],
                'address_line2' => $data['address_line2'] ?? '',
                'city' => $data['city'],
                'state' => $data['state'],
                'country' => $data['country'] ?? 'India',
                'postal_code' => $data['postal_code']
            ]
        ];
    }
}

function processPayment($data, $conn)
{
    try {
        $order_id = intval($data['order_id']);
        $payment_method = $data['payment_method'] ?? 'cod';

        // Fetch order (must be pending)
        $order_check = "SELECT * FROM orders WHERE id = ? AND payment_status = 'pending'";
        $stmt = $conn->prepare($order_check);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) {
            return ['status' => 'error', 'message' => 'Invalid order or order already processed'];
        }

        $conn->begin_transaction();

        if ($payment_method === 'cod') {
            // COD flow (same as yours)
            $update_order = "UPDATE orders SET status = 'confirmed', payment_status = 'pending', updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($update_order);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            $payment_query = "INSERT INTO payments (order_id, payment_method, amount, status, created_at) 
                              VALUES (?, 'cod', ?, 'pending', NOW())";
            $stmt = $conn->prepare($payment_query);
            $stmt->bind_param("id", $order_id, $order['total_amount']);
            $stmt->execute();

            // Clear cart
            $clear_cart = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($clear_cart);
            $stmt->bind_param("i", $order['user_id']);
            $stmt->execute();

            $conn->commit();

            return [
                'status' => 'success',
                'message' => 'Order confirmed! You can pay cash on delivery.',
                'order_id' => $order_id,
                'redirect' => 'order-success.php?order=' . $order_id
            ];
        } else {
            // Online payment via Razorpay: verify signature
            if (empty($data['razorpay_payment_id']) || empty($data['razorpay_signature'])) {
                $conn->rollback();
                return ['status' => 'error', 'message' => 'Missing Razorpay payment data'];
            }

            // Ensure server-stored razorpay_order_id exists for this order
            $stored_rzp_order_id = $order['razorpay_order_id'] ?? null;
            if (!$stored_rzp_order_id) {
                $conn->rollback();
                return ['status' => 'error', 'message' => 'No Razorpay order id stored for this order'];
            }

            // Verify signature using SDK helper
            $api = new \Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

            $attributes = [
                'razorpay_order_id' => $stored_rzp_order_id,
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature']
            ];

            try {
                $api->utility->verifyPaymentSignature($attributes);

                // Signature verified -> mark order paid
                $update_order = "UPDATE orders SET status = 'paid', payment_status = 'paid', updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($update_order);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();

                $payment_query = "INSERT INTO payments (order_id, payment_method, amount, status, transaction_id, created_at) 
                                  VALUES (?, 'razorpay', ?, 'success', ?, NOW())";
                $stmt = $conn->prepare($payment_query);
                $stmt->bind_param("ids", $order_id, $order['total_amount'], $data['razorpay_payment_id']);
                $stmt->execute();

                // Clear cart
                $clear_cart = "DELETE FROM cart WHERE user_id = ?";
                $stmt = $conn->prepare($clear_cart);
                $stmt->bind_param("i", $order['user_id']);
                $stmt->execute();

                $conn->commit();

                return [
                    'status' => 'success',
                    'message' => 'Payment successful! Your order has been confirmed.',
                    'order_id' => $order_id,
                    'redirect' => 'order-success.php?order=' . $order_id
                ];
            } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
                $conn->rollback();
                return ['status' => 'error', 'message' => 'Signature verification failed: ' . $e->getMessage()];
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        return ['status' => 'error', 'message' => 'Payment processing failed: ' . $e->getMessage()];
    }
}

/*
function processPayment($data, $conn)
{
    try {
        $order_id = intval($data['order_id']);
        $payment_method = $data['payment_method'] ?? 'cod'; // Default to Cash on Delivery

        // Verify order exists
        $order_check = "SELECT * FROM orders WHERE id = ? AND payment_status = 'pending'";
        $stmt = $conn->prepare($order_check);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) {
            return ['status' => 'error', 'message' => 'Invalid order or order already processed'];
        }

        $conn->begin_transaction();

        if ($payment_method === 'cod') {
            // Cash on Delivery
            $update_order = "UPDATE orders SET status = 'confirmed', payment_status = 'pending', updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($update_order);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            // Create payment record
            $payment_query = "INSERT INTO payments (order_id, payment_method, amount, status, created_at) 
                              VALUES (?, 'cod', ?, 'pending', NOW())";
            $stmt = $conn->prepare($payment_query);
            $stmt->bind_param("id", $order_id, $order['total_amount']);
            $stmt->execute();

            $message = 'Order confirmed! You can pay cash on delivery.';
        } else {
            // Online payment (simplified - you can integrate actual payment gateway later)
            $transaction_id = $data['transaction_id'] ?? 'TXN_' . $order_id . '_' . time();

            $update_order = "UPDATE orders SET status = 'paid', payment_status = 'paid', updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($update_order);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            // Create payment record
            $payment_query = "INSERT INTO payments (order_id, payment_method, amount, status, transaction_id, created_at) 
                              VALUES (?, 'online', ?, 'success', ?, NOW())";
            $stmt = $conn->prepare($payment_query);
            $stmt->bind_param("ids", $order_id, $order['total_amount'], $transaction_id);
            $stmt->execute();

            $message = 'Payment successful! Your order has been confirmed.';
        }

        // Clear user's cart
        $clear_cart = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($clear_cart);
        $stmt->bind_param("i", $order['user_id']);
        $stmt->execute();

        $conn->commit();

        return [
            'status' => 'success',
            'message' => $message,
            'order_id' => $order_id,
            'redirect' => 'order-success.php?order=' . $order_id
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return ['status' => 'error', 'message' => 'Payment processing failed: ' . $e->getMessage()];
    }
}
    */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CycleCity | Secure Checkout - Your Hub for Quality Bicycles</title>
    <link rel="shortcut icon" href="./assets/images/favicon.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script defer src="assets/js/main.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .checkout-progress {
            max-width: 600px;
            margin: 0 auto 2rem;
            padding: 0 1rem;
        }

        .progress-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            position: relative;
            margin-bottom: 1rem;
        }

        .progress-fill {
            height: 100%;
            background: #28a745;
            border-radius: 2px;
            transition: width 0.3s ease;
            width: 0%;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: #28a745;
            color: white;
        }

        .step.completed .step-circle {
            background: #28a745;
            color: white;
        }

        .step span {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .step.active span {
            color: #28a745;
            font-weight: 600;
        }

        .form-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-icon {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: #28a745;
        }

        .addresses-grid {
            display: grid;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .address-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .address-card:hover {
            border-color: #28a745;
            background: #f8fff9;
        }

        .address-card.selected {
            border-color: #28a745;
            background: #f8fff9;
        }

        .address-content {
            flex: 1;
        }

        .address-radio {
            margin-left: 1rem;
        }

        .input-group-enhanced {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control-enhanced {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            background: white;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control-enhanced:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        .floating-label {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: #6c757d;
            pointer-events: none;
            transition: all 0.3s ease;
            background: white;
            padding: 0 0.5rem;
        }

        .form-control-enhanced:focus+.floating-label,
        .form-control-enhanced:not(:placeholder-shown)+.floating-label {
            top: -0.5rem;
            font-size: 0.875rem;
            color: #28a745;
        }

        .payment-methods {
            display: grid;
            gap: 1rem;
        }

        .payment-option {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .payment-option:hover {
            border-color: #28a745;
            background: #f8fff9;
        }

        .payment-option.active {
            border-color: #28a745;
            background: #f8fff9;
        }

        .payment-content {
            display: flex;
            align-items: center;
            flex: 1;
            margin-left: 1rem;
        }

        .payment-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.25rem;
            color: #28a745;
        }

        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
            position: sticky;
            top: 2rem;
        }

        .product-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .product-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f8f9fa;
        }

        .product-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .quantity-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .coupon-section {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .order-totals {
            padding-top: 1rem;
            border-top: 2px solid #f8f9fa;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background: #6c757d;
            border-color: #6c757d;
        }

        .btn-success {
            background: #28a745;
            border-color: #28a745;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        @media (max-width: 768px) {
            .form-section {
                padding: 1.5rem;
            }

            .order-summary {
                position: static;
                margin-top: 2rem;
            }

            .addresses-grid {
                grid-template-columns: 1fr;
            }

            .address-card {
                flex-direction: column;
                text-align: left;
            }

            .address-radio {
                margin: 1rem 0 0 0;
                align-self: flex-start;
            }
        }
    </style>
</head>

<body>
    <header class="header-section position-fixed top-0 start-50 translate-middle-x border-bottom border-n100-1 bg-n0 d-none" data-lenis-prevent>
        <div class="container-fluid">
            <div class="row g-0 justify-content-center">
                <div class="col-3xl-11 px-3xl-0 px-xxl-8 px-sm-6 px-0">
                    <div class="d-flex align-items-center justify-content-between gap-4xl-10 gap-3xl-8 gap-xxl-6 gap-4 px-lg-0 px-sm-4 py-lg-5 py-3">
                        <div class="logo">
                            <a href="index.html">
                                <img class="w-100 d-block d-sm-none" src="./assets/images/favicon.png" alt="logo">
                                <img class="w-100 d-none d-sm-block" src="./assets/images/logo.png" alt="logo">
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <span class="text-sm text-muted d-none d-md-block">Secure Checkout</span>
                            <div class="d-flex align-items-center gap-2">
                                <i class="ph ph-lock text-success"></i>
                                <span class="text-sm text-success d-none d-sm-block">SSL Secured</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-12 mt-10">
        <div class="container-fluid px-xl-5">
            <!-- Progress Indicator -->
            <div class="checkout-progress animate-in">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-circle">1</div>
                        <span>Delivery Details</span>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-circle">2</div>
                        <span>Payment</span>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-circle">3</div>
                        <span>Confirmation</span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Left Column - Forms -->
                <div class="col-lg-7">
                    <!-- Address Section -->
                    <div class="form-section animate-in" id="addressSection">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="ph ph-map-pin"></i>
                            </div>
                            <div>
                                <h3 class="mb-1">Delivery Address</h3>
                                <p class="text-muted mb-0">Where should we deliver your order?</p>
                            </div>
                        </div>

                        <?php if (!empty($saved_addresses)): ?>
                            <div class="saved-addresses-section mb-4">
                                <h5 class="mb-3">Choose a delivery address</h5>
                                <div class="addresses-grid">
                                    <?php foreach ($saved_addresses as $address): ?>
                                        <div class="address-card <?= $address['is_default'] ? 'selected' : '' ?>" data-address-id="<?= $address['id'] ?>">
                                            <div class="address-content">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0"><?= htmlspecialchars($address['full_name']) ?></h6>
                                                    <?php if ($address['is_default']): ?>
                                                        <span class="badge bg-primary">Default</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-muted mb-1"><?= htmlspecialchars($address['address_line1']) ?></p>
                                                <?php if ($address['address_line2']): ?>
                                                    <p class="text-muted mb-1"><?= htmlspecialchars($address['address_line2']) ?></p>
                                                <?php endif; ?>
                                                <p class="text-muted mb-1">
                                                    <?= htmlspecialchars($address['city']) ?>,
                                                    <?= htmlspecialchars($address['state']) ?> -
                                                    <?= htmlspecialchars($address['postal_code']) ?>
                                                </p>
                                                <p class="text-muted mb-0">Phone: <?= htmlspecialchars($address['phone']) ?></p>
                                            </div>
                                            <div class="address-radio">
                                                <input type="radio" name="selected_address" value="<?= $address['id'] ?>"
                                                    <?= $address['is_default'] ? 'checked' : '' ?> class="form-check-input">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-primary" id="addNewAddressBtn">
                                        <i class="ph ph-plus me-2"></i>Add New Address
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- New Address Form -->
                        <div class="new-address-form" id="newAddressForm" <?= !empty($saved_addresses) ? 'style="display: none;"' : '' ?>>
                            <h5 class="mb-3"><?= !empty($saved_addresses) ? 'Add New Address' : 'Enter Delivery Address' ?></h5>
                            <form id="addressForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="input-group-enhanced">
                                            <input type="text" id="fullName" class="form-control-enhanced" placeholder=" " required>
                                            <label class="floating-label">Full Name *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group-enhanced">
                                            <div class="d-flex">
                                                <span class="form-control-enhanced" style="max-width: 80px; text-align: center; background: #f8f9fa;">+91</span>
                                                <input type="tel" id="phone" class="form-control-enhanced" placeholder="Phone Number *" maxlength="10" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="input-group-enhanced">
                                    <input type="text" id="addressLine1" class="form-control-enhanced" placeholder=" " required>
                                    <label class="floating-label">Address Line 1 *</label>
                                </div>

                                <div class="input-group-enhanced">
                                    <input type="text" id="addressLine2" class="form-control-enhanced" placeholder=" ">
                                    <label class="floating-label">Address Line 2 (Optional)</label>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="input-group-enhanced">
                                            <input type="text" id="city" class="form-control-enhanced" placeholder=" " required>
                                            <label class="floating-label">City *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group-enhanced">
                                            <input type="text" id="state" class="form-control-enhanced" placeholder=" " required>
                                            <label class="floating-label">State *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group-enhanced">
                                            <input type="text" id="postalCode" class="form-control-enhanced" placeholder=" " maxlength="6" required>
                                            <label class="floating-label">PIN Code *</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" id="saveAddress" checked>
                                    <label class="form-check-label" for="saveAddress">
                                        Save this address for future orders
                                    </label>
                                </div>

                                <?php if (!empty($saved_addresses)): ?>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="button" class="btn btn-success" id="saveAddressBtn">
                                            <i class="ph ph-check me-2"></i>Save Address
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="cancelAddressBtn">
                                            Cancel
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="form-section animate-in" id="paymentSection" style="display: none;">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="ph ph-credit-card"></i>
                            </div>
                            <div>
                                <h3 class="mb-1">Payment Method</h3>
                                <p class="text-muted mb-0">Choose your preferred payment method</p>
                            </div>
                        </div>

                        <div class="payment-methods">
                            <!-- <div class="payment-option active" data-method="cod">
                                <div class="payment-radio">
                                    <input type="radio" name="payment_method" value="cod" checked class="form-check-input">
                                </div>
                                 <div class="payment-content">
                                    <div class="payment-icon">
                                        <i class="ph ph-money"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Cash on Delivery</h6>
                                        <p class="text-muted mb-0">Pay when your order arrives</p>
                                    </div>
                                </div> 
                            </div> -->

                            <div class="payment-option" data-method="online">
                                <div class="payment-radio">
                                    <input type="radio" name="payment_method" value="online" class="form-check-input">
                                </div>
                                <div class="payment-content">
                                    <div class="payment-icon">
                                        <i class="ph ph-credit-card"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Online Payment</h6>
                                        <p class="text-muted mb-0">Pay securely with card/UPI/net banking</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" id="prevBtn" class="btn btn-outline-secondary" style="display: none;">
                            <i class="ph ph-arrow-left me-2"></i>Previous
                        </button>
                        <button type="button" id="nextBtn" class="btn btn-secondary ms-auto">
                            Next <span class="icon"><i class="ph ph-arrow-up-right"></i></span>
                        </button>
                        <button type="button" id="placeOrderBtn" class="btn btn-success ms-auto" style="display: none;">
                            <i class="ph ph-check me-2"></i>Place Order
                        </button>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="col-lg-5">
                    <div class="order-summary">
                        <h4 class="mb-4">Order Summary</h4>

                        <!-- Product list -->
                        <div class="product-list mb-4">
                            <?php while ($item = mysqli_fetch_assoc($carts)): ?>
                                <?php
                                $images = json_decode($item['product_images'], true);
                                $firstImage = $images[0] ?? 'default.jpg';
                                $item_price = $item['variant_id'] ?
                                    ($item['variant_discount'] > 0 ? $item['variant_discount'] : $item['variant_price']) : ($item['product_discount'] > 0 ? $item['product_discount'] : $item['product_price']);
                                ?>
                                <div class="product-item">
                                    <div class="product-image position-relative">
                                        <img src="./assets/uploads/product/<?php echo $firstImage ?>" alt="<?php echo htmlspecialchars($item['product_name']) ?>" class="w-100 h-80">
                                        <div class="quantity-badge"><?= htmlspecialchars($item['qty']) ?></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                        <?php if ($item['variant_name']): ?>
                                            <p class="text-muted mb-1">Variant: <?= htmlspecialchars($item['variant_name']) ?></p>
                                        <?php endif; ?>
                                        <p class="text-primary mb-0"><b>₹<?= number_format($item_price) ?></b></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Coupon section -->
                        <div class="coupon-section mb-4">
                            <div class="d-flex">
                                <input type="text" class="form-control me-2" placeholder="Enter coupon code" id="couponCode">
                                <button class="btn btn-outline-dark" id="applyCoupon">Apply</button>
                            </div>
                            <div id="couponMessage" class="mt-2"></div>
                        </div>

                        <!-- Order totals -->
                        <div class="order-totals">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><b>₹<span id="subtotalAmount"><?= number_format($subtotal) ?></span></b></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span class="text-success">FREE</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="discountRow" style="display: none;">
                                <span>Discount:</span>
                                <span class="text-success" id="discountAmount">-₹0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (GST 18%):</span>
                                <span id="taxAmount">₹<?= number_format($tax_amount) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong id="finalTotal" data-subtotal="<?= $subtotal ?>">₹<?= number_format($total_amount) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        $(document).ready(function() {
            let currentStep = 1;
            let orderId = null;
            let appliedCoupon = null;

            // Update progress
            function updateProgress() {
                $('.step').removeClass('active completed');
                $('.step[data-step="' + currentStep + '"]').addClass('active');
                for (let i = 1; i < currentStep; i++) {
                    $('.step[data-step="' + i + '"]').addClass('completed');
                }

                const progressPercent = ((currentStep - 1) / 2) * 100;
                $('#progressFill').css('width', progressPercent + '%');
            }

            // Navigation
            function showStep(step) {
                $('.form-section').hide();

                if (step === 1) {
                    $('#addressSection').show();
                    $('#prevBtn').hide();
                    $('#nextBtn').show().text('Next');
                    $('#placeOrderBtn').hide();
                } else if (step === 2) {
                    $('#paymentSection').show();
                    $('#prevBtn').show();
                    $('#nextBtn').hide();
                    $('#placeOrderBtn').show();
                }

                currentStep = step;
                updateProgress();
            }

            // Address card selection
            $(document).on('click', '.address-card', function() {
                $('.address-card').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
            });

            // Add new address button
            $('#addNewAddressBtn').click(function() {
                $('#newAddressForm').slideDown();
                $(this).hide();
            });

            // Cancel add address
            $('#cancelAddressBtn').click(function() {
                $('#newAddressForm').slideUp();
                $('#addNewAddressBtn').show();
                $('#addressForm')[0].reset();
            });

            // Save new address
            $('#saveAddressBtn').click(function() {
                const formData = {
                    action: 'save_address',
                    full_name: $('#fullName').val(),
                    phone: $('#phone').val(),
                    address_line1: $('#addressLine1').val(),
                    address_line2: $('#addressLine2').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    postal_code: $('#postalCode').val(),
                    country: 'India',
                    is_default: $('#saveAddress').is(':checked') ? 1 : 0
                };

                $.post(window.location.href, formData)
                    .done(function(response) {
                        if (response.status === 'success') {
                            alert('Address saved successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    })
                    .fail(function() {
                        alert('Error saving address. Please try again.');
                    });
            });

            // Payment method selection
            $(document).on('click', '.payment-option', function() {
                $('.payment-option').removeClass('active');
                $(this).addClass('active');
                $(this).find('input[type="radio"]').prop('checked', true);
            });

            // Apply coupon
            $('#applyCoupon').click(function() {
                const couponCode = $('#couponCode').val().trim();
                if (!couponCode) {
                    alert('Please enter a coupon code');
                    return;
                }

                const button = $(this);
                button.prop('disabled', true).text('Applying...');

                $.post(window.location.href, {
                        action: 'apply_coupon',
                        coupon_code: couponCode
                    })
                    .done(function(response) {
                        if (response.status === 'success') {
                            appliedCoupon = {
                                code: couponCode,
                                discount: response.discount
                            };

                            updateOrderTotals();
                            $('#couponMessage').html('<div class="text-success small">Coupon applied! You saved ₹' + response.discount + '</div>');
                            $('#applyCoupon').text('Applied').removeClass('btn-outline-dark').addClass('btn-success');
                        } else {
                            $('#couponMessage').html('<div class="text-danger small">' + response.message + '</div>');
                            button.prop('disabled', false).text('Apply');
                        }
                    })
                    .fail(function() {
                        alert('Error applying coupon. Please try again.');
                        button.prop('disabled', false).text('Apply');
                    });
            });

            // Update order totals
            function updateOrderTotals() {
                const subtotal = parseFloat($('#finalTotal').data('subtotal'));
                const discount = appliedCoupon ? appliedCoupon.discount : 0;
                const taxableAmount = subtotal - discount;
                const tax = taxableAmount * 0.18;
                const total = subtotal - discount + tax;

                if (discount > 0) {
                    $('#discountRow').show();
                    $('#discountAmount').text('-₹' + discount.toFixed(0));
                } else {
                    $('#discountRow').hide();
                }

                $('#taxAmount').text('₹' + tax.toFixed(0));
                $('#finalTotal').text('₹' + total.toFixed(0));
            }

            // Next button
            $('#nextBtn').click(function() {
                if (currentStep === 1) {
                    // Validate address selection
                    const selectedAddress = $('input[name="selected_address"]:checked').val();
                    const hasNewAddress = $('#fullName').val() && $('#phone').val() && $('#addressLine1').val() &&
                        $('#city').val() && $('#state').val() && $('#postalCode').val();

                    if (!selectedAddress && !hasNewAddress) {
                        alert('Please select an address or fill in the address form');
                        return;
                    }

                    showStep(2);
                }
            });

            // Previous button
            $('#prevBtn').click(function() {
                if (currentStep === 2) {
                    showStep(1);
                }
            });
            // inside your $(document).ready(...)
            $('#placeOrderBtn').click(function() {
                const button = $(this);
                button.prop('disabled', true).html('<i class="ph ph-spinner me-2"></i>Processing...');

                const paymentMethod = $('input[name="payment_method"]:checked').val() || 'cod';

                const formData = {
                    action: 'create_order',
                    payment_method: paymentMethod,
                    selected_address: $('input[name="selected_address"]:checked').val(),
                    full_name: $('#fullName').val(),
                    phone: $('#phone').val(),
                    address_line1: $('#addressLine1').val(),
                    address_line2: $('#addressLine2').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    postal_code: $('#postalCode').val(),
                    country: 'India'
                };

                $.post(window.location.href, formData)
                    .done(function(response) {
                        if (response.status === 'success') {
                            const orderId = response.order_id;
                            // If online, open Razorpay Checkout
                            if (paymentMethod === 'online' && response.razorpay_order_id) {
                                openRazorpayCheckout(response, orderId);
                            } else {
                                // COD — call processPayment to confirm order (as before)
                                processPaymentAjax(orderId, paymentMethod, button);
                            }
                        } else {
                            alert('Error: ' + response.message);
                            button.prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                        }
                    })
                    .fail(function() {
                        alert('Error creating order. Please try again.');
                        button.prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                    });
            });

            function openRazorpayCheckout(response, orderId) {
                const options = {
                    key: response.razorpay_key, // public key
                    amount: Math.round(response.amount * 100), // rupees -> paise
                    currency: 'INR',
                    name: 'CycleCity',
                    description: 'Order #' + orderId,
                    order_id: response.razorpay_order_id,
                    prefill: {
                        name: response.shipping?.full_name || $('#fullName').val() || '',
                        contact: response.shipping?.phone || $('#phone').val() || ''
                        // optionally: email
                    },
                    handler: function(res) {
                        // res.razorpay_payment_id, res.razorpay_signature, res.razorpay_order_id
                        // Post to server to verify & finalize
                        $.post(window.location.href, {
                            action: 'process_payment',
                            order_id: orderId,
                            payment_method: 'online',
                            razorpay_payment_id: res.razorpay_payment_id,
                            razorpay_order_id: res.razorpay_order_id,
                            razorpay_signature: res.razorpay_signature
                        }).done(function(result) {
                            if (result.status === 'success') {
                                alert(result.message);
                                window.location.href = result.redirect || 'orders.php';
                            } else {
                                alert('Payment verification failed: ' + result.message);
                                $('#placeOrderBtn').prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                            }
                        }).fail(function() {
                            alert('Server error verifying payment.');
                            $('#placeOrderBtn').prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                        });
                    },
                    modal: {
                        ondismiss: function() {
                            // user closed the checkout; re-enable button
                            $('#placeOrderBtn').prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                        }
                    }
                };

                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function(response) {
                    alert('Payment failed: ' + (response.error && response.error.description ? response.error.description : 'Unknown'));
                    $('#placeOrderBtn').prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                });
                rzp.open();
            }

            function processPaymentAjax(orderId, paymentMethod, button) {
                $.post(window.location.href, {
                        action: 'process_payment',
                        order_id: orderId,
                        payment_method: paymentMethod
                    })
                    .done(function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            window.location.href = response.redirect || 'orders.php';
                        } else {
                            alert('Error: ' + response.message);
                            button.prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                        }
                    })
                    .fail(function() {
                        alert('Error processing payment. Please try again.');
                        button.prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                    });
            }

            // Place Order button
            /*
            $('#placeOrderBtn').click(function() {
                const button = $(this);
                button.prop('disabled', true).html('<i class="ph ph-spinner me-2"></i>Processing...');

                // Get form data
                const formData = {
                    action: 'create_order',
                    selected_address: $('input[name="selected_address"]:checked').val(),
                    full_name: $('#fullName').val(),
                    phone: $('#phone').val(),
                    address_line1: $('#addressLine1').val(),
                    address_line2: $('#addressLine2').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    postal_code: $('#postalCode').val(),
                    country: 'India'
                };

                if (appliedCoupon) {
                    formData.coupon_code = appliedCoupon.code;
                }

                // Create order first
                $.post(window.location.href, formData)
                    .done(function(response) {
                        if (response.status === 'success') {
                            orderId = response.order_id;
                            processPayment();
                        } else {
                            alert('Error: ' + response.message);
                            button.prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                        }
                    })
                    .fail(function() {
                        alert('Error creating order. Please try again.');
                        button.prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                    });
            });
*/
            // Process payment
            function processPayment() {
                const paymentMethod = $('input[name="payment_method"]:checked').val();

                const paymentData = {
                    action: 'process_payment',
                    order_id: orderId,
                    payment_method: paymentMethod
                };

                if (paymentMethod === 'online') {
                    // For online payment, you can add transaction_id here
                    // This is a simplified version - integrate with actual payment gateway
                    paymentData.transaction_id = 'TXN_' + orderId + '_' + Date.now();
                }

                $.post(window.location.href, paymentData)
                    .done(function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.href = 'orders.php';
                            }
                        } else {
                            alert('Error: ' + response.message);
                            $('#placeOrderBtn').prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                        }
                    })
                    .fail(function() {
                        alert('Error processing payment. Please try again.');
                        $('#placeOrderBtn').prop('disabled', false).html('<i class="ph ph-check me-2"></i>Place Order');
                    });
            }

            // Initialize
            showStep(1);
        });
    </script>
</body>

</html>