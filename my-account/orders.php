<base href="/bingo/" />
<?php
session_start();
include_once '../include/connection.php';
include_once '../layout/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
<!-- main start -->
<main class="pt-lg-6">
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(../assets/images/inner-page-banner.png)">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3">My Orders</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">My Orders</a></li>
            </ul>
        </div>
    </section>

    <section class="product-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120">
        <div class="container-fluid">
            <!-- Professional Header Section -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-lg header-card">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h2 class="mb-2 fw-bold text-white">Order Management Center</h2>
                                    <p class="mb-0 text-light-subtle">Monitor your purchases, track deliveries, and manage returns all in one place</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="d-flex align-items-center justify-content-md-end gap-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 fw-bold text-white">₹45,690</div>
                                            <small class="text-light-subtle">Lifetime Value</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="h4 mb-0 fw-bold text-white">24</div>
                                            <small class="text-light-subtle">Total Orders</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters & Search -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 filter-card">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-secondary-custom">Filter by Status</label>
                                    <select class="form-select custom-select">
                                        <option value="">All Orders</option>
                                        <option value="pending">Pending Payment</option>
                                        <option value="confirmed">Order Confirmed</option>
                                        <option value="processing">Processing</option>
                                        <option value="shipped">Shipped</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="returned">Returned</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-secondary-custom">Date Range</label>
                                    <select class="form-select custom-select">
                                        <option value="">All Time</option>
                                        <option value="7">Last 7 Days</option>
                                        <option value="30">Last 30 Days</option>
                                        <option value="90">Last 3 Months</option>
                                        <option value="365">Last Year</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-secondary-custom">Search Orders</label>
                                    <div class="input-group custom-input-group">
                                        <span class="input-group-text custom-input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control custom-input" placeholder="Order ID, product name, or tracking number">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-custom-outline w-100">
                                        <i class="fas fa-filter me-2"></i>Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Statistics Cards -->
            <div class="row mb-5">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 h-100 stat-card stat-card-primary">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon stat-icon-primary me-3">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0 fw-bold text-dark-custom">24</div>
                                    <p class="text-secondary-custom small mb-0">Total Orders</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 h-100 stat-card stat-card-success">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon stat-icon-success me-3">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0 fw-bold text-dark-custom">18</div>
                                    <p class="text-secondary-custom small mb-0">Completed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 h-100 stat-card stat-card-warning">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon stat-icon-warning me-3">
                                    <i class="fas fa-truck-fast"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0 fw-bold text-dark-custom">4</div>
                                    <p class="text-secondary-custom small mb-0">In Transit</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 h-100 stat-card stat-card-info">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon stat-icon-info me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0 fw-bold text-dark-custom">2</div>
                                    <p class="text-secondary-custom small mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Orders List -->
            <div class="row">
                <div class="col-12">
                    <!-- Order Item 1 - Delivered -->
                    <div class="card border-0 mb-4 order-card">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <h6 class="mb-0 fw-bold text-dark-custom">Order #ORD-2025-001</h6>
                                        <span class="badge badge-success-custom">
                                            <i class="fas fa-check-circle me-1"></i>Delivered
                                        </span>
                                    </div>
                                    <small class="text-secondary-custom">Placed on July 25, 2025 • Delivered on July 27, 2025</small>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="h5 mb-0 fw-bold text-success-custom">₹2,999</div>
                                    <small class="text-secondary-custom">Payment: Credit Card</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <img src="https://placehold.co/100x100/f8f9fc/6c757d?text=Product"
                                                alt="Product" class="rounded custom-border" style="width: 100px; height: 100px; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 fw-semibold text-dark-custom">Premium Wireless Headphones - Noise Cancelling</h6>
                                            <p class="text-secondary-custom small mb-2">Brand: TechSound Pro | Model: WH-1000XM5 | Color: Midnight Black</p>
                                            <div class="d-flex gap-3 text-sm">
                                                <span class="text-secondary-custom"><strong>Qty:</strong> 1</span>
                                                <span class="text-secondary-custom"><strong>SKU:</strong> TSP-WH-001</span>
                                                <span class="text-secondary-custom"><strong>Warranty:</strong> 2 Years</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column gap-2 h-100 justify-content-center">
                                        <button class="btn btn-primary-custom btn-sm">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </button>
                                        <button class="btn btn-outline-custom btn-sm">
                                            <i class="fas fa-download me-2"></i>Download Invoice
                                        </button>
                                        <button class="btn btn-outline-warning-custom btn-sm">
                                            <i class="fas fa-star me-2"></i>Write Review
                                        </button>
                                        <button class="btn btn-outline-info-custom btn-sm">
                                            <i class="fas fa-redo me-2"></i>Buy Again
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Item 2 - Processing -->
                    <div class="card border-0 mb-4 order-card">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <h6 class="mb-0 fw-bold text-dark-custom">Order #ORD-2025-002</h6>
                                        <span class="badge badge-warning-custom">
                                            <i class="fas fa-cog fa-spin me-1"></i>Processing
                                        </span>
                                    </div>
                                    <small class="text-secondary-custom">Placed on July 26, 2025 • Expected delivery: July 30, 2025</small>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="h5 mb-0 fw-bold text-primary-custom">₹8,499</div>
                                    <small class="text-secondary-custom">Payment: UPI</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <img src="https://placehold.co/100x100/f8f9fc/6c757d?text=Watch"
                                                alt="Product" class="rounded custom-border" style="width: 100px; height: 100px; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 fw-semibold text-dark-custom">Smart Fitness Watch Series 9 - GPS + Cellular</h6>
                                            <p class="text-secondary-custom small mb-2">Brand: FitTech | Size: 45mm | Color: Space Gray Aluminum</p>
                                            <div class="d-flex gap-3 text-sm">
                                                <span class="text-secondary-custom"><strong>Qty:</strong> 1</span>
                                                <span class="text-secondary-custom"><strong>SKU:</strong> FT-SW-009</span>
                                                <span class="text-secondary-custom"><strong>Warranty:</strong> 1 Year</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column gap-2 h-100 justify-content-center">
                                        <button class="btn btn-primary-custom btn-sm">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </button>
                                        <button class="btn btn-outline-custom btn-sm">
                                            <i class="fas fa-route me-2"></i>Track Package
                                        </button>
                                        <button class="btn btn-danger-custom btn-sm">
                                            <i class="fas fa-times me-2"></i>Cancel Order
                                        </button>
                                        <button class="btn btn-outline-info-custom btn-sm">
                                            <i class="fas fa-edit me-2"></i>Modify Order
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Professional Progress Tracker -->
                            <hr class="my-4 custom-divider">
                            <div class="order-progress">
                                <div class="row text-center">
                                    <div class="col-3">
                                        <div class="progress-step completed">
                                            <div class="step-circle">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div class="step-label">
                                                <div class="fw-semibold small text-dark-custom">Order Placed</div>
                                                <div class="text-secondary-custom x-small">July 26, 10:30 AM</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="progress-step completed">
                                            <div class="step-circle">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div class="step-label">
                                                <div class="fw-semibold small text-dark-custom">Payment Confirmed</div>
                                                <div class="text-secondary-custom x-small">July 26, 10:32 AM</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="progress-step active">
                                            <div class="step-circle">
                                                <i class="fas fa-cogs"></i>
                                            </div>
                                            <div class="step-label">
                                                <div class="fw-semibold small text-dark-custom">Processing</div>
                                                <div class="text-secondary-custom x-small">In Progress</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="progress-step">
                                            <div class="step-circle">
                                                <i class="fas fa-shipping-fast"></i>
                                            </div>
                                            <div class="step-label">
                                                <div class="fw-semibold small text-dark-custom">Shipped</div>
                                                <div class="text-secondary-custom x-small">Pending</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress-line">
                                    <div class="progress-fill" style="width: 66%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Item 3 - Shipped with Multiple Items -->
                    <div class="card border-0 mb-4 order-card">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <h6 class="mb-0 fw-bold text-dark-custom">Order #ORD-2025-003</h6>
                                        <span class="badge badge-info-custom">
                                            <i class="fas fa-truck me-1"></i>Shipped
                                        </span>
                                    </div>
                                    <small class="text-secondary-custom">Placed on July 24, 2025 • Expected delivery: Today</small>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="h5 mb-0 fw-bold text-primary-custom">₹4,998</div>
                                    <small class="text-secondary-custom">Payment: Debit Card • 2 Items</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <!-- Multiple Items Display -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="d-flex gap-3 mb-3">
                                        <div class="flex-shrink-0">
                                            <img src="https://placehold.co/80x80/f8f9fc/6c757d?text=Speaker"
                                                alt="Product" class="rounded custom-border" style="width: 80px; height: 80px; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold text-dark-custom">Bluetooth Portable Speaker</h6>
                                            <p class="text-secondary-custom small mb-1">Brand: SoundWave | Model: BT-360 | Color: Ocean Blue</p>
                                            <div class="d-flex gap-3 text-sm">
                                                <span class="text-secondary-custom"><strong>Qty:</strong> 1</span>
                                                <span class="text-secondary-custom"><strong>Price:</strong> ₹2,499</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <img src="https://placehold.co/80x80/f8f9fc/6c757d?text=Case"
                                                alt="Product" class="rounded custom-border" style="width: 80px; height: 80px; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold text-dark-custom">Premium Carrying Case</h6>
                                            <p class="text-secondary-custom small mb-1">Brand: SoundWave | Type: Hard Shell | Color: Black</p>
                                            <div class="d-flex gap-3 text-sm">
                                                <span class="text-secondary-custom"><strong>Qty:</strong> 1</span>
                                                <span class="text-secondary-custom"><strong>Price:</strong> ₹2,499</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tracking Info -->
                            <div class="alert alert-info-custom border-0 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2 text-info-custom"></i>
                                    <div class="text-dark-custom">
                                        <strong>Tracking ID:</strong> TRK789456123 |
                                        <strong>Courier:</strong> FastShip Express |
                                        <strong>ETA:</strong> Today by 6:00 PM
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-primary-custom btn-sm">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </button>
                                <button class="btn btn-outline-custom btn-sm">
                                    <i class="fas fa-map-marker-alt me-2"></i>Live Tracking
                                </button>
                                <button class="btn btn-outline-info-custom btn-sm">
                                    <i class="fas fa-phone me-2"></i>Contact Courier
                                </button>
                                <button class="btn btn-outline-warning-custom btn-sm">
                                    <i class="fas fa-calendar me-2"></i>Reschedule
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <div class="text-secondary-custom">
                            Showing 1-3 of 24 orders
                        </div>
                        <nav aria-label="Orders pagination">
                            <ul class="pagination mb-0 custom-pagination">
                                <li class="page-item disabled">
                                    <a class="page-link custom-page-link" href="#" tabindex="-1">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link custom-page-link custom-page-active" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link custom-page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link custom-page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <span class="page-link custom-page-link">...</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link custom-page-link" href="#">8</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link custom-page-link" href="#">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<!-- main end -->

