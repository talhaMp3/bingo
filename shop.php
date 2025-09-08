<?php
/*
session_start();
include_once './include/connection.php';
include_once './layout/header.php';
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$category_slug = $_GET['category'] ?? null;

$base_query = "
SELECT 
    products.*, 
    categories.name AS category_name, 
    categories.slug AS category_slug,
    IF(wishlist.id IS NOT NULL, 1, 0) AS in_wishlist
FROM 
    products
JOIN 
    categories ON products.category_id = categories.id
LEFT JOIN 
    wishlist ON wishlist.product_id = products.id AND wishlist.user_id = ?
WHERE 
    products.status = 'active'
";

// add category filter if slug is set
if (!empty($category_slug)) {
    $base_query .= " AND categories.slug = ? ";
}

$base_query .= " ORDER BY products.name ASC";

// prepare
$stmt = $conn->prepare($base_query);

if (!empty($category_slug)) {
    $stmt->bind_param("is", $user_id, $category_slug); // i=int (user_id), s=string (slug)
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$products_result = $stmt->get_result();
*/
?>
<?php
session_start();
include_once './include/connection.php';
include_once './layout/header.php';

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$category_slug = $_GET['category'] ?? null;

$category_ids = [];

if (!empty($category_slug)) {
    // 1. Find the category by slug
    $cat_sql = "SELECT id FROM categories WHERE slug = ? AND status='active' LIMIT 1";
    $cat_stmt = $conn->prepare($cat_sql);
    $cat_stmt->bind_param("s", $category_slug);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();

    if ($cat_row = $cat_result->fetch_assoc()) {
        $parent_id = $cat_row['id'];
        $category_ids[] = $parent_id;

        // 2. Fetch child categories of this parent
        $child_sql = "SELECT id FROM categories WHERE parent_id = ? AND status='active'";
        $child_stmt = $conn->prepare($child_sql);
        $child_stmt->bind_param("i", $parent_id);
        $child_stmt->execute();
        $child_result = $child_stmt->get_result();

        while ($child = $child_result->fetch_assoc()) {
            $category_ids[] = $child['id'];
        }
    }
}

// 3. Base query
$base_query = "
SELECT 
    products.*, 
    categories.name AS category_name, 
    categories.slug AS category_slug,
    IF(wishlist.id IS NOT NULL, 1, 0) AS in_wishlist
FROM 
    products
JOIN 
    categories ON products.category_id = categories.id
LEFT JOIN 
    wishlist ON wishlist.product_id = products.id AND wishlist.user_id = ?
WHERE 
    products.status = 'active'
";

// 4. Add category filter
if (!empty($category_ids)) {
    // Generate placeholders (?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
    $base_query .= " AND products.category_id IN ($placeholders) ";
}

$base_query .= " ORDER BY products.name ASC";

// 5. Prepare statement
$stmt = $conn->prepare($base_query);

