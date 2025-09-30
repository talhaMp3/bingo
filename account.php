<?php
session_start();
include_once './layout/header.php';

// Protect this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? "Guest";
$userEmail = $_SESSION['user_email'] ?? "";
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
                                    <a href="account.php" class="d-flex align-items-center gap-3 p-3 radius-8 text-n700 hover-bg-n50 active">
                                        <i class="ph ph-squares-four"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="orders.php" class="d-flex align-items-center gap-3 p-3 radius-8 bg-primary-50 text-primary-600 fw-medium ">
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
                        <!-- Welcome Message -->
                        <div class="welcome-card p-6 radius-16 border border-n100-1 bg-gradient-primary text-white mb-6">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="text-white mb-2">Hello <?= htmlspecialchars($userName) ?> 👋</h4>
                                    <p class="text-white-80 mb-0">
                                        <?php if ($userEmail): ?>
                                            (not <?= htmlspecialchars($userName) ?>? <a href="logout.php" class="text-white text-decoration-underline">Log out</a>)
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Dashboard Description -->
                        <div class="dashboard-description p-6 radius-16 border border-n100-1 bg-n0 mb-6">
                            <p class="text-n700 mb-0">
                                From your account dashboard you can view your <strong>recent orders</strong>,
                                manage your <strong>shipping and billing addresses</strong>, and
                                <strong>edit your password and account details</strong>.
                            </p>
                        </div>

                        <!-- Dashboard Grid -->
                        <div class="row g-4">
                            <!-- Orders -->
                            <div class="col-lg-4 col-md-6">
                                <a href="orders.php" class="dashboard-item d-block p-6 radius-16 border border-n100-1 bg-n0 text-center hover-shadow transition-all">
                                    <div class="dashboard-item-icon mb-4">
                                        <i class="ph ph-shopping-cart-simple text-4xl text-primary-600"></i>
                                    </div>
                                    <h6 class="text-n800 mb-0">Orders</h6>
                                </a>
                            </div>

                            <!-- Downloads -->
                            <div class="col-lg-4 col-md-6">
                                <a href="downloads.php" class="dashboard-item d-block p-6 radius-16 border border-n100-1 bg-n0 text-center hover-shadow transition-all">
                                    <div class="dashboard-item-icon mb-4">
                                        <i class="ph ph-download-simple text-4xl text-primary-600"></i>
                                    </div>
                                    <h6 class="text-n800 mb-0">Downloads</h6>
                                </a>
                            </div>

                            <!-- Addresses -->
                            <div class="col-lg-4 col-md-6">
                                <a href="addresses.php" class="dashboard-item d-block p-6 radius-16 border border-n100-1 bg-n0 text-center hover-shadow transition-all">
                                    <div class="dashboard-item-icon mb-4">
                                        <i class="ph ph-map-pin text-4xl text-primary-600"></i>
                                    </div>
                                    <h6 class="text-n800 mb-0">Addresses</h6>
                                </a>
                            </div>

                            <!-- Account Details -->
                            <div class="col-lg-4 col-md-6">
                                <a href="account-details.php" class="dashboard-item d-block p-6 radius-16 border border-n100-1 bg-n0 text-center hover-shadow transition-all">
                                    <div class="dashboard-item-icon mb-4">
                                        <i class="ph ph-user-circle text-4xl text-primary-600"></i>
                                    </div>
                                    <h6 class="text-n800 mb-0">Account details</h6>
                                </a>
                            </div>

                            <!-- Wishlist -->
                            <div class="col-lg-4 col-md-6">
                                <a href="wishlist.php" class="dashboard-item d-block p-6 radius-16 border border-n100-1 bg-n0 text-center hover-shadow transition-all">
                                    <div class="dashboard-item-icon mb-4">
                                        <i class="ph ph-heart text-4xl text-primary-600"></i>
                                    </div>
                                    <h6 class="text-n800 mb-0">Wishlist</h6>
                                </a>
                            </div>

                            <!-- Logout -->
                            <div class="col-lg-4 col-md-6">
                                <a href="logout.php" class="dashboard-item d-block p-6 radius-16 border border-n100-1 bg-n0 text-center hover-shadow transition-all">
                                    <div class="dashboard-item-icon mb-4">
                                        <i class="ph ph-sign-out text-4xl text-danger-600"></i>
                                    </div>
                                    <h6 class="text-n800 mb-0">Logout</h6>
                                </a>
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
</style>

<?php include_once './layout/footer.php'; ?>