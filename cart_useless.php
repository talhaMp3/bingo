<?php include 'layout/header.php'; ?>
<!-- main start -->
<main class="pt-12">
    <!-- hero section start -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3">Your Cart</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">Your Shopping Cart</a></li>
            </ul>
        </div>
    </section>
    <!-- hero section end -->

    <!-- cart section start -->
    <section class="cart-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120">
        <div class="container-fluid">
            <div class="text-center py-lg-5 py-3 bg-n20 mb-lg-15 mb-lg-10 mb-8">
                <span class="text-n100 text-sm">Please, hurry! Someone has placed an order on one of the items you
                    have in the cart. Products are limited, checkout within <span class="text-secondary2">00 m 00
                        s</span>
                </span>
            </div>
            <div class="row g-6">
                <div class="col-xl-8">
                    <!-- cart table -->
                    <div class="product-cart-table table-responsive mb-lg-8 mb-6">
                        <table class="table common-table">
                            <thead>
                                <tr>
                                    <th class="p-xxl-6 p-lg-4 p-2">Product</th>
                                    <th class="p-xxl-6 p-lg-4 p-2">Quantity</th>
                                    <th class="p-xxl-6 p-lg-4 p-2">Total</th>
                                    <th class="p-xxl-6 p-lg-4 p-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="p-xxl-6 p-lg-4 p-2">
                                        <div class="cart-card d-flex align-items-center gap-lg-6 gap-4">
                                            <div class="product-thumb">
                                                <img src="assets/images/review-img-1.png" alt="product"
                                                    class="w-100">
                                            </div>
                                            <div class="product-info">
                                                <span class="text-n100 text-base fw-medium d-block">Giant Defy
                                                    Advanced</span>
                                                <span class="d-block text-n100 text-sm">Green / S2</span>
                                                <span class="d-block text-secondary2 text-base my-lg-2 my-1">$299.00
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-xxl-6 p-lg-4 p-2">
                                        <div
                                            class="quantity d-inline-flex gap-lg-5 gap-3 align-items-center py-1 px-2 border border-n100-1 bg-n20 radius-4">
                                            <button class="quantityDecrement text-n100"><i
                                                    class="ph ph-minus"></i></button>
                                            <input type="text" value="1"
                                                class="quantityValue border-0 p-0 outline-0 bg-n20">
                                            <button class="quantityIncrement text-n100"><i
                                                    class="ph ph-plus"></i></button>
                                        </div>
                                    </td>
                                    <td class="p-xxl-6 p-lg-4 p-2">
                                        <span class="text-n100 text-sm d-block text-nowrap">$ 120.00</span>
                                    </td>
                                    <td class="p-xxl-6 p-lg-4 p-2">
                                        <button class=""><i class="ph ph-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <label class="input-checkbox pb-lg-8 pb-6 border-bottom border-n100-1">
                        <input type="checkbox" hidden>
                        <span class="checkbox"></span>
                        <span class="text-base text-n50">
                            Please wrap the product carefully. Fee is only $5.00. (You can choose or not)
                        </span>
                    </label>
                    <!-- You may also like -->
                    <div class="pt-120">
                        <h3 class="text-animation-word text-n100 pb-lg-8 pb-6">You may
                            also like</h3>
                        <div class="swiper you-also-like mb-lg-6 mb-4">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div
                                        class="product-card5 d-xs-flex d-grid align-items-center gap-lg-6 gap-4 p-lg-5 p-3 border border-n100-1">
                                        <div class="product-thumb">
                                            <img src="assets/images/product-small-1.png" alt="product"
                                                class="w-100">
                                        </div>
                                        <div class="product-info">
                                            <span class="text-n100 text-base fw-medium d-block">Merida Scultura
                                                Sukura</span>
                                            <span class="d-block text-secondary2 text-base my-lg-2 my-1">$299.00
                                            </span>
                                            <a href="#" class="text-decoration-underline hover-text-secondary2">ADD
                                                TO CART</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div
                                        class="product-card5 d-xs-flex d-grid align-items-center gap-lg-6 gap-4 p-lg-5 p-3 border border-n100-1">
                                        <div class="product-thumb">
                                            <img src="assets/images/product-small-2.png" alt="product"
                                                class="w-100">
                                        </div>
                                        <div class="product-info">
                                            <span class="text-n100 text-base fw-medium d-block">Cannondale
                                                Topstone</span>
                                            <span class="d-block text-secondary2 text-base my-lg-2 my-1">$299.00
                                            </span>
                                            <a href="#" class="text-decoration-underline hover-text-secondary2">ADD
                                                TO CART</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div
                                        class="product-card5 d-xs-flex d-grid align-items-center gap-lg-6 gap-4 p-lg-5 p-3 border border-n100-1">
                                        <div class="product-thumb">
                                            <img src="assets/images/product-small-1.png" alt="product"
                                                class="w-100">
                                        </div>
                                        <div class="product-info">
                                            <span class="text-n100 text-base fw-medium d-block">Merida Scultura
                                                Sukura</span>
                                            <span class="d-block text-secondary2 text-base my-lg-2 my-1">$299.00
                                            </span>
                                            <a href="#" class="text-decoration-underline hover-text-secondary2">ADD
                                                TO CART</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div
                                        class="product-card5 d-xs-flex d-grid align-items-center gap-lg-6 gap-4 p-lg-5 p-3 border border-n100-1">
                                        <div class="product-thumb">
                                            <img src="assets/images/product-small-2.png" alt="product"
                                                class="w-100">
                                        </div>
                                        <div class="product-info">
                                            <span class="text-n100 text-base fw-medium d-block">Cannondale
                                                Topstone</span>
                                            <span class="d-block text-secondary2 text-base my-lg-2 my-1">$299.00
                                            </span>
                                            <a href="#" class="text-decoration-underline hover-text-secondary2">ADD
                                                TO CART</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div
                                        class="product-card5 d-xs-flex d-grid align-items-center gap-lg-6 gap-4 p-lg-5 p-3 border border-n100-1">
                                        <div class="product-thumb">
                                            <img src="assets/images/product-small-1.png" alt="product"
                                                class="w-100">
                                        </div>
                                        <div class="product-info">
                                            <span class="text-n100 text-base fw-medium d-block">Merida Scultura
                                                Sukura</span>
                                            <span class="d-block text-secondary2 text-base my-lg-2 my-1">$299.00
                                            </span>
                                            <a href="#" class="text-decoration-underline hover-text-secondary2">ADD
                                                TO CART</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div
                                        class="product-card5 d-xs-flex d-grid align-items-center gap-lg-6 gap-4 p-lg-5 p-3 border border-n100-1">
                                        <div class="product-thumb">
                                            <img src="assets/images/product-small-2.png" alt="product"
                                                class="w-100">
                                        </div>
                                        <div class="product-info">
                                            <span class="text-n100 text-base fw-medium d-block">Cannondale
                                                Topstone</span>
                                            <span class="d-block text-secondary2 text-base my-lg-2 my-1">$299.00
                                            </span>
                                            <a href="#" class="text-decoration-underline hover-text-secondary2">ADD
                                                TO CART</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div
                                        class="product-card5 d-xs-flex d-grid align-items-center gap-lg-6 gap-4 p-lg-5 p-3 border border-n100-1">
                                        <div class="product-thumb">
                                            <img src="assets/images/product-small-1.png" alt="product"
                                                class="w-100">
                                        </div>
                                        <div class="product-info">
                                            <span class="text-n100 text-base fw-medium d-block">Merida Scultura
                                                Sukura</span>
                                            <span class="d-block text-secondary2 text-base my-lg-2 my-1">$299.00
                                            </span>
                                            <a href="#" class="text-decoration-underline hover-text-secondary2">ADD
                                                TO CART</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div
                                        class="product-card5 d-xs-flex d-grid align-items-center gap-lg-6 gap-4 p-lg-5 p-3 border border-n100-1">
                                        <div class="product-thumb">
                                            <img src="assets/images/product-small-2.png" alt="product"
                                                class="w-100">
                                        </div>
                                        <div class="product-info">
                                            <span class="text-n100 text-base fw-medium d-block">Cannondale
                                                Topstone</span>
                                            <span class="d-block text-secondary2 text-base my-lg-2 my-1">$299.00
                                            </span>
                                            <a href="#" class="text-decoration-underline hover-text-secondary2">ADD
                                                TO CART</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="you-also-like-pagination d-flex justify-content-center"></div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="p-xxl-10 p-md-6 p-4 bg-n20 border border-n100-1 mb-lg-8 mb-6">
                        <!-- free shipping progress -->
                        <div class="free-shipping-progress">
                            <div class="progress-bar">
                                <div class="progress-bar-inner" style="width: 70%;">
                                    <span class="car-icon">
                                        <i class="ph ph-truck"></i>
                                    </span>
                                </div>
                            </div>
                            <!-- <span class="text-n100 text-sm fw-normal">Spend $100.00 more to enjoy <span class="text-secondary2">FREE
                             SHIPPING!</span></span> -->
                            <span class="text-n100 text-sm fw-normal">Spend $53.00 more to enjoy <span
                                    class="text-secondary2">FREE
                                    SHIPPING!</span></span>
                            <!-- <span class="text-n100 text-sm fw-normal">Congratulations! You've got <span class="text-secondary2">FREE
                             SHIPPING!</span></span> -->
                        </div>
                        <span class="border-bottom border-n100-1 w-100 d-block my-lg-6 my-4"></span>
                        <!-- note form start-->
                        <form action="#">
                            <span class="d-block text-n100 text-base fw-medium mb-lg-4 mb-2">
                                Add order
                                note</span>
                            <textarea class="note-form border-n100-1 p-4 w-100 focus-secondary2" rows="3"
                                placeholder="Add Note"></textarea>
                            <span class="d-block text-n100 text-base fw-medium mb-lg-4 mb-2 mt-lg-6 mt-4">Estimate
                                Shipping</span>

                            <div class="d-grid gap-3 mb-lg-5 mb-3">
                                <label>Country/region</label>
                                <div
                                    class="select d-flex align-items-center  border border-n100-1 focus-secondary2 bg-n0 position-relative">
                                    <select class="w-100 border-0 py-lg-4 py-2 px-md-6 px-4 bg-n0">
                                        <option value="1">United States</option>
                                        <option value="2">Canada</option>
                                        <option value="3">United Kingdom</option>
                                    </select>
                                    <span class="select-arrow"></span>
                                </div>
                            </div>
                            <div class="d-grid gap-3 mb-lg-5 mb-3">
                                <label>Province</label>
                                <div
                                    class="select d-flex align-items-center  border border-n100-1 focus-secondary2 bg-n0 position-relative">
                                    <select class="w-100 border-0 py-lg-4 py-2 px-md-6 px-4 bg-n0">
                                        <option value="1">Alabama</option>
                                        <option value="2">California</option>
                                        <option value="3">New York</option>
                                    </select>
                                    <span class="select-arrow"></span>
                                </div>
                            </div>
                            <div class="d-grid gap-3 mb-lg-5 mb-3">
                                <label>Postal/ZIP code</label>
                                <input type="text"
                                    class="py-lg-4 py-2 px-md-6 px-4 border border-n100-1 focus-secondary2 bg-n0">
                            </div>
                            <button type="button"
                                class="text-base fw-medium py-lg-4 py-2 px-xl-8 px-md-6 px-4 bg-n100 text-n0 hover-bg-n0 hover-text-n100 w-100 border border-n100">ESTIMATE</button>

                            <span
                                class="border-bottom border-n100-1 w-100 d-block mb-lg-6 mb-4 mt-xl-9 mt-md-7 mt-5"></span>
                            <div class="d-grid gap-2 mb-lg-6 mb-4">
                                <div class="d-between">
                                    <span class="text-n100 text-base fw-medium">Subtotal</span>
                                    <span class="text-n100 text-base fw-medium">$299.00</span>
                                </div>
                                <span class="text-n50 text-sm">Taxes and <a href="shipping-delivery.html"
                                        class="text-decoration-underline">shipping</a> calculated at
                                    checkout</span>
                            </div>
                            <label class="input-checkbox mb-lg-6 mb-4">
                                <input type="checkbox" hidden>
                                <span class="checkbox"></span>
                                <span class="text-base text-n50">I agree with <a href="terms-conditions.html"
                                        class="text-decoration-underline">Terms &
                                        Conditions</a></span>
                            </label>
                            <div class="d-grid gap-lg-4 gap-2">
                                <a href="checkout.html"
                                    class="d-block text-center text-base fw-medium py-lg-4 py-2 px-xl-8 px-md-6 px-4 bg-n0 text-n100 w-100 border border-n100 hover-bg-n100 hover-text-n0">CHECKOUT</a>
                                <button type="submit"
                                    class="btn-secondary radius-unset py-lg-4 py-2 px-xl-8 px-md-6 px-4">
                                    <img class="w-100 max-w-100px" src="assets/images/paypal-3.png" alt="paypal">
                                </button>
                            </div>
                        </form>
                        <!-- note form end -->
                    </div>
                    <div class="d-grid gap-lg-4 gap-2">
                        <span class="text-n100 text-base fw-medium text-center">GUARANTEED SAFE CHECKOUT:</span>
                        <div class="d-center gap-2">
                            <div>
                                <img class="w-100 max-w-100px" src="assets/images/visa-2.png" alt="visa">
                            </div>
                            <div>
                                <img class="w-100 max-w-100px" src="assets/images/master.png" alt="visa">
                            </div>
                            <div>
                                <img class="w-100 max-w-100px" src="assets/images/express.png" alt="visa">
                            </div>
                            <div>
                                <img class="w-100 max-w-100px" src="assets/images/paypal-2.png" alt="visa">
                            </div>
                            <div>
                                <img class="w-100 max-w-100px" src="assets/images/cirrus.png" alt="visa">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- related products section start -->
    <section class="product-section px-xl-20 px-lg-10 px-sm-7 pb-120">
        <div class="container-fluid">
            <div class="row g-6 justify-content-between align-items-end mb-lg-15 mb-md-10 mb-8">
                <div class="col-lg-6 col-md-9">
                    <h2 class="text-animation-word display-four text-n100 text-uppercase">RELATED PRODUCTS</h2>
                </div>
            </div>
            <div class="row g-0">
                <div class="col-lg-4 col-xs-6">
                    <!-- product item -->
                    <div
                        class="product-card2 position-relative p-xl-10 p-lg-8 p-6 bg-n0 border border-n100-5 box-style box-n20 card-tilt animate-box">
                        <div
                            class="product-type py-lg-3 py-2 ps-lg-4 ps-2 pe-lg-6 pe-4 bg-secondary2 position-absolute top-0 start-0 parallelogram-path">
                            <span class="text-sm fw-medium text-n0">New</span>
                        </div>
                        <div class="product-thumb-wrapper position-relative">
                            <button
                                class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n0 position-absolute top-0 right-0 z-3 tooltip-btn tooltip-left"
                                data-tooltip="Add to wishlist">
                                <i class="ph ph-heart"></i>
                            </button>
                            <div class="product-thumb reveal-left hover-cursor" data-hover-text="View Product">
                                <a href="shop-details.html" class="product-thumb-link d-block">
                                    <img class="w-100" src="assets/images/product-1.png" alt="product thumb">
                                </a>
                            </div>
                        </div>
                        <span class="d-block h-1px w-100 bg-n100-1 mb-lg-6 mb-4 mt-lg-10 mt-6"></span>
                        <div class="product-info-wrapper">
                            <div class="mb-xxl-7 mb-md-5 mb-3">
                                <a href="shop-details.html">
                                    <h4 class="text-animation-line text-n100 mb-2 hover-text-secondary2">City
                                        Commuter
                                    </h4>
                                </a>
                                <span class="text-sm fw-normal text-n50">Enduro</span>
                            </div>
                            <div class="d-between flex-wrap gap-4">
                                <div class="d-grid">
                                    <span class="text-sm fw-normal text-n50 text-decoration-underline">$21,599.00
                                        USD</span>
                                    <span class="text-xl fw-semibold text-secondary2">$ 14,599.00 USD</span>
                                </div>
                                <a href="#" class="outline-btn text-n100 fw-medium box-style box-secondary2">ADD
                                    TO CART </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-xs-6">
                    <!-- product item -->
                    <div
                        class="product-card2 position-relative p-xl-10 p-lg-8 p-6 bg-n0 border border-n100-5 box-style box-n20 card-tilt animate-box">
                        <div class="product-thumb-wrapper position-relative">
                            <button
                                class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n0 position-absolute top-0 right-0 z-3 tooltip-btn tooltip-left"
                                data-tooltip="Add to wishlist">
                                <i class="ph ph-heart"></i>
                            </button>
                            <div class="product-thumb reveal-left hover-cursor" data-hover-text="View Product">
                                <a href="shop-details.html" class="product-thumb-link d-block">
                                    <img class="w-100" src="assets/images/product-2.png" alt="product thumb">
                                </a>
                            </div>
                        </div>
                        <span class="d-block h-1px w-100 bg-n100-1 mb-lg-6 mb-4 mt-lg-10 mt-6"></span>
                        <div class="product-info-wrapper">
                            <div class="mb-xxl-7 mb-md-5 mb-3">
                                <a href="shop-details.html">
                                    <h4 class="text-animation-line text-n100 mb-2 hover-text-secondary2">
                                        Urban Explorer
                                    </h4>
                                </a>
                                <span class="text-sm fw-normal text-n50">Enduro</span>
                            </div>
                            <div class="d-between flex-wrap gap-4">
                                <div class="d-grid">
                                    <span class="text-sm fw-normal text-n50 text-decoration-underline">$21,599.00
                                        USD</span>
                                    <span class="text-xl fw-semibold text-secondary2">$ 14,599.00 USD</span>
                                </div>
                                <a href="#" class="outline-btn text-n100 fw-medium box-style box-secondary2">ADD
                                    TO CART </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-xs-6">
                    <!-- product item -->
                    <div
                        class="product-card2 position-relative p-xl-10 p-lg-8 p-6 bg-n0 border border-n100-5 box-style box-n20 card-tilt animate-box">
                        <div
                            class="product-type py-lg-3 py-2 ps-lg-4 ps-2 pe-lg-6 pe-4 bg-secondary2 position-absolute top-0 start-0 parallelogram-path">
                            <span class="text-sm fw-medium text-n0">New</span>
                        </div>
                        <div class="product-thumb-wrapper position-relative">
                            <button
                                class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n0 position-absolute top-0 right-0 z-3 tooltip-btn tooltip-left"
                                data-tooltip="Add to wishlist">
                                <i class="ph ph-heart"></i>
                            </button>
                            <div class="product-thumb reveal-left hover-cursor" data-hover-text="View Product">
                                <a href="shop-details.html" class="product-thumb-link d-block">
                                    <img class="w-100" src="assets/images/product-3.png" alt="product thumb">
                                </a>
                            </div>
                        </div>
                        <span class="d-block h-1px w-100 bg-n100-1 mb-lg-6 mb-4 mt-lg-10 mt-6"></span>
                        <div class="product-info-wrapper">
                            <div class="mb-xxl-7 mb-md-5 mb-3">
                                <a href="shop-details.html">
                                    <h4 class="text-animation-line text-n100 mb-2 hover-text-secondary2">
                                        Urban Wanderer
                                    </h4>
                                </a>
                                <span class="text-sm fw-normal text-n50">Enduro</span>
                            </div>
                            <div class="d-between flex-wrap gap-4">
                                <div class="d-grid">
                                    <span class="text-sm fw-normal text-n50 text-decoration-underline">$21,599.00
                                        USD</span>
                                    <span class="text-xl fw-semibold text-secondary2">$ 14,599.00 USD</span>
                                </div>
                                <a href="#" class="outline-btn text-n100 fw-medium box-style box-secondary2">ADD
                                    TO CART </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-xs-6">
                    <!-- product item -->
                    <div
                        class="product-card2 position-relative p-xl-10 p-lg-8 p-6 bg-n0 border border-n100-5 box-style box-n20 card-tilt animate-box">
                        <div class="product-thumb-wrapper position-relative">
                            <button
                                class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n0 position-absolute top-0 right-0 z-3 tooltip-btn tooltip-left"
                                data-tooltip="Add to wishlist">
                                <i class="ph ph-heart"></i>
                            </button>
                            <div class="product-thumb reveal-left hover-cursor" data-hover-text="View Product">
                                <a href="shop-details.html" class="product-thumb-link d-block">
                                    <img class="w-100" src="assets/images/product-4.png" alt="product thumb">
                                </a>
                            </div>
                        </div>
                        <span class="d-block h-1px w-100 bg-n100-1 mb-lg-6 mb-4 mt-lg-10 mt-6"></span>
                        <div class="product-info-wrapper">
                            <div class="mb-xxl-7 mb-md-5 mb-3">
                                <a href="shop-details.html">
                                    <h4 class="text-animation-line text-n100 mb-2 hover-text-secondary2">
                                        Electro Cruise
                                    </h4>
                                </a>
                                <span class="text-sm fw-normal text-n50">Enduro</span>
                            </div>
                            <div class="d-between flex-wrap gap-4">
                                <div class="d-grid">
                                    <span class="text-sm fw-normal text-n50 text-decoration-underline">$21,599.00
                                        USD</span>
                                    <span class="text-xl fw-semibold text-secondary2">$ 14,599.00 USD</span>
                                </div>
                                <a href="#" class="outline-btn text-n100 fw-medium box-style box-secondary2">ADD
                                    TO CART </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-xs-6">
                    <!-- product item -->
                    <div
                        class="product-card2 position-relative p-xl-10 p-lg-8 p-6 bg-n0 border border-n100-5 box-style box-n20 card-tilt animate-box">
                        <div class="product-thumb-wrapper position-relative">
                            <button
                                class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n0 position-absolute top-0 right-0 z-3 tooltip-btn tooltip-left"
                                data-tooltip="Add to wishlist">
                                <i class="ph ph-heart"></i>
                            </button>
                            <div class="product-thumb reveal-left hover-cursor" data-hover-text="View Product">
                                <a href="shop-details.html" class="product-thumb-link d-block">
                                    <img class="w-100" src="assets/images/product-5.png" alt="product thumb">
                                </a>
                            </div>
                        </div>
                        <span class="d-block h-1px w-100 bg-n100-1 mb-lg-6 mb-4 mt-lg-10 mt-6"></span>
                        <div class="product-info-wrapper">
                            <div class="mb-xxl-7 mb-md-5 mb-3">
                                <a href="shop-details.html">
                                    <h4 class="text-animation-line text-n100 mb-2 hover-text-secondary2">
                                        Watt Wheels
                                    </h4>
                                </a>
                                <span class="text-sm fw-normal text-n50">Enduro</span>
                            </div>
                            <div class="d-between flex-wrap gap-4">
                                <div class="d-grid">
                                    <span class="text-sm fw-normal text-n50 text-decoration-underline">$21,599.00
                                        USD</span>
                                    <span class="text-xl fw-semibold text-secondary2">$ 14,599.00 USD</span>
                                </div>
                                <a href="#" class="outline-btn text-n100 fw-medium box-style box-secondary2">ADD
                                    TO CART </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-xs-6">
                    <!-- product item -->
                    <div
                        class="product-card2 position-relative p-xl-10 p-lg-8 p-6 bg-n0 border border-n100-5 box-style box-n20 card-tilt animate-box">
                        <div
                            class="product-type py-lg-3 py-2 ps-lg-4 ps-2 pe-lg-6 pe-4 bg-secondary2 position-absolute top-0 start-0 parallelogram-path">
                            <span class="text-sm fw-medium text-n0">New</span>
                        </div>
                        <div class="product-thumb-wrapper position-relative">
                            <button
                                class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n0 position-absolute top-0 right-0 z-3 tooltip-btn tooltip-left"
                                data-tooltip="Add to wishlist">
                                <i class="ph ph-heart"></i>
                            </button>
                            <div class="product-thumb reveal-left hover-cursor" data-hover-text="View Product">
                                <a href="shop-details.html" class="product-thumb-link d-block">
                                    <img class="w-100" src="assets/images/product-6.png" alt="product thumb">
                                </a>
                            </div>
                        </div>
                        <span class="d-block h-1px w-100 bg-n100-1 mb-lg-6 mb-4 mt-lg-10 mt-6"></span>
                        <div class="product-info-wrapper">
                            <div class="mb-xxl-7 mb-md-5 mb-3">
                                <a href="shop-details.html">
                                    <h4 class="text-animation-line text-n100 mb-2 hover-text-secondary2">
                                        Electro Boost
                                    </h4>
                                </a>
                                <span class="text-sm fw-normal text-n50">Enduro</span>
                            </div>
                            <div class="d-between flex-wrap gap-4">
                                <div class="d-grid">
                                    <span class="text-sm fw-normal text-n50 text-decoration-underline">$21,599.00
                                        USD</span>
                                    <span class="text-xl fw-semibold text-secondary2">$ 14,599.00 USD</span>
                                </div>
                                <a href="#" class="outline-btn text-n100 fw-medium box-style box-secondary2">ADD
                                    TO CART </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- related products section end -->

    <!-- gallery slider -->
    <!-- gallery slider start -->
    <div class="overflow-hidden position-relative z-0">
        <div class="swiper gallery-slider">
            <div class="swiper-wrapper align-items-center z-1">
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-1.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-2.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-3.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-4.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-5.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-6.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-7.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-8.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-9.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- gallery slider end -->
</main>
<!-- main end -->

<?php include 'layout/footer.php'; ?>