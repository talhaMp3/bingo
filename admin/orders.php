<?php
include '../include/connection.php';

// Handle order status update
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['order_status'])) {
    $order_id = (int)$_POST['order_id'];
    $order_status = $_POST['order_status'];

    // Update order status
    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $order_status, $order_id);

    if ($update_stmt->execute()) {
        $success_message = "Order status updated successfully!";

        // Log the activity
        $log_sql = "INSERT INTO activity_logs (type, action, message, reference_id, user_type, user_id) 
                    VALUES ('order', 'update', 'Order status updated to {$order_status}', ?, 'admin', 1)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("i", $order_id);
        $log_stmt->execute();
        $log_stmt->close();
    } else {
        $error_message = "Error updating order status: " . $conn->error;
    }
    $update_stmt->close();
}

// Handle payment status update
if (isset($_POST['update_payment_status']) && isset($_POST['order_id']) && isset($_POST['payment_status'])) {
    $order_id = (int)$_POST['order_id'];
    $payment_status = $_POST['payment_status'];

    // Update payment status
    $update_sql = "UPDATE orders SET payment_status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $payment_status, $order_id);

    if ($update_stmt->execute()) {
        $success_message = "Payment status updated successfully!";

        // Log the activity
        $log_sql = "INSERT INTO activity_logs (type, action, message, reference_id, user_type, user_id) 
                    VALUES ('order', 'update', 'Payment status updated to {$payment_status}', ?, 'admin', 1)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("i", $order_id);
        $log_stmt->execute();
        $log_stmt->close();
    } else {
        $error_message = "Error updating payment status: " . $conn->error;
    }
    $update_stmt->close();
}

// Pagination settings
$orders_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $orders_per_page;

// Search and filter parameters
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$payment_filter = isset($_GET['payment_status']) ? trim($_GET['payment_status']) : '';

// Build WHERE clause for search and filters
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search_term)) {
    $where_conditions[] = "(o.id LIKE ? OR c.full_name LIKE ? OR c.email LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($payment_filter) && $payment_filter !== 'all') {
    $where_conditions[] = "o.payment_status = ?";
    $params[] = $payment_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM orders o 
              LEFT JOIN customers c ON o.user_id = c.id 
              $where_clause";

$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_orders = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $orders_per_page);
$count_stmt->close();

// Get orders with customer information
$sql = "SELECT o.id, o.user_id, o.total_amount, o.status, o.payment_status, 
               o.shipping_address, o.created_at, o.updated_at, o.payment_method,
               c.full_name, c.email, c.phone
        FROM orders o 
        LEFT JOIN customers c ON o.user_id = c.id 
        $where_clause
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $orders_per_page;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

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

// Function to parse shipping address
function parseShippingAddress($addressJson)
{
    $address = json_decode($addressJson, true);
    if (is_array($address)) {
        $formatted = '';
        if (!empty($address['full_name'])) $formatted .= $address['full_name'] . '<br>';
        if (!empty($address['address_line1'])) $formatted .= $address['address_line1'] . '<br>';
        if (!empty($address['address_line2'])) $formatted .= $address['address_line2'] . '<br>';
        if (!empty($address['city'])) $formatted .= $address['city'] . ', ';
        if (!empty($address['state'])) $formatted .= $address['state'] . ' ';
        if (!empty($address['postal_code'])) $formatted .= $address['postal_code'] . '<br>';
        if (!empty($address['country'])) $formatted .= $address['country'];
        return $formatted;
    }
    return 'Address not available';
}

