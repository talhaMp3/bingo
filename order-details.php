<?php
session_start();
include_once './layout/header.php';

// Protect this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? "Guest";
$userEmail = $_SESSION['user_email'] ?? "";
$userId = $_SESSION['user_id'];


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get order ID from URL
$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    header("Location: orders.php");
    exit;
}

// Fetch order details with user verification
$orderQuery = "
    SELECT o.*, c.full_name, c.email, c.phone as customer_phone,
           p.transaction_id, p.payment_method, p.status as payment_status,
           ca.full_name as billing_name, ca.phone as billing_phone,
           ca.address_line1, ca.address_line2, ca.city, ca.state, 
           ca.country, ca.postal_code
    FROM orders o
    LEFT JOIN customers c ON o.user_id = c.id
    LEFT JOIN payments p ON o.id = p.order_id
    LEFT JOIN customer_addresses ca ON o.user_id = ca.user_id AND ca.is_default = 1
    WHERE o.id = ? AND o.user_id = ?
";

$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$orderResult = $stmt->get_result();

if ($orderResult->num_rows === 0) {
    header("Location: orders.php");
    exit;
}

$order = $orderResult->fetch_assoc();

// Fetch order items
$itemsQuery = "
    SELECT oi.*, p.name, p.slug, p.image, p.sku
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
";

$stmt = $conn->prepare($itemsQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$itemsResult = $stmt->get_result();
$orderItems = [];
while ($row = $itemsResult->fetch_assoc()) {
    $orderItems[] = $row;
}
/*
// Calculate totals
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += ($item['price'] * $item['qty']);
}

$shipping = 50.00; // You can make this dynamic based on your business logic
$tax = $subtotal * 0.10; // 10% tax - adjust as needed
$total = $subtotal + $shipping + $tax;*/

// Calculate totals
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += ($item['price'] * $item['qty']);
}

$shipping = 00; // flat rate shipping
$tax = $subtotal * 0.18; // 18% tax
$promoDiscount = 0.00;

// Fetch promo discount (if any)
$couponQuery = "
    SELECT c.code, c.discount_type, c.amount
    FROM coupon_usage cu
    JOIN coupons c ON cu.coupon_id = c.id
    WHERE cu.order_id = ?
