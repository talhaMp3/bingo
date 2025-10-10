<?php
include_once './layout/header.php';
?>

<link rel="stylesheet" href="https://www.bingocycles.com/css/TrackStyle.css">

<style>
    .tracking-search-wrapper {
        background: #f8f9fa;
        min-height: 100vh;
    }

    .search-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        max-width: 600px;
        margin: 0 auto;
    }

    .search-header {
        background: linear-gradient(135deg, #eb453b, #ff6b6b);
        padding: 40px 32px;
        text-align: center;
        color: white;
    }

    .search-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .search-subtitle {
        font-size: 14px;
        opacity: 0.9;
        margin: 0;
    }

    .search-body {
        padding: 40px 32px;
    }

    .search-form {
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .tracking-input {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .tracking-input:focus {
        outline: none;
        border-color: #eb453b;
        background: white;
        box-shadow: 0 0 0 3px rgba(235, 69, 59, 0.1);
    }

    .tracking-input::placeholder {
        color: #9ca3af;
        font-weight: 400;
    }

    .search-btn {
        background: linear-gradient(135deg, #eb453b, #ff6b6b);
        color: white;
        border: none;
        padding: 16px 32px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(235, 69, 59, 0.3);
    }

    .search-btn:active {
        transform: translateY(0);
    }

    .search-features {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #f3f4f6;
    }

    .features-title {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 16px;
        text-align: center;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .feature-item {
        text-align: center;
        padding: 16px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .feature-item:hover {
        background: #eb453b;
        color: white;
        transform: translateY(-2px);
    }

    .feature-icon {
        font-size: 24px;
        margin-bottom: 8px;
    }

    .feature-text {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .help-section {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 10px;
        padding: 20px;
        margin-top: 24px;
    }

    .help-title {
        font-size: 14px;
        font-weight: 600;
        color: #0369a1;
        margin-bottom: 8px;
    }

    .help-text {
        font-size: 13px;
        color: #0c4a6e;
        margin: 0;
        line-height: 1.5;
    }

    .help-text a {
        color: #eb453b;
        font-weight: 600;
        text-decoration: none;
    }

    .help-text a:hover {
        text-decoration: underline;
    }

    .error-message {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .success-message {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 20px;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .search-card {
            margin: 20px;
        }

        .search-header {
            padding: 30px 24px;
        }

        .search-header h1 {
            font-size: 24px;
        }

        .search-body {
            padding: 30px 24px;
        }

        .features-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .feature-item {
            padding: 12px;
        }
    }

    @media (max-width: 480px) {
        .search-header {
            padding: 24px 20px;
        }

        .search-body {
            padding: 24px 20px;
        }

        .tracking-input {
            padding: 14px 16px;
            font-size: 14px;
        }

        .search-btn {
            padding: 14px 24px;
            font-size: 14px;
        }
    }

    /* Animation for search */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .search-btn.loading {
        animation: pulse 1.5s infinite;
        pointer-events: none;
    }
</style>

<main class="pt-12">
    <!-- hero section start -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3">Track Your Order</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">Track Order</a></li>
            </ul>
        </div>
    </section>
    <!-- hero section end -->

    <!-- tracking search section start -->
    <section class="tracking-search-wrapper pt-120 pb-120 px-xl-20 px-lg-10 px-sm-7">
        <div class="container-fluid">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10 col-sm-12">
                        <!-- Search Card -->
                        <div class="search-card">
                            <div class="search-header">
                                <h1>Track Your Package</h1>
                                <p class="search-subtitle">Enter your tracking ID to get real-time updates</p>
                            </div>

                            <div class="search-body">
                                <?php if (isset($_GET['error']) && $_GET['error'] == 'not_found'): ?>
                                    <div class="error-message">
                                        ❌ No tracking information found. Please check your tracking ID and try again.
                                    </div>
                                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'empty'): ?>
                                    <div class="error-message"> 
                                        ❌ Please enter a tracking ID to search.
                                    </div>
                                <?php elseif (isset($_GET['success']) && $_GET['success'] == 'redirecting'): ?>
                                    <div class="success-message">
                                        ✅ Tracking found! Redirecting to your order details...
                                    </div>
                                <?php endif; ?>

                                <form action="track-order-process.php" method="GET" class="search-form" id="trackingForm">
                                    <div class="form-group">
                                        <label for="tracking_id" class="form-label">Tracking ID</label>
                                        <div class="input-group">
                                            <input
                                                type="text"
                                                id="tracking_id"
                                                name="trackid"
                                                class="tracking-input"
                                                placeholder="Enter your tracking ID (e.g., DC020919)"
                                                value="<?php echo isset($_GET['trackid']) ? htmlspecialchars($_GET['trackid']) : ''; ?>"
                                                required
                                                autocomplete="off">
                                        </div>
                                    </div>

                                    <button type="submit" class="search-btn" id="searchBtn">
                                        <span class="btn-text">Track Package</span>
                                        <span class="btn-loading" style="display: none;">
                                            <i class="fas fa-spinner fa-spin"></i> Searching...
                                        </span>
                                    </button>
                                </form>

                                <!-- Features -->
                                <div class="search-features">
                                    <h3 class="features-title">Why Track With Us?</h3>
                                    <div class="features-grid">
                                        <div class="feature-item">
                                            <div class="feature-icon">🚚</div>
                                            <div class="feature-text">Real-time Updates</div>
                                        </div>
                                        <div class="feature-item">
                                            <div class="feature-icon">📱</div>
                                            <div class="feature-text">Mobile Friendly</div>
                                        </div>
                                        <div class="feature-item">
                                            <div class="feature-icon">⏰</div>
                                            <div class="feature-text">24/7 Access</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- tracking search section end -->
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('trackingForm');
        const searchBtn = document.getElementById('searchBtn');
        const btnText = searchBtn.querySelector('.btn-text');
        const btnLoading = searchBtn.querySelector('.btn-loading');

        form.addEventListener('submit', function(e) {
            const trackingId = document.getElementById('tracking_id').value.trim();

            if (!trackingId) {
                e.preventDefault();
                return;
            }

            // Show loading state
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            searchBtn.classList.add('loading');
        });

        // Auto-focus on input
        document.getElementById('tracking_id').focus();

        // Add input validation
        const trackingInput = document.getElementById('tracking_id');
        trackingInput.addEventListener('input', function(e) {
            // Remove any spaces
            this.value = this.value.replace(/\s/g, '');
        });
    });
</script>

<?php include_once './layout/footer.php'; ?>