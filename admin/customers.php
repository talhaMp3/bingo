<?php
include '../include/connection.php';

// Handle customer status update
if (isset($_POST['update_status']) && isset($_POST['customer_id']) && isset($_POST['status'])) {
    $customer_id = (int)$_POST['customer_id'];
    $status = $_POST['status'];

    // Update customer status
    $update_sql = "UPDATE customers SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $status, $customer_id);

    if ($update_stmt->execute()) {
        $success_message = "Customer status updated successfully!";

        // Log the activity
        $log_sql = "INSERT INTO activity_logs (type, action, message, reference_id, user_type, user_id) 
                    VALUES ('customer', 'update', 'Customer status updated to {$status}', ?, 'admin', 1)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("i", $customer_id);
        $log_stmt->execute();
        $log_stmt->close();
    } else {
        $error_message = "Error updating customer status: " . $conn->error;
    }
    $update_stmt->close();
}

// Handle customer deletion (soft delete by updating status to banned)
if (isset($_POST['delete_customer']) && isset($_POST['customer_id'])) {
    $customer_id = (int)$_POST['customer_id'];

    // Update status to 'banned' instead of actual deletion
    $delete_sql = "UPDATE customers SET status = 'banned', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $customer_id);

    if ($delete_stmt->execute()) {
        $success_message = "Customer has been banned successfully!";

        // Log the activity
        $log_sql = "INSERT INTO activity_logs (type, action, message, reference_id, user_type, user_id) 
                    VALUES ('customer', 'delete', 'Customer banned', ?, 'admin', 1)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("i", $customer_id);
        $log_stmt->execute();
        $log_stmt->close();
    } else {
        $error_message = "Error banning customer: " . $conn->error;
    }
    $delete_stmt->close();
}

// Pagination settings
$customers_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $customers_per_page;

// Search and filter parameters
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$login_type_filter = isset($_GET['login_type']) ? trim($_GET['login_type']) : '';

// Build WHERE clause for search and filters
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search_term)) {
    $where_conditions[] = "(c.full_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $where_conditions[] = "c.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($login_type_filter) && $login_type_filter !== 'all') {
    $where_conditions[] = "c.login_type = ?";
    $params[] = $login_type_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM customers c $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_customers = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_customers / $customers_per_page);
$count_stmt->close();

// Get customers with order count
$sql = "SELECT c.*, 
               COUNT(o.id) as total_orders,
               SUM(CASE WHEN o.payment_status = 'paid' THEN o.total_amount ELSE 0 END) as total_spent,
               MAX(o.created_at) as last_order_date
        FROM customers c 
        LEFT JOIN orders o ON c.id = o.user_id 
        $where_clause
        GROUP BY c.id 
        ORDER BY c.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $customers_per_page;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Function to get status badge class
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'active':
            return 'bg-success';
        case 'inactive':
            return 'bg-secondary';
        case 'banned':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Function to get login type badge class
