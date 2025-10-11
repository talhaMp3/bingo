<?php
session_start();
include_once './include/connection.php';

$slug = mysqli_real_escape_string($conn, $_GET['slug']);
$variant = isset($_GET['variant']) ? $_GET['variant'] : null;

// First, get the main product data
$query = "
SELECT 
    products.*, 
    categories.name AS category_name, 
    categories.slug AS category_slug,
    brands.name AS brand_name,
    brands.slug AS brand_slug,
    brands.logo AS brand_logo
FROM 
    products
LEFT JOIN 
    categories ON products.category_id = categories.id
LEFT JOIN 
    brands ON products.brand_id = brands.id
WHERE 
    products.slug = '$slug' 
LIMIT 1";

$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);
// echo '<pre>';
// print_r($product);
// exit();
if (!$product) {
    // Handle product not found
    die("Product not found");
}

$product_id = $product['id'];
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

if (isset($variant)) {

    $sql = "SELECT * FROM wishlist WHERE user_id = ? AND product_id = ? AND variant_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $product_id, $variant);
} else {

    $sql = "SELECT * FROM wishlist WHERE user_id = ? AND product_id = ? AND variant_id IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
}
$stmt->execute();
$wishlist_result = $stmt->get_result();
// $wishlist_item = $result->fetch_assoc();
// print_r($result);
// exit();

// Get all variants for this product
$variant_query = "
SELECT * FROM product_variants 
WHERE product_id = $product_id AND status = 'active'
ORDER BY id ASC";

$variant_result = mysqli_query($conn, $variant_query);
$variants = [];
while ($row = mysqli_fetch_assoc($variant_result)) {
    $variants[] = $row;
}

// Initialize display data with main product data
$displayProduct = $product;
$selectedVariant = null;

// If variant is specified, fetch and use variant data
if ($variant && !empty($variants)) {
    // Find the specific variant (assuming variant parameter is variant ID or variant_name)
    foreach ($variants as $var) {
        if ($var['id'] == $variant || $var['variant_name'] == $variant || $var['sku'] == $variant) {
            $selectedVariant = $var;
            break;
        }
    }

    // If variant found, override product data with variant data
    if ($selectedVariant) {
        $displayProduct['variant_id'] = $selectedVariant['id'];
        $displayProduct['price'] = $selectedVariant['price'];
        $displayProduct['discount_price'] = $selectedVariant['discount_price'];
        $displayProduct['stock_quantity'] = $selectedVariant['stock_quantity'];
        $displayProduct['sku'] = $selectedVariant['sku'];

        // Use variant image if available, otherwise use main product image
        if (!empty($selectedVariant['image'])) {
            $displayProduct['image'] = $selectedVariant['image'];
            // print_r($displayProduct['image']);
            // exit;
        }

        // Update product name to include variant
        $displayProduct['name'] = $product['name'] . ' - ' . $selectedVariant['variant_name'];
    }
}

$videos = json_decode($product['video'], true);

// Ensure $videos is always an array



// Set product name for display
$productName = $displayProduct['name'];

include_once './layout/header.php';
?>
<style>
    .variant-active {
        border: 2px solid rgba(0, 0, 0, 0.57) !important;
    }
</style>