";
$stmt = $conn->prepare($couponQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$couponResult = $stmt->get_result();

if ($couponResult->num_rows > 0) {
    $coupon = $couponResult->fetch_assoc();
    if ($coupon['discount_type'] === 'percentage') {
        $promoDiscount = ($subtotal * ($coupon['amount'] / 100));
    } else {
        $promoDiscount = $coupon['amount'];
    }
}

// Final total after discount
$total = $subtotal + $shipping + $tax - $promoDiscount;



// Format currency function
function formatCurrency($amount)
{
    return '₹' . number_format($amount, 2);
}

// Status mapping for display
function getStatusBadge($status)
{
    $badges = [
        'pending' => 'bg-warning text-dark',
        'paid' => 'bg-info text-white',
        'shipped' => 'bg-primary text-white',
        'delivered' => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white'
    ];
    return $badges[$status] ?? 'bg-secondary text-white';
}

// Create tracking timeline based on order status
function getTrackingSteps($status, $createdAt, $updatedAt)
{
    $steps = [
        [
            'status' => 'Order Placed',
            'date' => date('F j, Y g:i A', strtotime($createdAt)),
            'completed' => true
        ]
    ];

    if (in_array($status, ['paid', 'shipped', 'delivered'])) {
        $steps[] = [
            'status' => 'Payment Confirmed',
            'date' => date('F j, Y g:i A', strtotime($createdAt)),
            'completed' => true
        ];
    }

    if (in_array($status, ['paid', 'shipped', 'delivered'])) {
        $steps[] = [
            'status' => 'Processing',
            'date' => date('F j, Y g:i A', strtotime($updatedAt)),
            'completed' => true
        ];
    }

    if (in_array($status, ['shipped', 'delivered'])) {
        $steps[] = [
            'status' => 'Shipped',
            'date' => date('F j, Y g:i A', strtotime($updatedAt)),
            'completed' => true
        ];
    } else {
        $steps[] = [
            'status' => 'Shipped',
            'date' => 'Estimated: ' . date('F j, Y', strtotime('+2 days')),
            'completed' => false
        ];
    }

    if ($status === 'delivered') {
        $steps[] = [
            'status' => 'Delivered',
            'date' => date('F j, Y g:i A', strtotime($updatedAt)),
            'completed' => true
        ];
    } else {
        $steps[] = [
            'status' => 'Delivered',
            'date' => 'Estimated: ' . date('F j, Y', strtotime('+4 days')),
            'completed' => false
        ];
    }

    return $steps;
}

$trackingSteps = getTrackingSteps($order['status'], $order['created_at'], $order['updated_at']);
?>
<main class="pt-12">
    <!-- Hero Section -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid text-center">
            <span class="text-animation-word text-h1 text-n100 mb-3">Order Details</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="account.php">My Account</a></li>
                <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                <li class="breadcrumb-item active"><a href="#">#<?= htmlspecialchars($order['id']) ?></a></li>
            </ul>
        </div>
    </section>

    <!-- Order Details Section -->
    <section class="order-details-section pt-120 pb-120 px-xl-20 px-lg-10 px-sm-7">
        <div class="container-fluid">
            <div class="row g-6">
                <!-- Sidebar Navigation -->
                <div class="col-xl-3 col-lg-4">
                    <div class="dashboard-sidebar p-6 radius-16 border border-n100-1 bg-n0">
                        <h5 class="mb-4">MY ACCOUNT</h5>
                        <nav class="dashboard-nav">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <a href="account.php" class="d-flex align-items-center gap-3 p-3 radius-8 text-n700 hover-bg-n50">
                                        <i class="ph ph-squares-four"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="orders.php" class="d-flex align-items-center gap-3 p-3 radius-8 bg-primary-50 text-primary-600 fw-medium">
                                        <i class="ph ph-shopping-cart-simple"></i>
                                        Orders
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="downloads.php" class="d-flex align-items-center gap-3 p-3 radius-8 text-n700 hover-bg-n50">
                                        <i class="ph ph-download-simple"></i>
                                        Downloads
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="addresses.php" class="d-flex align-items-center gap-3 p-3 radius-8 text-n700 hover-bg-n50">
                                        <i class="ph ph-map-pin"></i>
                                        Addresses
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="account-details.php" class="d-flex align-items-center gap-3 p-3 radius-8 text-n700 hover-bg-n50">
                                        <i class="ph ph-user-circle"></i>
                                        Account details
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="wishlist.php" class="d-flex align-items-center gap-3 p-3 radius-8 text-n700 hover-bg-n50">
                                        <i class="ph ph-heart"></i>
                                        Wishlist
                                    </a>
                                </li>
                                <li>
                                    <a href="logout.php" class="d-flex align-items-center gap-3 p-3 radius-8 text-n700 hover-bg-n50">
                                        <i class="ph ph-sign-out"></i>
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-xl-9 col-lg-8">
                    <!-- Back Button -->
                    <div class="mb-4">
                        <a href="orders.php" class="btn btn-outline-secondary">
                            <i class="ph ph-arrow-left me-2"></i>Back to Orders
                        </a>
                    </div>

                    <!-- Order Header -->
                    <div class="order-header p-6 radius-16 border border-n100-1 bg-n0 mb-6">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-2">Order #<?= htmlspecialchars($order['id']) ?></h4>
                                <p class="text-n600 mb-0">
                                    Placed on <?= date('F j, Y', strtotime($order['created_at'])) ?> •
                                    <span class="badge <?= getStatusBadge($order['status']) ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </p>
                                <?php if ($order['transaction_id']): ?>
                                    <p class="text-n500 mb-0 mt-1">Transaction ID: <?= htmlspecialchars($order['transaction_id']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h5 class="text-success mb-0"><?= formatCurrency($order['total_amount']) ?></h5>
                                <p class="text-n500 mb-0">Total Amount</p>
                            </div>
                        </div>
                    </div>

                    <style>
                        .tracking-horizontal {
                            position: relative;
                            margin: 2rem 0;
                        }

                        .tracking-line {
                            height: 4px;
                            background-color: #dee2e6;
                            top: 16px;
                            /* aligns with center of dot */
                            border-radius: 2px;
                            z-index: 0;
                        }

                        .tracking-progress {
                            height: 4px;
                            background-color: #eb453b;
                            top: 16px;
                            border-radius: 2px;
                            z-index: 1;
                        }

                        .tracking-step {
                            position: relative;
                            z-index: 2;
                        }

                        .tracking-dot {
                            width: 34px;
                            height: 34px;
                            border-radius: 50%;
                            border: 2px solid #dee2e6;
                            background-color: #fff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 18px;
                            position: relative;
                        }

                        .tracking-dot.completed {
                            background-color: #eb453b;
                            border-color: #eb453b;
                            color: #fff;
                        }

                        .tracking-dot.pending {
                            background-color: #fff;
                            border-color: #dee2e6;
                            color: #adb5bd;
                        }

                        .tracking-dot i {
                            font-size: 16px;
                        }

                        .tracking-step h6 {
                            margin-top: 0.25rem;
                        }
                    </style>
                    <!-- Order Tracking -->
                    <div class="order-tracking mt-6 mb-6">
                        <div class="section-card p-6 radius-16 border border-n100-1 bg-n0">
                            <h5 class="mb-4">Order Tracking</h5>

                            <div class="tracking-horizontal d-flex justify-content-between align-items-start position-relative">

                                <!-- Background line -->
                                <div class="tracking-line position-absolute top-3 start-0 w-100"></div>

                                <!-- Filled progress -->
                                <?php
                                $completedCount = count(array_filter($trackingSteps, fn($s) => $s['completed']));
                                $percent = ($completedCount - 1) / (count($trackingSteps) - 1) * 100;
                                ?>
                                <div class="tracking-progress position-absolute top-3 start-0" style="width: <?= $percent ?>%;"></div>

                                <?php foreach ($trackingSteps as $index => $step): ?>
                                    <div class="tracking-step text-center flex-fill position-relative">

                                        <!-- Dot -->
                                        <div class="tracking-dot mx-auto mb-2 
                                        <?= $step['completed'] ? 'completed' : 'pending' ?>">
                                            <?php if ($step['completed']): ?>
                                                <i class="ph ph-check"></i>
                                            <?php elseif (!$step['completed'] && $index === array_search(current(array_filter($trackingSteps, fn($s) => !$s['completed'])), $trackingSteps)): ?>
                                                <i class="ph ph-hourglass"></i>
                                            <?php else: ?>
                                                <i class="ph ph-circle-notch"></i>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Label -->
                                        <h6 class="mb-1 small fw-semibold <?= $step['completed'] ? 'text-dark' : 'text-muted' ?>">
                                            <?= htmlspecialchars($step['status']) ?>
                                        </h6>
                                        <p class="text-dark small mb-0"><?= htmlspecialchars($step['date']) ?></p>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>



                    <!-- Order Items -->
                    <div class="order-items mb-6">
                        <div class="section-card p-6 radius-16 border border-n100-1 bg-n0">
                            <h5 class="mb-4">Order Items</h5>
                            <div class="items-list">
                                <?php foreach ($orderItems as $item):
                                    $images = json_decode($item['image'], true);
                                    $firstImage = is_array($images) && !empty($images) ? $images[0] : 'placeholder.png';
                                ?>
                                    <div class="item-row d-flex align-items-center p-4 radius-12 border border-n100-1 mb-3">
                                        <div class="item-image me-4" style="width: 80px; height: 80px;object-fit: cover;">
                                            <img src="assets/uploads/product/<?= htmlspecialchars($firstImage) ?>"
                                                alt="<?= htmlspecialchars($item['name']) ?>"
                                                class="img-fluid rounded"
                                                style="width: 100%; height: 100%; object-fit: cover;"
                                                onerror="this.src='assets/uploads/product/placeholder.png'">
                                        </div>
                                        <div class="item-details flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                            <?php if ($item['sku']): ?>
                                                <p class="text-n600 mb-1">SKU: <?= htmlspecialchars($item['sku']) ?></p>
                                            <?php endif; ?>
                                            <p class="text-n600 mb-0">Quantity: <?= htmlspecialchars($item['qty']) ?></p>
                                        </div>
                                        <div class="item-price text-end">
                                            <p class="fw-semibold text-n800 mb-0"><?= formatCurrency($item['price'] * $item['qty']) ?></p>
                                            <p class="text-n500 mb-0"><?= formatCurrency($item['price']) ?> each</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary & Addresses -->
                    <div class="row g-6">
                        <!-- Order Summary -->
                        <div class="col-lg-6">
                            <div class="section-card p-6 radius-16 border border-n100-1 bg-n0 h-100">
                                <h5 class="mb-4">Order Summary</h5>
                                <div class="summary-details">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-n600">Subtotal:</span>
                                        <span class="text-n800"><?= formatCurrency($subtotal) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-n600">Shipping:</span>
                                        <span class="text-n800"><?= formatCurrency($shipping) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-n600">Tax:</span>
                                        <span class="text-n800"><?= formatCurrency($tax) ?></span>
                                    </div>

                                    <?php if ($promoDiscount > 0): ?>
                                        <div class="d-flex justify-content-between mb-3 text-danger fw-semibold">
                                            <span>Promo Discount (<?= htmlspecialchars($coupon['code']) ?>):</span>
                                            <span>-<?= formatCurrency($promoDiscount) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <hr class="my-3">

                                    <div class="d-flex justify-content-between mb-4">
                                        <span class="fw-semibold text-n800">Total:</span>
                                        <span class="fw-semibold text-success h5 mb-0"><?= formatCurrency($total) ?></span>
                                    </div>

                                    <?php if ($order['payment_method']): ?>
                                        <div class="payment-method p-3 radius-8 ">
                                            <p class="text-n600 mb-1">Payment Method:</p>
                                            <p class="fw-semibold text-n800 mb-0">
                                                <i class="ph ph-credit-card me-2"></i><?= ucfirst($order['payment_method']) ?>
                                                <?php if ($order['payment_status']): ?>
                                                    <span class="badge bg-<?= $order['payment_status'] === 'success' ? 'success' : 'warning' ?> ms-2">
                                                        <?= ucfirst($order['payment_status']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Addresses -->
                        <div class="col-lg-6">
                            <div class="section-card p-6 radius-16 border border-n100-1 bg-n0 h-100">
                                <h5 class="mb-4">Billing Address</h5>

                                <?php if ($order['billing_name']): ?>
                                    <div class="address-section mb-4">
                                        <div class="address-details p-3 radius-8 ">
                                            <p class="text-n800 fw-semibold mb-1"><?= htmlspecialchars($order['billing_name']) ?></p>
                                            <?php if ($order['billing_phone']): ?>
                                                <p class="text-n600 mb-1">Phone: <?= htmlspecialchars($order['billing_phone']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($order['address_line1']): ?>
                                                <p class="text-n600 mb-1"><?= htmlspecialchars($order['address_line1']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($order['address_line2']): ?>
                                                <p class="text-n600 mb-1"><?= htmlspecialchars($order['address_line2']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($order['city']): ?>
                                                <p class="text-n600 mb-1">
                                                    <?= htmlspecialchars($order['city']) ?>
                                                    <?php if ($order['state']): ?>, <?= htmlspecialchars($order['state']) ?><?php endif; ?>
                                                    <?php if ($order['postal_code']): ?> - <?= htmlspecialchars($order['postal_code']) ?><?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($order['country']): ?>
                                                <p class="text-n600 mb-0"><?= htmlspecialchars($order['country']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="ph ph-warning me-2"></i>
                                        No billing address found for this order.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
$conn->close();
include_once './layout/footer.php';
?>