include './layout/sidebar.php';
?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <h5 class="mb-0 text-dark">Manage Orders</h5>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Welcome, Admin</span>
            <i class="bi bi-person-circle fs-4 text-secondary"></i>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid p-4">

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="text-dark mb-1">Orders Management</h4>
                <p class="text-muted mb-0">Manage customer orders and track their status</p>
            </div>
            <div class="d-flex gap-2">
                <a href="orders-export.php" class="btn btn-outline-dark">
                    <i class="bi bi-download me-2"></i>Export Orders
                </a>
            </div>
        </div>

        <!-- Orders Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $total_orders; ?></h4>
                                <p class="mb-0">Total Orders</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-cart fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">
                                    <?php
                                    $pending_sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
                                    $pending_result = $conn->query($pending_sql);
                                    echo $pending_result->fetch_assoc()['count'];
                                    ?>
                                </h4>
                                <p class="mb-0">Pending Orders</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-clock fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">
                                    <?php
                                    $paid_sql = "SELECT COUNT(*) as count FROM orders WHERE payment_status = 'paid'";
                                    $paid_result = $conn->query($paid_sql);
                                    echo $paid_result->fetch_assoc()['count'];
                                    ?>
                                </h4>
                                <p class="mb-0">Paid Orders</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-credit-card fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">
                                    <?php
                                    $delivered_sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'";
                                    $delivered_result = $conn->query($delivered_sql);
                                    echo $delivered_result->fetch_assoc()['count'];
                                    ?>
                                </h4>
                                <p class="mb-0">Delivered Orders</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-truck fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0 text-dark">All Orders</h5>
                    </div>
                    <div class="col-auto">
                        <form method="GET" class="d-flex gap-2">
                            <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <select class="form-select form-select-sm" name="payment_status" onchange="this.form.submit()">
                                <option value="all" <?php echo $payment_filter === 'all' ? 'selected' : ''; ?>>All Payment Status</option>
                                <option value="pending" <?php echo $payment_filter === 'pending' ? 'selected' : ''; ?>>Payment Pending</option>
                                <option value="paid" <?php echo $payment_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="failed" <?php echo $payment_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search"
                                    placeholder="Search by order ID, customer name or email"
                                    value="<?php echo htmlspecialchars($search_term); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <?php if (!empty($search_term) || !empty($status_filter) || !empty($payment_filter)): ?>
                                <a href="?" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Shipping Address</th>
                                <th>Order Status</th>
                                <th>Payment Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $row['id']; ?></strong>
                                            <br><small class="text-muted"><?php echo ucfirst($row['payment_method'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                                <?php if (!empty($row['phone'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($row['phone']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>₹<?php echo number_format($row['total_amount'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <small><?php echo parseShippingAddress($row['shipping_address']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($row['status']); ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <button type="button" class="btn btn-link p-0 text-muted"
                                                    data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $row['id']; ?>">
                                                    <i class="bi bi-pencil-square"></i> Change
                                                </button>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getPaymentStatusBadgeClass($row['payment_status']); ?>">
                                                <?php echo ucfirst($row['payment_status']); ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <button type="button" class="btn btn-link p-0 text-muted"
                                                    data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo $row['id']; ?>">
                                                    <i class="bi bi-pencil-square"></i> Change
                                                </button>
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                                <br><?php echo date('g:i A', strtotime($row['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="order-details.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-outline-primary btn-action" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="invoice.php?id=<?php echo $row['id']; ?>" target="_blank"
                                                class="btn btn-sm btn-outline-info btn-action" title="Invoice">
                                                <i class="bi bi-receipt"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Status Update Modal -->
                                    <div class="modal fade" id="statusModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Order Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <p>Update status for Order #<?php echo $row['id']; ?></p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Order Status</label>
                                                            <select class="form-select" name="order_status" required>
                                                                <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="paid" <?php echo $row['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                                <option value="shipped" <?php echo $row['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                                <option value="delivered" <?php echo $row['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                                <option value="cancelled" <?php echo $row['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Status Update Modal -->
                                    <div class="modal fade" id="paymentModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Payment Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <p>Update payment status for Order #<?php echo $row['id']; ?></p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Payment Status</label>
                                                            <select class="form-select" name="payment_status" required>
                                                                <option value="pending" <?php echo $row['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="paid" <?php echo $row['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                                <option value="failed" <?php echo $row['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="update_payment_status" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                                            No orders found
                                            <?php if (!empty($search_term) || !empty($status_filter) || !empty($payment_filter)): ?>
                                                matching your criteria
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($total_orders > 0): ?>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $orders_per_page, $total_orders); ?>
                            of <?php echo $total_orders; ?> entries
                        </span>
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Previous Button -->
                                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($payment_filter) ? '&payment_status=' . urlencode($payment_filter) : ''; ?>">
                                            Previous
                                        </a>
                                    </li>

                                    <!-- Page Numbers -->
                                    <?php
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $current_page + 2);

                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($payment_filter) ? '&payment_status=' . urlencode($payment_filter) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($payment_filter) ? '&payment_status=' . urlencode($payment_filter) : ''; ?>">
                                            Next
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .btn-action {
        margin-right: 2px;
    }

    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .top-navbar {
        box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        z-index: 1020;
    }

    .main-content {
        margin-top: 20px;
    }
</style>

<!-- JavaScript for auto-hiding alerts -->
<script>
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>