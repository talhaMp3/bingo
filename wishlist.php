<?php
session_start();
include_once './include/connection.php';
include_once './layout/header.php';



$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "Please log in to view your wishlist.";
    exit;
}



$query = "
    SELECT 
        w.id as wishlist_id,
        w.product_id,
        w.variant_id,
        p.name as product_name,
        p.image as product_image,
        p.stock_quantity as product_stock,
        COALESCE(pv.price, p.price) as price,
        COALESCE(pv.stock_quantity, p.stock_quantity) as stock_quantity,
        COALESCE(pv.image, p.image) as variant_image,
        pv.variant_name
    FROM wishlist w
    LEFT JOIN products p ON w.product_id = p.id
    LEFT JOIN product_variants pv ON w.variant_id = pv.id
    WHERE w.user_id = ? AND p.status = 'active'
    ORDER BY w.created_at DESC
";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- main start -->
<main class="pt-12">

    <!-- hero section start -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3">Wishlist</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">Wishlist</a></li>
            </ul>
        </div>
    </section>
    <!-- hero section end -->

    <!-- Wishlist section start -->
    <section class="wishlist-section py-5">
        <div class="container-fluid px-3 px-lg-4">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                        <div>
                            <h2 class="h3 text-dark fw-bold mb-2">My Wishlist</h2>
                            <p class="text-muted mb-0">Save your favorite items for later</p>
                        </div>
                        <?php if ($result->num_rows > 0): ?>
                            <div class="mt-3 mt-md-0">
                                <button class="btn btn-outline-danger" id="clearWishlist">
                                    <i class="ph ph-trash me-2"></i>Remove All Items
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-12">
                    <?php if ($result->num_rows > 0): ?>
                        <!-- Desktop Table View -->
                        <div class="d-none d-lg-block">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-0 py-3 px-4" width="5%">Action</th>
                                                    <th class="border-0 py-3" width="5%">#</th>
                                                    <th class="border-0 py-3" width="15%">Image</th>
                                                    <th class="border-0 py-3" width="35%">Product Name</th>
                                                    <th class="border-0 py-3" width="15%">Price</th>
                                                    <th class="border-0 py-3" width="15%">Status</th>
                                                    <th class="border-0 py-3 px-4" width="10%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i = 1; ?>
                                                <?php while ($row = $result->fetch_assoc()): ?>
                                                    <?php
                                                    // Handle product image
                                                    $product_images = json_decode($row['product_image'], true);
                                                    $default_image = 'assets/product/default-product.png';

                                                    // Determine which image to use
                                                    if ($row['variant_id'] && !empty($row['variant_image'])) {
                                                        $variant_images = json_decode($row['variant_image'], true);
                                                        $image_url = (is_array($variant_images) && !empty($variant_images[0]))
                                                            ? './assets/uploads/product/' . htmlspecialchars($variant_images[0])
                                                            : './assets/uploads/product/' . $default_image;
                                                    } else {
                                                        $image_url = (is_array($product_images) && !empty($product_images[0]))
                                                            ? './assets/uploads/product/' . htmlspecialchars($product_images[0])
                                                            : './assets/uploads/product/' . $default_image;
                                                    }

                                                    // Build product display name
                                                    $display_name = htmlspecialchars($row['product_name']);
                                                    if ($row['variant_id'] && !empty($row['variant_name'])) {
                                                        $display_name .= ' - ' . htmlspecialchars($row['variant_name']);
                                                    }
                                                    ?>
                                                    <tr class="wishlist-item">
                                                        <td class="px-4 py-3">
                                                            <button class="btn btn-sm btn-outline-danger removeFromWishlist fs-3 ph ph-x"
                                                                data-product="<?php echo $row['product_id'] ?>"
                                                                data-variant="<?php echo isset($row['variant_id']) ? $row['variant_id'] : ''; ?>"
                                                                title="Remove from wishlist">

                                                            </button>
                                                        </td>
                                                        <td class="py-3">
                                                            <span class="text-muted fw-medium"><?= $i++ ?></span>
                                                        </td>
                                                        <td class="py-3">
                                                            <div class="position-relative">
                                                                <img src="<?= $image_url ?>"
                                                                    class="rounded border img-fluid"
                                                                    style="width: 100px; height: 80px; "
                                                                    alt="Product">
                                                            </div>
                                                        </td>
                                                        <td class="py-3">
                                                            <h6 class="mb-1 fw-semibold text-dark"><?= $display_name ?></h6>
                                                        </td>
                                                        <td class="py-3">
                                                            <span class="h6 text-success fw-bold mb-0">₹<?= number_format($row['price'], 2) ?></span>
                                                        </td>
                                                        <td class="py-3">
                                                            <?php if ($row['stock_quantity'] > 0): ?>
                                                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                                                    <i class="ph ph-check-circle me-1"></i>In Stock (<?= $row['stock_quantity'] ?>)
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                                                                    <i class="ph ph-x-circle me-1"></i>Out of Stock
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <?php if ($row['stock_quantity'] > 0): ?>
                                                                <form method="POST" action="add_to_cart.php" class="d-inline">
                                                                    <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                                                    <?php if ($row['variant_id']): ?>
                                                                        <input type="hidden" name="variant_id" value="<?= $row['variant_id'] ?>">
                                                                    <?php endif; ?>
                                                                    <input type="hidden" name="quantity" value="1">
                                                                    <button type="submit" class="btn-secondary radius-unset px-3 addToCart"
                                                                        data-product="<?php echo $row['product_id'] ?>"
                                                                        data-variant="<?php echo isset($row['variant_id']) ? $row['variant_id'] : ''; ?>">
                                                                        ADD TO CART
                                                                    </button>
                                                                </form>
                                                            <?php else: ?>
                                                                <button class="btn btn-secondary btn-sm px-3" disabled>
                                                                    <i class="ph ph-shopping-cart me-1"></i>Unavailable
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="d-lg-none">
                            <?php
                            // Reset result pointer for mobile view
                            mysqli_data_seek($result, 0);
                            $i = 1;
                            ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                // Handle product image (same logic as above)
                                $product_images = json_decode($row['product_image'], true);
                                $default_image = 'assets/product/default-product.png';

                                if ($row['variant_id'] && !empty($row['variant_image'])) {
                                    $variant_images = json_decode($row['variant_image'], true);
                                    $image_url = (is_array($variant_images) && !empty($variant_images[0]))
                                        ? './assets/uploads/product/' . htmlspecialchars($variant_images[0])
                                        : './assets/uploads/product/' . $default_image;
                                } else {
                                    $image_url = (is_array($product_images) && !empty($product_images[0]))
                                        ? './assets/uploads/product/' . htmlspecialchars($product_images[0])
                                        : './assets/uploads/product/' . $default_image;
                                }

                                $display_name = htmlspecialchars($row['product_name']);
                                if ($row['variant_id'] && !empty($row['variant_name'])) {
                                    $display_name .= ' - ' . htmlspecialchars($row['variant_name']);
                                }
                                ?>
                                <div class="card border-0 shadow-sm mb-3 wishlist-item">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center">
                                            <div class="col-4 col-sm-3">
                                                <img src="<?= $image_url ?>"
                                                    class="img-fluid rounded border"
                                                    style="height: 80px; object-fit: cover; width: 100%;"
                                                    alt="Product">
                                            </div>
                                            <div class="col-8 col-sm-9">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-1 fw-semibold text-dark flex-grow-1 me-2"><?= $display_name ?></h6>
                                                    <button class="btn btn-sm btn-outline-danger rounded-circle p-1 removeFromWishlist"
                                                        data-product="<?php echo $row['product_id'] ?>"
                                                        data-variant="<?php echo isset($row['variant_id']) ? $row['variant_id'] : ''; ?>"
                                                        title="Remove from wishlist">
                                                        <i class="ph ph-x"></i>
                                                    </button>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="h6 text-success fw-bold mb-0">₹<?= number_format($row['price'], 2) ?></span>
                                                    <?php if ($row['stock_quantity'] > 0): ?>
                                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill small">
                                                            In Stock
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill small">
                                                            Out of Stock
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-grid">
                                                    <?php if ($row['stock_quantity'] > 0): ?>
                                                        <form method="POST" action="add_to_cart.php">
                                                            <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                                            <?php if ($row['variant_id']): ?>
                                                                <input type="hidden" name="variant_id" value="<?= $row['variant_id'] ?>">
                                                            <?php endif; ?>
                                                            <input type="hidden" name="quantity" value="1">
                                                            <button type="submit" class="btn btn-primary btn-sm addToCart"
                                                                data-product="<?php echo $row['product_id'] ?>"
                                                                data-variant="<?php echo isset($row['variant_id']) ? $row['variant_id'] : ''; ?>">
                                                                <i class="ph ph-shopping-cart me-1"></i>Add to Cart
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary btn-sm" disabled>
                                                            <i class="ph ph-shopping-cart me-1"></i>Unavailable
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                    <?php else: ?>
                        <!-- Empty Wishlist State -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle"
                                    style="width: 120px; height: 120px;">
                                    <i class="ph ph-heart text-muted" style="font-size: 3rem;"></i>
                                </div>
                            </div>
                            <div class="mb-4">
                                <h3 class="h4 text-dark fw-bold mb-2">Your Wishlist is Empty</h3>
                                <p class="text-muted mb-0 mx-auto" style="max-width: 400px;">
                                    You don't have any products in your wishlist yet. Browse our collection and save your favorite items.
                                </p>
                            </div>
                            <div>
                                <a href="shop.php" class="btn btn-outline-dark  px-4 py-2">
                                    <i class="ph ph-storefront me-2"></i>Start Shopping
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript for Remove All functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Remove All Items functionality
            const removeAllBtn = document.getElementById('removeAllFromWishlist');
            if (removeAllBtn) {
                removeAllBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to remove all items from your wishlist?')) {
                        // You can implement AJAX call here to remove all items
                        // For now, redirecting to a PHP script
                        window.location.href = 'remove_all_wishlist.php';
                    }
                });
            }

            // Individual remove item functionality
            document.querySelectorAll('.removeFromWishlist').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const productId = this.dataset.product;
                    const variantId = this.dataset.variant;
                    const wishlistItem = this.closest('.wishlist-item');

                    if (confirm('Remove this item from your wishlist?')) {
                        // You can implement AJAX call here
                        // For now, showing how to remove the item visually
                        wishlistItem.style.opacity = '0.5';
                        // Add your AJAX call to remove_from_wishlist.php here
                    }
                });
            });
        });
    </script>
    <!-- Wishlist section end -->

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