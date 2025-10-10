<?php
session_start();
require_once '../include/connection.php';

// Auth check
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: ../login.php');
//     exit();
// }

$edit_mode = false;
$tracking_data = [];
$errors = [];
$success_message = '';

// Check edit mode
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $tracking_id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);

    if ($tracking_id === false) {
        $errors[] = "Invalid tracking ID.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM order_tracking WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $tracking_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($tracking_data = mysqli_fetch_assoc($result)) {
            // Format dates for display
            if (!empty($tracking_data['dispatch_date'])) {
                $tracking_data['dispatch_date'] = date('Y-m-d', strtotime($tracking_data['dispatch_date']));
            }
            if (!empty($tracking_data['expected_delivery'])) {
                $tracking_data['expected_delivery'] = date('Y-m-d', strtotime($tracking_data['expected_delivery']));
            }
        } else {
            $errors[] = "Tracking record not found.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Enhanced validation
function validateTracking($data)
{
    $errors = [];

    // Required fields
    if (empty(trim($data['order_id']))) {
        $errors[] = "Order ID is required.";
    }
    if (empty(trim($data['tracking_id']))) {
        $errors[] = "Tracking ID is required.";
    }
    if (empty(trim($data['customer_name']))) {
        $errors[] = "Customer name is required.";
    }

    // Contact validation
    if (!empty($data['contact'])) {
        $contact = preg_replace('/[^0-9]/', '', $data['contact']);
        if (strlen($contact) < 10 || strlen($contact) > 15) {
            $errors[] = "Contact number must be between 10-15 digits.";
        }
    }

    // Amount validation
    if (!empty($data['total_amount'])) {
        if (!is_numeric($data['total_amount']) || floatval($data['total_amount']) < 0) {
            $errors[] = "Total amount must be a positive number.";
        }
    }

    if (!empty($data['advance_amount'])) {
        if (!is_numeric($data['advance_amount']) || floatval($data['advance_amount']) < 0) {
            $errors[] = "Advance amount must be a positive number.";
        }

        // Check if advance is not greater than total
        if (!empty($data['total_amount']) && floatval($data['advance_amount']) > floatval($data['total_amount'])) {
            $errors[] = "Advance amount cannot be greater than total amount.";
        }
    }

    // Date validation
    if (!empty($data['dispatch_date']) && !empty($data['expected_delivery'])) {
        $dispatch = strtotime($data['dispatch_date']);
        $expected = strtotime($data['expected_delivery']);

        if ($expected < $dispatch) {
            $errors[] = "Expected delivery date must be after dispatch date.";
        }
    }

    return $errors;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $errors = validateTracking($_POST);

    if (empty($errors)) {
        // Sanitize inputs
        $order_id = trim($_POST['order_id']);
        $track_id = trim($_POST['tracking_id']);
        $contact = trim($_POST['contact']);
        $customer_name = trim($_POST['customer_name']);
        $product_name = trim($_POST['product_name']);
        $total_amount = !empty($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;
        $advance_amount = !empty($_POST['advance_amount']) ? floatval($_POST['advance_amount']) : 0;
        $remaining_amount = $total_amount - $advance_amount;
        $transport = trim($_POST['transport_detail']);
        $from = trim($_POST['from_location']);
        $to = trim($_POST['to_location']);
        $dispatch = !empty($_POST['dispatch_date']) ? $_POST['dispatch_date'] : null;
        $expected = !empty($_POST['expected_delivery']) ? $_POST['expected_delivery'] : null;
        $status = $_POST['status'] ?? 'Shipping';

        mysqli_begin_transaction($conn);

        try {
            if ($edit_mode && isset($tracking_id)) {
                $query = "UPDATE order_tracking SET 
                    order_id=?, tracking_id=?, contact=?, customer_name=?, product_name=?, 
                    total_amount=?, advance_amount=?, remaining_amount=?, transport_detail=?, 
                    from_location=?, to_location=?, dispatch_date=?, expected_delivery=?, status=?, 
                    updated_at=NOW() 
                    WHERE id=?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssdddssssssi",
                    $order_id,
                    $track_id,
                    $contact,
                    $customer_name,
                    $product_name,
                    $total_amount,
                    $advance_amount,
                    $remaining_amount,
                    $transport,
                    $from,
                    $to,
                    $dispatch,
                    $expected,
                    $status,
                    $tracking_id
                );
            } else {
                $query = "INSERT INTO order_tracking 
                    (order_id, tracking_id, contact, customer_name, product_name, 
                    total_amount, advance_amount, remaining_amount, transport_detail, 
                    from_location, to_location, dispatch_date, expected_delivery, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssdddssssss",
                    $order_id,
                    $track_id,
                    $contact,
                    $customer_name,
                    $product_name,
                    $total_amount,
                    $advance_amount,
                    $remaining_amount,
                    $transport,
                    $from,
                    $to,
                    $dispatch,
                    $expected,
                    $status
                );
            }

            if (mysqli_stmt_execute($stmt)) {
                mysqli_commit($conn);
                mysqli_stmt_close($stmt);
                $_SESSION['success_message'] = $edit_mode ? "Tracking record updated successfully!" : "Tracking record added successfully!";
                header('Location: order-tracking-list.php');
                exit();
            } else {
                throw new Exception("Database error: " . mysqli_error($conn));
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = $e->getMessage();
        }
    }
}

include('./layout/sidebar.php');
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar border-bottom">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <h5 class="mb-0"><?= $edit_mode ? 'Edit' : 'Add New' ?> Tracking</h5>
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
        <?php if (!empty($errors)): ?>
            <div class="alert-custom alert-error">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert-custom alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong><?= htmlspecialchars($success_message) ?></strong>
            </div>
        <?php endif; ?>

        <form method="POST" id="trackingForm" novalidate>
            <div class="card main-card">
                <div class="card-header">
                    <h6><i class="bi bi-box-seam me-2"></i>Order Tracking Information</h6>
                </div>
                <div class="card-body p-4">

                    <!-- Order Information -->
                    <div class="section-title">
                        <i class="bi bi-info-circle me-2"></i>Order Information
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label required-field">Order ID</label>
                            <input type="text" name="order_id" class="form-control"
                                value="<?= htmlspecialchars($tracking_data['order_id'] ?? '') ?>"
                                placeholder="e.g., ORD001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required-field">Tracking ID</label>
                            <input type="text" name="tracking_id" class="form-control"
                                value="<?= htmlspecialchars($tracking_data['tracking_id'] ?? '') ?>"
                                placeholder="e.g., TRK001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php
                                $statuses = [
                                    'Order Placed' => 'Order Placed',
                                    'Shipping' => 'Shipping',
                                    'OnTheWay' => 'On The Way',
                                    'Near By City' => 'Near By City',
                                    'Deliver' => 'Deliver'
                                ];
                                $selected = $tracking_data['status'] ?? 'Shipping';
                                foreach ($statuses as $value => $label) {
                                    echo '<option value="' . $value . '" ' . ($selected == $value ? 'selected' : '') . '>' . $label . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="section-title">
                        <i class="bi bi-person me-2"></i>Customer Information
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label required-field">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control"
                                value="<?= htmlspecialchars($tracking_data['customer_name'] ?? '') ?>"
                                placeholder="Enter customer name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="tel" name="contact" class="form-control"
                                value="<?= htmlspecialchars($tracking_data['contact'] ?? '') ?>"
                                placeholder="e.g., +91 98765 43210">
                        </div>
                    </div>

                    <!-- Product Information -->
                    <div class="section-title">
                        <i class="bi bi-bicycle me-2"></i>Product Information
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="product_name" class="form-control"
                                value="<?= htmlspecialchars($tracking_data['product_name'] ?? '') ?>"
                                placeholder="Enter product details">
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="section-title">
                        <i class="bi bi-currency-rupee me-2"></i>Payment Information
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Total Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="total_amount" id="total_amount"
                                    class="form-control"
                                    value="<?= htmlspecialchars($tracking_data['total_amount'] ?? '') ?>"
                                    placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Advance Paid</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="advance_amount" id="advance_amount"
                                    class="form-control"
                                    value="<?= htmlspecialchars($tracking_data['advance_amount'] ?? '') ?>"
                                    placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Remaining Amount</label>
                            <div class="amount-display" id="remaining_amount">
                                ₹<?= number_format($tracking_data['remaining_amount'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    <div class="section-title">
                        <i class="bi bi-truck me-2"></i>Shipping Information
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Transport Company</label>
                            <textarea name="transport_detail" class="form-control" rows="2"
                                placeholder="Enter transport company details"><?= htmlspecialchars($tracking_data['transport_detail'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Location</label>
                            <input type="text" name="from_location" class="form-control"
                                value="<?= htmlspecialchars($tracking_data['from_location'] ?? '') ?>"
                                placeholder="e.g., Surat">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Location</label>
                            <input type="text" name="to_location" class="form-control"
                                value="<?= htmlspecialchars($tracking_data['to_location'] ?? '') ?>"
                                placeholder="e.g., Mumbai">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dispatch Date</label>
                            <input type="date" name="dispatch_date" id="dispatch_date" class="form-control"
                                value="<?= htmlspecialchars($tracking_data['dispatch_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expected Delivery</label>
                            <input type="date" name="expected_delivery" id="expected_delivery" class="form-control"
                                value="<?= htmlspecialchars($tracking_data['expected_delivery'] ?? '') ?>">
                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Fields marked with * are required</small>
                    <div class="d-flex gap-2">
                        <a href="order-tracking-list.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i><?= $edit_mode ? 'Update Tracking' : 'Save Tracking' ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
    :root {
        --primary-color: #eb453b;
        --secondary-color: #6c757d;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --light-bg: #f8f9fa;
        --border-color: #e5e7eb;
    }

    body {
        background-color: var(--light-bg);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .navbar {
        background: white !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        padding: 1rem 0;
    }

    .navbar h5 {
        font-weight: 600;
        color: #111827;
    }

    .main-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .card-header {
        background: white;
        border-bottom: 2px solid var(--primary-color);
        padding: 1.5rem;
    }

    .card-header h6 {
        color: #111827;
        font-weight: 600;
        font-size: 1.1rem;
        margin: 0;
    }

    .section-title {
        color: var(--primary-color);
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1.25rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
    }

    .form-label {
        font-size: 0.813rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 0.625rem 0.875rem;
        font-size: 0.938rem;
        transition: all 0.2s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(235, 69, 59, 0.1);
    }

    .form-control:read-only {
        background-color: #f9fafb;
        color: #6b7280;
    }

    .required-field::after {
        content: '*';
        color: var(--danger-color);
        margin-left: 3px;
    }

    .alert-custom {
        border: none;
        border-radius: 8px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .alert-error {
        background: #fef2f2;
        border-left: 4px solid var(--danger-color);
        color: #991b1b;
    }

    .alert-success {
        background: #f0fdf4;
        border-left: 4px solid var(--success-color);
        color: #166534;
    }

    .alert-custom strong {
        font-weight: 600;
    }

    .alert-custom ul {
        margin: 0.75rem 0 0 0;
        padding-left: 1.25rem;
    }

    .btn {
        padding: 0.625rem 1.5rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 0.938rem;
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
    }

    .btn-primary:hover {
        background: #d63d33;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(235, 69, 59, 0.3);
    }

    .btn-outline-secondary {
        border: 1px solid var(--border-color);
        color: var(--secondary-color);
    }

    .btn-outline-secondary:hover {
        background: var(--secondary-color);
        border-color: var(--secondary-color);
        color: white;
    }

    .card-footer {
        background: #f9fafb;
        border-top: 1px solid var(--border-color);
        padding: 1.25rem 1.5rem;
    }

    .input-group-text {
        background: white;
        border: 1px solid var(--border-color);
        color: #6b7280;
    }

    .form-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }

    .amount-display {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 0.625rem 0.875rem;
        font-weight: 600;
        color: var(--primary-color);
        font-size: 0.938rem;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1.25rem;
        }

        .section-title {
            font-size: 0.813rem;
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Calculate remaining amount
    function calculateRemaining() {
        const total = parseFloat(document.getElementById('total_amount').value) || 0;
        const advance = parseFloat(document.getElementById('advance_amount').value) || 0;
        const remaining = total - advance;
        document.getElementById('remaining_amount').textContent = '₹' + remaining.toFixed(2);

        // Validate advance not greater than total
        if (advance > total && total > 0) {
            document.getElementById('advance_amount').classList.add('is-invalid');
        } else {
            document.getElementById('advance_amount').classList.remove('is-invalid');
        }
    }

    // Event listeners for amount calculation
    document.getElementById('total_amount').addEventListener('input', calculateRemaining);
    document.getElementById('advance_amount').addEventListener('input', calculateRemaining);

    // Date validation
    document.getElementById('dispatch_date').addEventListener('change', function() {
        const dispatchDate = new Date(this.value);
        const expectedInput = document.getElementById('expected_delivery');

        if (expectedInput.value) {
            const expectedDate = new Date(expectedInput.value);
            if (expectedDate < dispatchDate) {
                expectedInput.setCustomValidity('Expected delivery must be after dispatch date');
            } else {
                expectedInput.setCustomValidity('');
            }
        }
    });

    document.getElementById('expected_delivery').addEventListener('change', function() {
        const dispatchInput = document.getElementById('dispatch_date');
        if (dispatchInput.value) {
            const dispatchDate = new Date(dispatchInput.value);
            const expectedDate = new Date(this.value);

            if (expectedDate < dispatchDate) {
                this.setCustomValidity('Expected delivery must be after dispatch date');
            } else {
                this.setCustomValidity('');
            }
        }
    });

    // Form validation
    document.getElementById('trackingForm').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });

    // Initialize calculations on page load
    document.addEventListener('DOMContentLoaded', calculateRemaining);
</script>
</body>

</html>