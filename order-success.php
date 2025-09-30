<?php
session_start();
include_once './layout/header.php';
?>

<main class="pt-12">

    <!-- hero section start -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3">Order Success</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">Order Success</a></li>
            </ul>
        </div>
    </section>
    <!-- hero section end -->

    <!-- order success section start -->
    <section class="login-section pt-120 pb-120 px-xl-20 px-lg-10 px-sm-7">
        <div class="container-fluid">
            <div class="row g-6 justify-content-center">
                <div class="col-xl-6 col-lg-8 col-md-10">
                    <div class="register-form p-xl-15 p-lg-10 p-md-8 p-6 radius-16 border border-n100-1 bg-n20 text-center">

                        <!-- Success Icon -->
                        <div class="mb-lg-12 mb-8">
                            <span class="d-inline-flex justify-content-center align-items-center rounded-circle bg-success text-white"
                                style="width:80px; height:80px; font-size:36px;">
                                <i class="ph ph-check"></i>
                            </span>
                        </div>

                        <!-- Success Message -->
                        <h2 class="text-n100 mb-lg-6 mb-4">Thank You for Your Order!</h2>
                        <p class="text-n50 mb-lg-8 mb-6">
                            Your order has been placed successfully. A confirmation email has been sent to your registered email address.
                            We’ll notify you once your items are shipped.
                        </p>

                        <!-- Order Details -->
                        <div class="border radius-12 bg-n0 py-lg-6 py-4 px-lg-8 px-4 mb-lg-8 mb-6">
                            <p class="mb-2 text-n100"><strong>Order ID:</strong> #<?= rand(100000, 999999); ?></p>
                            <p class="mb-0 text-n100"><strong>Date:</strong> <?= date("d M, Y"); ?></p>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="orders.php" class="btn-secondary py-lg-3 py-2 px-lg-6 px-4 radius-8">View Orders</a>
                            <a href="index.php" class="btn-outline-secondary py-lg-3 py-2 px-lg-6 px-4 radius-8">Continue Shopping</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?= include_once './layout/footer.php'; ?>