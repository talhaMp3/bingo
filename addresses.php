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

// Fetch addresses
function getAddresses($conn, $userId)
{
    $sql = "SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $addresses = [];
    while ($row = $result->fetch_assoc()) {
        $addresses[] = $row;
    }
    return $addresses;
}

$addresses = getAddresses($conn, $userId);

// Handle Add Address
if (isset($_POST['add_address'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $line1 = trim($_POST['address_line1']);
    $line2 = trim($_POST['address_line2']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']);
    $postal_code = trim($_POST['postal_code']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // If this is set as default, remove default from other addresses
    if ($is_default) {
        $updateStmt = $conn->prepare("UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?");
        $updateStmt->bind_param("i", $userId);
        $updateStmt->execute();
    }

    $stmt = $conn->prepare("INSERT INTO customer_addresses (user_id, full_name, phone, address_line1, address_line2, city, state, country, postal_code, is_default) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("issssssssi", $userId, $full_name, $phone, $line1, $line2, $city, $state, $country, $postal_code, $is_default);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Address added successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to add address. Please try again.";
    }

    header("Location: addresses.php");
    exit;
}

// Handle Edit Address
if (isset($_POST['edit_address'])) {
    $id = (int)$_POST['id'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $line1 = trim($_POST['address_line1']);
    $line2 = trim($_POST['address_line2']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']);
    $postal_code = trim($_POST['postal_code']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // If this is set as default, remove default from other addresses
    if ($is_default) {
        $updateStmt = $conn->prepare("UPDATE customer_addresses SET is_default = 0 WHERE user_id = ? AND id != ?");
        $updateStmt->bind_param("ii", $userId, $id);
        $updateStmt->execute();
    }

    $stmt = $conn->prepare("UPDATE customer_addresses SET full_name=?, phone=?, address_line1=?, address_line2=?, city=?, state=?, country=?, postal_code=?, is_default=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssssssssiii", $full_name, $phone, $line1, $line2, $city, $state, $country, $postal_code, $is_default, $id, $userId);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Address updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update address. Please try again.";
    }

    header("Location: addresses.php");
    exit;
}

// Handle Delete Address
if (isset($_POST['delete_address'])) {
    $id = (int)$_POST['id'];

    // Check if this is the default address
    $checkStmt = $conn->prepare("SELECT is_default FROM customer_addresses WHERE id=? AND user_id=?");
    $checkStmt->bind_param("ii", $id, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $address = $result->fetch_assoc();

    if ($address && $address['is_default'] == 1) {
        // If deleting default address, make another address default if exists
        $otherStmt = $conn->prepare("SELECT id FROM customer_addresses WHERE user_id=? AND id!=? ORDER BY id DESC LIMIT 1");
        $otherStmt->bind_param("ii", $userId, $id);
        $otherStmt->execute();
        $otherResult = $otherStmt->get_result();
        $otherAddress = $otherResult->fetch_assoc();

        if ($otherAddress) {
            $makeDefaultStmt = $conn->prepare("UPDATE customer_addresses SET is_default=1 WHERE id=?");
            $makeDefaultStmt->bind_param("i", $otherAddress['id']);
            $makeDefaultStmt->execute();
        }
    }

    $stmt = $conn->prepare("DELETE FROM customer_addresses WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $userId);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Address deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete address. Please try again.";
    }

    header("Location: addresses.php");
    exit;
}

// Handle Set Default Address
if (isset($_POST['set_default'])) {
    $id = (int)$_POST['id'];

    // Remove default from all addresses
    $updateStmt = $conn->prepare("UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?");
    $updateStmt->bind_param("i", $userId);
    $updateStmt->execute();

    // Set this address as default
    $stmt = $conn->prepare("UPDATE customer_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Default address updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update default address. Please try again.";
    }

    header("Location: addresses.php");
    exit;
}
?>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="addresses.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address Line 1 *</label>
                        <input type="text" name="address_line1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="address_line2" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State *</label>
                            <input type="text" name="state" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country *</label>
                            <input type="text" name="country" class="form-control" value="India" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Postal Code *</label>
                            <input type="text" name="postal_code" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_default" class="form-check-input" value="1" id="addDefault">
                        <label class="form-check-label" for="addDefault">Set as Default Address</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_address" class="btn btn-primary">Save Address</button>
                </div>
            </div>
        </form>
    </div>
</div>

<main class="pt-12">
    <!-- Hero Section -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid text-center">
            <span class="text-animation-word text-h1 text-n100 mb-3">My Account</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">Addresses</a></li>
            </ul>
        </div>
    </section>

    <!-- Account Dashboard -->
    <section class="account-dashboard-section pt-120 pb-120 px-xl-20 px-lg-10 px-sm-7">
        <div class="container-fluid">
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="ph ph-check-circle me-2"></i><?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="ph ph-warning me-2"></i><?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

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
                                    <a href="orders.php" class="d-flex align-items-center gap-3 p-3 radius-8 text-n700 hover-bg-n50">
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
                                    <a href="addresses.php" class="d-flex align-items-center gap-3 p-3 radius-8 bg-primary-50 text-primary-600 fw-medium active">
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
                    <div class="dashboard-content">
                        <!-- Addresses Section -->
                        <div class="addresses-section">
                            <!-- Section Header -->
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h4 class="mb-1">Saved Addresses</h4>
                                    <p class="text-n600 mb-0">Manage your billing and shipping addresses</p>
                                </div>
                                <!-- <button class="btn btn-primary px-4 py-2 radius-8" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                    <i class="ph ph-plus me-2"></i>Add New Address
                                </button> -->
                            </div>

                            <?php if (!empty($addresses)): ?>
                                <div class="row g-4">
                                    <?php foreach ($addresses as $address): ?>
                                        <div class="col-lg-6">
                                            <div class="address-card p-4 radius-16 border border-n100-1 bg-n0 position-relative h-100">
                                                <!-- Default Badge -->
                                                <?php if ($address['is_default']): ?>
                                                    <div class="position-absolute top-0 end-0 mt-3 me-3">
                                                        <span class="badge bg-success">DEFAULT</span>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Address Details -->
                                                <div class="address-details mb-4">
                                                    <h6 class="text-n800 mb-2 fw-semibold">
                                                        <i class="ph ph-user me-2 text-primary-600"></i>
                                                        <?= htmlspecialchars($address['full_name']) ?>
                                                    </h6>

                                                    <?php if ($address['address_line1']): ?>
                                                        <p class="text-n600 mb-1 d-flex align-items-start">
                                                            <i class="ph ph-map-pin me-2 text-n500 mt-1 flex-shrink-0"></i>
                                                            <span>
                                                                <?= htmlspecialchars($address['address_line1']) ?>
                                                                <?php if ($address['address_line2']): ?>
                                                                    <br><?= htmlspecialchars($address['address_line2']) ?>
                                                                <?php endif; ?>
                                                                <br><?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> - <?= htmlspecialchars($address['postal_code']) ?>
                                                                <br><?= htmlspecialchars($address['country']) ?>
                                                            </span>
                                                        </p>
                                                    <?php endif; ?>

                                                    <?php if ($address['phone']): ?>
                                                        <p class="text-n600 mb-0 d-flex align-items-center">
                                                            <i class="ph ph-phone me-2 text-n500"></i>
                                                            <?= htmlspecialchars($address['phone']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Address Actions -->
                                                <div class="address-actions d-flex flex-wrap gap-2 mt-auto">
                                                    <!-- <button class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editAddressModal<?= $address['id'] ?>">
                                                        <i class="ph ph-pencil me-1"></i>Edit
                                                    </button> -->

                                                    <?php if (!$address['is_default']): ?>
                                                        <form method="POST" action="addresses.php" class="d-inline">
                                                            <input type="hidden" name="id" value="<?= $address['id'] ?>">
                                                            <button type="submit" name="set_default" class="btn btn-sm btn-outline-success" onclick="return confirm('Set this as your default address?')">
                                                                <i class="ph ph-check me-1"></i>Set Default
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <form method="POST" action="addresses.php" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $address['id'] ?>">
                                                        <button type="submit" name="delete_address" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Are you sure you want to delete this address?')">
                                                            <i class="ph ph-trash me-1"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Edit Address Modal -->
                                        <div class="modal fade" id="editAddressModal<?= $address['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <form method="POST" action="addresses.php">
                                                    <input type="hidden" name="id" value="<?= $address['id'] ?>">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Address</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Full Name *</label>
                                                                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($address['full_name']) ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Phone *</label>
                                                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($address['phone']) ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Address Line 1 *</label>
                                                                <input type="text" name="address_line1" class="form-control" value="<?= htmlspecialchars($address['address_line1']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Address Line 2</label>
                                                                <input type="text" name="address_line2" class="form-control" value="<?= htmlspecialchars($address['address_line2']) ?>">
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">City *</label>
                                                                    <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($address['city']) ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">State *</label>
                                                                    <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($address['state']) ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Country *</label>
                                                                    <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($address['country']) ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Postal Code *</label>
                                                                    <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($address['postal_code']) ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="is_default" class="form-check-input" value="1"
                                                                    id="editDefault<?= $address['id'] ?>" <?= $address['is_default'] ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="editDefault<?= $address['id'] ?>">Set as Default Address</label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_address" class="btn btn-primary">Update Address</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Address Summary -->
                                <div class="address-summary mt-6 p-4 radius-16 ">
                                    <h6 class="mb-3 text-n800">Address Summary</h6>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="summary-item">
                                                <h6 class="text-n700 mb-2 d-flex align-items-center">
                                                    <i class="ph ph-check-circle text-success me-2"></i>Default Address
                                                </h6>
                                                <?php
                                                $defaultAddress = array_filter($addresses, function ($addr) {
                                                    return $addr['is_default'] == 1;
                                                });
                                                $defaultAddress = reset($defaultAddress);
                                                ?>
                                                <?php if ($defaultAddress): ?>
                                                    <p class="text-n600 mb-1 fw-semibold"><?= htmlspecialchars($defaultAddress['full_name']) ?></p>
                                                    <p class="text-n600 mb-0 small">
                                                        <?= htmlspecialchars($defaultAddress['address_line1']) ?>
                                                        <?php if ($defaultAddress['address_line2']): ?>, <?= htmlspecialchars($defaultAddress['address_line2']) ?><?php endif; ?>
                                                    </p>
                                                    <p class="text-n600 mb-0 small">
                                                        <?= htmlspecialchars($defaultAddress['city']) ?>, <?= htmlspecialchars($defaultAddress['state']) ?> - <?= htmlspecialchars($defaultAddress['postal_code']) ?>
                                                    </p>
                                                <?php else: ?>
                                                    <p class="text-n500 mb-0">No default address set</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="summary-item">
                                                <h6 class="text-n700 mb-2 d-flex align-items-center">
                                                    <i class="ph ph-buildings text-info me-2"></i>Total Addresses
                                                </h6>
                                                <p class="text-n600 mb-0">
                                                    <span class="fs-4 fw-bold text-dark"><?= count($addresses) ?></span>
                                                    <span class="text-n500 ms-1">saved address<?= count($addresses) > 1 ? 'es' : '' ?></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- No Addresses State -->
                                <div class="no-addresses text-center py-5">
                                    <div class="no-addresses-content">
                                        <i class="ph ph-map-pin display-1 text-n400 mb-4 d-block"></i>
                                        <h5 class="text-n600 mb-3">No addresses saved yet</h5>
                                        <p class="text-n500 mb-4">Add your first address for faster checkout and delivery.</p>
                                        <button class="btn btn-primary px-4 py-3" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                            <i class="ph ph-plus me-2"></i>Add Your First Address
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    /* .address-card {
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .address-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .address-actions {
        margin-top: auto;
    }

    .billing-badge {
        background-color: #e3f2fd;
        color: #1976d2;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .shipping-badge {
        background-color: #f3e5f5;
        color: #7b1fa2;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .timeline-connector {
        margin-left: 12px;
        width: 2px;
        height: 30px;
    }

    .timeline-connector.completed {
        background-color: #28a745;
    }

    .timeline-connector.pending {
        background-color: #dee2e6;
    }*/
</style>
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #eb453b 0%, #fb7871ff 100%);
    }

    .text-white-80 {
        color: rgba(255, 255, 255, 0.8);
    }

    .hover-bg-n50:hover {
        background-color: #f8fafc;
    }

    .dashboard-nav a.active,
    .dashboard-nav a:hover {
        background-color: #f1f5f9;
        color: #eb453b;
    }

    .primary-600 {
        color: #eb453b;
    }

    .text-6xl {
        font-size: 3.75rem;
    }

    /* Address Cards */
    .address-card {
        transition: all 0.3s ease;
        height: 100%;
    }

    .address-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    /* Address Type Badges */
    .address-type-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }

    .billing-badge {
        background-color: #fef3c7;
        color: #d97706;
    }

    .shipping-badge {
        background-color: #dbeafe;
        color: #2563eb;
    }

    /* Default Badge */
    .default-badge {
        padding: 4px 8px;
        background-color: #d1fae5;
        color: #059669;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    /* Address Actions */
    .address-actions .btn {
        font-size: 13px;
        border-width: 1px;
    }

    .btn-outline-primary {
        border-color: #eb453b;
        color: #eb453b;
    }

    .btn-outline-primary:hover {
        background-color: #eb453b;
        border-color: #eb453b;
    }

    .btn-outline-danger:hover {
        background-color: #dc2626;
        border-color: #dc2626;
    }

    .btn-outline-success:hover {
        background-color: #059669;
        border-color: #059669;
    }

    /* No Addresses State */
    .no-addresses {
        background-color: #fafbfc;
        border: 2px dashed #d1d5db;
        border-radius: 16px;
    }

    .no-addresses-content {
        max-width: 400px;
        margin: 0 auto;
    }

    /* Address Summary */
    .address-summary {
        border-left: 4px solid #eb453b;
    }

    .summary-item h6 {
        display: flex;
        align-items: center;
    }

    /* Button Styles */
    .btn-primary {
        background-color: #eb453b;
        border-color: #eb453b;
    }

    .btn-primary:hover {
        background-color: #d73d33;
        border-color: #d73d33;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .address-actions {
            flex-wrap: wrap;
        }

        .address-actions .btn {
            flex: 1;
            min-width: 80px;
        }

        .address-summary .row {
            text-align: center;
        }
    }
</style>

<?php include_once './layout/footer.php'; ?>