<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CycleCity | Secure Checkout - Your Hub for Quality Bicycles</title>
    <link rel="shortcut icon" href="./assets/images/favicon.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Enhanced CSS -->
    <style>
        /* Progress indicator styles */
        .checkout-progress {
            position: relative;
            margin-bottom: 2rem;
        }

        .progress-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #eb453b, #fd7069ff);
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.875rem;
        }

        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: #eb453b;
            color: white;
        }

        .step.completed .step-circle {
            background: #28a745;
            color: white;
        }

        /* Form enhancements */
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f8f9fa;
        }

        .section-icon {
            width: 24px;
            height: 24px;
            background: #eb453b;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .input-group-enhanced {
            position: relative;
            margin-bottom: 1rem;
        }

        .floating-label {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: #6c757d;
            transition: all 0.3s ease;
            pointer-events: none;
            background: white;
            padding: 0 0.25rem;
        }

        .form-control-enhanced {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control-enhanced:focus {
            outline: none;
            border-color: #eb453b;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .form-control-enhanced:focus+.floating-label,
        .form-control-enhanced:not(:placeholder-shown)+.floating-label {
            top: -0.5rem;
            left: 0.75rem;
            font-size: 0.875rem;
            color: #eb453b;
            font-weight: 500;
        }

        .input-success {
            border-color: #28a745 !important;
        }

        .input-error {
            border-color: #dc3545 !important;
        }

        /* Address suggestions */
        .address-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e9ecef;
            border-top: none;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }

        .suggestion-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .suggestion-item:hover {
            background-color: #f8f9fa;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        /* Payment options */
        .payment-option {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .payment-option:hover {
            border-color: #eb453b;
            background: #f8f9fa;
        }

        .payment-option.selected {
            border-color: #eb453b;
            background: #e3f2fd;
        }

        /* Order summary enhancements */
        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 2rem;
        }

        .product-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            /* overflow: hidden; */
            position: relative;
        }

        .quantity-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #eb453b;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: slideIn 0.5s ease-out;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .form-section {
                padding: 1rem;
            }

            .checkout-container {
                flex-direction: column;
            }

            .order-summary {
                position: static;
                margin-top: 2rem;
            }
        }

        /* Loading states */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #eb453b;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Delivery time estimates */
        .delivery-estimate {
            background: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .estimate-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        /* Security badges */
        .security-badges {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .security-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #28a745;
        }
    </style>

    <script defer src="assets/js/main.js"></script>
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .btn-outline-primary {
            --bs-btn-color: #eb453b;
            --bs-btn-border-color: #eb453b;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #eb453b;
            --bs-btn-hover-border-color: #eb453b;
            --bs-btn-focus-shadow-rgb: 13, 110, 253;
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #eb453b;
            --bs-btn-active-border-color: #eb453b;
            --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
            --bs-btn-disabled-color: #eb453b;
            --bs-btn-disabled-bg: transparent;
            --bs-btn-disabled-border-color: #eb453b;
            --bs-gradient: none;
        }
    </style>
</head>

<body>
    <button class="back-to-top position-fixed end-0 bottom-0 d-center me-5">
        <span class="text-h4">
            <i class="ph ph-arrow-up"></i>
        </span>
    </button>

    <!-- Enhanced Header -->
    <header class="header-section position-fixed top-0 start-50 translate-middle-x border-bottom border-n100-1 bg-n0" data-lenis-prevent>
        <div class="container-fluid">
            <div class="row g-0 justify-content-center">
                <div class="col-3xl-11 px-3xl-0 px-xxl-8 px-sm-6 px-0">
                    <div class="d-flex align-items-center justify-content-between gap-4xl-10 gap-3xl-8 gap-xxl-6 gap-4 px-lg-0 px-sm-4 py-lg-5 py-3">
                        <div class="logo">
                            <a href="index.html">
                                <img class="w-100 d-block d-sm-none" src="./assets/images/favicon.png" alt="logo">
                                <img class="w-100 d-none d-sm-block" src="./assets/images/logo.png" alt="logo">
                            </a>
                        </div>

                        <!-- Enhanced navigation with checkout steps -->
                        <div class="d-flex align-items-center gap-4">
                            <span class="text-sm text-muted d-none d-md-block">Secure Checkout</span>
                            <div class="d-flex align-items-center gap-2">
                                <i class="ph ph-lock text-success"></i>
                                <span class="text-sm text-success d-none d-sm-block">SSL Secured</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-12 mt-10">
        <div class="container-fluid px-xl-5">
            <!-- Progress Indicator -->
            <div class="checkout-progress animate-in">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-circle">1</div>
                        <span>Delivery</span>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-circle">2</div>
                        <span>Payment</span>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-circle">3</div>
                        <span>Review</span>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-circle">4</div>
                        <span>Complete</span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Left Column - Forms -->
                <div class="col-lg-7">
                    <!-- Delivery Information -->
                    <div class="form-section animate-in" id="deliverySection">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="ph ph-truck"></i>
                            </div>
                            <div>
                                <h3 class="mb-1">Delivery Information</h3>
                                <p class="text-muted mb-0">Where should we deliver your order?</p>
                            </div>
                        </div>

                        <!-- Quick Address Selection -->
                        <div id="savedAddressesSection" class="mb-4" style="display: none;">
                            <h6 class="mb-3">Use Saved Address</h6>
                            <div id="savedAddressesList"></div>
                        </div>

                        <!-- Enhanced Address Form -->
                        <form id="deliveryForm">
                            <!-- Pincode with enhanced UX -->
                            <div class="input-group-enhanced">
                                <input type="text" id="pincode" class="form-control-enhanced" placeholder=" " maxlength="6" pattern="[0-9]{6}" required>
                                <label class="floating-label">PIN Code *</label>
                                <div id="pincodeLoader" class="position-absolute end-0 top-50 translate-middle-y me-3 d-none">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                </div>
                                <div id="pincodeStatus" class="position-absolute end-0 top-50 translate-middle-y me-3 d-none">
                                    <i class="ph ph-check-circle text-success"></i>
                                </div>
                                <div id="addressSuggestions" class="address-suggestions d-none"></div>
                            </div>

                            <!-- Name fields -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group-enhanced">
                                        <input type="text" id="firstName" class="form-control-enhanced" placeholder=" " required>
                                        <label class="floating-label">First Name *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group-enhanced">
                                        <input type="text" id="lastName" class="form-control-enhanced" placeholder=" " required>
                                        <label class="floating-label">Last Name *</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Address fields -->
                            <div class="input-group-enhanced">
                                <input type="text" id="address" class="form-control-enhanced" placeholder=" " required>
                                <label class="floating-label">Street Address *</label>
                            </div>
                            <!-- Location fields -->
                            <div class="row g-3">
                                <!-- <div class="col-md-4">
                                    <div class="input-group-enhanced">
                                        <input type="text" id="city" class="form-control-enhanced" placeholder=" " readonly>
                                        <label class="floating-label">City</label>
                                    </div>
                                </div> -->
                                <div class="col-md-4">
                                    <div class="input-group-enhanced">
                                        <input type="text" id="landmark" class="form-control-enhanced" placeholder=" ">
                                        <label class="floating-label">Landmark (Optional)</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group-enhanced">
                                        <input type="text" id="state" class="form-control-enhanced" placeholder=" " readonly>
                                        <label class="floating-label">State</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group-enhanced">
                                        <select id="area" class="form-control-enhanced">
                                            <option value="">Select Area</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact information -->
                            <div class="input-group-enhanced">
                                <div class="d-flex">
                                    <span class="form-control-enhanced" style="max-width: 80px; text-align: center; background: #f8f9fa;">+91</span>
                                    <input type="tel" id="phone" class="form-control-enhanced" placeholder="Phone Number *" maxlength="10">
                                </div>
                                <!-- <label class="floating-label">Phone Number *</label> -->
                            </div>

                            <!-- Delivery preferences -->
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Preferred Delivery Time</label>
                                    <select class="form-control-enhanced" id="deliveryTime">
                                        <option value="">Any time</option>
                                        <option value="morning">Morning (9 AM - 12 PM)</option>
                                        <option value="afternoon">Afternoon (12 PM - 6 PM)</option>
                                        <option value="evening">Evening (6 PM - 9 PM)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Delivery Instructions</label>
                                    <input type="text" class="form-control-enhanced" placeholder="Special instructions">
                                </div>
                            </div>

                            <!-- Save address option -->
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="saveAddress">
                                <label class="form-check-label" for="saveAddress">
                                    Save this address for future orders
                                </label>
                            </div>

                            <!-- Delivery estimate -->
                            <div id="deliveryEstimate" class="delivery-estimate d-none">
                                <h6 class="text-success mb-2">
                                    <i class="ph ph-check-circle me-2"></i>Delivery Available
                                </h6>
                                <div class="estimate-item">
                                    <span>Standard Delivery:</span>
                                    <strong>3-5 business days</strong>
                                </div>
                                <div class="estimate-item">
                                    <span>Express Delivery:</span>
                                    <strong>1-2 business days (+₹50)</strong>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Payment Information -->
                    <div class="form-section animate-in" id="paymentSection" style="display: none;">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="ph ph-credit-card"></i>
                            </div>
                            <div>
                                <h3 class="mb-1">Payment Method</h3>
                                <p class="text-muted mb-0">Choose your preferred payment method</p>
                            </div>
                        </div>

                        <!-- Enhanced payment options -->
                        <div class="payment-options">
                            <div class="payment-option" data-payment="cod">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment" value="cod">
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Cash on Delivery</h6>
                                            <small class="text-muted">Pay when you receive your order</small>
                                        </div>
                                    </div>
                                    <i class="ph ph-money fs-4 text-success"></i>
                                </div>
                                <div class="payment-details mt-3 d-none">
                                    <div class="alert alert-info">
                                        <i class="ph ph-info me-2"></i>
                                        Additional ₹30 COD charges apply. Available for orders up to ₹50,000.
                                    </div>
                                </div>
                            </div>

                            <div class="payment-option" data-payment="upi">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment" value="upi">
                                        </div>
                                        <div>
                                            <h6 class="mb-1">UPI Payment</h6>
                                            <small class="text-muted">Pay using UPI apps like GPay, PhonePe, Paytm</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <img src="./assets/images/gpay.png" alt="GPay" height="24">
                                        <img src="./assets/images/phonepe.png" alt="PhonePe" height="24">
                                    </div>
                                </div>
                                <div class="payment-details mt-3 d-none">
                                    <div class="input-group-enhanced">
                                        <input type="text" class="form-control-enhanced" placeholder="Enter UPI ID">
                                        <label class="floating-label">UPI ID</label>
                                    </div>
                                </div>
                            </div>

                            <div class="payment-option" data-payment="card">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment" value="card">
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Credit/Debit Card</h6>
                                            <small class="text-muted">Visa, Mastercard, Rupay accepted</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <img src="./assets/images/visa.png" alt="Visa" height="24">
                                        <img src="./assets/images/mastercard.png" alt="Mastercard" height="24">
                                    </div>
                                </div>
                                <div class="payment-details mt-3 d-none">
                                    <div class="input-group-enhanced">
                                        <input type="text" class="form-control-enhanced" placeholder="1234 5678 9012 3456">
                                        <label class="floating-label">Card Number</label>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="input-group-enhanced">
                                                <input type="text" class="form-control-enhanced" placeholder="MM/YY">
                                                <label class="floating-label">Expiry Date</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group-enhanced">
                                                <input type="text" class="form-control-enhanced" placeholder="123">
                                                <label class="floating-label">CVV</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-group-enhanced">
                                        <input type="text" class="form-control-enhanced" placeholder="John Doe">
                                        <label class="floating-label">Cardholder Name</label>
                                    </div>
                                </div>
                            </div>

                            <div class="payment-option" data-payment="wallet">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment" value="wallet">
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Digital Wallets</h6>
                                            <small class="text-muted">Paytm, Amazon Pay, MobiKwik</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <img src="./assets/images/paytm.png" alt="Paytm" height="24">
                                        <img src="./assets/images/amazonpay.png" alt="Amazon Pay" height="24">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security badges -->
                        <div class="security-badges">
                            <div class="security-badge">
                                <i class="ph ph-shield-check"></i>
                                <span>256-bit SSL</span>
                            </div>
                            <div class="security-badge">
                                <i class="ph ph-lock"></i>
                                <span>Secure Payment</span>
                            </div>
                            <div class="security-badge">
                                <i class="ph ph-credit-card"></i>
                                <span>PCI Compliant</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Review -->
                    <div class="form-section animate-in" id="reviewSection" style="display: none;">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="ph ph-eye"></i>
                            </div>
                            <div>
                                <h3 class="mb-1">Review Your Order</h3>
                                <p class="text-muted mb-0">Please review your order before completing</p>
                            </div>
                        </div>

                        <div id="orderReview">
                            <!-- Order review content will be populated by JavaScript -->
                        </div>
                    </div>

                    <!-- Navigation buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" id="prevBtn" class="btn btn-outline-secondary" style="display: none;">
                            <i class="ph ph-arrow-left me-2"></i>Previous
                        </button>

                        <button type="button" id="nextBtn" class="btn btn-secondary ms-auto">
                            Next <span class="icon">
                                <i class="ph ph-arrow-up-right"></i>
                                <i class="ph ph-arrow-up-right"></i>
                            </span>
                        </button>
                        <button type="button" id="placeOrderBtn" class="btn btn-success ms-auto" style="display: none;">
                            <i class="ph ph-check me-2"></i>Place Order
                        </button>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="col-lg-5">
                    <div class="order-summary">
                        <h4 class="mb-4">Order Summary</h4>

                        <!-- Product list -->
                        <div class="product-list mb-4">
                            <div class="product-item">
                                <div class="product-image position-relative">
                                    <img src="assets/images/product-1.png" alt="Giant Defy Advanced" class="w-100 h-100 object-fit-cover">
                                    <div class="quantity-badge">1</div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Giant Defy Advanced</h6>
                                    <p class="text-muted mb-1">Green / S2</p>
                                    <p class="text-primary mb-0">₹29,900</p>
                                </div>
                            </div>

                            <div class="product-item">
                                <div class="product-image position-relative">
                                    <img src="assets/images/product-2.png" alt="Cannondale Topstone" class="w-100 h-100 object-fit-cover">
                                    <div class="quantity-badge">1</div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Cannondale Topstone</h6>
                                    <p class="text-muted mb-1">Orange / S2</p>
                                    <p class="text-primary mb-0">₹25,000</p>
                                </div>
                            </div>
                        </div>

                        <!-- Coupon section -->
                        <div class="coupon-section mb-4">
                            <div class="d-flex">
                                <input type="text" class="form-control me-2" placeholder="Enter coupon code" id="couponCode">
                                <button class="btn btn-outline-primary" id="applyCoupon">Apply</button>
                            </div>
                            <div id="couponMessage" class="mt-2"></div>
                        </div>

                        <!-- Order totals -->
                        <div class="order-totals">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₹54,900</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span class="text-success">FREE</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="discountRow" style="display: none;">
                                <span>Discount:</span>
                                <span class="text-success" id="discountAmount">-₹0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (GST 18%):</span>
                                <span id="taxAmount">₹9,882</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong id="finalTotal">₹64,782</strong>
                            </div>
                        </div>

                        <!-- Estimated delivery -->
                        <div class="estimated-delivery mt-4 p-3 bg-light rounded">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ph ph-truck text-success"></i>
                                <strong>Estimated Delivery</strong>
                            </div>
                            <p class="mb-0 text-muted">Your order will be delivered between <strong>Jan 31 - Feb 3, 2025</strong></p>
                        </div>

                        <!-- Customer support -->
                        <div class="customer-support mt-4 text-center">
                            <p class="mb-2">Need help with your order?</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="tel:+918000123456" class="btn btn-sm btn-outline-primary">
                                    <i class="ph ph-phone me-1"></i>Call
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="ph ph-chat-circle me-1"></i>Chat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Enhanced jQuery Checkout -->
    <script>
        class EnhancedCheckout {
            constructor() {
                this.currentStep = 1;
                this.totalSteps = 4;
                this.formData = {};
                this.init();
            }

            init() {
                this.bindEvents();
                this.loadSavedAddresses();
                this.updateProgress();
                this.initializeFormValidation();
            }

            bindEvents() {
                // Navigation buttons
                $('#nextBtn').on('click', () => this.nextStep());
                $('#prevBtn').on('click', () => this.prevStep());
                $('#placeOrderBtn').on('click', () => this.placeOrder());

                // Form inputs
                $('#pincode').on('input', (e) => this.handlePincodeInput(e));
                $('#phone').on('input', (e) => this.validatePhone(e));

                // Payment options
                $('.payment-option').on('click', (e) => this.selectPaymentOption(e));

                // Coupon code
                $('#applyCoupon').on('click', () => this.applyCoupon());

                // Real-time validation
                this.setupRealTimeValidation();

                // Auto-save form data
                this.setupAutoSave();
            }

            handlePincodeInput(e) {
                const pincode = e.target.value.replace(/\D/g, '');
                $(e.target).val(pincode);

                if (pincode.length === 6) {
                    this.fetchLocationData(pincode);
                } else {
                    this.resetLocationFields();
                }
            }

            async fetchLocationData(pincode) {
                const $loader = $('#pincodeLoader');
                const $status = $('#pincodeStatus');

                $loader.removeClass('d-none');
                $status.addClass('d-none');

                try {
                    const response = await fetch(`https://api.postalpincode.in/pincode/${pincode}`);
                    const data = await response.json();

                    if (data[0].Status === "Success" && data[0].PostOffice.length > 0) {
                        this.populateLocationData(data[0].PostOffice);
                        this.showDeliveryEstimate();
                        $status.html('<i class="ph ph-check-circle text-success"></i>');
                        $status.removeClass('d-none');

                        // Mark pincode field as valid
                        $('#pincode').addClass('input-success');
                    } else {
                        this.showError('Invalid PIN code');
                        $('#pincode').addClass('input-error');
                    }
                } catch (error) {
                    this.showError('Unable to fetch location data');
                } finally {
                    $loader.addClass('d-none');
                }
            }

            populateLocationData(postOffices) {
                const primaryOffice = postOffices[0];

                $('#city').val(primaryOffice.District);
                $('#state').val(primaryOffice.State);

                const $areaSelect = $('#area');
                $areaSelect.html('<option value="">Select Area</option>');

                postOffices.forEach(office => {
                    $areaSelect.append($('<option>', {
                        value: office.Name,
                        text: office.Name
                    }));
                });

                // Enable form fields
                this.enableAddressForm();
            }

            showDeliveryEstimate() {
                const $estimateDiv = $('#deliveryEstimate');
                $estimateDiv.removeClass('d-none');
                $estimateDiv.addClass('animate-in');
            }

            enableAddressForm() {
                const $form = $('#deliveryForm');
                const $inputs = $form.find('input:not(#pincode), select');

                $inputs.each(function() {
                    $(this).prop('disabled', false);
                    $(this).css('opacity', '1');
                });

                // Focus on first name
                setTimeout(() => {
                    $('#firstName').focus();
                }, 300);
            }

            resetLocationFields() {
                ['city', 'state', 'area'].forEach(id => {
                    const $field = $('#' + id);
                    if ($field.is('select')) {
                        $field.html('<option value="">Select Area</option>');
                    } else {
                        $field.val('');
                    }
                });

                $('#deliveryEstimate').addClass('d-none');
            }

            validatePhone(e) {
                const phone = e.target.value;
                $(e.target).val(phone);

                const isValid = phone.length === 10;
                $(e.target).toggleClass('input-success', isValid);
                $(e.target).toggleClass('input-error', phone.length > 0 && !isValid);
            }

            selectPaymentOption(e) {
                // Remove selection from all options
                $('.payment-option').removeClass('selected');
                $('.payment-details').addClass('d-none');

                // Select clicked option
                const $option = $(e.currentTarget);
                $option.addClass('selected');

                const $radio = $option.find('input[type="radio"]');
                $radio.prop('checked', true);

                // Show payment details if any
                const $details = $option.find('.payment-details');
                if ($details.length) {
                    $details.removeClass('d-none');
                }

                this.updateOrderTotal();
            }

            applyCoupon() {
                const couponCode = $('#couponCode').val().trim();
                const $messageDiv = $('#couponMessage');

                if (!couponCode) {
                    this.showCouponMessage('Please enter a coupon code', 'error');
                    return;
                }

                // Simulate coupon validation
                const validCoupons = {
                    'SAVE10': {
                        discount: 10,
                        type: 'percentage'
                    },
                    'FLAT500': {
                        discount: 500,
                        type: 'fixed'
                    },
                    'WELCOME': {
                        discount: 15,
                        type: 'percentage'
                    }
                };

                if (validCoupons[couponCode.toUpperCase()]) {
                    const coupon = validCoupons[couponCode.toUpperCase()];
                    this.applyCouponDiscount(coupon);
                    this.showCouponMessage(`Coupon applied! You saved ₹${this.calculateDiscount(coupon)}`, 'success');
                } else {
                    this.showCouponMessage('Invalid coupon code', 'error');
                }
            }

            applyCouponDiscount(coupon) {
                const $discountRow = $('#discountRow');
                const $discountAmount = $('#discountAmount');

                const discount = this.calculateDiscount(coupon);
                $discountAmount.text(`-₹${discount}`);
                $discountRow.show();

                this.updateOrderTotal();
            }

            calculateDiscount(coupon) {
                const subtotal = 54900;
                if (coupon.type === 'percentage') {
                    return Math.floor((subtotal * coupon.discount) / 100);
                } else {
                    return coupon.discount;
                }
            }

            showCouponMessage(message, type) {
                const $messageDiv = $('#couponMessage');
                $messageDiv.text(message);
                $messageDiv.attr('class', `mt-2 text-${type === 'success' ? 'success' : 'danger'}`);

                setTimeout(() => {
                    $messageDiv.text('');
                    $messageDiv.attr('class', 'mt-2');
                }, 3000);
            }

            updateOrderTotal() {
                // Calculate total with discounts and payment charges
                let subtotal = 54900;
                let discount = 0;
                let tax = Math.floor(subtotal * 0.18);
                let paymentCharges = 0;

                // Add COD charges if selected
                const selectedPayment = $('input[name="payment"]:checked').val();
                if (selectedPayment === 'cod') {
                    paymentCharges = 30;
                }

                // Apply discount if any
                const $discountElement = $('#discountAmount');
                if ($discountElement.length && $discountElement.text() !== '-₹0') {
                    discount = parseInt($discountElement.text().replace('-₹', ''));
                }

                const total = subtotal - discount + tax + paymentCharges;
                $('#finalTotal').text(`₹${total.toLocaleString()}`);
            }

            nextStep() {
                if (this.validateCurrentStep()) {
                    this.currentStep++;
                    this.updateStepVisibility();
                    this.updateProgress();
                    this.saveFormData();
                }
            }

            prevStep() {
                this.currentStep--;
                this.updateStepVisibility();
                this.updateProgress();
            }

            validateCurrentStep() {
                switch (this.currentStep) {
                    case 1:
                        return this.validateDeliveryForm();
                    case 2:
                        return this.validatePaymentForm();
                    case 3:
                        return this.validateReviewForm();
                    default:
                        return true;
                }
            }

            validateDeliveryForm() {
                const requiredFields = ['pincode', 'firstName', 'lastName', 'address', 'phone'];
                let isValid = true;

                requiredFields.forEach(fieldId => {
                    const $field = $('#' + fieldId);
                    if (!$field.val().trim()) {
                        $field.addClass('input-error');
                        isValid = false;
                    } else {
                        $field.removeClass('input-error');
                        $field.addClass('input-success');
                    }
                });

                // Additional phone validation
                const phone = $('#phone').val();
                if (phone.length !== 10) {
                    $('#phone').addClass('input-error');
                    isValid = false;
                }

                if (!isValid) {
                    this.showError('Please fill all required fields correctly');
                }

                return isValid;
            }

            validatePaymentForm() {
                const selectedPayment = $('input[name="payment"]:checked');
                if (selectedPayment.length === 0) {
                    this.showError('Please select a payment method');
                    return false;
                }
                return true;
            }

            validateReviewForm() {
                return true; // Review step doesn't need validation
            }

            updateStepVisibility() {
                // Hide all sections
                $('#deliverySection, #paymentSection, #reviewSection').hide();

                // Show current section
                switch (this.currentStep) {
                    case 1:
                        $('#deliverySection').show();
                        break;
                    case 2:
                        $('#paymentSection').show();
                        break;
                    case 3:
                        $('#reviewSection').show();
                        this.populateReview();
                        break;
                }

                // Update button visibility
                $('#prevBtn').toggle(this.currentStep > 1);
                $('#nextBtn').toggle(this.currentStep < 3);
                $('#placeOrderBtn').toggle(this.currentStep === 3);
            }

            updateProgress() {
                const progress = (this.currentStep / this.totalSteps) * 100;
                $('#progressFill').css('width', `${progress}%`);

                // Update step indicators
                $('.step').each((index, element) => {
                    const stepNumber = index + 1;
                    const $step = $(element);
                    $step.removeClass('active completed');

                    if (stepNumber < this.currentStep) {
                        $step.addClass('completed');
                        $step.find('.step-circle').html('✓');
                    } else if (stepNumber === this.currentStep) {
                        $step.addClass('active');
                        $step.find('.step-circle').html(stepNumber);
                    } else {
                        $step.find('.step-circle').html(stepNumber);
                    }
                });
            }

            populateReview() {
                const $reviewDiv = $('#orderReview');
                const deliveryData = this.getDeliveryData();
                const paymentData = this.getPaymentData();

                $reviewDiv.html(`
                <div class="review-section mb-4">
                    <h6>Delivery Address</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-1"><strong>${deliveryData.firstName} ${deliveryData.lastName}</strong></p>
                        <p class="mb-1">${deliveryData.address}</p>
                        <p class="mb-1">${deliveryData.city}, ${deliveryData.state} - ${deliveryData.pincode}</p>
                        <p class="mb-0">Phone: +91 ${deliveryData.phone}</p>
                    </div>
                </div>
                
                <div class="review-section mb-4">
                    <h6>Payment Method</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">${paymentData.method}</p>
                    </div>
                </div>
                
                <div class="review-section">
                    <h6>Order Items</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Giant Defy Advanced (Green / S2)</span>
                            <span>₹29,900</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Cannondale Topstone (Orange / S2)</span>
                            <span>₹25,000</span>
                        </div>
                    </div>
                </div>
            `);
            }

            getDeliveryData() {
                return {
                    firstName: $('#firstName').val(),
                    lastName: $('#lastName').val(),
                    address: $('#address').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    pincode: $('#pincode').val(),
                    phone: $('#phone').val()
                };
            }

            getPaymentData() {
                const selectedPayment = $('input[name="payment"]:checked').val();
                const paymentMethods = {
                    'cod': 'Cash on Delivery',
                    'upi': 'UPI Payment',
                    'card': 'Credit/Debit Card',
                    'wallet': 'Digital Wallet'
                };

                return {
                    method: paymentMethods[selectedPayment] || 'Not selected'
                };
            }

            saveFormData() {
                // Auto-save form data to localStorage
                this.formData = {
                    delivery: this.getDeliveryData(),
                    payment: this.getPaymentData(),
                    step: this.currentStep
                };

                localStorage.setItem('checkoutData', JSON.stringify(this.formData));
            }

            loadSavedAddresses() {
                const savedAddresses = JSON.parse(localStorage.getItem('savedAddresses') || '[]');

                if (savedAddresses.length > 0) {
                    const $section = $('#savedAddressesSection');
                    const $list = $('#savedAddressesList');

                    const addressHtml = savedAddresses.map((addr, index) => `
                    <div class="saved-address-item border rounded p-3 mb-2 cursor-pointer" data-index="${index}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${addr.firstName} ${addr.lastName}</h6>
                                <p class="mb-1 text-muted">${addr.address}</p>
                                <p class="mb-0 text-muted">${addr.city}, ${addr.state} - ${addr.pincode}</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Use This</button>
                        </div>
                    </div>
                `).join('');

                    $list.html(addressHtml);
                    $section.show();

                    // Bind click events
                    $list.find('.saved-address-item').on('click', (e) => {
                        const index = $(e.currentTarget).data('index');
                        this.fillSavedAddress(savedAddresses[index]);
                    });
                }
            }

            fillSavedAddress(address) {
                Object.keys(address).forEach(key => {
                    const $field = $('#' + key);
                    if ($field.length) {
                        $field.val(address[key]);
                        if (key === 'pincode') {
                            this.fetchLocationData(address[key]);
                        }
                    }
                });
            }

            setupRealTimeValidation() {
                $('input, select').on('blur', function() {
                    const $this = $(this);
                    if ($this.is('[required]') && !$this.val().trim()) {
                        $this.addClass('input-error');
                    } else {
                        $this.removeClass('input-error');
                        $this.addClass('input-success');
                    }
                });
            }

            setupAutoSave() {
                $('input, select').on('change', () => {
                    this.saveFormData();
                });
            }

            async placeOrder() {
                const $btn = $('#placeOrderBtn');
                $btn.addClass('loading');
                $btn.prop('disabled', true);

                try {
                    // Simulate order processing
                    await this.processOrder();

                    // Show success message
                    this.showOrderSuccess();

                } catch (error) {
                    this.showError('Failed to place order. Please try again.');
                } finally {
                    $btn.removeClass('loading');
                    $btn.prop('disabled', false);
                }
            }

            async processOrder() {
                // Simulate API call
                return new Promise((resolve) => {
                    setTimeout(() => {
                        resolve({
                            orderId: 'CYC' + Math.random().toString(36).substr(2, 9).toUpperCase()
                        });
                    }, 2000);
                });
            }

            showOrderSuccess() {
                // Replace checkout with success message
                $('main').html(`
                <div class="container-fluid text-center py-5">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="success-animation mb-4">
                                <i class="ph ph-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="text-success mb-3">Order Placed Successfully!</h2>
                            <p class="text-muted mb-4">Thank you for your purchase. Your order is being processed and you'll receive a confirmation email shortly.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="index.html" class="btn-secondary">Continue Shopping</a>
                                <a href="account.html" class="btn btn-outline-primary">Track Order</a>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            }

            showError(message) {
                // Create toast notification
                const $toast = $(`
                <div class="toast-notification bg-danger text-white p-3 rounded position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
                    <div class="d-flex align-items-center">
                        <i class="ph ph-warning-circle me-2"></i>
                        <span>${message}</span>
                    </div>
                </div>
            `);

                $('body').append($toast);

                setTimeout(() => {
                    $toast.remove();
                }, 3000);
            }

            initializeFormValidation() {
                // Any additional initialization can go here
            }
        }

        // Initialize checkout when DOM is ready
        $(document).ready(() => {
            new EnhancedCheckout();
        });
    </script>

</body>

</html>