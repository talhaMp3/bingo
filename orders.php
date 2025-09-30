<?php
session_start();
include_once './include/connection.php';
include_once './layout/header.php';

// Protect this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? "Guest";
$userEmail = $_SESSION['user_email'] ?? "";

// Fetch orders with item count
$sql = "
    SELECT o.id, o.total_amount, o.status, o.payment_status, o.created_at,
           COUNT(oi.id) AS items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = [
        'id' => $row['id'],
        'date' => date("F d, Y", strtotime($row['created_at'])),
        'status' => ucfirst($row['status']),
        'total' => '$' . number_format($row['total_amount'], 2),
        'items' => $row['items'] . ' ' . ($row['items'] > 1 ? 'items' : 'item')
    ];
}
?>


<main class="pt-12">
    <!-- Hero Section -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid text-center">
            <span class="text-animation-word text-h1 text-n100 mb-3">My Account</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">My Account</a></li>
            </ul>
        </div>
    </section>

    <!-- Account Dashboard -->
    <section class="account-dashboard-section pt-120 pb-120 px-xl-20 px-lg-10 px-sm-7">
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
                                    <a href="orders.php" class="d-flex align-items-center gap-3 p-3 radius-8 bg-primary-50 text-primary-600 fw-medium active">
                                        <i class="ph ph-shopping-cart-simple"></i>
                                        Orders
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
                    <div class="dashboard-content">
                        <!-- Orders Table -->
                        <div class="orders-table-container radius-16 border border-n100-1 bg-n0">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <thead class="table-header">
                                        <tr>
                                            <th class="p-4 text-n700 fw-semibold">ORDER</th>
                                            <th class="p-4 text-n700 fw-semibold">DATE</th>
                                            <th class="p-4 text-n700 fw-semibold">STATUS</th>
                                            <th class="p-4 text-n700 fw-semibold">TOTAL</th>
                                            <th class="p-4 text-n700 fw-semibold">ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($orders)): ?>
                                            <?php foreach ($orders as $order): ?>
                                                <tr class="table-row">
                                                    <td class="p-4">
                                                        <span class="fw-semibold text-n800"><?= '#' . htmlspecialchars($order['id']) ?></span>
                                                    </td>
                                                    <td class="p-4">
                                                        <span class="text-n600"><?= htmlspecialchars($order['date']) ?></span>
                                                    </td>
                                                    <td class="p-4">
                                                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                                            <?= htmlspecialchars($order['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="p-4">
                                                        <span class="fw-semibold text-success"><?= htmlspecialchars($order['total']) ?></span>
                                                        <small class="text-muted d-block"><?= htmlspecialchars($order['items']) ?></small>
                                                    </td>
                                                    <td class="p-4">
                                                        <a href="order-details.php?id=<?= $order['id'] ?>"
                                                            class="btn btn-sm btn-success px-4 py-2 radius-8">
                                                            VIEW
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="p-8 text-center">
                                                    <div class="no-orders">
                                                        <i class="ph ph-shopping-cart text-4xl text-n400 mb-3 d-block"></i>
                                                        <h6 class="text-n600 mb-2">No orders found</h6>
                                                        <p class="text-n500">You haven't placed any orders yet.</p>
                                                        <a href="shop.php" class="btn-secondary mt-3">Start Shopping</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #eb453b 0%, #fb7871ff 100%);
    }

    .text-white-80 {
        color: rgba(255, 255, 255, 0.8);
    }

    .hover-shadow {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .hover-bg-n50:hover {
        background-color: #f8fafc;
    }

    .dashboard-nav a.active,
    .dashboard-nav a:hover {
        background-color: #f1f5f9;
        color: #eb453b;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .text-4xl {
        font-size: 2.25rem;
    }

    .primary-600 {
        color: #eb453b;
    }

    .danger-600 {
        color: #dc2626;
    }

    .dashboard-item:hover {
        text-decoration: none;
    }

    .dashboard-item:hover .dashboard-item-icon i {
        transform: scale(1.1);
        transition: transform 0.3s ease;
    }

    /* Orders Table Styles */
    .orders-table-container {
        overflow: hidden;
    }

    .table-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .table-header th {
        border: none;
        font-size: 13px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .table-row {
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s ease;
    }

    .table-row:hover {
        background-color: #f8fafc;
    }

    .table-row:last-child {
        border-bottom: none;
    }

    .table-row td {
        border: none;
        vertical-align: middle;
    }

    /* Status Badges */
    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-processing {
        background-color: #fef3c7;
        color: #d97706;
    }

    .status-completed {
        background-color: #d1fae5;
        color: #059669;
    }

    .status-shipped {
        background-color: #dbeafe;
        color: #2563eb;
    }

    .status-cancelled {
        background-color: #fee2e2;
        color: #dc2626;
    }

    /* View Button */
    .btn-success {
        background-color: #10b981;
        border-color: #10b981;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .btn-success:hover {
        background-color: #059669;
        border-color: #059669;
    }

    /* No Orders State */
    .no-orders {
        padding: 2rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 14px;
        }

        .table th,
        .table td {
            padding: 12px 8px;
        }

        .btn-sm {
            font-size: 11px;
            padding: 6px 12px;
        }
    }
</style>

<?php include_once './layout/footer.php'; ?>