<?php
session_start();
include_once '../layout/header.php';
include_once '../include/connection.php';
?>
<!-- main start -->
<main class="pt-lg-6">
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(../assets/images/inner-page-banner.png)">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3">Premium Bikes Collection</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">Bikes</a></li>
            </ul>
        </div>
    </section>

    <section class="product-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120">
        <div class="container-fluid">
            <!-- Professional Header Section -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-lg bg-gradient-primary text-white">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h2 class="mb-2 fw-bold">
                                        <i class="ph ph-bicycle me-2"></i>Bike Catalog & Management
                                    </h2>
                                    <p class="mb-0 opacity-75">Discover our extensive collection of premium bicycles and manage your cycling journey</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="d-flex align-items-center justify-content-md-end gap-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 fw-bold">150+</div>
                                            <small class="opacity-75">Bike Models</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="h4 mb-0 fw-bold">25</div>
                                            <small class="opacity-75">Top Brands</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Search & Filters -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold text-muted">Category</label>
                                    <select class="form-select">
                                        <option value="">All Categories</option>
                                        <option value="mountain">Mountain Bikes</option>
                                        <option value="road">Road Bikes</option>
                                        <option value="hybrid">Hybrid Bikes</option>
                                        <option value="electric">Electric Bikes</option>
                                        <option value="bmx">BMX Bikes</option>
                                        <option value="folding">Folding Bikes</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold text-muted">Price Range</label>
                                    <select class="form-select">
                                        <option value="">All Prices</option>
                                        <option value="0-25000">Under ₹25,000</option>
                                        <option value="25000-50000">₹25,000 - ₹50,000</option>
                                        <option value="50000-100000">₹50,000 - ₹1,00,000</option>
                                        <option value="100000+">Above ₹1,00,000</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold text-muted">Brand</label>
                                    <select class="form-select">
                                        <option value="">All Brands</option>
                                        <option value="hero">Hero Cycles</option>
                                        <option value="trek">Trek</option>
                                        <option value="giant">Giant</option>
                                        <option value="specialized">Specialized</option>
                                        <option value="cannondale">Cannondale</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-muted">Search Bikes</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-magnifying-glass"></i></span>
                                        <input type="text" class="form-control" placeholder="Search by name, model, or features...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100">
                                        <i class="ph ph-funnel me-2"></i>Filter
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
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="ph ph-bicycle"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0 fw-bold">150+</div>
                                    <p class="text-muted small mb-0">Available Models</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                    <i class="ph ph-heart"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0 fw-bold">8</div>
                                    <p class="text-muted small mb-0">Favorites</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="ph ph-shopping-cart"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0 fw-bold">3</div>
                                    <p class="text-muted small mb-0">In Cart</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100 stat-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                    <i class="ph ph-currency-inr"></i>
                                </div>
                                <div>
                                    <div class="h4 mb-0 fw-bold">₹2,45,000</div>
                                    <p class="text-muted small mb-0">Total Investment</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Toggle & Sort Options -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 fw-bold">Featured Bikes</h4>
                            <p class="text-muted mb-0">Handpicked selection of premium bicycles</p>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="d-flex gap-1">
                                <button class="btn btn-outline-secondary btn-sm active">
                                    <i class="ph ph-squares-four"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm">
                                    <i class="ph ph-list"></i>
                                </button>
                            </div>
                            <select class="form-select form-select-sm" style="width: 200px;">
                                <option>Sort by: Featured</option>
                                <option>Price: Low to High</option>
                                <option>Price: High to Low</option>
                                <option>Customer Rating</option>
                                <option>Newest First</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Bikes Grid -->
            <div class="row">
                <!-- Bike Card 1 - Mountain Bike -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm bike-card h-100">
                        <div class="position-relative overflow-hidden">
                            <img src="https://placehold.co/400x280/2c3e50/ffffff?text=Trek+Mountain+Bike"
                                alt="Trek Mountain Bike" class="card-img-top bike-image" style="height: 280px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 p-3">
                                <button class="btn btn-light btn-sm rounded-pill shadow-sm wishlist-btn">
                                    <i class="ph ph-heart"></i>
                                </button>
                            </div>
                            <div class="position-absolute top-0 start-0 p-3">
                                <span class="badge bg-success rounded-pill">Featured</span>
                            </div>
                            <div class="card-overlay">
                                <button class="btn btn-white btn-sm shadow">Quick View</button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 card-title">Trek Mountain Explorer 29</h6>
                                <div class="text-warning">
                                    <i class="ph ph-star-fill"></i>
                                    <small class="ms-1 fw-semibold">4.8</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">Professional mountain bike with advanced suspension system</p>

                            <!-- Specifications -->
                            <div class="row text-center border rounded py-2 mb-3 bg-light">
                                <div class="col-4">
                                    <div class="small fw-semibold">Frame</div>
                                    <div class="x-small text-muted">Aluminum</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Gears</div>
                                    <div class="x-small text-muted">21-Speed</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Size</div>
                                    <div class="x-small text-muted">29"</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="h5 mb-0 fw-bold text-primary">₹45,999</div>
                                    <small class="text-muted text-decoration-line-through">₹52,999</small>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill">13% OFF</span>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-modern">
                                    <i class="ph ph-shopping-cart me-2"></i>Add to Cart
                                </button>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary flex-fill btn-modern-outline">
                                        <i class="ph ph-eye me-1"></i>Details
                                    </button>
                                    <button class="btn btn-outline-info flex-fill btn-modern-outline">
                                        <i class="ph ph-arrows-clockwise me-1"></i>Compare
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bike Card 2 - Road Bike -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm bike-card h-100">
                        <div class="position-relative overflow-hidden">
                            <img src="https://placehold.co/400x280/34495e/ffffff?text=Giant+Road+Bike"
                                alt="Giant Road Bike" class="card-img-top bike-image" style="height: 280px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 p-3">
                                <button class="btn btn-light btn-sm rounded-pill shadow-sm wishlist-btn favorited">
                                    <i class="ph ph-heart-fill text-danger"></i>
                                </button>
                            </div>
                            <div class="position-absolute top-0 start-0 p-3">
                                <span class="badge bg-warning rounded-pill">Best Seller</span>
                            </div>
                            <div class="card-overlay">
                                <button class="btn btn-white btn-sm shadow">Quick View</button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 card-title">Giant Road Speedster Pro</h6>
                                <div class="text-warning">
                                    <i class="ph ph-star-fill"></i>
                                    <small class="ms-1 fw-semibold">4.9</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">Lightweight carbon fiber road bike for competitive cycling</p>

                            <!-- Specifications -->
                            <div class="row text-center border rounded py-2 mb-3 bg-light">
                                <div class="col-4">
                                    <div class="small fw-semibold">Frame</div>
                                    <div class="x-small text-muted">Carbon</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Gears</div>
                                    <div class="x-small text-muted">16-Speed</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Weight</div>
                                    <div class="x-small text-muted">8.5kg</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="h5 mb-0 fw-bold text-primary">₹89,999</div>
                                    <small class="text-muted text-decoration-line-through">₹99,999</small>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill">10% OFF</span>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-modern">
                                    <i class="ph ph-shopping-cart me-2"></i>Add to Cart
                                </button>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary flex-fill btn-modern-outline">
                                        <i class="ph ph-eye me-1"></i>Details
                                    </button>
                                    <button class="btn btn-outline-info flex-fill btn-modern-outline">
                                        <i class="ph ph-arrows-clockwise me-1"></i>Compare
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bike Card 3 - Electric Bike -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm bike-card h-100">
                        <div class="position-relative overflow-hidden">
                            <img src="https://placehold.co/400x280/27ae60/ffffff?text=Hero+Electric+Bike"
                                alt="Hero Electric Bike" class="card-img-top bike-image" style="height: 280px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 p-3">
                                <button class="btn btn-light btn-sm rounded-pill shadow-sm wishlist-btn">
                                    <i class="ph ph-heart"></i>
                                </button>
                            </div>
                            <div class="position-absolute top-0 start-0 p-3">
                                <span class="badge bg-info rounded-pill">Electric</span>
                            </div>
                            <div class="card-overlay">
                                <button class="btn btn-white btn-sm shadow">Quick View</button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 card-title">Hero Electric Urban E-Bike</h6>
                                <div class="text-warning">
                                    <i class="ph ph-star-fill"></i>
                                    <small class="ms-1 fw-semibold">4.7</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">Smart electric bike with app connectivity and GPS tracking</p>

                            <!-- Specifications -->
                            <div class="row text-center border rounded py-2 mb-3 bg-light">
                                <div class="col-4">
                                    <div class="small fw-semibold">Battery</div>
                                    <div class="x-small text-muted">48V 12Ah</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Range</div>
                                    <div class="x-small text-muted">80km</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Speed</div>
                                    <div class="x-small text-muted">25kmph</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="h5 mb-0 fw-bold text-primary">₹65,999</div>
                                    <small class="text-muted text-decoration-line-through">₹74,999</small>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill">12% OFF</span>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-modern">
                                    <i class="ph ph-shopping-cart me-2"></i>Add to Cart
                                </button>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary flex-fill btn-modern-outline">
                                        <i class="ph ph-eye me-1"></i>Details
                                    </button>
                                    <button class="btn btn-outline-info flex-fill btn-modern-outline">
                                        <i class="ph ph-arrows-clockwise me-1"></i>Compare
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional bike cards would continue here... -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm bike-card h-100">
                        <div class="position-relative overflow-hidden">
                            <img src="https://placehold.co/400x280/8e44ad/ffffff?text=Hybrid+Comfort"
                                alt="Hybrid Bike" class="card-img-top bike-image" style="height: 280px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 p-3">
                                <button class="btn btn-light btn-sm rounded-pill shadow-sm wishlist-btn">
                                    <i class="ph ph-heart"></i>
                                </button>
                            </div>
                            <div class="position-absolute top-0 start-0 p-3">
                                <span class="badge bg-secondary rounded-pill">New</span>
                            </div>
                            <div class="card-overlay">
                                <button class="btn btn-white btn-sm shadow">Quick View</button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 card-title">Specialized Hybrid Comfort</h6>
                                <div class="text-warning">
                                    <i class="ph ph-star-fill"></i>
                                    <small class="ms-1 fw-semibold">4.6</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">Perfect blend of comfort and performance for city commuting</p>

                            <div class="row text-center border rounded py-2 mb-3 bg-light">
                                <div class="col-4">
                                    <div class="small fw-semibold">Frame</div>
                                    <div class="x-small text-muted">Steel</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Gears</div>
                                    <div class="x-small text-muted">7-Speed</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Type</div>
                                    <div class="x-small text-muted">Hybrid</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="h5 mb-0 fw-bold text-primary">₹32,999</div>
                                </div>
                                <span class="badge bg-primary-subtle text-primary rounded-pill">New Launch</span>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-modern">
                                    <i class="ph ph-shopping-cart me-2"></i>Add to Cart
                                </button>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary flex-fill btn-modern-outline">
                                        <i class="ph ph-eye me-1"></i>Details
                                    </button>
                                    <button class="btn btn-outline-info flex-fill btn-modern-outline">
                                        <i class="ph ph-arrows-clockwise me-1"></i>Compare
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm bike-card h-100">
                        <div class="position-relative overflow-hidden">
                            <img src="https://placehold.co/400x280/e74c3c/ffffff?text=BMX+Freestyle"
                                alt="BMX Bike" class="card-img-top bike-image" style="height: 280px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 p-3">
                                <button class="btn btn-light btn-sm rounded-pill shadow-sm wishlist-btn">
                                    <i class="ph ph-heart"></i>
                                </button>
                            </div>
                            <div class="position-absolute top-0 start-0 p-3">
                                <span class="badge bg-danger rounded-pill">Limited</span>
                            </div>
                            <div class="card-overlay">
                                <button class="btn btn-white btn-sm shadow">Quick View</button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 card-title">Mongoose BMX Freestyle Pro</h6>
                                <div class="text-warning">
                                    <i class="ph ph-star-fill"></i>
                                    <small class="ms-1 fw-semibold">4.5</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">Professional BMX bike designed for tricks and stunts</p>

                            <div class="row text-center border rounded py-2 mb-3 bg-light">
                                <div class="col-4">
                                    <div class="small fw-semibold">Frame</div>
                                    <div class="x-small text-muted">Chromoly</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Wheel</div>
                                    <div class="x-small text-muted">20"</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Style</div>
                                    <div class="x-small text-muted">Freestyle</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="h5 mb-0 fw-bold text-primary">₹28,999</div>
                                    <small class="text-muted text-decoration-line-through">₹34,999</small>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill">17% OFF</span>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-modern">
                                    <i class="ph ph-shopping-cart me-2"></i>Add to Cart
                                </button>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary flex-fill btn-modern-outline">
                                        <i class="ph ph-eye me-1"></i>Details
                                    </button>
                                    <button class="btn btn-outline-info flex-fill btn-modern-outline">
                                        <i class="ph ph-arrows-clockwise me-1"></i>Compare
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm bike-card h-100">
                        <div class="position-relative overflow-hidden">
                            <img src="https://placehold.co/400x280/f39c12/ffffff?text=Folding+Commuter"
                                alt="Folding Bike" class="card-img-top bike-image" style="height: 280px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 p-3">
                                <button class="btn btn-light btn-sm rounded-pill shadow-sm wishlist-btn favorited">
                                    <i class="ph ph-heart-fill text-danger"></i>
                                </button>
                            </div>
                            <div class="position-absolute top-0 start-0 p-3">
                                <span class="badge bg-warning rounded-pill">Compact</span>
                            </div>
                            <div class="card-overlay">
                                <button class="btn btn-white btn-sm shadow">Quick View</button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 card-title">Urban Folding Commuter</h6>
                                <div class="text-warning">
                                    <i class="ph ph-star-fill"></i>
                                    <small class="ms-1 fw-semibold">4.4</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">Space-saving folding bike perfect for urban transportation</p>

                            <div class="row text-center border rounded py-2 mb-3 bg-light">
                                <div class="col-4">
                                    <div class="small fw-semibold">Wheel</div>
                                    <div class="x-small text-muted">20"</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Weight</div>
                                    <div class="x-small text-muted">12kg</div>
                                </div>
                                <div class="col-4">
                                    <div class="small fw-semibold">Folded</div>
                                    <div class="x-small text-muted">Compact</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="h5 mb-0 fw-bold text-primary">₹24,999</div>
                                </div>
                                <span class="badge bg-info-subtle text-info rounded-pill">Space Saver</span>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-modern">
                                    <i class="ph ph-shopping-cart me-2"></i>Add to Cart
                                </button>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary flex-fill btn-modern-outline">
                                        <i class="ph ph-eye me-1"></i>Details
                                    </button>
                                    <button class="btn btn-outline-info flex-fill btn-modern-outline">
                                        <i class="ph ph-arrows-clockwise me-1"></i>Compare
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load More Section -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <button class="btn btn-outline-primary btn-lg px-5">
                        <i class="ph ph-plus me-2"></i>Load More Bikes
                    </button>
                </div>
            </div>

            <!-- Professional Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                <div class="text-muted">
                    Showing 1-6 of 150+ bikes
                </div>
                <nav aria-label="Bikes pagination">
                    <ul class="pagination mb-0">
                        <li class="page-item disabled">
                            <a class="page-link rounded-start" href="#" tabindex="-1">
                                <i class="ph ph-caret-left"></i>
                            </a>
                        </li>
                        <li class="page-item active">
                            <a class="page-link" href="#">1</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">3</a>
                        </li>
                        <li class="page-item">
                            <span class="page-link">...</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">25</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link rounded-end" href="#">
                                <i class="ph ph-caret-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>