function getLoginTypeBadgeClass($login_type)
{
    switch ($login_type) {
        case 'email':
            return 'bg-primary';
        case 'google':
            return 'bg-warning text-dark';
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

include './layout/sidebar.php';
?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <h5 class="mb-0 text-dark">Manage Customers</h5>
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
                <h4 class="text-dark mb-1">Customers Management</h4>
                <p class="text-muted mb-0">Manage your customer accounts and their activities</p>
            </div>
            <div class="d-flex gap-2">
                <a href="customers-export.php" class="btn btn-outline-dark">
                    <i class="bi bi-download me-2"></i>Export Customers
                </a>
            </div>
        </div>

        <!-- Customers Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $total_customers; ?></h4>
                                <p class="mb-0">Total Customers</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-people fs-1 opacity-50"></i>
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
                                    $active_sql = "SELECT COUNT(*) as count FROM customers WHERE status = 'active'";
                                    $active_result = $conn->query($active_sql);
                                    echo $active_result->fetch_assoc()['count'];
                                    ?>
                                </h4>
                                <p class="mb-0">Active Customers</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-person-check fs-1 opacity-50"></i>
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
                                    $google_sql = "SELECT COUNT(*) as count FROM customers WHERE login_type = 'google'";
                                    $google_result = $conn->query($google_sql);
                                    echo $google_result->fetch_assoc()['count'];
                                    ?>
                                </h4>
                                <p class="mb-0">Google Login</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-google fs-1 opacity-50"></i>
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
                                    $verified_sql = "SELECT COUNT(*) as count FROM customers WHERE email_verified = 1";
                                    $verified_result = $conn->query($verified_sql);
                                    echo $verified_result->fetch_assoc()['count'];
                                    ?>
                                </h4>
                                <p class="mb-0">Email Verified</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-envelope-check fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0 text-dark">All Customers</h5>
                    </div>
                    <div class="col-auto">
                        <form method="GET" class="d-flex gap-2">
                            <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="banned" <?php echo $status_filter === 'banned' ? 'selected' : ''; ?>>Banned</option>
                            </select>
                            <select class="form-select form-select-sm" name="login_type" onchange="this.form.submit()">
                                <option value="all" <?php echo $login_type_filter === 'all' ? 'selected' : ''; ?>>All Login Types</option>
                                <option value="email" <?php echo $login_type_filter === 'email' ? 'selected' : ''; ?>>Email</option>
                                <option value="google" <?php echo $login_type_filter === 'google' ? 'selected' : ''; ?>>Google</option>
                            </select>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search"
                                    placeholder="Search by name, email or phone"
                                    value="<?php echo htmlspecialchars($search_term); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <?php if (!empty($search_term) || !empty($status_filter) || !empty($login_type_filter)): ?>
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
                                <th>Customer</th>
                                <th>Contact Info</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Last Activity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($customer = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <?php if (!empty($customer['profile_image'])): ?>
                                                        <img src="<?php echo $customer['profile_image']; ?>"
                                                            alt="Customer" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 40px; height: 40px;">
                                                            <i class="bi bi-person text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($customer['full_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Joined: <?php echo formatDate($customer['created_at']); ?>
                                                    </small>
                                                    <br>
                                                    <span class="badge <?php echo getLoginTypeBadgeClass($customer['login_type']); ?> small">
                                                        <?php echo ucfirst($customer['login_type']); ?>
                                                    </span>
                                                    <?php if ($customer['email_verified']): ?>
                                                        <span class="badge bg-success small">Verified</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark small">Unverified</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted">Email:</small>
                                                <br><?php echo htmlspecialchars($customer['email']); ?>
                                                <?php if (!empty($customer['phone'])): ?>
                                                    <br>
                                                    <small class="text-muted">Phone:</small>
                                                    <br><?php echo htmlspecialchars($customer['phone']); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($customer['gender']) && $customer['gender'] !== 'other'): ?>
                                                    <br>
                                                    <small class="text-muted">Gender:</small>
                                                    <br><?php echo ucfirst($customer['gender']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <strong class="h5"><?php echo $customer['total_orders']; ?></strong>
                                                <br>
                                                <small class="text-muted">Orders</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <strong class="h5 text-success">₹<?php echo number_format($customer['total_spent'] ?? 0, 2); ?></strong>
                                                <br>
                                                <small class="text-muted">Total Spent</small>
                                            </div>
                                        </td>
                                        <td>
                                            <small>
                                                <strong>Last Login:</strong>
                                                <br><?php echo formatDateTime($customer['last_login']); ?>
                                                <?php if ($customer['last_order_date']): ?>
                                                    <br>
                                                    <strong>Last Order:</strong>
                                                    <br><?php echo formatDate($customer['last_order_date']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($customer['status']); ?>">
                                                <?php echo ucfirst($customer['status']); ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <button type="button" class="btn btn-link p-0 text-muted"
                                                    data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $customer['id']; ?>">
                                                    <i class="bi bi-pencil-square"></i> Change
                                                </button>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="customer-details.php?id=<?php echo $customer['id']; ?>"
                                                    class="btn btn-sm btn-outline-primary btn-action" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="customer-orders.php?id=<?php echo $customer['id']; ?>"
                                                    class="btn btn-sm btn-outline-info btn-action" title="View Orders">
                                                    <i class="bi bi-cart"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger btn-action"
                                                    title="Ban Customer"
                                                    onclick="confirmBan(<?php echo $customer['id']; ?>, '<?php echo addslashes($customer['full_name']); ?>')">
                                                    <i class="bi bi-person-x"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Status Update Modal -->
                                    <div class="modal fade" id="statusModal<?php echo $customer['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Customer Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <p>Update status for <?php echo htmlspecialchars($customer['full_name']); ?></p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select class="form-select" name="status" required>
                                                                <option value="active" <?php echo $customer['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                                <option value="inactive" <?php echo $customer['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                <option value="banned" <?php echo $customer['status'] === 'banned' ? 'selected' : ''; ?>>Banned</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                                            No customers found
                                            <?php if (!empty($search_term) || !empty($status_filter) || !empty($login_type_filter)): ?>
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
            <?php if ($total_customers > 0): ?>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $customers_per_page, $total_customers); ?>
                            of <?php echo $total_customers; ?> entries
                        </span>
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Previous Button -->
                                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($login_type_filter) ? '&login_type=' . urlencode($login_type_filter) : ''; ?>">
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
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($login_type_filter) ? '&login_type=' . urlencode($login_type_filter) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($login_type_filter) ? '&login_type=' . urlencode($login_type_filter) : ''; ?>">
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

<!-- Ban Confirmation Modal -->
<div class="modal fade" id="banModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Ban</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to ban the customer "<span id="customerName"></span>"?</p>
                <p class="text-danger small">This action will prevent the customer from logging in and making purchases.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="customer_id" id="banCustomerId" value="">
                    <button type="submit" name="delete_customer" class="btn btn-danger">Ban Customer</button>
                </form>
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
</style>

<!-- JavaScript -->
<script>
    function confirmBan(customerId, customerName) {
        document.getElementById('banCustomerId').value = customerId;
        document.getElementById('customerName').textContent = customerName;
        new bootstrap.Modal(document.getElementById('banModal')).show();
    }

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