<?php
include_once '../include/connection.php';

// Main stats
$totalCategories = $conn->query("SELECT COUNT(*) AS c FROM categories")->fetch_assoc()['c'];
$totalProducts   = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$totalOrders     = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$totalRevenue    = $conn->query("SELECT SUM(amount) AS r FROM payments WHERE status='Completed'")->fetch_assoc()['r'] ?? 0;

// Extra stats
$totalCustomers  = $conn->query("SELECT COUNT(*) AS c FROM customers")->fetch_assoc()['c'];
$totalBrands     = $conn->query("SELECT COUNT(*) AS c FROM brands WHERE status='active'")->fetch_assoc()['c'];
$totalWishlists  = $conn->query("SELECT COUNT(*) AS c FROM wishlist")->fetch_assoc()['c'];

// Recent orders (join with customers + payments)
$recentOrders = $conn->query("
  SELECT o.id, c.full_name AS customer, pmt.amount, pmt.status, 
         (SELECT name FROM products WHERE id = (SELECT product_id FROM order_items WHERE order_id=o.id LIMIT 1)) AS product
  FROM orders o
  JOIN customers c ON c.id=o.user_id
  LEFT JOIN payments pmt ON pmt.order_id=o.id
  ORDER BY o.created_at DESC
  LIMIT 5
");
?>

<!-- Top Navbar -->

<?php include './layout/sidebar.php'; ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <h5 class="mb-0 text-dark">Dashboard</h5>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Welcome, Admin</span>
            <i class="bi bi-person-circle fs-4 text-secondary"></i>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid p-4">

        <div class="row mb-4">
            <!-- Categories -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-tags"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Total Categories</h6>
                            <h3 class="mb-0 text-dark"><?= $totalCategories ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-box-seam"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Total Products</h6>
                            <h3 class="mb-0 text-dark"><?= $totalProducts ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-cart-check"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Total Orders</h6>
                            <h3 class="mb-0 text-dark"><?= $totalOrders ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-currency-ruppey"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Revenue</h6>
                            <h3 class="mb-0 text-dark">₹<?= number_format($totalRevenue, 2) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-people"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Customers</h6>
                            <h3 class="mb-0 text-dark"><?= $totalCustomers ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-building"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Active Brands</h6>
                            <h3 class="mb-0 text-dark"><?= $totalBrands ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-heart"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Wishlists</h6>
                            <h3 class="mb-0 text-dark"><?= $totalWishlists ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mb-4">
            <div class="col-12">
                <table class="table ">
                    <tbody>
                        <?php while ($row = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td>#ORD-<?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['customer']) ?></td>
                                <td><?= htmlspecialchars($row['product']) ?></td>
                                <td>$<?= number_format($row['amount'], 2) ?></td>
                                <td>
                                    <span class="badge 
        <?= $row['status'] === 'Completed' ? 'bg-success' : ($row['status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-info') ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>
</div>
</body>

</html>