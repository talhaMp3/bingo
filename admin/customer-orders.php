<?php
include '../include/connection.php';

// Check if customer ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: customers.php');
    exit();
}

$customer_id = (int)$_GET['id'];

// Get customer details
$customer_sql = "SELECT * FROM customers WHERE id = ?";
$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();

if ($customer_result->num_rows === 0) {
    echo "Customer not found!";
    exit();
}

$customer = $customer_result->fetch_assoc();
$customer_stmt->close();

// Handle order status update
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['order_status'])) {
    $order_id = (int)$_POST['order_id'];
    $order_status = $_POST['order_status'];

    // Update order status
    $update_sql = "UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $order_status, $order_id, $customer_id);

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
    $update_sql = "UPDATE orders SET payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $payment_status, $order_id, $customer_id);

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
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$payment_filter = isset($_GET['payment_status']) ? trim($_GET['payment_status']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// Build WHERE clause for search and filters
$where_conditions = ["o.user_id = ?"];
$params = [$customer_id];
$types = 'i';

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

if (!empty($date_from)) {
    $where_conditions[] = "DATE(o.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(o.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM orders o WHERE $where_clause";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_orders = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $orders_per_page);
$count_stmt->close();

// Get orders with item count
$sql = "SELECT o.*, 
               COUNT(oi.id) as item_count,
               SUM(oi.qty) as total_items
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE $where_clause
        GROUP BY o.id 
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $orders_per_page;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get orders summary for the customer
$summary_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_spent,
    AVG(total_amount) as avg_order_value,
    MIN(created_at) as first_order_date,
    MAX(created_at) as last_order_date
    FROM orders 
    WHERE user_id = ?";
$summary_stmt = $conn->prepare($summary_sql);
$summary_stmt->bind_param("i", $customer_id);
$summary_stmt->execute();
$summary_result = $summary_stmt->get_result();
$summary = $summary_result->fetch_assoc();
$summary_stmt->close();

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

// Function to format date
function formatDate($date)
{
    if (!$date) return 'Never';
    return date('M j, Y', strtotime($date));
}

// Function to format datetime
function formatDateTime($datetime)
{
    if (!$datetime) return 'Never';
    return date('M j, Y g:i A', strtotime($datetime));
}

// Function to parse shipping address
function parseShippingAddress($addressJson)
{
    $address = json_decode($addressJson, true);
    if (is_array($address)) {
        $formatted = '';
        if (!empty($address['full_name'])) $formatted .= $address['full_name'] . '<br>';
        if (!empty($address['address_line1'])) $formatted .= $address['address_line1'] . '<br>';
        if (!empty($address['city'])) $formatted .= $address['city'] . ', ';
        if (!empty($address['state'])) $formatted .= $address['state'];
        return $formatted;
    }
    return 'Address not available';
}

include './layout/sidebar.php';
?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <h5 class="mb-0 text-dark">Customer Orders</h5>
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
            <a href="customer-details.php?id=<?php echo $customer_id; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Customer Details
            </a>
            <a href="customers.php" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-people me-2"></i>All Customers
            </a>
        </div>

        <!-- Customer Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <?php if (!empty($customer['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($customer['profile_image']); ?>"
                                alt="Customer" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <i class="bi bi-person fs-4 text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 class="text-dark mb-1"><?php echo htmlspecialchars($customer['full_name']); ?></h2>
                        <p class="text-muted mb-0">Order History</p>
                        <div class="d-flex gap-2 mt-1">
                            <span class="badge bg-primary"><?php echo $summary['total_orders']; ?> Orders</span>
                            <span class="badge bg-success">₹<?php echo number_format($summary['total_spent'] ?? 0, 2); ?> Spent</span>
                            <span class="badge bg-info">Since <?php echo formatDate($summary['first_order_date']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                    <a href="orders-export.php?customer_id=<?php echo $customer_id; ?>" class="btn btn-outline-dark">
                        <i class="bi bi-download me-2"></i>Export Orders
                    </a>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>

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

        <!-- Orders Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $summary['total_orders']; ?></h4>
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
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $summary['delivered_orders']; ?></h4>
                                <p class="mb-0">Delivered</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-check-circle fs-1 opacity-50"></i>
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
                                <h4 class="mb-0">₹<?php echo number_format($summary['total_spent'] ?? 0, 2); ?></h4>
                                <p class="mb-0">Total Spent</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-currency-rupee fs-1 opacity-50"></i>
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
                                <h4 class="mb-0">₹<?php echo number_format($summary['avg_order_value'] ?? 0, 2); ?></h4>
                                <p class="mb-0">Avg. Order Value</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-graph-up fs-1 opacity-50"></i>
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
                        <p class="text-muted mb-0">Showing orders for <?php echo htmlspecialchars($customer['full_name']); ?></p>
                    </div>
                    <div class="col-auto">
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="id" value="<?php echo $customer_id; ?>">

                            <div class="input-group input-group-sm" style="width: 200px;">
                                <span class="input-group-text">From</span>
                                <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>

                            <div class="input-group input-group-sm" style="width: 200px;">
                                <span class="input-group-text">To</span>
                                <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>

                            <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>

                            <select class="form-select form-select-sm" name="payment_status" onchange="this.form.submit()">
                                <option value="all" <?php echo $payment_filter === 'all' ? 'selected' : ''; ?>>All Payment</option>
                                <option value="pending" <?php echo $payment_filter === 'pending' ? 'selected' : ''; ?>>Payment Pending</option>
                                <option value="paid" <?php echo $payment_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="failed" <?php echo $payment_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>

                            <?php if (!empty($status_filter) || !empty($payment_filter) || !empty($date_from) || !empty($date_to)): ?>
                                <a href="?id=<?php echo $customer_id; ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            <?php endif; ?>

                            <button class="btn btn-outline-secondary btn-sm" type="submit">
                                <i class="bi bi-filter"></i> Apply
                            </button>
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
                                <th>Date</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Shipping Address</th>
                                <th>Order Status</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($order = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $order['id']; ?></strong>
                                            <br><small class="text-muted"><?php echo ucfirst($order['payment_method'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo formatDate($order['created_at']); ?>
                                                <br><?php echo date('g:i A', strtotime($order['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <strong><?php echo $order['item_count']; ?></strong>
                                                <br><small class="text-muted">Items</small>
                                                <br><small class="text-muted">(Qty: <?php echo $order['total_items']; ?>)</small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <small><?php echo parseShippingAddress($order['shipping_address']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <button type="button" class="btn btn-link p-0 text-muted"
                                                    data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $order['id']; ?>">
                                                    <i class="bi bi-pencil-square"></i> Change
                                                </button>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getPaymentStatusBadgeClass($order['payment_status']); ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <button type="button" class="btn btn-link p-0 text-muted"
                                                    data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo $order['id']; ?>">
                                                    <i class="bi bi-pencil-square"></i> Change
                                                </button>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="order-details.php?id=<?php echo $order['id']; ?>"
                                                    class="btn btn-sm btn-outline-primary btn-action" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="invoice.php?id=<?php echo $order['id']; ?>" target="_blank"
                                                    class="btn btn-sm btn-outline-info btn-action" title="Invoice">
                                                    <i class="bi bi-receipt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Status Update Modal -->
                                    <div class="modal fade" id="statusModal<?php echo $order['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Order Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <p>Update status for Order #<?php echo $order['id']; ?></p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Order Status</label>
                                                            <select class="form-select" name="order_status" required>
                                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Status Update Modal -->
                                    <div class="modal fade" id="paymentModal<?php echo $order['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Payment Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <p>Update payment status for Order #<?php echo $order['id']; ?></p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Payment Status</label>
                                                            <select class="form-select" name="payment_status" required>
                                                                <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                                <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
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
                                            <?php if (!empty($status_filter) || !empty($payment_filter) || !empty($date_from) || !empty($date_to)): ?>
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
                            of <?php echo $total_orders; ?> orders
                        </span>
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Previous Button -->
                                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?id=<?php echo $customer_id; ?>&page=<?php echo $current_page - 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($payment_filter) ? '&payment_status=' . urlencode($payment_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>">
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
                                            <a class="page-link" href="?id=<?php echo $customer_id; ?>&page=<?php echo $i; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($payment_filter) ? '&payment_status=' . urlencode($payment_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?id=<?php echo $customer_id; ?>&page=<?php echo $current_page + 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($payment_filter) ? '&payment_status=' . urlencode($payment_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>">
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
    .top-navbar {
        box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        z-index: 1020;
    }

    .main-content {
        margin-top: 20px;
    }

    .btn-action {
        margin-right: 2px;
    }

    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .table td {
        vertical-align: middle;
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
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>