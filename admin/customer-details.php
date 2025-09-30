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

// Get customer addresses
$addresses_sql = "SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC";
$addresses_stmt = $conn->prepare($addresses_sql);
$addresses_stmt->bind_param("i", $customer_id);
$addresses_stmt->execute();
$addresses_result = $addresses_stmt->get_result();
$addresses = $addresses_result->fetch_all(MYSQLI_ASSOC);
$addresses_stmt->close();

// Get customer orders summary
$orders_summary_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_spent,
    MAX(created_at) as last_order_date
    FROM orders 
    WHERE user_id = ?";
$orders_summary_stmt = $conn->prepare($orders_summary_sql);
$orders_summary_stmt->bind_param("i", $customer_id);
$orders_summary_stmt->execute();
$orders_summary_result = $orders_summary_stmt->get_result();
$orders_summary = $orders_summary_result->fetch_assoc();
$orders_summary_stmt->close();

// Get recent orders (last 5)
$recent_orders_sql = "SELECT id, total_amount, status, payment_status, created_at 
                     FROM orders 
                     WHERE user_id = ? 
                     ORDER BY created_at DESC 
                     LIMIT 5";
$recent_orders_stmt = $conn->prepare($recent_orders_sql);
$recent_orders_stmt->bind_param("i", $customer_id);
$recent_orders_stmt->execute();
$recent_orders_result = $recent_orders_stmt->get_result();
$recent_orders = $recent_orders_result->fetch_all(MYSQLI_ASSOC);
$recent_orders_stmt->close();

// Get wishlist items count
$wishlist_sql = "SELECT COUNT(*) as wishlist_count FROM wishlist WHERE user_id = ?";
$wishlist_stmt = $conn->prepare($wishlist_sql);
$wishlist_stmt->bind_param("i", $customer_id);
$wishlist_stmt->execute();
$wishlist_result = $wishlist_stmt->get_result();
$wishlist_count = $wishlist_result->fetch_assoc()['wishlist_count'];
$wishlist_stmt->close();

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_sql = "UPDATE customers SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $customer_id);

    if ($update_stmt->execute()) {
        // Log the activity
        $log_sql = "INSERT INTO activity_logs (type, action, message, reference_id, user_type, user_id) 
                    VALUES ('customer', 'update', 'Customer status updated to {$new_status}', ?, 'admin', 1)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("i", $customer_id);
        $log_stmt->execute();
        $log_stmt->close();

        // Refresh the page to show updated status
        header("Location: customer-details.php?id=" . $customer_id);
        exit();
    }
    $update_stmt->close();
}

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

// Function to get order status badge class
function getOrderStatusBadgeClass($status)
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

