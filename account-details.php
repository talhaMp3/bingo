<?php
session_start();
include_once './include/connection.php';
include_once './layout/header.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Protect this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    $errors = [];

    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if email already exists for other users
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }

    // Phone validation (optional)
    if (!empty($phone) && !preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $phone)) {
        $errors[] = "Invalid phone number format";
    }

    // Password validation (only if user wants to change password)
    $update_password = false;
    if (!empty($new_password) || !empty($confirm_password) || !empty($current_password)) {
        // For Google users, they don't have a current password
        if ($user['login_type'] === 'google' && $user['password'] === null) {
            if (empty($new_password)) {
                $errors[] = "New password is required";
            } elseif (strlen($new_password) < 6) {
                $errors[] = "Password must be at least 6 characters long";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "Passwords do not match";
            } else {
                $update_password = true;
            }
        } else {
            // For email users, verify current password
            if (empty($current_password)) {
                $errors[] = "Current password is required to change password";
            } elseif (!password_verify($current_password, $user['password'])) {
                $errors[] = "Current password is incorrect";
            } elseif (empty($new_password)) {
                $errors[] = "New password is required";
            } elseif (strlen($new_password) < 6) {
                $errors[] = "Password must be at least 6 characters long";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "Passwords do not match";
            } else {
                $update_password = true;
            }
        }
    }

    if (empty($errors)) {
        // Update user data
        if ($update_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE customers SET full_name = ?, email = ?, phone = ?, gender = ?, password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("sssssi", $full_name, $email, $phone, $gender, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE customers SET full_name = ?, email = ?, phone = ?, gender = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $gender, $user_id);
        }

        if ($stmt->execute()) {
            // Update session data
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;

            // Fetch updated user data
            $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $success_message = "Account details updated successfully!";
        } else {
            $error_message = "Error updating account details. Please try again.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

$userName = $_SESSION['user_name'] ?? "Guest";
?>

<main class="pt-12">
    <!-- Hero Section -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid text-center">
            <span class="text-animation-word text-h1 text-n100 mb-3">Account Details</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="account.php">My Account</a></li>
                <li class="breadcrumb-item active"><a href="#">Account Details</a></li>
            </ul>
        </div>
    </section>

    <!-- Account Details -->
    <section class="account-details-section pt-120 pb-120 px-xl-20 px-lg-10 px-sm-7">
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
                                    <a href="orders.php" class="d-flex align-items-center gap-3 p-3 radius-8 text-n700 hover-bg-n50">
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
                                    <a href="account-details.php" class="d-flex align-items-center gap-3 p-3 radius-8 bg-primary-50 text-primary-600 fw-medium active">
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
                    <div class="account-details-content">
                        <!-- Page Title -->
                        <div class="page-title-card p-6 radius-16 border border-n100-1 bg-n0 mb-6">
                            <h4 class="text-n800 mb-2">Account Details</h4>
                            <p class="text-n600 mb-0">Edit your account information and password</p>
                        </div>

                        <!-- Success/Error Messages -->
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                <i class="ph ph-check-circle me-2"></i>
                                <?= $success_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <i class="ph ph-x-circle me-2"></i>
                                <?= $error_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Account Details Form -->
                        <div class="account-form-card p-6 radius-16 border border-n100-1 bg-n0">
                            <form method="POST" action="" class="account-details-form">
                                <div class="row g-4">
                                    <!-- Profile Information Section -->
                                    <div class="col-12">
                                        <h5 class="text-n800 mb-4 pb-3 border-bottom">
                                            <i class="ph ph-user-circle me-2 text-primary-600"></i>
                                            Profile Information
                                        </h5>
                                    </div>

                                    <!-- Profile Image Display (for Google users) -->
                                    <?php if ($user['login_type'] === 'google' && $user['profile_image']): ?>
                                        <div class="col-12">
                                            <div class="profile-image-section mb-4">
                                                <label class="form-label text-n700 fw-medium mb-3">Profile Picture</label>
                                                <div class="d-flex align-items-center gap-4">
                                                    <img src="<?= $user['profile_image'] ?>"
                                                        alt="Profile Picture"
                                                        class="profile-avatar rounded-circle border border-n200"
                                                        style="width: 80px; height: 80px; object-fit: cover;">
                                                    <div>
                                                        <p class="text-n600 mb-1">Signed in with Google</p>
                                                        <small class="text-n500">Profile picture is managed through your Google account</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Full Name -->
                                    <div class="col-md-6">
                                        <label for="full_name" class="form-label text-n700 fw-medium">Full Name *</label>
                                        <input type="text"
                                            class="form-control form-control-lg p-4 radius-12 border border-n200"
                                            id="full_name"
                                            name="full_name"
                                            value="<?= htmlspecialchars($user['full_name']) ?>"
                                            required>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <label for="email" class="form-label text-n700 fw-medium">Email Address *</label>
                                        <?php if ($user['login_type'] === 'google'): ?>
                                            <input type="email"
                                                class="form-control form-control-lg p-4 radius-12 border border-n200 disabled"
                                                value="<?= htmlspecialchars($user['email']) ?>"
                                                disabled>
                                            <?php if ($user['login_type'] === 'google'): ?>
                                                <small class="text-n500 mt-1 d-block">
                                                    <i class="ph ph-info me-1"></i>
                                                    This email is linked to your Google account
                                                </small>
                                            <?php endif; ?>
                                        <?php endif;
                                        if ($user['login_type'] === 'email') {
                                        ?>
                                            <input type="email"
                                                class="form-control form-control-lg p-4 radius-12 border border-n200"
                                                id="email"
                                                name="email"
                                                value="<?= htmlspecialchars($user['email']) ?>"
                                                required>
                                        <?php
                                        }
                                        ?>
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label text-n700 fw-medium">Phone Number</label>
                                        <input type="tel"
                                            class="form-control form-control-lg p-4 radius-12 border border-n200"
                                            id="phone"
                                            name="phone"
                                            value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                            placeholder="+91 (555) 123-4567">
                                    </div>

                                    <!-- Gender -->
                                    <div class="col-md-6">
                                        <label for="gender" class="form-label text-n700 fw-medium">Gender</label>
                                        <select class="form-select form-select-lg p-4 radius-12 border border-n200"
                                            id="gender"
                                            name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                            <option value="other" <?= $user['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>

                                    <!-- Account Information -->
                                    <div class="col-12 mt-5">
                                        <h5 class="text-n800 mb-4 pb-3 border-bottom">
                                            <i class="ph ph-info me-2 text-primary-600"></i>
                                            Account Information
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="info-item p-4 radius-12 bg-n50 mb-3">
                                                    <label class="text-n600 small fw-medium">Account Type</label>
                                                    <p class="text-n800 mb-0 fw-medium">
                                                        <?php if ($user['login_type'] === 'google'): ?>
                                                            <i class="ph ph-google-logo me-2 text-danger"></i>Google Account
                                                        <?php else: ?>
                                                            <i class="ph ph-envelope me-2 text-primary-600"></i>Email Account
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-item p-4 radius-12 bg-n50 mb-3">
                                                    <label class="text-n600 small fw-medium">Member Since</label>
                                                    <p class="text-n800 mb-0 fw-medium">
                                                        <?= date('F j, Y', strtotime($user['created_at'])) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Password Section -->

                                    <?php if ($user['login_type'] === 'email'): ?>
                                        <div class="col-12 mt-5">
                                            <h5 class="text-n800 mb-4 pb-3 border-bottom">
                                                <i class="ph ph-lock me-2 text-primary-600"></i>
                                                Password Settings
                                            </h5>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="current_password" class="form-label text-n700 fw-medium">Current Password</label>
                                            <div class="position-relative">
                                                <input type="password"
                                                    class="form-control form-control-lg p-4 radius-12 border border-n200 pe-5"
                                                    id="current_password"
                                                    name="current_password"
                                                    placeholder="Enter current password">
                                                <button type="button"
                                                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 text-n500 toggle-password"
                                                    data-target="current_password">
                                                    <i class="ph ph-eye"></i>
                                                </button>
                                            </div>
                                            <small class="text-n500 mt-1">Leave blank if you don't want to change password</small>
                                        </div>
                                        <div class="col-md-6"></div>


                                        <!-- New Password -->
                                        <div class="col-md-6">
                                            <label for="new_password" class="form-label text-n700 fw-medium">
                                                <?= ($user['login_type'] === 'google' && $user['password'] === null) ? 'Set Password' : 'New Password' ?>
                                            </label>
                                            <div class="position-relative">
                                                <input type="password"
                                                    class="form-control form-control-lg p-4 radius-12 border border-n200 pe-5"
                                                    id="new_password"
                                                    name="new_password"
                                                    placeholder="Enter new password">
                                                <button type="button"
                                                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 text-n500 toggle-password"
                                                    data-target="new_password">
                                                    <i class="ph ph-eye"></i>
                                                </button>
                                            </div>
                                            <small class="text-n500 mt-1">Minimum 6 characters</small>
                                        </div>

                                        <!-- Confirm Password -->
                                        <div class="col-md-6">
                                            <label for="confirm_password" class="form-label text-n700 fw-medium">Confirm Password</label>
                                            <div class="position-relative">
                                                <input type="password"
                                                    class="form-control form-control-lg p-4 radius-12 border border-n200 pe-5"
                                                    id="confirm_password"
                                                    name="confirm_password"
                                                    placeholder="Confirm new password">
                                                <button type="button"
                                                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 text-n500 toggle-password"
                                                    data-target="confirm_password">
                                                    <i class="ph ph-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Submit Button -->
                                    <div class="col-12 mt-5">
                                        <div class="d-flex gap-3 flex-wrap">
                                            <button type="submit" class="btn btn-primary btn-lg px-5 py-3 radius-12">
                                                <i class="ph ph-floppy-disk me-2"></i>
                                                Save Changes
                                            </button>
                                            <a href="account.php" class="btn btn-outline-secondary btn-lg px-5 py-3 radius-12">
                                                <i class="ph ph-arrow-left me-2"></i>
                                                Back to Dashboard
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .bg-primary-50 {
        background-color: rgba(235, 69, 59, 0.1);
    }

    .text-primary-600 {
        color: #eb453b;
    }

    .hover-bg-n50:hover {
        background-color: #f8fafc;
    }

    .dashboard-nav a.active,
    .dashboard-nav a:hover {
        background-color: #f1f5f9;
        color: #eb453b;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #eb453b;
        box-shadow: 0 0 0 0.2rem rgba(235, 69, 59, 0.25);
    }

    .btn-primary {
        background-color: #eb453b;
        border-color: #eb453b;
    }

    .btn-primary:hover {
        background-color: #d63d33;
        border-color: #d63d33;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    .toggle-password {
        border: none !important;
        background: none !important;
        padding: 0 !important;
    }

    .toggle-password:focus {
        box-shadow: none !important;
    }

    .profile-avatar {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .info-item {
        transition: all 0.3s ease;
    }

    .info-item:hover {
        background-color: #f1f5f9;
    }

    .form-control-lg,
    .form-select-lg {
        font-size: 1rem;
    }

    .border-n200 {
        border-color: #e5e7eb;
    }

    .bg-n0 {
        background-color: #ffffff;
    }

    .bg-n50 {
        background-color: #f9fafb;
    }

    .text-n500 {
        color: #6b7280;
    }

    .text-n600 {
        color: #4b5563;
    }

    .text-n700 {
        color: #374151;
    }

    .text-n800 {
        color: #1f2937;
    }

    .radius-8 {
        border-radius: 8px;
    }

    .radius-12 {
        border-radius: 12px;
    }

    .radius-16 {
        border-radius: 16px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    icon.className = 'ph ph-eye-slash';
                } else {
                    targetInput.type = 'password';
                    icon.className = 'ph ph-eye';
                }
            });
        });

        // Auto-dismiss alerts after 5 seconds
        // setTimeout(function() {
        //     const alerts = document.querySelectorAll('.alert');
        //     alerts.forEach(alert => {
        //         const bsAlert = new bootstrap.Alert(alert);
        //         bsAlert.close();
        //     });
        // }, 5000);
    });
</script>

<?php
$conn->close();
include_once './layout/footer.php';
?>