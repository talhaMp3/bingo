<!DOCTYPE html>
<html lang="en">
<?php
require_once './include/connection.php';
$base_url = "http://localhost/bingo/";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $base_url = "https://" . $_SERVER['HTTP_HOST'] . "/bingo/";
}
$cartTotal = 0;
$wishlistTotal = 0;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // ✅ Cart total
    $cart_sql = "SELECT SUM(qty) as total FROM cart WHERE user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_stmt->bind_result($cart_total_result);
    $cart_stmt->fetch();
    $cart_stmt->close();

    $cartTotal = $cart_total_result ?? 0;

    // ✅ Wishlist total
    $wishlist_sql = "SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?";
    $wishlist_stmt = $conn->prepare($wishlist_sql);
    $wishlist_stmt->bind_param("i", $user_id);
    $wishlist_stmt->execute();
    $wishlist_stmt->bind_result($wishlist_total_result);
    $wishlist_stmt->fetch();
    $wishlist_stmt->close();

    $wishlistTotal = $wishlist_total_result ?? 0;
}
?>

<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <base href="./" />
    <meta name="description" content="CycleCity offers a wide range of bicycles, gear, and accessories for every type of cyclist. Explore our collection and gear up for your next adventure!" />
    <meta name="keywords" content="bicycles, bikes, cycling gear, bike accessories, mountain bikes, road bikes, CycleCity" />
    <meta name="author" content="CycleCity Team" />

    <meta property="og:title" content="CycleCity | Quality Bicycles and Cycling Gear" />
    <meta property="og:description" content="Discover the best selection of bicycles, gear, and accessories at CycleCity. Shop now for top brands and quality service." />
    <meta property="og:image" content="/assets/images/logo.png" />
    <meta property="og:url" content="" />
    <meta property="og:type" content="website" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="CycleCity | Quality Bicycles and Cycling Gear" />
    <meta name="twitter:description" content="Explore the latest in bicycles, cycling gear, and accessories at CycleCity. Gear up for your next adventure!" />
    <meta name="twitter:image" content="/assets/images/logo.png" />
    <meta name="twitter:site" content="@CycleCity" />
    <title>CycleCity | Your Hub for Quality Bicycles, Gear, and Accessories</title>
    <link rel="shortcut icon" href="/assets/images/favicon.png" type="image/x-icon" />

    <script defer src="/assets/js/main.js"></script>
    <link href="/assets/css/style.css" rel="stylesheet" />
    <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=68875bd39b73432f18d61440&product=sop' async='async'></script>
</head>