<!-- main start -->
<main class="pt-12">
    <!-- hero section start -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3"><?= $displayProduct['name'] ?></span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active"><a href="#"><?= $displayProduct['name'] ?></a></li>
            </ul>
        </div>
    </section>
    <!-- hero section end -->

    <!-- product details section start -->
    <section
        class="product-details-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120 border-bottom border-n100-1 overflow-lg-visible">
        <div class="container-fluid">
            <!-- product info -->
            <div class="row g-6 justify-content-between">
                <div class="col-lg-7">
                    <div class="position-sticky top-15">
                        <div class="swiper product-swiper mb-6">
                            <div class="swiper-wrapper">
                                <?php
                                $images = json_decode($displayProduct['image'], true);
                                foreach ($images as  $value) {  ?>
                                    <div class="swiper-slide">
                                        <div class="product-item zoomable-image-container position-relative bg-n20"
                                            data-lenis-prevent>
                                            <a href="./assets/uploads/product/<?= $value ?>"
                                                class="product-popup-box d-block position-absolute bottom-0 start-0 mb-6 ms-6 z-2">
                                                <span
                                                    class="icon-40px border border-n100-1 radius-unset text-2xl bg-n0">
                                                    <i class="ph-fill ph-arrows-in-simple"></i>
                                                </span>
                                            </a>
                                            <img class="w-100 radius-16" src="./assets/uploads/product/<?= $value ?>"
                                                alt="product">
                                        </div>
                                    </div>
                                <?php } ?>


                            </div>
                        </div>
                        <div class="position-relative z-1">
                            <div class="position-absolute top-50 start-0 z-3 translate-middle">
                                <button
                                    class="thumb-prev icon-40px hover-text-n0 bg-n0 box-style box-secondary2 shadow-2">
                                    <i class="ph ph-caret-left"></i>
                                </button>
                            </div>
                            <div class="position-absolute top-50 start-100 z-3 translate-middle">
                                <button
                                    class="thumb-next icon-40px hover-text-n0 bg-n0 box-style box-secondary2 shadow-2">
                                    <i class="ph ph-caret-right"></i>
                                </button>
                            </div>
                            <div class="swiper swiper-thumb">
                                <div class="swiper-wrapper">
                                    <?php foreach ($images as  $value) {  ?>
                                        <div class="swiper-slide bg-n20">
                                            <img class="w-100" src="./assets/uploads/product/<?= $value ?>" alt="product">
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-5">
                    <div class="position-sticky top-15">
                        <div class="d-flex align-items-center justify-content-between mb-lg-4 mb-md-3 mb-2">
                            <h1 class="mb-lg-4 mb-2 fs-2"><?= $displayProduct['name'] ?></h1>

                            <?php
                            if ($wishlist_result->num_rows > 0) {
                            ?>
                                <button class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n20 position-relative z-3 tooltip-btn tooltip-left removeFromWishlist"
                                    data-tooltip="Remove from wishlist"
                                    data-product="<?= $displayProduct['id'] ?>"
                                    data-variant="<?= $variant ?>">
                                    <i class="ph-heart ph-fill"></i>
                                </button>
                            <?php
                            } else {
                            ?>
                                <button class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n20 position-relative z-3 tooltip-btn tooltip-left addToWishlist"
                                    data-tooltip="Add to wishlist"
                                    data-product="<?= $displayProduct['id'] ?>"
                                    data-variant="<?= $variant ?>">
                                    <i class="ph ph-heart"></i>
                                </button>
                            <?php
                            }
                            ?>
                        </div>

                        <div
                            class="d-flex align-items-center gap-4xl-20 gap-3xl-15 gap-xl-10 gap-lg-8 gap-md-6 gap-4 gap-4 mb-lg-8 mb-md-6 mb-4">
                            <div class="product-rating d-flex align-items-center gap-1">
                                <ul class="d-flex align-items-center">
                                    <li class="text-secondary2"><i class="ph-fill ph-star"></i></li>
                                    <li class="text-secondary2"><i class="ph-fill ph-star"></i></li>
                                    <li class="text-secondary2"><i class="ph-fill ph-star"></i></li>
                                    <li class="text-secondary2"><i class="ph-fill ph-star"></i></li>
                                    <li class="text-n50"><i class="ph ph-star"></i></li>
                                </ul>
                                <span class="text-sm text-n50 text-nowrap">5 REVIEWS</span>
                            </div>
                            <button class="view-all-reviews text-sm text-n100 hover-text-secondary2">VIEW
                                ALL
                                REVIEWS</button>
                        </div>
                        <h5 class="text-n100 mb-lg-1 mb-1">₹<del><?= number_format($displayProduct['price'], 2) ?></del></h5>
                        <h3 class="text-n100 mb-lg-4 mb-2">₹<?= number_format($displayProduct['discount_price'], 2) ?></h3>

                        <p class="text-n50 text-base">
                            <?= $product['short_description'] ?>
                        </p>
                        <?php
                        // Get current URL components for variant links
                        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
                        $baseUrl = strtok($currentUrl, '?') ?: '';
                        $queryParams = [];

                        // Parse existing query parameters
                        if (!empty($_SERVER['QUERY_STRING'])) {
                            parse_str($_SERVER['QUERY_STRING'], $queryParams);
                        }

                        // Get current variant ID for highlighting active variant
                        $currentVariantId = $_GET['variant'] ?? null;

                        // Check if we should show variants (either variants exist OR we want to always show the main product)
                        $variant_result = mysqli_query($conn, $variant_query);
                        $has_variants = mysqli_num_rows($variant_result) > 0;
                        $show_variants_section = $has_variants || true; // Always show to include main product

                        if ($show_variants_section) {
                        ?>
                            <!-- Variants Section Separator -->
                            <div class="variant-separator my-lg-5 my-md-4 my-3 border-bottom border-n100-1"></div>

                            <!-- Variants Container -->
                            <div class="variants-section mb-lg-5 mb-md-4 mb-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                                    <div class="variants-content w-100">
                                        <!-- Section Title -->
                                        <div class="variants-header mb-lg-4 mb-3">
                                            <h6 class="text-h6 mb-0">Available Options:</h6>
                                            <small class="text-muted">Choose from different available options</small>
                                        </div>

                                        <!-- Variants Grid -->
                                        <div class="variants-grid">
                                            <div class="row g-3">
                                                <?php
                                                // First, show the main product as a variant card
                                                $main_product_images = json_decode($product['image'], true);
                                                $main_product_images = is_array($main_product_images) ? $main_product_images : [];
                                                $main_first_image = !empty($main_product_images[0]) ? $main_product_images[0] : 'placeholder.jpg';

                                                // Build main product URL (without variant parameter)
                                                $mainProductParams = $queryParams;
                                                unset($mainProductParams['variant']); // Remove variant parameter for main product
                                                $mainProductUrl = $baseUrl . (!empty($mainProductParams) ? '?' . http_build_query($mainProductParams) : '');

                                                // Check if main product is active (no variant selected)
                                                $isMainActive = (empty($currentVariantId));
                                                $mainActiveClass = $isMainActive ? 'variant-active' : '';

                                                // Calculate discount percentage for main product
                                                $mainOriginalPrice = floatval($product['price']);
                                                $mainDiscountPrice = floatval($product['discount_price']);
                                                ?>

                                                <!-- Main Product Card -->
                                                <div class="col-lg-2 col-md-6 col-6">
                                                    <div class="variant-card card  p-1 border-0 shadow-sm <?= $mainActiveClass ?>"
                                                        role="button"
                                                        tabindex="0"
                                                        onclick="navigateToVariant('<?= htmlspecialchars($mainProductUrl, ENT_QUOTES) ?>')"
                                                        onkeypress="handleVariantKeyPress(event, '<?= htmlspecialchars($mainProductUrl, ENT_QUOTES) ?>')"
                                                        data-variant-id="main"
                                                        aria-label="Select main product priced at ₹<?= number_format($mainDiscountPrice, 2) ?>">

                                                        <!-- Main Product Image -->
                                                        <div class="variant-image-container position-relative overflow-hidden">
                                                            <!-- Main Product Badge -->
                                                            <img src="./assets/uploads/product/<?= htmlspecialchars($main_first_image) ?>"
                                                                class="card-img-top variant-image"
                                                                alt="<?= htmlspecialchars($product['product_name'] ?? 'Main Product Image') ?>"
                                                                loading="lazy"
                                                                onerror="this.src='./assets/uploads/product/placeholder.jpg'">
                                                        </div>

                                                        <!-- Main Product Details -->
                                                        <div class="card-body p-3">
                                                            <div class="variant-pricing">
                                                                <small class="current-price fw-bold ">
                                                                    ₹<?= $mainDiscountPrice ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php
                                                // Now show all variants if they exist
                                                if ($has_variants) {
                                                    while ($variant = mysqli_fetch_assoc($variant_result)) {
                                                        // Safely decode variant images
                                                        $variant_images = json_decode($variant['image'], true);
                                                        $variant_images = is_array($variant_images) ? $variant_images : [];
                                                        $first_image = !empty($variant_images[0]) ? $variant_images[0] : 'placeholder.jpg';

                                                        // Build variant URL
                                                        $variantParams = $queryParams;
                                                        $variantParams['variant'] = $variant['id'];
                                                        $variantUrl = $baseUrl . '?' . http_build_query($variantParams);

                                                        // Check if this is the active variant
                                                        $isActive = ($currentVariantId == $variant['id']);
                                                        $activeClass = $isActive ? 'variant-active' : '';

                                                        // Calculate discount percentage
                                                        $originalPrice = floatval($variant['price']);
                                                        $discountPrice = floatval($variant['discount_price']);

                                                ?>
                                                        <div class="col-lg-2 col-md-6 col-6">
                                                            <div class="variant-card card p-1 border-0 shadow-sm <?= $activeClass ?>"
                                                                role="button"
                                                                tabindex="0"
                                                                onclick="navigateToVariant('<?= htmlspecialchars($variantUrl, ENT_QUOTES) ?>')"
                                                                onkeypress="handleVariantKeyPress(event, '<?= htmlspecialchars($variantUrl, ENT_QUOTES) ?>')"
                                                                data-variant-id="<?= htmlspecialchars($variant['product_id']) ?>"
                                                                aria-label="Select variant priced at ₹<?= number_format($discountPrice, 2) ?>">

                                                                <!-- Variant Image -->
                                                                <div class="variant-image-container position-relative overflow-hidden">
                                                                    <img src="./assets/uploads/product/<?= htmlspecialchars($first_image) ?>"
                                                                        class="card-img-top variant-image"
                                                                        alt="<?= htmlspecialchars($variant['product_name'] ?? 'Variant Image') ?>"
                                                                        loading="lazy"
                                                                        onerror="this.src='./assets/uploads/product/placeholder.jpg'">
                                                                </div>

                                                                <!-- Variant Details -->
                                                                <div class="card-body p-3">
                                                                    <div class="variant-pricing">
                                                                        <small class="current-price fw-bold ">
                                                                            ₹<?= $discountPrice ?>
                                                                        </small>
                                                                    </div>

                                                                    <!-- Additional variant info if available -->
                                                                    <?php if (!empty($variant['variant_name'])) { ?>
                                                                        <!-- <div class="variant-name mt-2">
                                                                            <small class="text-muted"><?= htmlspecialchars($variant['variant_name']) ?></small>
                                                                        </div> -->
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                <?php
                                                    } // End while loop
                                                } // End if has_variants
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- JavaScript for variant functionality -->
                            <script>
                                function navigateToVariant(url) {
                                    if (url) {
                                        window.location.href = url;
                                    }
                                }

                                function handleVariantKeyPress(event, url) {
                                    if (event.key === 'Enter' || event.key === ' ') {
                                        event.preventDefault();
                                        navigateToVariant(url);
                                    }
                                }

                                // Add loading state when variant is clicked
                                document.addEventListener('DOMContentLoaded', function() {
                                    const variantCards = document.querySelectorAll('.variant-card');

                                    variantCards.forEach(card => {
                                        card.addEventListener('click', function() {
                                            // Add loading state
                                            this.style.opacity = '0.7';
                                            this.style.pointerEvents = 'none';

                                            // Create loading indicator
                                            const loadingDiv = document.createElement('div');
                                            loadingDiv.className = 'position-absolute w-100 h-100 top-0 start-0 d-flex align-items-center justify-content-center bg-white bg-opacity-75';
                                            loadingDiv.innerHTML = '<div class="spinner-border spinner-border-sm " role="status"></div>';
                                            this.appendChild(loadingDiv);
                                        });
                                    });
                                });
                            </script>



                        <?php } ?>

                        <div>
                            <div class="d-grid gap-lg-4 gap-2 mb-3">
                                <span class="text-h6">QUANTITY:</span>
                                <div class="d-flex align-items-center gap-2">

                                    <div
                                        class="quantity d-flex align-items-center py-3 px-lg-5 px-3 border border-n100-1">
                                        <button class="quantityDecrement text-n100"><i
                                                class="ph ph-minus"></i></button>
                                        <input type="text" value="1" class="quantityValue border-0 p-0 outline-0">
                                        <button class="quantityIncrement text-n100"><i
                                                class="ph ph-plus"></i></button>
                                    </div>
                                    <button
                                        class="text-sm fw-bold text-n100 bg-n20 hover-text-n0 hover-bg-n100 py-3 px-lg-5 px-3 border border-n100 w-100 addToCart"
                                        data-product="<?php echo  $displayProduct['id'] ?>"
                                        data-variant="<?php echo isset($displayProduct['variant_id']) ? $displayProduct['variant_id'] : ''; ?>">
                                        ADD TO CART
                                    </button>
                                </div>
                            </div>
                            <button
                                class="text-base fw-bold text-n100 bg-n0 hover-text-n0 hover-bg-secondary2 py-3 px-lg-5 px-3 border border-n100-1 w-100 mb-lg-6 mb-4">
                                Buy Now
                            </button>
                            <div class="d-flex gap-lg-5 gap-3">
                                <button class="share-btn d-flex align-items-center gap-1 text-base">
                                    <span class="d-flex text-xl"><i class="ph ph-share-network"></i></span>
                                    Share
                                </button>
                                <button class="aks-question-btn d-flex align-items-center gap-1 text-base">
                                    <span class="d-flex text-xl"><i class="ph ph-chat-circle"></i></span>
                                    Ask a Questions
                                </button>
                            </div>
                        </div>
                        <span class="d-block border-bottom border-n100-1 my-lg-8 my-md-6 my-4"></span>
                        <div class="d-grid gap-lg-4 gap-2 mb-lg-8 mb-md-6 mb-4">
                            <span class="text-h6">GUARANTEED SAFE CHECKOUT:</span>
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    <img class="w-100" src="assets/images/visa-2.png" alt="payment methods">
                                </div>
                                <div>
                                    <img class="w-100" src="assets/images/master.png" alt="payment methods">
                                </div>
                                <div>
                                    <img class="w-100" src="assets/images/express.png" alt="payment methods">
                                </div>
                                <div>
                                    <img class="w-100" src="assets/images/paypal-2.png" alt="payment methods">
                                </div>
                                <div>
                                    <img class="w-100" src="assets/images/cirrus.png" alt="payment methods">
                                </div>
                            </div>
                        </div>
                        <ul class="d-grid gap-lg-4 gap-2">
                            <li class="d-flex gap-2 text-base text-n100">
                                <span class="d-flex text-xl"><i class="ph ph-clock"></i></span>
                                Orders ship within 5 to 10 business days.
                            </li>
                            <li class="d-flex gap-2 text-base text-n100">
                                <span class="d-flex text-xl"><i class="ph ph-truck"></i></span>
                                Hoorey ! This item ships free to the US
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- product details section end -->

    <!-- share option -->
    <div class="share-option popup-wrapper">
        <div class="popup-overlay"></div>
        <div class="popup-content-wrapper d-grid gap-lg-6 gap-4 p-xl-10 p-md-8 p-6 bg-n0">
            <button
                class="popup-close-btn text-xl text-n100 position-absolute top-0 end-0 mt-lg-5 mt-3 me-lg-5 me-3">
                <i class="ph ph-x"></i>
            </button>
            <!-- link copied -->
            <div class="d-grid gap-2">
                <h6>Copy link</h6>
                <div class="copy-section d-flex align-items-center gap-2">
                    <input type="text" id="share-link" class="w-100 py-2 px-3 radius-2"
                        value="https://your-product-link.com/product/xyz" readonly>
                    <button class="copy-btn py-2 px-3 bg-n100 text-n0 radius-2">Copy</button>
                </div>
            </div>

            <!-- share options -->
            <div class="d-grid gap-2">
                <h6>Share</h6>
                <div class="share-buttons d-flex align-items-center gap-2">
                    <a href="#" class="icon-32px text-lg share-item border border-n100-1 hover-bg-n100 hover-text-n0"
                        target="_blank" data-platform="facebook">
                        <i class="ph ph-facebook-logo"></i>
                    </a>
                    <a href="#" class="icon-32px text-lg share-item border border-n100-1 hover-bg-n100 hover-text-n0"
                        target="_blank" data-platform="twitter">
                        <i class="ph ph-twitter-logo"></i>
                    </a>
                    <a href="#" class="icon-32px text-lg share-item border border-n100-1 hover-bg-n100 hover-text-n0"
                        target="_blank" data-platform="pinterest">
                        <i class="ph ph-pinterest-logo"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- aks a questions -->
    <div class="aks-question popup-wrapper">
        <div class="popup-overlay"></div>
        <div class="popup-content-wrapper d-grid gap-lg-6 gap-4 p-xl-10 p-md-8 p-6 bg-n0">
            <button
                class="popup-close-btn text-xl text-n100 position-absolute top-0 end-0 mt-lg-5 mt-3 me-lg-5 me-3">
                <i class="ph ph-x"></i>
            </button>
            <form action="#">
                <h6 class="mb-3">Ask a Question</h6>
                <div class="d-grid gap-2 mb-4">
                    <input type="text" class="w-100 py-2 px-3 radius-2 border border-n100-1 focus-secondary2"
                        placeholder="Your Name">
                    <input type="email" class="w-100 py-2 px-3 radius-2 border border-n100-1 focus-secondary2"
                        placeholder="Your Email">
                    <textarea name="question" rows="4"
                        class="w-100 py-2 px-3 radius-2 border border-n100-1 focus-secondary2"
                        placeholder="Your Message"></textarea>
                </div>
                <button type="submit" class="submit-btn py-2 px-3 bg-n100 text-n0 radius-2">Submit</button>
            </form>
        </div>
    </div>

    <!-- product review section start -->
    <section class="product-review-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120">
        <div class="container-fluid">
            <div class="row">
                <div class="col-3xl-7 col-xxl-8 col-xl-10">
                    <!-- product review -->
                    <div class="tab-btn-area mb-lg-10 mb-md-8 mb-6">
                        <ul class="d-flex gap-4 flex-wrap">
                            <li><button class="tab-btn py-lg-3 py-sm-2 py-1 px-lg-6 px-4 radius-pill active"
                                    data-tab="1">Description</button>
                            </li>
                            <li><button class="tab-btn py-lg-3 py-sm-2 py-1 px-lg-6 px-4 radius-pill"
                                    data-tab="2">Specifications</button>
                            </li>
                            <li><button class="tab-btn py-lg-3 py-sm-2 py-1 px-lg-6 px-4 radius-pill"
                                    data-tab="3">Video</button>
                            </li>
                            <li><button class="tab-btn py-lg-3 py-sm-2 py-1 px-lg-6 px-4 radius-pill"
                                    data-tab="4">Customer
                                    Reviews</button></li>
                        </ul>
                    </div>
                    <div class="mb-lg-15 mb-md-10 mb-8">
                        <!-- tab content 1 -->
                        <div class="tab-content active" data-tab="1">
                            <div class="d-grid gap-lg-6 gap-4 mb-lg-10 mb-md-8 mb-6">
                                <p class="text-n50 text-base">
                                    <?= $product['long_description'] ?>
                                </p>
                            </div>
                        </div>

                        <!-- tab content 2 -->
                        <div class="tab-content" data-tab="2">
                            <ul class="d-grid gap-lg-6 gap-4">
                                <?php $specifications = json_decode($product['specifications'], true);
                                foreach ($specifications as $key => $item) { ?>
                                    <li class="row gx-lg-6 gy-lg-0 gx-0 gy-2 pb-lg-6 pb-4 border-bottom border-n100-1">
                                        <div class="col-lg-4">
                                            <span class="text-n100 text-base fw-semibold max-w-300px w-100"><?= $item['name'] ?></span>
                                        </div>
                                        <div class="col-lg-8">
                                            <span class="text-n50 text-base"><?= $item['value'] ?></span>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>

                        <div class="tab-content" data-tab="3">
                            <?php if (!empty($videos) && is_array($videos)) : ?>
                                <?php foreach ($videos as $iframe) : ?>
                                    <?php if (!empty($iframe)) : ?>
                                        <div class="video-area w-100 h-100">
                                            <?= $iframe ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No videos available.</p>
                            <?php endif; ?>
                            <?php if (!is_array($videos)) {
                                // If it's a single string, wrap it in an array
                                $videos = !empty($product['video']) ? [$product['video']] : [];
                            } ?>

                            <?php if (!empty($videos)) {
                                foreach ($videos as $video) {
                                    $video = str_replace('["', '', $video);
                                    $video = str_replace('"]', '', $video); ?>
                                    <div class="video-area w-100 h-100">
                                        <?= $video ?>
                                    </div>
                            <?php
                                }
                            } else {
                                echo "No videos available.";
                            } ?>
                        </div>

                        <!-- tab content 3 -->

                        <!-- tab content 4 -->
                        <div class="tab-content" data-tab="4">
                            <h3 class="text-n100 text-center mb-lg-8 mb-6">Customer Reviews</h3>
                            <div
                                class="d-md-flex d-grid justify-content-md-between justify-content-center align-items-center gap-4">
                                <div class="text-center text-md-start">
                                    <div class="d-flex gap-2 justify-content-center justify-content-md-start">
                                        <ul class="d-flex align-items-center">
                                            <li>
                                                <span class="text-n100 text-base"><i
                                                        class="ph-fill ph-star"></i></span>
                                            </li>
                                            <li>
                                                <span class="text-n100 text-base"><i
                                                        class="ph-fill ph-star"></i></span>
                                            </li>
                                            <li>
                                                <span class="text-n100 text-base"><i
                                                        class="ph-fill ph-star"></i></span>
                                            </li>
                                            <li>
                                                <span class="text-n100 text-base"><i
                                                        class="ph-fill ph-star"></i></span>
                                            </li>
                                            <li>
                                                <span class="text-n100 text-base"><i
                                                        class="ph-fill ph-star"></i></span>
                                            </li>
                                        </ul>
                                        <span class="text-n50 text-sm">4.5 out of 5</span>
                                    </div>
                                    <span class="text-n50 text-sm">Based on 4 reviews</span>
                                </div>
                                <div class="w-md-1px w-100 bg-n100-1 h-md-112px h-1px"></div>
                                <div>
                                    <div class="rating-container">
                                        <div class="rating-row d-flex gap-1 align-items-center">
                                            <span>★★★★★</span>
                                            <div class="progress-bar h-6 bg-n100-1 radius-pill">
                                                <div class="filled-bar h-100 bg-n100 radius-pill" style="width: 0;">
                                                </div>
                                            </div>
                                            <span>0</span>
                                        </div>
                                        <div class="rating-row d-flex gap-1 align-items-center">
                                            <span>★★★★☆</span>
                                            <div class="progress-bar h-6 bg-n100-1 radius-pill">
                                                <div class="filled-bar h-100 bg-n100 radius-pill" style="width: 0;">
                                                </div>
                                            </div>
                                            <span>0</span>
                                        </div>
                                        <div class="rating-row d-flex gap-1 align-items-center">
                                            <span>★★★☆☆</span>
                                            <div class="progress-bar h-6 bg-n100-1 radius-pill">
                                                <div class="filled-bar h-100 bg-n100 radius-pill" style="width: 0;">
                                                </div>
                                            </div>
                                            <span>0</span>
                                        </div>
                                        <div class="rating-row d-flex gap-1 align-items-center">
                                            <span>★★☆☆☆</span>
                                            <div class="progress-bar h-6 bg-n100-1 radius-pill">
                                                <div class="filled-bar h-100 bg-n100 radius-pill" style="width: 0;">
                                                </div>
                                            </div>
                                            <span>0</span>
                                        </div>
                                        <div class="rating-row d-flex gap-1 align-items-center">
                                            <span>★☆☆☆☆</span>
                                            <div class="progress-bar h-6 bg-n100-1 radius-pill">
                                                <div class="filled-bar h-100 bg-n100 radius-pill" style="width: 0;">
                                                </div>
                                            </div>
                                            <span>0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-md-1px w-100 bg-n100-1 h-md-112px h-1px"></div>
                                <div class="text-center text-md-start">
                                    <button
                                        class="review-toggle-btn text-n100 fw-medium py-lg-3 py-sm-2 py-1 px-lg-6 px-4 radius-pill border border-n100 hover-text-n0 hover-bg-n100">
                                        ADD REVIEW
                                    </button>
                                </div>
                            </div>
                            <span class="d-block border-bottom border-n100-1 my-lg-8 my-6"></span>
                            <div class="review-form">
                                <form action="#">
                                    <div class="mb-lg-8 mb-6">
                                        <h3 class="text-n100 text-center mb-lg-4 mb-2">Write a review</h3>
                                        <span class="d-block text-n50 text-base text-center">Rating</span>
                                        <div class="rating-input-container text-center">
                                            <div class="stars d-flex justify-content-center">
                                                <input type="radio" id="star5" name="rating" value="5">
                                                <label for="star5" title="5 stars">&#9733;</label>
                                                <input type="radio" id="star4" name="rating" value="4">
                                                <label for="star4" title="4 stars">&#9733;</label>
                                                <input type="radio" id="star3" name="rating" value="3">
                                                <label for="star3" title="3 stars">&#9733;</label>
                                                <input type="radio" id="star2" name="rating" value="2">
                                                <label for="star2" title="2 stars">&#9733;</label>
                                                <input type="radio" id="star1" name="rating" value="1">
                                                <label for="star1" title="1 star">&#9733;</label>
                                            </div>
                                            <div id="selected-rating"></div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-lg-6 gap-4 mb-lg-8 mb-6">
                                        <div class="d-grid gap-2">
                                            <label class="text-n100 fw-medium text-base text-center">
                                                Review Title(100)
                                            </label>
                                            <input type="text"
                                                class="text-n100 fw-medium text-base py-lg-4 py-3 px-xl-8 px-lg-6 px-4 border border-n100-1"
                                                placeholder="Give your review a title">
                                        </div>
                                        <div class="d-grid gap-2">
                                            <label class="text-n100 fw-medium text-base text-center">
                                                Review
                                            </label>
                                            <textarea
                                                class="text-n100 fw-medium text-base py-lg-4 py-3 px-xl-8 px-lg-6 px-4 border border-n100-1"
                                                placeholder="Write your comment here..." rows="5"></textarea>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <label class="text-n100 fw-medium text-base text-center">
                                                Picture/video (optional)
                                            </label>
                                            <div
                                                class="file-upload-wrapper border border-n100-1 w-100 cursor-pointer">
                                                <span
                                                    class="upload-icon text-h1 text-center py-lg-20 py-md-15 py-10 bg-n20 w-100">
                                                    <i class="ph ph-upload-simple"></i>
                                                </span>
                                                <input type="file" accept="image/*,video/*" multiple
                                                    class="file-upload-input" hidden>
                                                <div class="file-upload-preview"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-lg-6 gap-4 mb-lg-8 mb-6">
                                        <div class="d-grid gap-2">
                                            <label class="text-n100 fw-medium text-base text-center">
                                                Name (displayed publicly)
                                            </label>
                                            <input type="text"
                                                class="text-n100 fw-medium text-base py-lg-4 py-3 px-xl-8 px-lg-6 px-4 border border-n100-1"
                                                placeholder="Enter your name (public)">
                                        </div>
                                        <div class="d-grid gap-2">
                                            <label class="text-n100 fw-medium text-base text-center">
                                                Email
                                            </label>
                                            <input type="email"
                                                class="text-n100 fw-medium text-base py-lg-4 py-3 px-xl-8 px-lg-6 px-4 border border-n100-1"
                                                placeholder="Enter your email (private)">
                                        </div>
                                        <span class="text-n50 text-base">
                                            How we use your data: We'll only contact you about the review you left,
                                            and only if necessary. By submitting your review, you agree to
                                            Judge.me's <a href="terms-conditions.html"
                                                class="text-n100 text-decoration-underline">terms</a>, <a
                                                href="privacy-policy.html"
                                                class="text-n100 text-decoration-underline">privacy</a> and content
                                            policies.
                                        </span>
                                    </div>
                                    <div class="d-center flex-wrap gap-lg-6 gap-4">
                                        <button type="reset"
                                            class="review-cancel-btn text-n100 fw-medium py-lg-3 py-sm-2 py-1 px-lg-6 px-4 radius-pill border border-n100 hover-text-n0 hover-bg-n100">CANCEL
                                            REVIEW</button>
                                        <button type="submit"
                                            class="text-n0 fw-medium py-lg-3 py-sm-2 py-1 px-lg-6 px-4 radius-pill border border-n100 bg-n100 hover-text-n100 hover-bg-n0">ADD
                                            REVIEW</button>
                                    </div>
                                </form>
                                <span class="d-block border-bottom border-n100-1 my-lg-8 my-6"></span>
                            </div>
                            <!-- review filter -->
                            <div
                                class="review-filter d-inline-block py-lg-3 py-2 px-lg-6 px-4 border border-n100-1 radius-pill bg-n0">
                                <select class="border-0 bg-n0">
                                    <option value="1">Sort by: Newest</option>
                                    <option value="2">Sort by: Oldest</option>
                                    <option value="3">Sort by: Highest Rating</option>
                                    <option value="4">Sort by: Lowest Rating</option>
                                </select>
                            </div>
                            <span class="d-block border-bottom border-n100-1 my-lg-8 my-6"></span>
                            <div class="review-wrapper d-grid gap-lg-6 gap-4">
                                <div class="single-review d-grid gap-lg-6 gap-4">
                                    <div class="d-between gap-2">
                                        <ul class="d-flex align-items-center text-base">
                                            <li><span><i class="ph-fill ph-star"></i></span></li>
                                            <li><span><i class="ph-fill ph-star"></i></span></li>
                                            <li><span><i class="ph-fill ph-star"></i></span></li>
                                            <li><span><i class="ph-fill ph-star"></i></span></li>
                                            <li><span><i class="ph-fill ph-star"></i></span></li>
                                        </ul>
                                        <span class="text-n50 text-base">1 day ago</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-lg-6 gap-4 flex-wrap">
                                        <div class="reviewer-thumb icon-60px overflow-hidden">
                                            <img class="w-100" src="assets/images/reviewer-thumb-1.png"
                                                alt="reviewer">
                                        </div>
                                        <span class="text-n100 text-lg fw-semibold">Angelina Jolie</span>
                                    </div>
                                    <div class="review-msg">
                                        <span class="d-block text-n100 text-base fw-medium mb-1">Gorgeous and
                                            stylish.</span>
                                        <p class="text-n50 text-base">
                                            I rarely leave reviews. So many of you do it so much better. But— this
                                            sweater right here is my favorite Umino item. My closet is almost
                                            exclusively Umino and Eileen but this sweater is so deliciously soft,
                                            warm, and the boxiness is perfect. Only thing I don't like is that it
                                            sheds a little bit. Can't be walking around like that at this adult age
                                            😂 but I keep a roller in my desk.
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center gap-lg-6 gap-4">
                                        <div class="reviewer-img icon-100px radius-unset overflow-hidden">
                                            <a href="assets/images/product-1.png" class="review-img-gallery">
                                                <img class="w-100" src="assets/images/review-img-1.png"
                                                    alt="product">
                                            </a>
                                        </div>
                                        <div class="reviewer-img icon-100px radius-unset overflow-hidden">
                                            <a href="assets/images/product-2.png" class="review-img-gallery">
                                                <img class="w-100" src="assets/images/review-img-2.png"
                                                    alt="product">
                                            </a>
                                        </div>
                                        <div class="reviewer-img icon-100px radius-unset overflow-hidden">
                                            <a href="assets/images/product-3.png" class="review-img-gallery">
                                                <img class="w-100" src="assets/images/review-img-3.png"
                                                    alt="product">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Size Chart -->
                    <a href="assets/file/size-chart.pdf" target="_pdfFileSizeChart" class="w-100 d-between p-lg-6 p-4 bg-n20 mb-lg-6 mb-4">
                        <span class="text-base fw-semibold font-noto-sans">Size Chart</span>
                        <span class="d-flex text-xl"><i class="ph ph-warning"></i></span>
                    </a>
                    <!-- faq -->
                    <div class="accordion-area d-grid gap-lg-6 gap-4">
                        <div class="accordion-item p-lg-6 p-4 bg-n20 show">
                            <div class="accordion-header d-between gap-2 text-n100 text-base fw-semibold">
                                <button class="accordion-button text-n100">
                                    6 Year Guarantee
                                </button>
                                <span class="accordion-icon d-center"></span>
                            </div>
                            <div class="accordion-content">
                                <div class="pt-lg-4 pt-2">
                                    <p class="text-n50 text-base">
                                        That's our quality promise - if you're not 100% happy with your bike, we're
                                        not happy either.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item p-lg-6 p-4 bg-n20">
                            <div class="accordion-header d-between gap-2 text-n100 text-base fw-semibold">
                                <button class="accordion-button text-n100">
                                    Simply assembly
                                </button>
                                <span class="accordion-icon d-center"></span>
                            </div>
                            <div class="accordion-content">
                                <div class="pt-lg-4 pt-2">
                                    <p class="text-n50 text-base">
                                        That's our quality promise - if you're not 100% happy with your bike, we're
                                        not happy either.
                                    </p>
                                </div>
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
                    <h2 class="display-four text-n100 text-uppercase">RELATED PRODUCTS</h2>
                </div>
            </div>
            <div class="row g-0">
                <?php
                // Fetch related products based on category
                $related_query = "
                SELECT 
                    p.*, 
                    c.name AS category_name, 
                    c.slug AS category_slug,
                    b.name AS brand_name,
                    b.slug AS brand_slug
                FROM 
                    products p
                LEFT JOIN 
                    categories c ON p.category_id = c.id
                LEFT JOIN 
                    brands b ON p.brand_id = b.id
                WHERE 
                    p.category_id = {$product['category_id']}
                    AND p.id != {$product_id}
                    AND p.status = 'active'
                ORDER BY 
                    RAND()
                LIMIT 6";

                $related_result = mysqli_query($conn, $related_query);
                $related_products = [];
                while ($row = mysqli_fetch_assoc($related_result)) {
                    $related_products[] = $row;
                }
                if (!empty($related_products)) {
                    foreach ($related_products as $related) {
                        // Decode images
                        $related_images = json_decode($related['image'], true);
                        $related_first_image = !empty($related_images[0]) ? $related_images[0] : 'placeholder.jpg';

                        // Check if product is in wishlist
                        if ($user_id) {
                            // echo "SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = {$related['id']} AND variant_id  0";
                            $wishlist_check = mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = {$related['id']} AND variant_id IS NULL");
                            $in_wishlist = mysqli_num_rows($wishlist_check) > 0;
                        } else {
                            $in_wishlist = false; 
                        }
                ?>
                        <div class="col-lg-4 col-xs-6">
                            <!-- product item -->
                            <div class="product-card2 position-relative p-xl-10 p-lg-8 p-6 bg-n0 border border-n100-5 box-style box-n20 card-tilt animate-box">
                                <?php if ($related['is_featured']) { ?>
                                    <div class="product-type py-lg-3 py-2 ps-lg-4 ps-2 pe-lg-6 pe-4 bg-secondary2 position-absolute top-0 start-0 parallelogram-path z-2">
                                        <span class="text-sm fw-medium text-n0">New</span>
                                    </div>
                                <?php } ?>
                                <div class="product-thumb-wrapper position-relative">

                                    <button class="single-wishlist-btn text-secondary2 text-xl icon-52px bg-n0 position-absolute top-0 right-0 z-3 tooltip-btn tooltip-left <?= $in_wishlist ? 'removeFromWishlist' : 'addToWishlist' ?>"
                                        data-tooltip="<?= $in_wishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>"
                                        data-product="<?= $related['id'] ?>"
                                        data-variant="">
                                        <i class="ph <?= $in_wishlist ? 'ph-fill' : '' ?> ph-heart"></i>
                                    </button>

                                    <div class="product-thumb hover-cursor" data-hover-text="View Product">
                                        <a href="shop-details.php?slug=<?= htmlspecialchars($related['slug']) ?>" class="product-thumb-link d-block">
                                            <img class="w-100" src="./assets/uploads/product/<?= htmlspecialchars($related_first_image) ?>"
                                                alt="<?= htmlspecialchars($related['name']) ?>"
                                                onerror="this.src='./assets/uploads/product/placeholder.jpg'">
                                        </a>
                                    </div>
                                </div>

                                <span class="d-block h-1px w-100 bg-n100-1 mb-lg-6 mb-4 mt-lg-10 mt-6"></span>

                                <div class="product-info-wrapper">
                                    <div class="mb-xxl-7 mb-md-5 mb-3">
                                        <a href="shop-details.php?slug=<?= htmlspecialchars($related['slug']) ?>">
                                            <h4 class="text-n100 mb-2 hover-text-secondary2">
                                                <?= htmlspecialchars($related['name']) ?>
                                            </h4>
                                        </a>
                                        <span class="text-sm fw-normal text-n50"><?= htmlspecialchars($related['category_name']) ?></span>
                                    </div>

                                    <div class="d-between flex-wrap gap-4">
                                        <div class="d-grid">
                                            <?php if ($related['price'] > $related['discount_price']) { ?>
                                                <span class="text-sm fw-normal text-n50 text-decoration-line-through">
                                                    ₹<?= number_format($related['price'], 2) ?>
                                                </span>
                                            <?php } ?>
                                            <span class="text-xl fw-semibold text-secondary2">
                                                ₹<?= number_format($related['discount_price'], 2) ?>
                                            </span>
                                        </div>

                                        <button class="outline-btn text-n100 fw-medium box-style box-secondary2 addToCart"
                                            data-product="<?= $related['id'] ?>"
                                            data-variant="">
                                            ADD TO CART
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="col-12 text-center py-5">';
                    echo '<p class="text-n50">No related products found.</p>';
                    echo '</div>';
                }
                ?>
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

    <!-- call to action -->
    <!-- call to action section start -->
    <section class="call-to-action-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120 bg-n100">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-lg-8 mb-6">
                        <h2 class="text-animation-word display-four text-n0 text-uppercase mb-lg-5 mb-3">
                            JOIN THE
                            <span class="text-secondary2 text-decoration-underline">CYCLECITY</span>
                            COMMUNITY
                        </h2>
                        <p class="text-sm text-n30 fw-normal ch-100 mx-auto">
                            Stay updated with the latest in cycling. Sign up for our newsletter to receive exclusive
                            offers, product updates, and tips straight to your inbox. Join our biking community
                            today!
                        </p>
                    </div>
                    <form action="#" class="d-center flex-wrap flex-sm-nowrap cta-form mx-auto">
                        <input type="email" placeholder="Enter your email address"
                            class="bg-transparent text-n0  py-lg-4 py-3 px-lg-6 px-4 border border-n20-1 focus-primary">
                        <button type="submit"
                            class="text-n100 fw-medium text-capitalize bg-n0 font-instrument py-lg-4 py-3 px-lg-6 px-4 hover-text-n0 box-style box-primary2">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- call to action section end -->

</main>
<!-- main end -->

<?php include_once './layout/footer.php'; ?>