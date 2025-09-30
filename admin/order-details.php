<?php
include '../include/connection.php';

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = (int)$_GET['id'];

// Get order details
$order_sql = "SELECT o.*, c.full_name, c.email, c.phone, c.profile_image 
              FROM orders o 
              LEFT JOIN customers c ON o.user_id = c.id 
              WHERE o.id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "Order not found!";
    exit();
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

// Get order items with product details
$items_sql = "SELECT oi.*, p.name as product_name, p.image as product_images, 
                     pv.variant_name, pv.image as variant_image
              FROM order_items oi 
              LEFT JOIN products p ON oi.product_id = p.id 
              LEFT JOIN product_variants pv ON oi.variant_id = pv.id 
              WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();

// Get payment details
$payment_sql = "SELECT * FROM payments WHERE order_id = ?";
$payment_stmt = $conn->prepare($payment_sql);
$payment_stmt->bind_param("i", $order_id);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();
$payment = $payment_result->fetch_assoc();
$payment_stmt->close();

// Check if coupon was used
$coupon_sql = "SELECT cu.*, c.code, c.discount_type, c.amount 
               FROM coupon_usage cu 
               LEFT JOIN coupons c ON cu.coupon_id = c.id 
               WHERE cu.order_id = ?";
$coupon_stmt = $conn->prepare($coupon_sql);
$coupon_stmt->bind_param("i", $order_id);
$coupon_stmt->execute();
$coupon_result = $coupon_stmt->get_result();
$coupon = $coupon_result->fetch_assoc();
$coupon_stmt->close();

// Parse shipping address
$shipping_address = json_decode($order['shipping_address'], true);

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_sql = "UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $order_id);

    if ($update_stmt->execute()) {
        // Log the activity
        $log_sql = "INSERT INTO activity_logs (type, action, message, reference_id, user_type, user_id) 
                    VALUES ('order', 'update', 'Order status updated to {$new_status}', ?, 'admin', 1)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("i", $order_id);
        $log_stmt->execute();
        $log_stmt->close();

        // Refresh the page to show updated status
        header("Location: order-details.php?id=" . $order_id);
        exit();
    }
    $update_stmt->close();
}

// Handle payment status update
if (isset($_POST['update_payment_status'])) {
    $new_payment_status = $_POST['payment_status'];
    $update_sql = "UPDATE orders SET payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_payment_status, $order_id);

    if ($update_stmt->execute()) {
        // Log the activity
        $log_sql = "INSERT INTO activity_logs (type, action, message, reference_id, user_type, user_id) 
                    VALUES ('order', 'update', 'Payment status updated to {$new_payment_status}', ?, 'admin', 1)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("i", $order_id);
        $log_stmt->execute();
        $log_stmt->close();

        // Refresh the page to show updated status
        header("Location: order-details.php?id=" . $order_id);
        exit();
    }
    $update_stmt->close();
}

// Function to get status badge class
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-secondary';
        case 'paid':
            return 'bg-info';
        case 'shipped':
            return 'bg-warning text-dark';
        case 'delivered':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Function to get payment status badge class
