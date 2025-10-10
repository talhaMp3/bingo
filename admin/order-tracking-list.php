<?php
session_start();
require_once '../include/connection.php';

// Auth check
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: ../login.php');
//     exit();
// }

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

// Handle delete request
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM order_tracking WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Tracking record deleted successfully!";
        header('Location: order-tracking-list.php');
        exit();
    } else {
        $error_message = "Error deleting record: " . mysqli_error($conn);
    }
}

// Fetch filters
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

// Build query
$query = "SELECT * FROM order_tracking WHERE 1=1";
$params = [];
$types = '';

if ($status_filter) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}
if ($search) {
    $query .= " AND (order_id LIKE ? OR tracking_id LIKE ? OR customer_name LIKE ? OR product_name LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= "ssss";
}
$query .= " ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
include('./layout/sidebar.php');
?>


<!-- <nav class="navbar navbar-light bg-white border-bottom shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order Tracking List</h5>
            <div>
                <a href="order-tracking.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add Tracking
                </a>
            </div>
        </div>
    </nav> -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar border-bottom">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 text-dark">Order Tracking List</h5>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
            <div class="dropdown">
                <button class="btn btn-link text-secondary" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-4"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="main-content">
    <div class="container py-4">

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <form method="GET" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Order ID, Tracking ID, Customer..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <?php
                    $statuses = ['Shipping', 'OnTheWay', 'Near By City', 'Deliver'];
                    foreach ($statuses as $st) {
                        echo '<option value="' . $st . '" ' . ($status_filter == $st ? 'selected' : '') . '>' . $st . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="order-tracking-list.php" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Reset</a>
            </div>
            <div class="col-md-2">
                <!-- <a href="order-tracking-list.php" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Reset</a> -->
                <a href="order-tracking.php" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle me-1"></i> Add Tracking
                </a>
            </div>
        </form>

        <!-- Table -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Order ID</th>
                                <th>Tracking ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Dispatch</th>
                                <th>Expected Delivery</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['order_id']) ?></td>
                                        <td><?= htmlspecialchars($row['tracking_id']) ?></td>
                                        <td><?= htmlspecialchars($row['customer_name']) ?><br><small class="text-muted"><?= htmlspecialchars($row['contact']) ?></small></td>
                                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                                        <td>
                                            ₹<?= number_format($row['total_amount'], 2) ?><br>
                                            <small class="text-muted">Adv: ₹<?= number_format($row['advance_amount'], 2) ?></small>
                                        </td>
                                        <td>
                                            <span class="text-primary badge badge-status badge-<?= str_replace(' ', '', $row['status']) ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $row['dispatch_date'] ? date('d M Y', strtotime($row['dispatch_date'])) : '-' ?></td>
                                        <td><?= $row['expected_delivery'] ? date('d M Y', strtotime($row['expected_delivery'])) : '-' ?></td>
                                        <td>
                                            <!-- <a href="https://wa.me/<?= htmlspecialchars($row['contact']) ?>?text=http://localhost:8000/track-order-process.php?trackid=<?= htmlspecialchars($row['tracking_id']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                                <i class="bi bi-whatsapp"></i>
                                            </a> -->
                                            <a href="https://wa.me/<?= htmlspecialchars($row['contact']) ?>?
text=<?= urlencode("Hello {$row['customer_name']}, your order (Tracking ID: {$row['tracking_id']}) is currently *{$row['status']}*. 
You can track it here:
http://localhost:8000/track-order-process.php?trackid={$row['tracking_id']}
We'll keep you updated. Thanks for shopping with us! 💚") ?>"
                                                class="btn btn-sm btn-outline-success" target="_blank">
                                                <i class="bi bi-whatsapp"></i>
                                            </a>



                                            <a href="order-tracking.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this record?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">No tracking records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>