<style>
    /* Custom Color Palette */
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #7f8c8d;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
        --info-color: #3498db;
        --light-bg: #f8f9fc;
        --dark-text: #2c3e50;
        --medium-text: #5a6c7d;
        --light-text: #95a5a6;
        --border-color: #e8eaed;
        --shadow-light: rgba(44, 62, 80, 0.08);
        --shadow-medium: rgba(44, 62, 80, 0.12);
        --gradient-primary: linear-gradient(135deg, #000000 0%, #34495e 100%);
    }

    /* Custom Text Colors */
    .text-dark-custom {
        color: var(--dark-text) !important;
    }

    .text-secondary-custom {
        color: var(--medium-text) !important;
    }

    .text-light-subtle {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .text-primary-custom {
        color: var(--primary-color) !important;
    }

    .text-success-custom {
        color: var(--success-color) !important;
    }

    .text-warning-custom {
        color: var(--warning-color) !important;
    }

    .text-danger-custom {
        color: var(--danger-color) !important;
    }

    .text-info-custom {
        color: var(--info-color) !important;
    }

    /* Custom Background Colors */
    .bg-light-custom {
        background-color: var(--light-bg) !important;
    }

    /* Header Card */
    .header-card {
        background: var(--gradient-primary);
        box-shadow: 0 8px 32px var(--shadow-medium);
    }

    /* Filter Card */
    .filter-card {
        background: white;
        box-shadow: 0 4px 16px var(--shadow-light);
        border: 1px solid var(--border-color);
    }

    /* Statistics Cards */
    .stat-card {
        background: white;
        box-shadow: 0 4px 16px var(--shadow-light);
        border: 1px solid var(--border-color);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 32px var(--shadow-medium);
    }

    .stat-card-primary:hover {
        border-left-color: var(--primary-color);
    }

    .stat-card-success:hover {
        border-left-color: var(--success-color);
    }

    .stat-card-warning:hover {
        border-left-color: var(--warning-color);
    }

    .stat-card-info:hover {
        border-left-color: var(--info-color);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .stat-icon-primary {
        background: rgba(44, 62, 80, 0.1);
        color: var(--primary-color);
    }

    .stat-icon-success {
        background: rgba(39, 174, 96, 0.1);
        color: var(--success-color);
    }

    .stat-icon-warning {
        background: rgba(243, 156, 18, 0.1);
        color: var(--warning-color);
    }

    .stat-icon-info {
        background: rgba(52, 152, 219, 0.1);
        color: var(--info-color);
    }

    /* Order Cards */
    .order-card {
        background: white;
        box-shadow: 0 4px 16px var(--shadow-light);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid transparent;
    }

    .order-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px var(--shadow-medium);
        border-left-color: var(--primary-color);
    }

    /* Custom Buttons */
    .btn-primary-custom {
        background: var(--primary-color);
        border: 1px solid var(--primary-color);
        color: white;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-primary-custom:hover {
        background: #34495e;
        border-color: #34495e;
        color: white;
        transform: translateY(-1px);
    }

    .btn-outline-custom {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--medium-text);
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-outline-custom:hover {
        background: var(--light-bg);
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateY(-1px);
    }

    .btn-custom-outline {
        background: transparent;
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-custom-outline:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-1px);
    }

    .btn-danger-custom {
        background: var(--danger-color);
        border: 1px solid var(--danger-color);
        color: white;
        font-weight: 500;
        border-radius: 8px;
    }

    .btn-danger-custom:hover {
        background: #c0392b;
        border-color: #c0392b;
        color: white;
        transform: translateY(-1px);
    }

    .btn-outline-warning-custom {
        background: transparent;
        border: 1px solid var(--warning-color);
        color: var(--warning-color);
        font-weight: 500;
        border-radius: 8px;
    }

    .btn-outline-warning-custom:hover {
        background: var(--warning-color);
        color: white;
        transform: translateY(-1px);
    }

    .btn-outline-info-custom {
        background: transparent;
        border: 1px solid var(--info-color);
        color: var(--info-color);
        font-weight: 500;
        border-radius: 8px;
    }

    .btn-outline-info-custom:hover {
        background: var(--info-color);
        color: white;
        transform: translateY(-1px);
    }

    /* Custom Badges */
    .badge-success-custom {
        background: rgba(39, 174, 96, 0.1);
        color: var(--success-color);
        border: 1px solid rgba(39, 174, 96, 0.2);
        font-weight: 500;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        border-radius: 20px;
    }

    .badge-warning-custom {
        background: rgba(243, 156, 18, 0.1);
        color: var(--warning-color);
        border: 1px solid rgba(243, 156, 18, 0.2);
        font-weight: 500;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        border-radius: 20px;
    }

    .badge-info-custom {
        background: rgba(52, 152, 219, 0.1);
        color: var(--info-color);
        border: 1px solid rgba(52, 152, 219, 0.2);
        font-weight: 500;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        border-radius: 20px;
    }

    /* Custom Form Elements */
    .custom-select,
    .custom-input {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--dark-text);
        font-weight: 500;
        transition: all 0.3s ease;
        background: white;
    }

    .custom-select:focus,
    .custom-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.15);
        background: white;
        color: var(--dark-text);
    }

    .custom-input-group-text {
        background: var(--light-bg);
        border: 1px solid var(--border-color);
        color: var(--medium-text);
        border-right: none;
    }

    .custom-input-group .custom-input {
        border-left: none;
    }

    /* Custom Dividers */
    .custom-divider {
        border-color: var(--border-color);
        opacity: 0.5;
    }

    .custom-border {
        border: 2px solid var(--border-color) !important;
    }

    /* Progress Tracker */
    .order-progress {
        position: relative;
        background: var(--light-bg);
        padding: 2rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }

    .progress-line {
        position: absolute;
        top: 45px;
        left: 12.5%;
        right: 12.5%;
        height: 3px;
        background-color: var(--border-color);
        z-index: 1;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--success-color), var(--info-color));
        border-radius: 2px;
        transition: width 0.5s ease;
    }

    .progress-step {
        position: relative;
        z-index: 2;
    }

    .step-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: white;
        color: var(--light-text);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.1rem;
        border: 3px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .progress-step.completed .step-circle {
        background: var(--success-color);
        color: white;
        border-color: var(--success-color);
    }

    .progress-step.active .step-circle {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        animation: pulse-custom 2s infinite;
    }

    .step-label {
        margin-top: 8px;
    }

    .x-small {
        font-size: 0.7rem;
    }

    /* Custom Alert */
    .alert-info-custom {
        background: rgba(52, 152, 219, 0.1);
        border: 1px solid rgba(52, 152, 219, 0.2);
        border-radius: 8px;
    }

    /* Custom Pagination */
    .custom-pagination .custom-page-link {
        color: var(--medium-text);
        background: white;
        border: 1px solid var(--border-color);
        padding: 0.5rem 0.75rem;
        margin: 0 2px;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .custom-pagination .custom-page-link:hover {
        color: var(--primary-color);
        background: var(--light-bg);
        border-color: var(--primary-color);
        transform: translateY(-1px);
    }

    .custom-pagination .custom-page-active {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .custom-pagination .custom-page-active:hover {
        background: var(--primary-color);
        color: white;
        transform: none;
    }

    /* Animations */
    @keyframes pulse-custom {
        0% {
            box-shadow: 0 0 0 0 rgba(44, 62, 80, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(44, 62, 80, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(44, 62, 80, 0);
        }
    }

    /* Button Enhancements */
    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .order-progress .row {
            flex-direction: column;
            gap: 1rem;
        }

        .progress-line {
            display: none;
        }

        .d-flex.gap-2.flex-wrap {
            gap: 0.5rem !important;
        }

        .btn-sm {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        .order-card:hover {
            transform: translateY(-2px);
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1.5rem !important;
        }

        .px-xl-20,
        .px-lg-10,
        .px-sm-7 {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .order-progress {
            padding: 1.5rem;
        }
    }

    /* Print Styles */
    @media print {

        .btn,
        .pagination,
        .card-header {
            display: none !important;
        }

        .card {
            border: 1px solid var(--border-color) !important;
            box-shadow: none !important;
        }

        .header-card {
            background: white !important;
        }

        .text-white,
        .text-light-subtle {
            color: var(--dark-text) !important;
        }
    }
</style>

<?= include_once '../layout/footer.php' ?>