include './layout/sidebar.php';
?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <h5 class="mb-0 text-dark">Customer Details</h5>
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
            <a href="customers.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Customers
            </a>
        </div>

        <!-- Customer Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <?php if (!empty($customer['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($customer['profile_image']); ?>"
                                alt="Customer" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="bi bi-person fs-1 text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 class="text-dark mb-1"><?php echo htmlspecialchars($customer['full_name']); ?></h2>
                        <p class="text-muted mb-1">
                            Customer since <?php echo formatDate($customer['created_at']); ?>
                        </p>
                        <div class="d-flex gap-2">
                            <span class="badge <?php echo getStatusBadgeClass($customer['status']); ?>">
                                <?php echo ucfirst($customer['status']); ?>
                            </span>
                            <span class="badge bg-primary">
                                <?php echo ucfirst($customer['login_type']); ?> Login
                            </span>
                            <?php if ($customer['email_verified']): ?>
                                <span class="badge bg-success">Email Verified</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Email Not Verified</span>
                            <?php endif; ?>
                            <?php if (!empty($customer['gender']) && $customer['gender'] !== 'other'): ?>
                                <span class="badge bg-info"><?php echo ucfirst($customer['gender']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                    <a href="customer-orders.php?id=<?php echo $customer['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-cart me-2"></i>View All Orders
                    </a>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Customer Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-cart fs-1 text-primary"></i>
                        </div>
                        <h3 class="mb-1"><?php echo $orders_summary['total_orders'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">Total Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-check-circle fs-1 text-success"></i>
                        </div>
                        <h3 class="mb-1"><?php echo $orders_summary['delivered_orders'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">Delivered Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-currency-rupee fs-1 text-warning"></i>
                        </div>
                        <h3 class="mb-1">₹<?php echo number_format($orders_summary['total_spent'] ?? 0, 2); ?></h3>
                        <p class="text-muted mb-0">Total Spent</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-heart fs-1 text-danger"></i>
                        </div>
                        <h3 class="mb-1"><?php echo $wishlist_count; ?></h3>
                        <p class="text-muted mb-0">Wishlist Items</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Customer Information -->
            <div class="col-lg-8">
                <!-- Personal Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Full Name:</strong></td>
                                        <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td><?php echo !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'Not provided'; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Account Status:</strong></td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($customer['status']); ?>">
                                                <?php echo ucfirst($customer['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Login Type:</strong></td>
                                        <td><?php echo ucfirst($customer['login_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email Verified:</strong></td>
                                        <td>
                                            <?php if ($customer['email_verified']): ?>
                                                <span class="badge bg-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">No</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if (!empty($customer['gender']) && $customer['gender'] !== 'other'): ?>
                                        <tr>
                                            <td><strong>Gender:</strong></td>
                                            <td><?php echo ucfirst($customer['gender']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Activity Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Account Created</h6>
                                    <p class="text-muted mb-0"><?php echo formatDateTime($customer['created_at']); ?></p>
                                </div>
                            </div>
                            <?php if ($customer['last_login']): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Last Login</h6>
                                        <p class="text-muted mb-0"><?php echo formatDateTime($customer['last_login']); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($orders_summary['last_order_date']): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Last Order Placed</h6>
                                        <p class="text-muted mb-0"><?php echo formatDateTime($orders_summary['last_order_date']); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Profile Last Updated</h6>
                                    <p class="text-muted mb-0"><?php echo formatDateTime($customer['updated_at']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Orders</h5>
                        <a href="customer-orders.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_orders)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo formatDate($order['created_at']); ?></td>
                                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge <?php echo getOrderStatusBadgeClass($order['status']); ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo getPaymentStatusBadgeClass($order['payment_status']); ?>">
                                                        <?php echo ucfirst($order['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="order-details.php?id=<?php echo $order['id']; ?>"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-cart-x fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No orders found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Addresses & Actions -->
            <div class="col-lg-4">
                <!-- Customer Addresses -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Saved Addresses</h5>
                        <span class="badge bg-primary"><?php echo count($addresses); ?></span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($addresses)): ?>
                            <?php foreach ($addresses as $address): ?>
                                <div class="address-card mb-3 p-3 border rounded <?php echo $address['is_default'] ? 'border-primary' : ''; ?>">
                                    <?php if ($address['is_default']): ?>
                                        <span class="badge bg-primary mb-2">Default Address</span>
                                    <?php endif; ?>
                                    <address class="mb-0">
                                        <strong><?php echo htmlspecialchars($address['full_name']); ?></strong><br>
                                        <?php echo htmlspecialchars($address['address_line1']); ?><br>
                                        <?php if (!empty($address['address_line2'])): ?>
                                            <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($address['city']); ?>,
                                        <?php echo htmlspecialchars($address['state']); ?>
                                        <?php echo htmlspecialchars($address['postal_code']); ?><br>
                                        <?php echo htmlspecialchars($address['country']); ?><br>
                                        <?php if (!empty($address['phone'])): ?>
                                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($address['phone']); ?>
                                        <?php endif; ?>
                                    </address>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="bi bi-house-door fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No addresses saved</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Customer Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Customer Actions</h5>
                    </div>
                    <div class="card-body">
                        <!-- Update Status -->
                        <form method="POST" class="mb-3">
                            <div class="mb-2">
                                <label class="form-label">Update Account Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo $customer['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $customer['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="banned" <?php echo $customer['status'] === 'banned' ? 'selected' : ''; ?>>Banned</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                        </form>

                        <!-- Quick Actions -->
                        <div class="d-grid gap-2">
                            <a href="customer-orders.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-cart me-2"></i>View Orders
                            </a>
                            <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>" class="btn btn-outline-info">
                                <i class="bi bi-envelope me-2"></i>Send Email
                            </a>
                            <?php if (!empty($customer['phone'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($customer['phone']); ?>" class="btn btn-outline-success">
                                    <i class="bi bi-telephone me-2"></i>Call Customer
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Customer ID:</strong></td>
                                <td>#<?php echo $customer['id']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Registered:</strong></td>
                                <td><?php echo formatDateTime($customer['created_at']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Last Login:</strong></td>
                                <td><?php echo formatDateTime($customer['last_login']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td><?php echo formatDateTime($customer['updated_at']); ?></td>
                            </tr>
                            <?php if (!empty($customer['google_id'])): ?>
                                <tr>
                                    <td><strong>Google ID:</strong></td>
                                    <td class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($customer['google_id']); ?>">
                                        <?php echo htmlspecialchars($customer['google_id']); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
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

    .address-card {
        background: #f8f9fa;
    }

    .table-borderless td {
        border: none;
        padding: 0.3rem 0;
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