</main>
<!-- main end -->

<style>
    /* Professional Card Styling */
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        border-left-color: var(--bs-primary);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
    }

    /* Enhanced Bike Cards */
    .bike-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
    }

    .bike-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15) !important;
    }

    .bike-image {
        transition: transform 0.4s ease;
    }

    .bike-card:hover .bike-image {
        transform: scale(1.08);
    }

    /* Card Overlay Effect */
    .card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .bike-card:hover .card-overlay {
        opacity: 1;
    }

    .btn-white {
        background: white;
        color: var(--bs-dark);
        border: none;
        font-weight: 600;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
    }

    /* Modern Button Styling */
    .btn-modern {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
    }

    .btn-modern-outline {
        border-radius: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-modern-outline:hover {
        transform: translateY(-1px);
    }

    /* Wishlist Button */
    .wishlist-btn {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .wishlist-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .wishlist-btn.favorited {
        background: #ffe6e6;
        animation: heartbeat 1.5s ease-in-out infinite;
    }

    /* Enhanced Badge Styling */
    .badge {
        font-weight: 600;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* Gradient Background */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* Form Enhancements */
    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #495057;
    }

    .form-control,
    .form-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        transform: translateY(-1px);
    }

    /* Enhanced Typography */
    .card-title {
        font-size: 1.1rem;
        line-height: 1.4;
        color: #2c3e50;
    }

    .x-small {
        font-size: 0.7rem;
        font-weight: 500;
    }

    /* Star Rating Enhancement */
    .text-warning i {
        filter: drop-shadow(0 2px 4px rgba(255, 193, 7, 0.3));
    }

    /* Price Styling */
    .text-decoration-line-through {
        opacity: 0.6;
        font-size: 0.9rem;
    }

    /* Specifications Box */
    .bg-light {
        background-color: #f8f9fa !important;
        border-color: #e9ecef !important;
    }

    /* Animations */
    @keyframes heartbeat {
        0% {
            transform: scale(1);
        }

        14% {
            transform: scale(1.15);
        }

        28% {
            transform: scale(1);
        }

        42% {
            transform: scale(1.15);
        }

        70% {
            transform: scale(1);
        }
    }

    /* Pagination Enhancement */
    .pagination .page-link {
        border-radius: 8px;
        margin: 0 2px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .bike-card:hover {
            transform: translateY(-4px);
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .btn-modern {
            padding: 0.6rem 1.2rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }

        .card-overlay {
            display: none;
        }
    }

    @media (max-width: 576px) {
        .bike-card {
            margin-bottom: 1.5rem;
        }

        .card-body {
            padding: 1.5rem !important;
        }

        .px-xl-20,
        .px-lg-10,
        .px-sm-7 {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .row.g-3 {
            gap: 1rem !important;
        }

        .d-flex.gap-3 {
            flex-direction: column;
            gap: 1rem !important;
        }
    }

    /* Loading Animation */
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }

        100% {
            background-position: 1000px 0;
        }
    }

    .bike-image {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 1000px 100%;
        animation: shimmer 2s infinite linear;
    }

    /* Print Styles */
    @media print {

        .btn,
        .pagination,
        .badge,
        .card-overlay {
            display: none !important;
        }

        .bike-card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }
    }

    /* Accessibility Improvements */
    .btn:focus-visible {
        outline: 2px solid #667eea;
        outline-offset: 2px;
    }

    .form-control:focus-visible,
    .form-select:focus-visible {
        outline: 2px solid #667eea;
        outline-offset: 2px;
    }
</style>

<?= include_once '../layout/footer.php' ?>