<body>
    <!-- preloader -->
    <!-- preloader  -->
    <!-- <div class="preloader">
        <svg class="loader" viewBox="0 0 48 30" width="48px" height="30px">
            <g
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1">
                <g transform="translate(9.5,19)">
                    <circle
                        class="loader_tire"
                        r="9"
                        stroke-dasharray="56.549 56.549"></circle>
                    <g
                        class="loader_spokes-spin"
                        stroke-dasharray="31.416 31.416"
                        stroke-dashoffset="-23.562">
                        <circle class="loader_spokes" r="5"></circle>
                        <circle
                            class="loader_spokes"
                            r="5"
                            transform="rotate(180,0,0)"></circle>
                    </g>
                </g>
                <g transform="translate(24,19)">
                    <g
                        class="loader_pedals-spin"
                        stroke-dasharray="25.133 25.133"
                        stroke-dashoffset="-21.991"
                        transform="rotate(67.5,0,0)">
                        <circle class="loader_pedals" r="4"></circle>
                        <circle
                            class="loader_pedals"
                            r="4"
                            transform="rotate(180,0,0)"></circle>
                    </g>
                </g>
                <g transform="translate(38.5,19)">
                    <circle
                        class="loader_tire"
                        r="9"
                        stroke-dasharray="56.549 56.549"></circle>
                    <g
                        class="loader_spokes-spin"
                        stroke-dasharray="31.416 31.416"
                        stroke-dashoffset="-23.562">
                        <circle class="loader_spokes" r="5"></circle>
                        <circle
                            class="loader_spokes"
                            r="5"
                            transform="rotate(180,0,0)"></circle>
                    </g>
                </g>
                <polyline
                    class="loader_seat"
                    points="14 3,18 3"
                    stroke-dasharray="5 5"></polyline>
                <polyline
                    class="loader_body"
                    points="16 3,24 19,9.5 19,18 8,34 7,24 19"
                    stroke-dasharray="79 79"></polyline>
                <path
                    class="loader_handlebars"
                    d="m30,2h6s1,0,1,1-1,1-1,1"
                    stroke-dasharray="10 10"></path>
                <polyline
                    class="loader_front"
                    points="32.5 2,38.5 19"
                    stroke-dasharray="19 19"></polyline>
            </g>
        </svg>
    </div> -->
    <!-- back to top -->
    <button class="back-to-top position-fixed end-0 bottom-0 d-center me-5">
        <span class="text-h4">
            <i class="ph ph-arrow-up"></i>
        </span>
    </button>
    <!-- include header -->
    <!-- header -->
    <!-- mouse -->
    <div class="cursor"></div>
    <div class="cursor-follower"></div>

    <!-- header section start -->
    <header
        class="header-section position-fixed top-0 start-50 translate-middle-x"
        data-lenis-prevent>
        <!-- top navbar -->
        <div class="top-navbar bg-n100 py-3 d-none d-lg-block">
            <div class="row g-0 justify-content-center">
                <div class="col-3xl-11 px-3xl-0 px-xxl-8 px-sm-6 px-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-xl-6 gap-4">
                            <a
                                href="#"
                                class="d-flex align-items-center gap-2 text-n0 hover-text-secondary2">
                                <span class="text-base"><i class="ph ph-map-pin"></i></span>
                                <span class="text-sm text-nowrap">
                                    1200 Main St. Santa Rosa, CA 93541, USA
                                </span>
                            </a>
                            <a href="tel:+1234567890" class="d-flex align-items-center gap-2 text-n0 hover-text-secondary2">
                                <span class="text-base"><i class="ph ph-phone-call"></i></span>
                                <span class="text-sm text-nowrap"> +123 456 7890 </span>
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-xl-6 gap-4">
                            <div class="language-option custom-select-container">
                                <!-- selected item  -->
                                <div class="custom-select">
                                    <span class="selected-option fw-medium text-n0">
                                        <img src="<?= $base_url ?>assets/images/flags/ac.png" alt="language" class="option-flag" />
                                        AC
                                    </span>
                                    <ul class="options-list">
                                        <li data-value="ac"><img src="<?= $base_url ?>assets/images/flags/ac.png" alt="language" class="option-flag" /> AC </li>
                                        <li data-value="ad"><img src="<?= $base_url ?>assets/images/flags/ad.png" alt="language" class="option-flag" /> AD </li>
                                        <li data-value="az"><img src="<?= $base_url ?>assets/images/flags/az.png" alt="language" class="option-flag" /> AZ </li>
                                        <li data-value="ba"><img src="<?= $base_url ?>assets/images/flags/ba.png" alt="language" class="option-flag" /> BA </li>
                                        <li data-value="bq"><img src="<?= $base_url ?>assets/images/flags/bq.png" alt="language" class="option-flag" /> BQ </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-sm text-nowrap fw-medium text-n0">Follow Us:</span>
                                <ul class="d-flex align-items-center gap-4">
                                    <li><a href="#" class="text-n0 text-xl hover-text-secondary2"><i class="ph ph-facebook-logo"></i></a></li>
                                    <li><a href="#" class="text-n0 text-xl hover-text-secondary2"><i class="ph ph-x-logo"></i></a></li>
                                    <li><a href="#" class="text-n0 text-xl hover-text-secondary2"><i class="ph ph-dribbble-logo"></i></a></li>
                                    <li><a href="#" class="text-n0 text-xl hover-text-secondary2"><i class="ph ph-instagram-logo"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- header section start -->
        <div class="container-fluid">
            <div class="row g-0 justify-content-center">
                <div class="col-3xl-11 px-3xl-0 px-xxl-8 px-sm-6 px-0">
                    <!-- navbar area -->
                    <div
                        class="d-flex align-items-center justify-content-between gap-4xl-10 gap-3xl-8 gap-xxl-6 gap-4 px-lg-0 px-sm-4 py-lg-5 py-3">
                        <div class="logo">
                            <a href="index.html">
                                <img class="w-100 d-block d-sm-none" src="<?= $base_url ?>assets/images/favicon.png" alt="logo" />
                                <img class="w-100 d-none d-sm-block" src="<?= $base_url ?>assets/images/logo.png" alt="logo" />
                            </a>
                        </div>
                        <nav class="navbar-area">
                            <!-- navbar close btn -->
                            <button class="menu-close-btn d-block d-lg-none">
                                <span class="icon-32px text-2xl text-n0 bg-n100 mb-4">
                                    <i class="ph ph-x"></i>
                                </span>
                            </button>

                            <ul class="nav-menu-items d-lg-flex d-grid align-items-lg-center gap-lg-0 gap-lg-4 gap-1">

                                <li class="menu-link">
                                    <a
                                        href="index.php"
                                        class="slide-vertical"
                                        data-splitting>Home </a>
                                </li>
                                <li class="menu-link">
                                    <a
                                        href="about-us.html"
                                        class="slide-vertical"
                                        data-splitting>About Us</a>
                                </li>
                                <?php
                                // Step 1: Fetch all active categories with product counts (direct count only)
                                $sql = " SELECT c.id, c.name, c.slug, c.parent_id, c.image,
                                            COUNT(p.id) AS product_count
                                        FROM categories c
                                        LEFT JOIN products p 
                                            ON p.category_id = c.id 
                                        AND p.status = 'active'
                                        WHERE c.status = 'active'
                                        GROUP BY c.id, c.name, c.slug, c.parent_id, c.image
                                        ORDER BY c.parent_id ASC, c.name ASC";

                                $result = $conn->query($sql);

                                // Step 2: Store categories in array
                                $allCategories = [];
                                while ($row = $result->fetch_assoc()) {
                                    $row['children'] = [];
                                    $allCategories[$row['id']] = $row;
                                }

                                // Step 3: Build parent-child tree
                                $categories = [];
                                foreach ($allCategories as $id => &$cat) {
                                    if ($cat['parent_id'] == NULL) {
                                        $categories[$id] = &$cat; // parent
                                    } else {
                                        if (isset($allCategories[$cat['parent_id']])) {
                                            $allCategories[$cat['parent_id']]['children'][] = &$cat;
                                        }
                                    }
                                }
                                unset($cat); // break reference

                                // Step 4: Recursive function to calculate total product count (parent + children)
                                function getTotalCount(&$category)
                                {
                                    $total = $category['product_count'];
                                    if (!empty($category['children'])) {
                                        foreach ($category['children'] as &$child) {
                                            $total += getTotalCount($child); // add child + its sub-children
                                        }
                                    }
                                    $category['total_count'] = $total; // store in array for later use
                                    return $total;
                                }

                                // Apply recursive count calculation
                                foreach ($categories as &$parent) {
                                    getTotalCount($parent);
                                }
                                unset($parent);
                                ?>

                                <!-- 🟢 Mega Menu -->
                                <li class="menu-item">
                                    <button class="slide-vertical" data-splitting>
                                        Shop
                                        <span class="menu-icon"><i class="ph-fill ph-caret-down"></i></span>
                                    </button>

                                    <div class="mega-menu">
                                        <div class="row g-6 justify-content-between">
                                            <?php foreach ($categories as $parent): ?>
                                                <div class="col-lg-auto col-12">
                                                    <div class="mega-menu-item d-grid gap-lg-6">
                                                        <div class="menu-title-wrapper d-between">
                                                            <div>
                                                                <?php if (!empty($parent['image'])): ?>
                                                                    <div class="menu-item-thumb icon-48px radius-unset d-none d-lg-block mb-3 overflow-hidden">
                                                                        <img class="w-100" src="<?= $base_url ?>assets/uploads/categories/<?= $parent['image'] ?>" alt="<?= htmlspecialchars($parent['name']) ?>">
                                                                    </div>
                                                                <?php endif; ?>

                                                                <span class="menu-title text-h5 text-uppercase">
                                                                    <?= htmlspecialchars($parent['name']) ?>
                                                                </span>
                                                                <span class="hr-line-40px radius-4 bg-secondary2 d-lg-block d-none h-2"></span>
                                                            </div>
                                                            <span class="menu-icon d-lg-none"><i class="ph-fill ph-caret-down"></i></span>
                                                        </div>

                                                        <ul class="mega-sub-menu">
                                                            <!-- Parent with total count (own + children) -->
                                                            <li class="mega-menu-link">
                                                                <a href="shop.php?category=<?= $parent['slug'] ?>">
                                                                    <?= htmlspecialchars($parent['name']) ?> (<?= $parent['total_count'] ?>)
                                                                </a>
                                                            </li>

                                                            <!-- Children with their own direct+recursive counts -->
                                                            <?php foreach ($parent['children'] as $child): ?>
                                                                <li class="mega-menu-link">
                                                                    <a href="shop.php?category=<?= $child['slug'] ?>">
                                                                        <?= htmlspecialchars($child['name']) ?> (<?= $child['total_count'] ?>)
                                                                    </a>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </li>

                                <li class="menu-link">
                                    <a
                                        href="contact-us.html"
                                        class="slide-vertical"
                                        data-splitting>Contact</a>
                                </li>
                            </ul>
                        </nav>
                        <!-- search bar area -->
                        <!-- search bar area -->
                        <div class="search-bar search-form-wrapper position-relative">
                            <form
                                action="#"
                                class="header-search-form d-flex align-items-center gap-3 py-lg-3 py-2 px-xxl-6 px-md-4 px-3 radius-pill border border-n100-6 bg-n20 w-100 focus-secondary2">
                                <input
                                    type="text"
                                    id="searchInput"
                                    placeholder="Search Bikes, Gear & Accessories"
                                    class="w-100 border-0 outline-0 bg-transparent"
                                    autocomplete="off" />
                                <button type="submit" class="text-xl border-0 bg-transparent p-0">
                                    <i class="ph ph-magnifying-glass"></i>
                                </button>
                            </form>

                            <button
                                type="button"
                                class="search-close-btn text-2xl position-absolute top-0 end-0 translate-middle me-5 mt-10 p-sm-2 p-1 bg-primary2 text-n0 d-xl-none">
                                <i class="ph ph-x"></i>
                            </button>

                            <!-- Search Results Container -->
                            <div id="search-results"
                                class="position-absolute top-100 start-0 mt-2 w-100 bg-white border rounded shadow-lg"
                                style="display: none; z-index: 1000; max-height: 400px; overflow-y: auto;">

                                <!-- Loading State -->
                                <div id="loading-state" class="text-center py-4" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Searching...</span>
                                    </div>
                                    <p class="mt-2 mb-0 text-muted">Searching...</p>
                                </div>

                                <!-- No Results State -->
                                <div id="no-results" class="text-center py-4" style="display: none;">
                                    <i class="ph ph-magnifying-glass text-muted" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0 text-muted">No results found for "<span id="search-term"></span>"</p>
                                </div>

                                <!-- Static Suggestions (shown when empty or no query) -->
                                <div id="static-suggestions" class="p-3">
                                    <p class="fw-bold mb-3 text-secondary fs-6">Popular Searches</p>
                                    <div class="row g-0">
                                        <div class="col-12">
                                            <a href="#" class="d-flex align-items-center py-2 px-2 text-decoration-none text-dark rounded hover-bg-light suggestion-item" data-value="Mountain Bike">
                                                <span class="me-3">🚴</span>
                                                <span>Mountain Bike</span>
                                                <small class="ms-auto text-muted">Popular</small>
                                            </a>
                                        </div>
                                        <div class="col-12">
                                            <a href="#" class="d-flex align-items-center py-2 px-2 text-decoration-none text-dark rounded hover-bg-light suggestion-item" data-value="Road Bike">
                                                <span class="me-3">🚴‍♀️</span>
                                                <span>Road Bike</span>
                                                <small class="ms-auto text-muted">Trending</small>
                                            </a>
                                        </div>
                                        <div class="col-12">
                                            <a href="#" class="d-flex align-items-center py-2 px-2 text-decoration-none text-dark rounded hover-bg-light suggestion-item" data-value="Cycling Helmet">
                                                <span class="me-3">⚙️</span>
                                                <span>Cycling Helmet</span>
                                                <small class="ms-auto text-muted">Safety</small>
                                            </a>
                                        </div>
                                        <div class="col-12">
                                            <a href="#" class="d-flex align-items-center py-2 px-2 text-decoration-none text-dark rounded hover-bg-light suggestion-item" data-value="Riding Glasses">
                                                <span class="me-3">🕶️</span>
                                                <span>Riding Glasses</span>
                                                <small class="ms-auto text-muted">Accessories</small>
                                            </a>
                                        </div>
                                        <div class="col-12">
                                            <a href="#" class="d-flex align-items-center py-2 px-2 text-decoration-none text-dark rounded hover-bg-light suggestion-item" data-value="Bike Backpack">
                                                <span class="me-3">🎒</span>
                                                <span>Bike Backpack</span>
                                                <small class="ms-auto text-muted">Gear</small>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Search Results (for future AJAX) -->
                                <div id="search-results-content" style="display: none;">
                                    <!-- Results will be populated here via AJAX -->
                                </div>
                            </div>
                        </div>

                        <style>
                            .hover-bg-light:hover {
                                background-color: #f8f9fa !important;
                                transition: background-color 0.15s ease-in-out;
                            }

                            .suggestion-item:hover {
                                transform: translateX(2px);
                                transition: transform 0.15s ease-in-out;
                            }

                            #search-results {
                                border: 1px solid #dee2e6;
                                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                            }

                            .search-bar input:focus {
                                box-shadow: none !important;
                            }
                        </style>

                        <script>
                            class SearchManager {
                                constructor() {
                                    this.searchInput = document.getElementById('searchInput');
                                    this.searchResults = document.getElementById('search-results');
                                    this.staticSuggestions = document.getElementById('static-suggestions');
                                    this.loadingState = document.getElementById('loading-state');
                                    this.noResults = document.getElementById('no-results');
                                    this.searchResultsContent = document.getElementById('search-results-content');
                                    this.searchTerm = document.getElementById('search-term');

                                    this.debounceTimer = null;
                                    this.minQueryLength = 2;
                                    this.debounceDelay = 300;

                                    this.init();
                                }

                                init() {
                                    // Input event with debouncing
                                    this.searchInput.addEventListener('input', (e) => {
                                        clearTimeout(this.debounceTimer);
                                        this.debounceTimer = setTimeout(() => {
                                            this.handleSearch(e.target.value.trim());
                                        }, this.debounceDelay);
                                    });

                                    // Focus event - show suggestions
                                    this.searchInput.addEventListener('focus', () => {
                                        if (this.searchInput.value.trim().length === 0) {
                                            this.showStaticSuggestions();
                                        } else {
                                            this.handleSearch(this.searchInput.value.trim());
                                        }
                                    });

                                    // Click outside to close
                                    document.addEventListener('click', (e) => {
                                        if (!this.searchInput.contains(e.target) && !this.searchResults.contains(e.target)) {
                                            this.hideResults();
                                        }
                                    });

                                    // Handle suggestion clicks
                                    this.searchResults.addEventListener('click', (e) => {
                                        const suggestionItem = e.target.closest('.suggestion-item');
                                        if (suggestionItem) {
                                            e.preventDefault();
                                            const value = suggestionItem.dataset.value;
                                            this.selectSuggestion(value);
                                        }
                                    });

                                    // Handle form submission
                                    this.searchInput.closest('form').addEventListener('submit', (e) => {
                                        e.preventDefault();
                                        this.performSearch(this.searchInput.value.trim());
                                    });

                                    // Keyboard navigation (future enhancement)
                                    this.searchInput.addEventListener('keydown', (e) => {
                                        this.handleKeyNavigation(e);
                                    });
                                }

                                handleSearch(query) {
                                    if (query.length === 0) {
                                        this.showStaticSuggestions();
                                        return;
                                    }

                                    if (query.length < this.minQueryLength) {
                                        this.hideResults();
                                        return;
                                    }

                                    // Show loading state
                                    this.showLoadingState();

                                    // Simulate search delay (replace with actual AJAX call)
                                    setTimeout(() => {
                                        this.performStaticSearch(query);
                                    }, 500);
                                }

                                performStaticSearch(query) {
                                    // Static search logic (replace with AJAX call later)
                                    const staticData = [
                                        'Mountain Bike', 'Road Bike', 'Hybrid Bike', 'Electric Bike',
                                        'Cycling Helmet', 'Bike Lock', 'Riding Glasses', 'Bike Lights',
                                        'Bike Backpack', 'Water Bottle', 'Bike Pump', 'Cycling Shorts'
                                    ];

                                    const results = staticData.filter(item =>
                                        item.toLowerCase().includes(query.toLowerCase())
                                    );

                                    if (results.length > 0) {
                                        this.showSearchResults(results, query);
                                    } else {
                                        this.showNoResults(query);
                                    }
                                }

                                // Future method for AJAX search
                                async performAjaxSearch(query) {
                                    try {
                                        // Example AJAX structure for future implementation
                                        /*
                                        const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
                                        const data = await response.json();
                                        
                                        if (data.results && data.results.length > 0) {
                                            this.showSearchResults(data.results, query);
                                        } else {
                                            this.showNoResults(query);
                                        }
                                        */
                                    } catch (error) {
                                        console.error('Search error:', error);
                                        this.showNoResults(query);
                                    }
                                }

                                showStaticSuggestions() {
                                    this.hideAllStates();
                                    this.staticSuggestions.style.display = 'block';
                                    this.searchResults.style.display = 'block';
                                }

                                showLoadingState() {
                                    this.hideAllStates();
                                    this.loadingState.style.display = 'block';
                                    this.searchResults.style.display = 'block';
                                }

                                showNoResults(query) {
                                    this.hideAllStates();
                                    this.searchTerm.textContent = query;
                                    this.noResults.style.display = 'block';
                                    this.searchResults.style.display = 'block';
                                }

                                showSearchResults(results, query) {
                                    this.hideAllStates();

                                    // Generate results HTML
                                    let resultsHTML = `<div class="p-3">
            <p class="fw-bold mb-3 text-secondary fs-6">Search Results for "${query}"</p>
            <div class="row g-0">`;

                                    results.forEach(result => {
                                        resultsHTML += `
                <div class="col-12">
                    <a href="#" class="d-flex align-items-center py-2 px-2 text-decoration-none text-dark rounded hover-bg-light suggestion-item" data-value="${result}">
                        <span class="me-3">🔍</span>
                        <span>${result}</span>
                    </a>
                </div>`;
                                    });

                                    resultsHTML += '</div></div>';

                                    this.searchResultsContent.innerHTML = resultsHTML;
                                    this.searchResultsContent.style.display = 'block';
                                    this.searchResults.style.display = 'block';
                                }

                                hideAllStates() {
                                    this.staticSuggestions.style.display = 'none';
                                    this.loadingState.style.display = 'none';
                                    this.noResults.style.display = 'none';
                                    this.searchResultsContent.style.display = 'none';
                                }

                                hideResults() {
                                    this.searchResults.style.display = 'none';
                                }

                                selectSuggestion(value) {
                                    this.searchInput.value = value;
                                    this.hideResults();
                                    this.performSearch(value);
                                }

                                performSearch(query) {
                                    console.log('Performing search for:', query);
                                    // Add your search logic here
                                }

                                handleKeyNavigation(e) {
                                    // Future implementation for arrow key navigation
                                    // This would allow users to navigate through suggestions with keyboard
                                }
                            }

                            // Initialize search manager when DOM is loaded
                            document.addEventListener('DOMContentLoaded', () => {
                                new SearchManager();
                            });
                        </script>


                        <div
                            class="nav-btns d-flex align-items-center gap-xl-4 gap-lg-3 gap-4">
                            <!-- toggle search bar -->
                            <button
                                type="submit"
                                class="toggle-search-btn text-xl d-xl-none">
                                <i class="ph ph-magnifying-glass"></i>
                            </button>

                            <!-- user profile -->
                            <a
                                href="login.html"
                                class="user-btn icon-36px text-n100 hover-text-secondary2">
                                <span class="text-2xl">
                                    <i class="ph ph-user"></i>
                                </span>
                            </a>

                            <!-- wishlist btn -->
                            <?php
                            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') {

                                $cartBtClass = "fetch-cart-btn cart-btn";
                            ?>
                                <a
                                    href="wishlist.html"
                                    class="wishlist-btn icon-36px position-relative text-n100 hover-text-secondary2 <?= $cartBtClass ?>">
                                    <span class="badge radius-pill text-n0 text-sm fw-medium bg-secondary2 position-absolute top-50 start-100 translate-middle z-1 mt-n3  whishlist_count"><?= $wishlistTotal ?></span>
                                    <span class="text-2xl">
                                        <i class="ph ph-heart"></i>
                                    </span>
                                </a>
                            <?php
                            } else {
                                $cartBtClass = "fetch-cart-btn ";
                            ?>
                                <button
                                    class="wishlist-btn icon-36px position-relative text-n100 hover-text-secondary2 <?= $cartBtClass ?>">
                                    <span class="badge radius-pill text-n0 text-sm fw-medium bg-secondary2 position-absolute top-50 start-100 translate-middle z-1 mt-n3  whishlist_count"><?= $wishlistTotal ?></span>
                                    <span class="text-2xl">
                                        <i class="ph ph-heart"></i>
                                    </span>
                                </button>
                            <?php
                            }
                            ?>


                            <!-- cart btn -->
                            <button class="<?= $cartBtClass ?> icon-36px position-relative text-n100 hover-text-secondary2">
                                <span
                                    class="badge radius-pill text-n0 text-sm fw-medium bg-secondary2 position-absolute top-50 start-100 translate-middle z-1 mt-n3 card-count"><?= $cartTotal ?></span>
                                <span class="text-2xl">
                                    <i class="ph ph-shopping-cart"></i>
                                </span>
                            </button>

                            <button class="menu-toggle-btn text-2xl d-block d-lg-none">
                                <i class="ph ph-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- header section end -->

    <!-- cart box -->
    <div class="cart-box">
        <div class="cart-box-overlay"></div>
        <div class="cart-content-wrapper">
            <div class="position-relative overflow-hidden h-100">
                <div class="cart-box-top">
                    <!-- title and close button -->
                    <div
                        class="d-between gap-3 py-3xl-5 py-lg-3 py-2 px-xl-8 px-md-6 px-4">
                        <span class="text-n100 text-base fw-semibold">Shopping Cart</span>
                        <button class="cart-close-btn text-xl">
                            <i class="ph ph-x"></i>
                        </button>
                    </div>
                    <!-- free shipping progress -->
                    <div
                        class="free-shipping-progress py-3xl-5 py-lg-3 py-2 px-xl-8 px-md-6 px-4 bg-n20 mb-4xl-10 mb-lg-8 mb-md-6 mb-4">
                        <div class="progress-bar">
                            <div class="progress-bar-inner" style="width: 50%">
                                <span class="car-icon">
                                    <i class="ph ph-truck"></i>
                                </span>
                            </div>
                        </div>
                        <!-- <span class="text-n100 text-sm fw-normal">Spend $100.00 more to enjoy <span class="text-secondary2">FREE
                                 SHIPPING!</span></span> -->
                        <span class="text-n100 text-sm fw-normal">Spend $53.00 more to enjoy
                            <span class="text-secondary2">FREE SHIPPING!</span></span>
                        <!-- <span class="text-n100 text-sm fw-normal">Congratulations! You've got <span class="text-secondary2">FREE
                                 SHIPPING!</span></span> -->
                    </div>
                </div>
                <div class="cart-box-bottom">
                    <!-- cart items -->
                    <div class="cart-items-wrapper">
                        <div class="cart-items d-flex flex-column gap-lg-6 gap-4 px-xl-8 px-md-6 px-4">

                        </div>
                    </div>
                    <div class="cart-box-form">
                        <!-- cart actions -->
                        <span class="d-block border-bottom border-n100-1 my-4"></span>
                        <div
                            class="cart-actions d-flex align-items-center justify-content-around px-lg-20">
                            <button
                                class="add-note text-xl tooltip-btn tooltip-top position-relative"
                                data-tooltip="Add Note">
                                <i class="ph ph-notepad"></i>
                            </button>
                            <button
                                class="add-gift-wrap text-xl tooltip-btn tooltip-top position-relative"
                                data-tooltip="Add Gift">
                                <i class="ph ph-gift"></i>
                            </button>
                            <button
                                class="add-estimate text-xl tooltip-btn tooltip-top position-relative"
                                data-tooltip="Estimate Shipping">
                                <i class="ph ph-calculator"></i>
                            </button>
                        </div>

                        <!-- note form start-->
                        <form
                            action="#"
                            class="note-form-wrapper p-xl-8 p-md-6 p-4 bg-n0">
                            <span
                                class="d-flex align-items-center gap-2 text-n100 text-base fw-medium mb-4">
                                <span class="d-flex text-xl"><i class="ph-fill ph-note-pencil"></i></span>
                                Add order note</span>
                            <textarea
                                class="note-form border-n100-1 p-4 w-100"
                                rows="3"
                                placeholder="Add Note"></textarea>
                            <button
                                type="submit"
                                class="text-base fw-bold text-n100 bg-n0 hover-text-n0 hover-bg-secondary2 py-3 px-lg-5 px-3 border border-n100-1 w-100 mt-4">
                                Save Note
                            </button>
                            <button
                                type="reset"
                                class="note-cancel-btn text-base fw-bold text-n100 bg-n0 hover-text-n0 hover-bg-secondary2 py-3 px-lg-5 px-3 border border-n100-1 w-100 mt-4">
                                Cancel
                            </button>
                        </form>
                        <!-- note form end -->

                        <!-- gift wrap start -->
                        <form
                            action="#"
                            class="gift-form-wrapper p-xl-8 p-md-6 p-4 bg-n0">
                            <span class="d-center text-xl mb-3"><i class="ph-fill ph-gift"></i></span>
                            <span class="d-block text-sm text-center">
                                Please wrap the product carefully. Fee is only $5.00. (You can
                                choose or not)
                            </span>
                            <button
                                type="submit"
                                class="text-base fw-bold text-n100 bg-n0 hover-text-n0 hover-bg-secondary2 py-3 px-lg-5 px-3 border border-n100-1 w-100 mt-4">
                                Add a Gift Wrap
                            </button>
                            <button
                                type="reset"
                                class="gift-cancel-btn text-base fw-bold text-n100 bg-n0 hover-text-n0 hover-bg-secondary2 py-3 px-lg-5 px-3 border border-n100-1 w-100 mt-4">
                                Cancel
                            </button>
                        </form>
                        <!-- gift wrap end -->
                        <span class="d-block border-bottom border-n100-1 mt-4"></span>

                        <div class="checkout-wrapper p-xl-8 p-md-6 p-4 bg-n20">
                            <!-- cart total -->
                            <div class="cart-total-wrapper mb-xxl-6 mb-4">
                                <div class="cart-total d-flex justify-content-between">
                                    <span class="text-n100 text-base fw-medium">Subtotal</span>
                                    <span class="text-n100 text-base fw-medium cart-overall_total">$299.00</span>
                                </div>
                                <!-- <div class="cart-total d-flex justify-content-between">
                                    <span class="text-n100 text-base fw-medium">Shipping</span>
                                    <span class="text-n100 text-base fw-medium">$0.00</span>
                                </div> -->
                            </div>
                            <div>
                                <label class="input-checkbox mb-lg-6 mb-4">
                                    <input type="checkbox" hidden />
                                    <span class="checkbox"></span>
                                    <span class="text-base text-n50">I agree with
                                        <a
                                            href="terms-conditions.html"
                                            class="text-decoration-underline">Terms & Conditions</a></span>
                                </label>
                                <div class="d-grid gap-4">
                                    <!-- <a
                                        href="cart.html"
                                        class="d-block text-center text-n100 fw-medium py-lg-3 py-2 px-lg-6 px-4 border border-n100 bg-n0 hover-text-n0 hover-bg-n100">View Cart</a> -->
                                    <a
                                        href="checkout.php"
                                        class="d-block text-center text-n0 fw-medium py-lg-3 py-2 px-lg-6 px-4 border-0 bg-secondary2 hover-text-n0 hover-bg-n100">Checkout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- cart box -->
    <!-- include newsletter popup -->
    <!-- newsletter modal -->
    <div class="newsletter-modal popup-wrapper">
        <div class="popup-overlay"></div>
        <div class="popup-content-wrapper d-center bg-n0">
            <button
                class="newsletter-close-btn popup-close-btn text-xl text-n100 position-absolute top-0 end-0 mt-lg-5 mt-3 me-lg-5 me-3">
                <i class="ph ph-x"></i>
            </button>

            <div class="newsletter-banner d-none d-lg-block">
                <img class="w-100" src="<?= $base_url ?>/assets/images/newsletter.png" alt="banner" />
            </div>
            <div
                class="newsletter-form-wrapper text-center p-xxl-10 p-xl-8 p-lg-6 p-4">
                <h3 class="text-n100 mb-lg-4 mb-2">NEWSLETTER</h3>
                <p class="text-base fw-normal mb-lg-8 mb-md-6 mb-4 text-n50">
                    Enjoy special offers, exclusive discounts and promotions only
                    available to our subscribers
                </p>
                <form class="newsletter-form mb-xl-10 mb-lg-8 mb-md-6 mb-4">
                    <div
                        class="d-flex align-items-center gap-1 py-lg-4 py-2 px-lg-6 px-4 border border-n100-1 bg-n0 mb-lg-6 mb-4">
                        <span class="text-xl text-n50">
                            <i class="ph ph-envelope-open"></i>
                        </span>
                        <input
                            type="email"
                            class="w-100 border-0 bg-n0"
                            placeholder="Enter your email address" />
                    </div>
                    <button type="submit" class="btn-secondary radius-unset">
                        Subscribe
                    </button>
                </form>
                <span class="text-base text-n100">
                    Have a Questions? E-mail us at info@example.com
                </span>
            </div>
        </div>
    </div>
    <!-- newsletter modal -->