if (!empty($category_ids)) {
    // Build bind params dynamically
    $types = 'i' . str_repeat('i', count($category_ids)); // first is user_id (int), rest are category ids
    $params = array_merge([$types, $user_id], $category_ids);

    // Bind params dynamically
    $tmp = [];
    foreach ($params as $key => $value) {
        $tmp[$key] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $tmp);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$products_result = $stmt->get_result();
?>


<!-- Additional CSS for enhanced functionality -->
<style>
    .quick-filter-btn.active {
        background-color: var(--secondary2);
        color: var(--n0);
        border-color: var(--secondary2);
    }

    .view-btn.active {
        background-color: var(--secondary2);
        color: var(--n0);
        border-color: var(--secondary2);
    }

    .form-select {
        transition: all 0.3s ease;
    }

    .form-select:focus {
        border-color: var(--secondary2);
        box-shadow: 0 0 0 0.2rem rgba(var(--secondary2-rgb), 0.25);
    }

    .quick-filter-btn {
        transition: all 0.3s ease;
    }

    .view-btn {
        transition: all 0.3s ease;
    }

    @media (max-width: 768px) {
        .filter-sort-wrapper {
            padding: 1rem;
        }

        .quick-filters {
            margin-top: 1rem;
            padding-top: 1rem;
        }

        .results-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
</style>
<!-- main start -->
<main>
    <!-- hero section start -->
    <section
        class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png)">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3">Bikes</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">Bikes</a></li>
            </ul>
        </div>
    </section>
    <!-- hero section end -->
    <!-- product section start -->
    <section class="product-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120">
        <div class="container-fluid">
            <!-- Enhanced filter section for bike shop -->
            <?php if ($products_result->num_rows > 0): ?>
                <div class="row g-6 mb-lg-8 mb-6 d-none ">
                    <div class="col-12">
                        <!-- Filter and Sort Section -->
                        <div class="filter-sort-wrapper bg-n0 border border-n100-5 radius-12 p-lg-6 p-4 mb-6">
                            <div class="row g-4 align-items-center">
                                <!-- Category Filter -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="text-sm fw-medium text-n100 mb-2 d-block">Category</label>
                                    <select class="form-select bg-n0 border border-n100-1 text-n100 py-2 px-3 radius-8">
                                        <option value="all">All Bikes</option>
                                        <option value="mountain">Mountain Bikes</option>
                                        <option value="road">Road Bikes</option>
                                        <option value="hybrid">Hybrid Bikes</option>
                                        <option value="electric">Electric Bikes</option>
                                        <option value="kids">Kids Bikes</option>
                                    </select>
                                </div>

                                <!-- Price Range Filter -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="text-sm fw-medium text-n100 mb-2 d-block">Price Range</label>
                                    <select class="form-select bg-n0 border border-n100-1 text-n100 py-2 px-3 radius-8">
                                        <option value="all">All Prices</option>
                                        <option value="0-500">$0 - $500</option>
                                        <option value="500-1000">$500 - $1,000</option>
                                        <option value="1000-2000">$1,000 - $2,000</option>
                                        <option value="2000-plus">$2,000+</option>
                                    </select>
                                </div>

                                <!-- Brand Filter -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="text-sm fw-medium text-n100 mb-2 d-block">Brand</label>
                                    <select class="form-select bg-n0 border border-n100-1 text-n100 py-2 px-3 radius-8">
                                        <option value="all">All Brands</option>
                                        <option value="trek">Trek</option>
                                        <option value="specialized">Specialized</option>
                                        <option value="giant">Giant</option>
                                        <option value="cannondale">Cannondale</option>
                                        <option value="scott">Scott</option>
                                    </select>
                                </div>

                                <!-- Sort Options -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="text-sm fw-medium text-n100 mb-2 d-block">Sort By</label>
                                    <select class="form-select bg-n0 border border-n100-1 text-n100 py-2 px-3 radius-8">
                                        <option value="featured">Featured</option>
                                        <option value="price-low">Price: Low to High</option>
                                        <option value="price-high">Price: High to Low</option>
                                        <option value="newest">Newest First</option>
                                        <option value="rating">Best Rating</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Quick Filter Buttons -->
                            <div class="quick-filters mt-4 pt-4 border-top border-n100-5">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <span class="text-sm fw-medium text-n100">Quick Filters:</span>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button class="quick-filter-btn text-sm text-n100 py-2 px-3 radius-pill border border-n100-1 hover-bg-secondary2 hover-text-n0 hover-border-secondary2 active">
                                            <i class="ph ph-lightning me-1"></i>
                                            New Arrivals
                                        </button>
                                        <button class="quick-filter-btn text-sm text-n100 py-2 px-3 radius-pill border border-n100-1 hover-bg-secondary2 hover-text-n0 hover-border-secondary2">
                                            <i class="ph ph-tag me-1"></i>
                                            On Sale
                                        </button>
                                        <button class="quick-filter-btn text-sm text-n100 py-2 px-3 radius-pill border border-n100-1 hover-bg-secondary2 hover-text-n0 hover-border-secondary2">
                                            <i class="ph ph-star me-1"></i>
                                            Best Sellers
                                        </button>
                                        <button class="quick-filter-btn text-sm text-n100 py-2 px-3 radius-pill border border-n100-1 hover-bg-secondary2 hover-text-n0 hover-border-secondary2">
                                            <i class="ph ph-battery-charging me-1"></i>
                                            Electric
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Results Info and View Toggle -->
                        <div class="results-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                            <div class="results-info">
                                <span class="text-sm text-n50">Showing <span class="fw-medium text-n100">1-12</span> of <span class="fw-medium text-n100">48</span> results</span>
                            </div>
                            <div class="view-toggle d-flex align-items-center gap-2">
                                <!-- <span class="text-sm text-n50 me-2">View:</span>
                                <button class="view-btn text-n100 p-2 border border-n100-1 radius-6 hover-bg-secondary2 hover-text-n0 active">
                                    <i class="ph ph-squares-four"></i>
                                </button>
                                <button class="view-btn text-n100 p-2 border border-n100-1 radius-6 hover-bg-secondary2 hover-text-n0">
                                    <i class="ph ph-list"></i>
                                </button> -->
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <!-- tab content 1 -->
            <div class="tab-content active" data-tab="all">
                <div class="row g-0 mb-1">
                    <?php if ($products_result->num_rows > 0): ?>
                        <?php while ($item = mysqli_fetch_assoc($products_result)): ?>
                            <div class="col-lg-4 col-xs-6">
                                <!-- product item -->
                                <div class="product-card2 position-relative p-xl-10 p-lg-8 p-6 bg-n0 border border-n100-5 box-style box-n20 card-tilt animate-box">
                                    <div class="product-thumb-wrapper position-relative">
                                        <?php if ($item['in_wishlist']) { ?>
                                            <button class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n0 position-absolute top-0 right-0 z-3 tooltip-btn tooltip-left removeFromWishlist" data-tooltip="Remove from wishlist" data-product="<?php echo  $item['id'] ?>">
                                                <i class="ph-heart ph-fill"></i>
                                            </button>

                                        <?php  } else { ?>

                                            <button class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n0 position-absolute top-0 right-0 z-3 tooltip-btn tooltip-left addToWishlist" data-tooltip="Add to wishlist" data-product="<?php echo  $item['id'] ?>">
                                                <i class="ph ph-heart"></i>
                                            </button>
                                        <?php } ?>
                                        <div class="product-thumb reveal-left hover-cursor" data-hover-text="View Product">
                                            <a href="shop-details.php?slug=<?php echo $item['slug']; ?>" class="product-thumb-link d-block">
                                                <?php
                                                $images = json_decode($item['image'], true);
                                                $firstImage = $images[0] ?? 'default.jpg';
                                                ?>
                                                <img class="w-100" src="./assets/uploads/product/<?= $firstImage; ?>" alt="product thumb" />
                                            </a>
                                        </div>
                                    </div>
                                    <span class="d-block h-1px w-100 bg-n100-1 mb-lg-6 mb-4 mt-lg-10 mt-6"></span>
                                    <div class="product-info-wrapper">
                                        <div class="mb-xxl-7 mb-md-5 mb-3">
                                            <a href="shop-details.php?slug=<?php echo $item['slug']; ?>">
                                                <h4 class="text-animation-line text-n100 mb-2 hover-text-secondary2">
                                                    <?php echo $item['name']; ?>
                                                </h4>
                                            </a>
                                            <span class="text-sm fw-normal text-n50"><?= $item['category_name'] ?></span>
                                        </div>
                                        <div class="d-between flex-wrap gap-4">
                                            <div class="d-grid">
                                                <span class="text-sm fw-normal text-n50 text-decoration-underline">₹<?= $item['price'] ?>
                                                    INR</span>
                                                <span class="text-xl fw-semibold text-secondary2">₹<?= $item['discount_price'] ?>
                                                    INR</span>
                                            </div>
                                            <a href="#" class="outline-btn text-n100 fw-medium box-style box-secondary2">ADD
                                                TO CART
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <h4 class="text-n100">😢 No products found in this category.</h4>
                            <p class="text-n50">Try browsing other categories or check back later.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- product section end -->

    <!-- gallery slider -->
    <!-- gallery slider start -->
    <div class="overflow-hidden position-relative z-0">
        <div class="swiper gallery-slider">
            <div class="swiper-wrapper align-items-center z-1">
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-1.png" alt="gallery logo" />
                        <div
                            class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a
                                href="#"
                                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-2.png" alt="gallery logo" />
                        <div
                            class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a
                                href="#"
                                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-3.png" alt="gallery logo" />
                        <div
                            class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a
                                href="#"
                                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-4.png" alt="gallery logo" />
                        <div
                            class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a
                                href="#"
                                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-5.png" alt="gallery logo" />
                        <div
                            class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a
                                href="#"
                                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-6.png" alt="gallery logo" />
                        <div
                            class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a
                                href="#"
                                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-7.png" alt="gallery logo" />
                        <div
                            class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a
                                href="#"
                                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-8.png" alt="gallery logo" />
                        <div
                            class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a
                                href="#"
                                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-9.png" alt="gallery logo" />
                        <div
                            class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a
                                href="#"
                                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- gallery slider end -->

    <!-- call to action -->
    <!-- call to action section start -->
    <section
        class="call-to-action-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120 bg-n100">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-lg-8 mb-6">
                        <h2
                            class="text-animation-word display-four text-n0 text-uppercase mb-lg-5 mb-3">
                            JOIN THE
                            <span class="text-secondary2 text-decoration-underline">CYCLECITY</span>
                            COMMUNITY
                        </h2>
                        <p class="text-sm text-n30 fw-normal ch-100 mx-auto">
                            Stay updated with the latest in cycling. Sign up for our
                            newsletter to receive exclusive offers, product updates, and tips
                            straight to your inbox. Join our biking community today!
                        </p>
                    </div>
                    <form
                        action="#"
                        class="d-center flex-wrap flex-sm-nowrap cta-form mx-auto">
                        <input
                            type="email"
                            placeholder="Enter your email address"
                            class="bg-transparent text-n0 py-lg-4 py-3 px-lg-6 px-4 border border-n20-1 focus-primary" />
                        <button
                            type="submit"
                            class="text-n100 fw-medium text-capitalize bg-n0 font-instrument py-lg-4 py-3 px-lg-6 px-4 hover-text-n0 box-style box-primary2">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- call to action section end -->
</main>
<!-- JavaScript for filter functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Quick filter buttons
        const quickFilterBtns = document.querySelectorAll('.quick-filter-btn');
        quickFilterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                quickFilterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // View toggle buttons
        const viewBtns = document.querySelectorAll('.view-btn');
        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                viewBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Filter change handlers
        const filterSelects = document.querySelectorAll('.form-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // Add your filter logic here
                console.log('Filter changed:', this.value);
            });
        });
    });
</script>
<!-- main end -->
<?= include_once('./layout/footer.php') ?>