function getPaymentStatusBadgeClass($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-warning text-dark';
        case 'paid':
            return 'bg-success';
        case 'failed':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Function to get product image
function getProductImage($imageJson, $variantImage = null)
{
    if ($variantImage) {
        $variantImages = json_decode($variantImage, true);
        if (is_array($variantImages) && !empty($variantImages)) {
            return $variantImages[0];
        }
    }

    $images = json_decode($imageJson, true);
    if (is_array($images) && !empty($images)) {
        return $images[0];
    }
    return 'default-product.png';
}

include './layout/sidebar.php';
?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <h5 class="mb-0 text-dark">Order Details</h5>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Welcome, Admin</span>
            <i class="bi bi-person-circle fs-4 text-secondary"></i>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid p-4">

        <!-- Back Button -->
        <div class="mb-4">
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Orders
            </a>
        </div>

        <!-- Order Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div>
                        <h2 class="text-dark mb-1">Order #<?php echo $order['id']; ?></h2>
                        <p class="text-muted mb-0">
                            Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                    <a href="invoice.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="bi bi-receipt me-2"></i>View Invoice
                    </a>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <span class="badge <?php echo getStatusBadgeClass($order['status']); ?> fs-6">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <h6 class="card-title mb-0">Order Status</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <span class="badge <?php echo getPaymentStatusBadgeClass($order['payment_status']); ?> fs-6">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                        <h6 class="card-title mb-0">Payment Status</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-credit-card fs-4 text-primary"></i>
                        </div>
                        <h6 class="card-title mb-0"><?php echo ucfirst($order['payment_method'] ?? 'N/A'); ?></h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-currency-rupee fs-4 text-success"></i>
                        </div>
                        <h6 class="card-title mb-0">₹<?php echo number_format($order['total_amount'], 2); ?></h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Order Items -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <?php
                                                        $product_image = getProductImage(
                                                            $item['product_images'] ?? '[]',
                                                            $item['variant_image'] ?? null
                                                        );
                                                        ?>
                                                        <?php if ($product_image !== 'default-product.png'): ?>
                                                            <img src="../assets/uploads/product/<?php echo htmlspecialchars($product_image); ?>"
                                                                alt="Product" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                                        <?php else: ?>
                                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                                style="width: 60px; height: 60px; border-radius: 5px;">
                                                                <i class="bi bi-bicycle text-secondary"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                        <?php if (!empty($item['variant_name'])): ?>
                                                            <small class="text-muted">Variant: <?php echo htmlspecialchars($item['variant_name']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['qty']; ?></td>
                                            <td><strong>₹<?php echo number_format($item['price'] * $item['qty'], 2); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Notes/Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Placed</h6>
                                    <p class="text-muted mb-0"><?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                                </div>
                            </div>
                            <?php if ($order['status'] === 'paid' || $order['status'] === 'shipped' || $order['status'] === 'delivered'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Payment Confirmed</h6>
                                        <p class="text-muted mb-0">
                                            <?php
                                            if ($payment && $payment['status'] === 'success') {
                                                echo date('F j, Y \a\t g:i A', strtotime($payment['created_at']));
                                            } else {
                                                echo 'Pending';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($order['status'] === 'shipped' || $order['status'] === 'delivered'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Order Shipped</h6>
                                        <p class="text-muted mb-0">
                                            <?php echo date('F j, Y \a\t g:i A', strtotime($order['updated_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($order['status'] === 'delivered'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Order Delivered</h6>
                                        <p class="text-muted mb-0">
                                            <?php echo date('F j, Y \a\t g:i A', strtotime($order['updated_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary & Actions -->
            <div class="col-lg-4">
                <!-- Customer Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <?php if (!empty($order['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($order['profile_image']); ?>"
                                    alt="Customer" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px;">
                                    <i class="bi bi-person text-secondary"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($order['full_name']); ?></h6>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($order['email']); ?></p>
                                <?php if (!empty($order['phone'])): ?>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($order['phone']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($shipping_address && is_array($shipping_address)): ?>
                            <address class="mb-0">
                                <strong><?php echo htmlspecialchars($shipping_address['full_name'] ?? ''); ?></strong><br>
                                <?php echo htmlspecialchars($shipping_address['address_line1'] ?? ''); ?><br>
                                <?php if (!empty($shipping_address['address_line2'])): ?>
                                    <?php echo htmlspecialchars($shipping_address['address_line2']); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($shipping_address['city'] ?? ''); ?>,
                                <?php echo htmlspecialchars($shipping_address['state'] ?? ''); ?>
                                <?php echo htmlspecialchars($shipping_address['postal_code'] ?? ''); ?><br>
                                <?php echo htmlspecialchars($shipping_address['country'] ?? ''); ?><br>
                                <?php if (!empty($shipping_address['phone'])): ?>
                                    <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($shipping_address['phone']); ?>
                                <?php endif; ?>
                            </address>
                        <?php else: ?>
                            <p class="text-muted mb-0">No shipping address provided</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <?php if ($coupon): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount (<?php echo htmlspecialchars($coupon['code']); ?>):</span>
                                <span class="text-danger">
                                    -₹<?php
                                        if ($coupon['discount_type'] === 'percentage') {
                                            echo number_format(($order['total_amount'] * $coupon['amount']) / 100, 2);
                                        } else {
                                            echo number_format($coupon['amount'], 2);
                                        }
                                        ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total:</strong>
                            <strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Order Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Actions</h5>
                    </div>
                    <div class="card-body">
                        <!-- Update Order Status -->
                        <form method="POST" class="mb-3">
                            <div class="mb-2">
                                <label class="form-label">Update Order Status</label>
                                <select class="form-select" name="status">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                        </form>

                        <!-- Update Payment Status -->
                        <form method="POST">
                            <div class="mb-2">
                                <label class="form-label">Update Payment Status</label>
                                <select class="form-select" name="payment_status">
                                    <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                </select>
                            </div>
                            <button type="submit" name="update_payment_status" class="btn btn-outline-primary w-100">Update Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .top-navbar {
        box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        z-index: 1020;
    }

    .main-content {
        margin-top: 20px;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .timeline-content {
        padding-bottom: 10px;
    }

    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: -25px;
        top: 17px;
        bottom: -10px;
        width: 2px;
        background-color: #e9ecef;
    }

    @media print {

        .top-navbar,
        .btn,
        .card-header .btn {
            display: none !important;
        }

        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }

        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }
</style>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>