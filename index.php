<?php
session_start();
include_once './include/connection.php';
include_once './layout/header.php';
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

$products_query = "
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
ORDER BY 
    products.name ASC
";

$stmt = $conn->prepare($products_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$products_result = $stmt->get_result();




$sql = "SELECT * FROM hero_banners WHERE status='active' ORDER BY sort_order ASC";
$result = $conn->query($sql);

$text_sliders_sql = "SELECT * FROM text_sliders WHERE status='active' ORDER BY sort_order ASC";
$text_sliders_result = $conn->query($text_sliders_sql);



$landing_sql = "SELECT id, banner_image, circle_image, heading, description, primary_btn_text, primary_btn_link, secondary_btn_text, secondary_btn_link, features, status, created_at 
                FROM landing_page 
                WHERE status = 'active' 
                ORDER BY id DESC 
                LIMIT 1";

$landing_res = $conn->query($landing_sql);
$landing     = $landing_res->fetch_assoc();


$features = [];
if (!empty($landing['features'])) {
  $features = json_decode($landing['features'], true);
}


$brand_logos_sql = "SELECT * FROM brand_logos WHERE status = 'active' ORDER BY sort_order ASC";
$brand_logos_res = $conn->query($brand_logos_sql);



$hero_slides_sql = "SELECT * FROM hero_slides WHERE status=1 ORDER BY sort_order ASC";
$hero_slides_res = $conn->query($hero_slides_sql);


?>

<!-- main start -->
<main class="pt-lg-6">
  <!-- hero section start -->
  <section class="hero-section px-xl-20 px-lg-10 px-sm-7 pt-120">
    <div class="container-fluid">
      <div class="row g-6">


        <div class="col-lg-7">
          <div class="swiper hero-swiper">
            <div class="swiper-wrapper">

              <?php while ($row = $hero_slides_res->fetch_assoc()): ?>
                <div class="swiper-slide">
                  <div class="hero-swiper-wrapper py-3xl-12 py-lg-8 py-6 px-4xl-20 px-3xl-10 px-lg-8 px-sm-6 p-4 bg-secondary">

                    <!-- Title -->
                    <span class="text-animation-word display-two text-n100 mb-1 text-center">
                      <?php foreach (explode(" ", $row['title']) as $word): ?>
                        <div class="word"><?= htmlspecialchars($word) ?></div>
                      <?php endforeach; ?>
                    </span>

                    <!-- Subtitle -->
                    <span class="text-animation-word d-block text-n100 mb-lg-5 mb-3 text-center">
                      <?php foreach (explode(" ", $row['subtitle']) as $word): ?>
                        <div class="word"><?= htmlspecialchars($word) ?></div>
                      <?php endforeach; ?>
                      <?php if (!empty($row['price_text'])): ?>
                        <span class="text-secondary2">
                          <div class="word"><?= htmlspecialchars($row['price_text']) ?></div>
                        </span>
                      <?php endif; ?>
                    </span>

                    <!-- Buttons -->
                    <div class="d-center gap-lg-5 gap-3">
                      <?php if (!empty($row['btn1_text'])): ?>
                        <a href="<?= htmlspecialchars($row['btn1_link']) ?>" class="btn-secondary">
                          <?= htmlspecialchars($row['btn1_text']) ?>
                          <span class="icon">
                            <i class="ph ph-arrow-up-right"></i>
                            <i class="ph ph-arrow-up-right"></i>
                          </span>
                        </a>
                      <?php endif; ?>

                      <?php if (!empty($row['btn2_text'])): ?>
                        <a href="<?= htmlspecialchars($row['btn2_link']) ?>" class="text-decoration-underline fw-medium hover-text-secondary2">
                          <?= htmlspecialchars($row['btn2_text']) ?>
                        </a>
                      <?php endif; ?>
                    </div>

                    <!-- Image -->
                    <div class="hero-swiper-item position-relative z-1 mt-5">
                      <?php if (!empty($row['badge_text'])): ?>
                        <span class="bg-text text-uppercase font-archivo top-30 left-50">
                          <?= htmlspecialchars($row['badge_text']) ?>
                        </span>
                      <?php endif; ?>
                      <img class="w-100" src="./assets/uploads/hero_slide/<?= htmlspecialchars($row['image']) ?>" alt="hero swiper">
                    </div>

                  </div>
                </div>
              <?php endwhile; ?>

            </div>

            <!-- Navigation buttons -->
            <div class="d-flex align-items-center justify-content-center gap-3 mt-n15 position-relative z-3">
              <button class="hero-swiper-prev icon-48px hover-text-n0 border border-n100-2 box-style box-secondary2">
                <i class="ph ph-caret-left"></i>
              </button>
              <button class="hero-swiper-next icon-48px hover-text-n0 border border-n100-2 box-style box-secondary2">
                <i class="ph ph-caret-right"></i>
              </button>
            </div>
          </div>
        </div>




        <div class="col-lg-5">
          <!-- hero banner -->
          <div class="d-lg-grid d-sm-flex d-grid gap-6">
            <?php while ($banner = $result->fetch_assoc()): ?>
              <div class="animate-box">
                <a href="<?php echo htmlspecialchars($banner['link']); ?>"
                  class="hero-banner-item d-block hover-border-secondary2 position-relative z-1 overflow-hidden">
                  <div class="hero-banner-wrapper">
                    <img class="w-100 transition"
                      src="./assets/uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>"
                      alt="<?php echo htmlspecialchars($banner['alt_text']); ?>" />
                  </div>
                </a>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- hero section end -->

  <!-- text slider start -->
  <div class="my-6 p-lg-6 p-4 bg-primary overflow-hidden">
    <div class="swiper text-slider">
      <div class="swiper-wrapper">
        <?php while ($slider = $text_sliders_result->fetch_assoc()): ?>
          <div class="swiper-slide w-fit">
            <div class="d-flex align-items-center gap-lg-6 gap-4">
              <span class="text-sm font-noto-sans fw-medium text-uppercase text-n100">
                <?php echo htmlspecialchars($slider['message']); ?>
              </span>
              <span class="d-block w-1px h-24px bg-n100"></span>
            </div>
          </div>
        <?php endwhile; ?>

      </div>
    </div>
  </div>
  <!-- text slider end -->

  <!-- product section start -->
  <section class="product-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120 bg-n20">
    <div class="container-fluid">
      <div class="row g-6 justify-content-between align-items-end mb-lg-15 mb-md-10 mb-8">
        <div class="col-lg-6 col-md-9">
          <h2 class="text-animation-word display-four text-n100 text-uppercase">
            NEW ARRIVALS
          </h2>
        </div>
        <div class="col-auto">
          <a href="#"
            class="outline-btn radius-pill text-n100 fw-medium box-style box-secondary2">More Shop</a>
        </div>
      </div>
      <div class="row g-0">
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
      </div>
    </div>
  </section>
  <!-- product section end -->

  <!-- landing section start -->
  <!-- landing section start -->
  <section class="landing-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-lg-20 pb-10 custom-cursor-none">
    <div class="container-fluid">
      <div class="row g-6 align-items-center justify-content-between">

        <!-- Left Banner -->
        <div class="col-lg-6 col-md-8 col-xs-10 mx-lg-0 mx-auto">
          <div class="landing-banner reveal-left">
            <?php if (!empty($landing['circle_image'])): ?>
              <div class="circle-spin position-absolute top-0 start-100 translate-middle z-3">
                <img class="w-100" src="./assets/uploads/feature/<?= $landing['circle_image'] ?>" alt="circle" />
              </div>
            <?php endif; ?>
            <img class="w-100" src="./assets/uploads/feature/<?= $landing['banner_image'] ?>" alt="banner" />
          </div>
        </div>

        <!-- Right Content -->
        <div class="col-4xl-5 col-lg-6">
          <div class="mb-xxl-15 mb-xl-10 mb-8">
            <div class="mb-xl-10 mb-lg-8 mb-6">
              <span class="text-animation-line text-h1 text-n100 mb-lg-4 mb-3">
                <?= htmlspecialchars($landing['heading']) ?>
              </span>
              <p class="text-sm fw-normal text-n50">
                <?= htmlspecialchars($landing['description']) ?>
              </p>
            </div>
            <div class="d-flex align-items-center gap-lg-5 gap-3">
              <?php if (!empty($landing['primary_btn_text'])): ?>
                <a href="<?= $landing['primary_btn_link'] ?>" class="btn-secondary">
                  <?= htmlspecialchars($landing['primary_btn_text']) ?>
                  <span class="icon">
                    <i class="ph ph-arrow-up-right"></i>
                    <i class="ph ph-arrow-up-right"></i>
                  </span>
                </a>
              <?php endif; ?>
              <?php if (!empty($landing['secondary_btn_text'])): ?>
                <a href="<?= $landing['secondary_btn_link'] ?>" class="text-decoration-underline fw-medium hover-text-secondary2">
                  <?= htmlspecialchars($landing['secondary_btn_text']) ?>
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Features -->
          <div class="landing-feature-wrapper border-8 border-n0 py-xxl-16 py-xl-10 py-8 pe-4xl-12 pe-lg-10 pe-md-8 pe-xs-6 bg-primary position-relative z-1 animate-box">
            <ul class="d-flex flex-xs-row flex-column align-items-center justify-content-between gap-4">
              <?php foreach ($features as $feature): ?>
                <li>
                  <div class="feature-item d-grid gap-2">
                    <img src="./assets/uploads/feature/<?= $feature['icon'] ?>" alt="icon" />
                    <span class="text-sm ch-20 d-block"><?= htmlspecialchars($feature['text']) ?></span>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

      </div>
    </div>
  </section>
  <!-- landing section end -->

  <!-- landing section end -->


  <!-- brand slider start -->
  <div class="pb-120">
    <div class="swiper brand-slider">
      <div class="swiper-wrapper align-items-center">
        <?php while ($row = $brand_logos_res->fetch_assoc()): ?>
          <div class="swiper-slide w-fit">
            <?php if (!empty($row['link'])): ?>
              <a href="<?= $row['link'] ?>" target="_blank">
                <img src="./assets/uploads/brands/<?= $row['logo'] ?>" alt="<?= htmlspecialchars($row['brand_name']) ?>" />
              </a>
            <?php else: ?>
              <img src="./assets/uploads/brands/<?= $row['logo'] ?>" alt="<?= htmlspecialchars($row['brand_name']) ?>" />
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
  <!-- brand slider end -->

  <!-- category section start -->
  <section class="category-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120 bg-n20">
    <div class="container-fluid">
      <div class="row g-6 justify-content-center align-items-end mb-lg-15 mb-md-10 mb-8">
        <div class="col-lg-6 text-center">
          <h2 class="text-animation-word display-four text-n100 text-uppercase mb-lg-6 mb-4">
            SHOP THE LOOK
          </h2>
          <p class="text-sm text-n50 fw-normal ch-85 mx-auto">
            Our latest endeavour features designs from around the world with
            materials so comfortable you won't want to wear anything else
            every again.
          </p>
        </div>
      </div>
      <div class="row g-6">
        <div class="col-lg-4 col-xs-6">
          <div class="animate-box">
            <a href="shop.html" class="d-block category-card shake-animation">
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-bikes.png" alt="category" />
              </div>
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-bikes.png" alt="category" />
              </div>
              <div class="position-absolute bottom-0 left-0 z-3 mb-lg-8 mb-6 ms-lg-8 ms-6">
                <span class="outline-btn bg-n0 radius-pill box-style box-secondary2">BIKES</span>
              </div>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-xs-6">
          <div class="animate-box">
            <a href="shop.html" class="d-block category-card shake-animation">
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-accessories.png" alt="category" />
              </div>
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-accessories.png" alt="category" />
              </div>
              <div class="position-absolute bottom-0 left-0 z-3 mb-lg-8 mb-6 ms-lg-8 ms-6">
                <span class="outline-btn bg-n0 radius-pill box-style box-secondary2">ACCESSORIES</span>
              </div>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-xs-6">
          <div class="animate-box">
            <a href="shop.html" class="d-block category-card shake-animation">
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-parts.png" alt="category" />
              </div>
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-parts.png" alt="category" />
              </div>
              <div class="position-absolute bottom-0 left-0 z-3 mb-lg-8 mb-6 ms-lg-8 ms-6">
                <span class="outline-btn bg-n0 radius-pill box-style box-secondary2">PARTS</span>
              </div>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-xs-6">
          <div class="animate-box">
            <a href="shop.html" class="d-block category-card shake-animation">
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-gear.png" alt="category" />
              </div>
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-gear.png" alt="category" />
              </div>
              <div class="position-absolute bottom-0 left-0 z-3 mb-lg-8 mb-6 ms-lg-8 ms-6">
                <span class="outline-btn bg-n0 radius-pill box-style box-secondary2">GEAR</span>
              </div>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-xs-6">
          <div class="animate-box">
            <a href="shop.html" class="d-block category-card shake-animation">
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-electronics.png" alt="category" />
              </div>
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-electronics.png" alt="category" />
              </div>
              <div class="position-absolute bottom-0 left-0 z-3 mb-lg-8 mb-6 ms-lg-8 ms-6">
                <span class="outline-btn bg-n0 radius-pill box-style box-secondary2">ELECTRONICS</span>
              </div>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-xs-6">
          <div class="animate-box">
            <a href="shop.html" class="d-block category-card shake-animation">
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-equioment.png" alt="category" />
              </div>
              <div class="shake-thumb">
                <img class="w-100" src="assets/images/category-equioment.png" alt="category" />
              </div>
              <div class="position-absolute bottom-0 left-0 z-3 mb-lg-8 mb-6 ms-lg-8 ms-6">
                <span class="outline-btn bg-n0 radius-pill box-style box-secondary2">EQUIPMENT</span>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- category section end -->

  <!-- best product in year section start -->
  <section class="best-product-section pt-120 pb-120">
    <div class="px-xl-20 px-lg-10 px-sm-7 mb-lg-15 mb-md-10 mb-8">
      <div class="container-fluid">
        <div class="row g-6 justify-content-between align-items-end">
          <div class="col-lg-6 col-md-9">
            <h2 class="text-animation-word display-four text-n100 text-uppercase mb-lg-6 mb-4">
              BEST PRODUCTS IN 2024
            </h2>
            <p class="text-sm text-n50 fw-normal ch-85">
              Welcome to CycleCity, where your cycling journey begins! As
              avid cyclists ourselves, we understand the joy and freedom
              that comes from pedaling on two wheels
            </p>
          </div>
          <div class="col-auto">
            <a href="#"
              class="outline-btn radius-pill text-n100 fw-medium box-style box-secondary2">
              More Shop
              <span class="icon">
                <i class="ph ph-arrow-up-right"></i>
                <i class="ph ph-arrow-up-right"></i>
              </span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- best product in year swiper -->
    <div class="swiper best-product-slider mb-lg-15 mb-md-10 mb-8">
      <div class="swiper-wrapper">
        <div class="swiper-slide">
          <div class="product-card3 has-color-option">
            <div
              class="product-thumb-wrapper d-grid gap-xl-10 gap-md-8 gap-md-6 gap-4 p-xl-10 p-lg-8 p-md-6 p-4 mb-lg-7 mb-5 bg-n20">
              <!-- product type and wishlist btn -->
              <div class="d-flex align-items-center justify-content-end">
                <button
                  class="single-wishlist-btn text-secondary2 text-xl icon-40px bg-n0 tooltip-btn tooltip-left position-relative"
                  data-tooltip="Add to wishlist">
                  <i class="ph ph-heart"></i>
                </button>
              </div>
              <!-- product thumb -->
              <div class="product-thumb scale-animation">
                <img class="product-image w-100" src="assets/images/bike-yellow.png"
                  alt="product" />
              </div>
              <!-- button -->
              <a href="shop.html" class="outline-btn box-style box-secondary2 fw-bold w-100 radius-8">
                Shop Bike
              </a>
            </div>
            <div class="d-grid gap-lg-5 gap-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <ul class="d-flex align-items-center text-n100 gap-1 text-base">
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                    </ul>
                    <span class="text-sm fw-normal text-n50 text-nowrap">1 Reviews</span>
                  </div>
                  <span class="text-sm fw-normal text-n50 text-nowrap">Brand:
                    <span class="text-n100 fw-bold">Schwmin</span></span>
                </div>
                <div class="d-flex align-items-center gap-2 custom-cursor-none">
                  <!-- select product color -->
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Green" data-image="assets/images/bike-green.png"
                    style="background-color: #008000"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Yellow" data-image="assets/images/bike-yellow.png"
                    style="background-color: #fde34d"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Orange" data-image="assets/images/bike-orange.png"
                    style="background-color: #eb453b"></button>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <a href="shop-details.html">
                  <h4 class="text-animation-word text-n100 hover-text-secondary2">
                    Merida Scultura Sukura
                  </h4>
                </a>
                <span class="text-h4 text-n100">$321</span>
              </div>
            </div>
          </div>
        </div>
        <div class="swiper-slide">
          <div class="product-card3 has-color-option">
            <div
              class="product-thumb-wrapper d-grid gap-xl-10 gap-md-8 gap-md-6 gap-4 p-xl-10 p-lg-8 p-md-6 p-4 mb-lg-7 mb-5 bg-n20">
              <!-- product type and wishlist btn -->
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-1">
                  <span
                    class="py-lg-2 py-1 px-lg-5 px-3 radius-pill border border-n100 text-n100">NEW</span>
                  <span
                    class="py-lg-2 py-1 px-lg-5 px-3 radius-pill border border-secondary2 text-n0 bg-secondary2">SALE</span>
                </div>
                <button
                  class="single-wishlist-btn text-secondary2 text-xl icon-40px bg-n0 tooltip-btn tooltip-left position-relative"
                  data-tooltip="Add to wishlist">
                  <i class="ph ph-heart"></i>
                </button>
              </div>
              <!-- product thumb -->
              <div class="product-thumb scale-animation">
                <img class="product-image w-100" src="assets/images/bike-orange.png"
                  alt="product" />
              </div>
              <!-- button -->
              <a href="shop.html" class="outline-btn box-style box-secondary2 fw-bold w-100 radius-8">
                Shop Bike
              </a>
            </div>
            <div class="d-grid gap-lg-5 gap-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <ul class="d-flex align-items-center text-n100 gap-1 text-base">
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                    </ul>
                    <span class="text-sm fw-normal text-n50 text-nowrap">1 Reviews</span>
                  </div>
                  <span class="text-sm fw-normal text-n50 text-nowrap">Brand:
                    <span class="text-n100 fw-bold">Schwmin</span></span>
                </div>
                <div class="d-flex align-items-center gap-2 custom-cursor-none">
                  <!-- select product color -->
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Green" data-image="assets/images/bike-green.png"
                    style="background-color: #008000"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Yellow" data-image="assets/images/bike-yellow.png"
                    style="background-color: #fde34d"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Orange" data-image="assets/images/bike-orange.png"
                    style="background-color: #eb453b"></button>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <a href="shop-details.html">
                  <h4 class="text-animation-word text-n100 hover-text-secondary2">
                    Urban Wanderer
                  </h4>
                </a>
                <span class="text-h4 text-n100">$321</span>
              </div>
            </div>
          </div>
        </div>
        <div class="swiper-slide">
          <div class="product-card3 has-color-option">
            <div
              class="product-thumb-wrapper d-grid gap-xl-10 gap-md-8 gap-md-6 gap-4 p-xl-10 p-lg-8 p-md-6 p-4 mb-lg-7 mb-5 bg-n20">
              <!-- product type and wishlist btn -->
              <div class="d-flex align-items-center justify-content-end">
                <button
                  class="single-wishlist-btn text-secondary2 text-xl icon-40px bg-n0 tooltip-btn tooltip-left position-relative"
                  data-tooltip="Add to wishlist">
                  <i class="ph ph-heart"></i>
                </button>
              </div>
              <!-- product thumb -->
              <div class="product-thumb scale-animation">
                <img class="product-image w-100" src="assets/images/bike-deep-green.png"
                  alt="product" />
              </div>
              <!-- button -->
              <a href="shop.html" class="outline-btn box-style box-secondary2 fw-bold w-100 radius-8">
                Shop Bike
              </a>
            </div>
            <div class="d-grid gap-lg-5 gap-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <ul class="d-flex align-items-center text-n100 gap-1 text-base">
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                    </ul>
                    <span class="text-sm fw-normal text-n50 text-nowrap">1 Reviews</span>
                  </div>
                  <span class="text-sm fw-normal text-n50 text-nowrap">Brand:
                    <span class="text-n100 fw-bold">Schwmin</span></span>
                </div>
                <div class="d-flex align-items-center gap-2 custom-cursor-none">
                  <!-- select product color -->
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Green" data-image="assets/images/bike-green.png"
                    style="background-color: #008000"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Yellow" data-image="assets/images/bike-yellow.png"
                    style="background-color: #fde34d"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Orange" data-image="assets/images/bike-orange.png"
                    style="background-color: #eb453b"></button>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <a href="shop-details.html">
                  <h4 class="text-animation-word text-n100 hover-text-secondary2">
                    Electro Boost
                  </h4>
                </a>
                <span class="text-h4 text-n100">$321</span>
              </div>
            </div>
          </div>
        </div>
        <div class="swiper-slide">
          <div class="product-card3 has-color-option">
            <div
              class="product-thumb-wrapper d-grid gap-xl-10 gap-md-8 gap-md-6 gap-4 p-xl-10 p-lg-8 p-md-6 p-4 mb-lg-7 mb-5 bg-n20">
              <!-- product type and wishlist btn -->
              <div class="d-flex align-items-center justify-content-end">
                <button
                  class="single-wishlist-btn text-secondary2 text-xl icon-40px bg-n0 tooltip-btn tooltip-left position-relative"
                  data-tooltip="Add to wishlist">
                  <i class="ph ph-heart"></i>
                </button>
              </div>
              <!-- product thumb -->
              <div class="product-thumb scale-animation">
                <img class="product-image w-100" src="assets/images/bike-green.png" alt="product" />
              </div>
              <!-- button -->
              <a href="shop.html" class="outline-btn box-style box-secondary2 fw-bold w-100 radius-8">
                Shop Bike
              </a>
            </div>
            <div class="d-grid gap-lg-5 gap-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <ul class="d-flex align-items-center text-n100 gap-1 text-base">
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                    </ul>
                    <span class="text-sm fw-normal text-n50 text-nowrap">1 Reviews</span>
                  </div>
                  <span class="text-sm fw-normal text-n50 text-nowrap">Brand:
                    <span class="text-n100 fw-bold">Schwmin</span></span>
                </div>
                <div class="d-flex align-items-center gap-2 custom-cursor-none">
                  <!-- select product color -->
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Green" data-image="assets/images/bike-green.png"
                    style="background-color: #008000"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Yellow" data-image="assets/images/bike-yellow.png"
                    style="background-color: #fde34d"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Orange" data-image="assets/images/bike-orange.png"
                    style="background-color: #eb453b"></button>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <a href="shop-details.html">
                  <h4 class="text-animation-word text-n100 hover-text-secondary2">
                    Terra Roamer
                  </h4>
                </a>
                <span class="text-h4 text-n100">$321</span>
              </div>
            </div>
          </div>
        </div>
        <div class="swiper-slide">
          <div class="product-card3 has-color-option">
            <div
              class="product-thumb-wrapper d-grid gap-xl-10 gap-md-8 gap-md-6 gap-4 p-xl-10 p-lg-8 p-md-6 p-4 mb-lg-7 mb-5 bg-n20">
              <!-- product type and wishlist btn -->
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-1">
                  <span
                    class="py-lg-2 py-1 px-lg-5 px-3 radius-pill border border-n100 text-n100">NEW</span>
                  <span
                    class="py-lg-2 py-1 px-lg-5 px-3 radius-pill border border-secondary2 text-n0 bg-secondary2">SALE</span>
                </div>
                <button
                  class="single-wishlist-btn text-secondary2 text-xl icon-40px bg-n0 tooltip-btn tooltip-left position-relative"
                  data-tooltip="Add to wishlist">
                  <i class="ph ph-heart"></i>
                </button>
              </div>
              <!-- product thumb -->
              <div class="product-thumb scale-animation">
                <img class="product-image w-100" src="assets/images/bike-deep-green.png"
                  alt="product" />
              </div>
              <!-- button -->
              <a href="shop.html" class="outline-btn box-style box-secondary2 fw-bold w-100 radius-8">
                Shop Bike
              </a>
            </div>
            <div class="d-grid gap-lg-5 gap-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <ul class="d-flex align-items-center text-n100 gap-1 text-base">
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                    </ul>
                    <span class="text-sm fw-normal text-n50 text-nowrap">1 Reviews</span>
                  </div>
                  <span class="text-sm fw-normal text-n50 text-nowrap">Brand:
                    <span class="text-n100 fw-bold">Schwmin</span></span>
                </div>
                <div class="d-flex align-items-center gap-2 custom-cursor-none">
                  <!-- select product color -->
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Green" data-image="assets/images/bike-green.png"
                    style="background-color: #008000"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Yellow" data-image="assets/images/bike-yellow.png"
                    style="background-color: #fde34d"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Orange" data-image="assets/images/bike-orange.png"
                    style="background-color: #eb453b"></button>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <a href="shop-details.html">
                  <h4 class="text-animation-word text-n100 hover-text-secondary2">
                    Electro Boost
                  </h4>
                </a>
                <span class="text-h4 text-n100">$321</span>
              </div>
            </div>
          </div>
        </div>
        <div class="swiper-slide">
          <div class="product-card3 has-color-option">
            <div
              class="product-thumb-wrapper d-grid gap-xl-10 gap-md-8 gap-md-6 gap-4 p-xl-10 p-lg-8 p-md-6 p-4 mb-lg-7 mb-5 bg-n20">
              <!-- product type and wishlist btn -->
              <div class="d-flex align-items-center justify-content-end">
                <button
                  class="single-wishlist-btn text-secondary2 text-xl icon-40px bg-n0 tooltip-btn tooltip-left position-relative"
                  data-tooltip="Add to wishlist">
                  <i class="ph ph-heart"></i>
                </button>
              </div>
              <!-- product thumb -->
              <div class="product-thumb scale-animation">
                <img class="product-image w-100" src="assets/images/bike-green.png" alt="product" />
              </div>
              <!-- button -->
              <a href="shop.html" class="outline-btn box-style box-secondary2 fw-bold w-100 radius-8">
                Shop Bike
              </a>
            </div>
            <div class="d-grid gap-lg-5 gap-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <ul class="d-flex align-items-center text-n100 gap-1 text-base">
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                      <li>
                        <span><i class="ph-fill ph-star"></i></span>
                      </li>
                    </ul>
                    <span class="text-sm fw-normal text-n50 text-nowrap">1 Reviews</span>
                  </div>
                  <span class="text-sm fw-normal text-n50 text-nowrap">Brand:
                    <span class="text-n100 fw-bold">Schwmin</span></span>
                </div>
                <div class="d-flex align-items-center gap-2 custom-cursor-none">
                  <!-- select product color -->
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Green" data-image="assets/images/bike-green.png"
                    style="background-color: #008000"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Yellow" data-image="assets/images/bike-yellow.png"
                    style="background-color: #fde34d"></button>
                  <button class="color-option icon-16px tooltip-btn tooltip-top position-relative"
                    data-tooltip="Orange" data-image="assets/images/bike-orange.png"
                    style="background-color: #eb453b"></button>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <a href="shop-details.html">
                  <h4 class="text-animation-word text-n100 hover-text-secondary2">
                    Terra Roamer
                  </h4>
                </a>
                <span class="text-h4 text-n100">$321</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="px-xl-20 px-lg-10 px-sm-7">
      <div class="container-fluid">
        <div class="bg-n100-1 best-product-pagination h-1px position-relative d-between"></div>
      </div>
    </div>
  </section>
  <!-- best product in year section end -->

  <!-- news and articles section start -->
  <section class="news-and-articles-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120 bg-n20">
    <div class="container-fluid">
      <div class="row g-6 justify-content-center align-items-end mb-lg-15 mb-md-10 mb-8">
        <div class="col-lg-6 text-center">
          <h2 class="text-animation-word display-four text-n100 text-uppercase mb-lg-6 mb-4">
            OUR NEWS & ARTICLES
          </h2>
          <p class="text-sm text-n50 fw-normal ch-85 mx-auto">
            Our latest endeavour features designs from around the world with
            materials so comfortable you won't want to wear anything else
            every again.
          </p>
        </div>
      </div>

      <div class="row g-6 news-and-articles-wrapper mb-lg-15 mb-md-10 mb-8">
        <div class="col-lg-4 col-xs-6">
          <div class="news-and-articles-card position-relative">
            <div class="card-thumb mb-lg-6 mb-4">
              <img class="w-100" src="assets/images/news-1.png" alt="news and articles" />
              <div class="overlay"></div>
            </div>
            <div class="card-body">
              <div class="post-info d-flex align-items-center mb-lg-4 mb-2 gap-1">
                <a href="#"
                  class="text-n100 text-sm fw-normal hover-text-secondary2">Trending</a>
                <span class="text-secondary2 text-sm fw-normal"><i class="ph-fill ph-dot"></i></span>
                <span class="text-n100 text-sm fw-normal">Feb 26, 2022</span>
              </div>
              <a href="blog-details.html">
                <h4 class="post-title text-n100 mb-lg-6 mb-4 ch-35 hover-text-primary2">
                  Cycling and Fitness: Health Benefits of Regular Riding
                </h4>
              </a>
              <a href="blog-details.html"
                class="read-more-btn text-n100 fw-medium text-decoration-underline hover-text-secondary2">Read
                Articles</a>
            </div>
            <div class="hit"></div>
          </div>
        </div>
        <div class="col-lg-4 col-xs-6">
          <div class="news-and-articles-card position-relative">
            <div class="card-thumb mb-lg-6 mb-4">
              <img class="w-100" src="assets/images/news-2.png" alt="news and articles" />
              <div class="overlay"></div>
            </div>
            <div class="card-body">
              <div class="post-info d-flex align-items-center mb-lg-4 mb-2 gap-1">
                <a href="#" class="text-n100 text-sm fw-normal hover-text-secondary2">Cycling
                  Nutrition</a>
                <span class="text-secondary2 text-sm fw-normal"><i class="ph-fill ph-dot"></i></span>
                <span class="text-n100 text-sm fw-normal">Feb 26, 2022</span>
              </div>
              <a href="blog-details.html">
                <h4 class="post-title text-n100 mb-lg-6 mb-4 ch-35 hover-text-primary2">
                  Keep your bike in top shape with essential maintenance
                  tips.
                </h4>
              </a>
              <a href="blog-details.html"
                class="read-more-btn text-n100 fw-medium text-decoration-underline hover-text-secondary2">Read
                Articles</a>
            </div>
            <div class="hit"></div>
          </div>
        </div>
        <div class="col-lg-4 col-xs-6">
          <div class="news-and-articles-card position-relative">
            <div class="card-thumb mb-lg-6 mb-4">
              <img class="w-100" src="assets/images/news-3.png" alt="news and articles" />
              <div class="overlay"></div>
            </div>
            <div class="card-body">
              <div class="post-info d-flex align-items-center mb-lg-4 mb-2 gap-1">
                <a href="#" class="text-n100 text-sm fw-normal hover-text-secondary2">Events</a>
                <span class="text-secondary2 text-sm fw-normal"><i class="ph-fill ph-dot"></i></span>
                <span class="text-n100 text-sm fw-normal">Feb 26, 2022</span>
              </div>
              <a href="blog-details.html">
                <h4 class="post-title text-n100 mb-lg-6 mb-4 ch-35 hover-text-primary2">
                  Explore picturesque cycling destinations for your next
                  adventure.
                </h4>
              </a>
              <a href="blog-details.html"
                class="read-more-btn text-n100 fw-medium text-decoration-underline hover-text-secondary2">Read
                Articles</a>
            </div>
            <div class="hit"></div>
          </div>
        </div>
      </div>
      <div class="text-center">
        <a href="blogs.html" class="outline-btn radius-pill text-n100 fw-medium box-style box-secondary2">
          More Blogs
          <span class="icon">
            <i class="ph ph-arrow-up-right"></i>
            <i class="ph ph-arrow-up-right"></i>
          </span>
        </a>
      </div>
    </div>
  </section>
  <!-- news and articles section end -->

  <!-- gallery slider -->
  <!-- gallery slider start -->
  <div class="overflow-hidden position-relative z-0">
    <div class="swiper gallery-slider">
      <div class="swiper-wrapper align-items-center z-1">
        <div class="swiper-slide w-fit z-1">
          <div class="gallery-item position-relative">
            <img src="assets/images/gallery-1.png" alt="gallery logo" />
            <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
              <a href="#"
                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                <i class="ph ph-instagram-logo"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide w-fit z-1">
          <div class="gallery-item position-relative">
            <img src="assets/images/gallery-2.png" alt="gallery logo" />
            <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
              <a href="#"
                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                <i class="ph ph-instagram-logo"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide w-fit z-1">
          <div class="gallery-item position-relative">
            <img src="assets/images/gallery-3.png" alt="gallery logo" />
            <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
              <a href="#"
                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                <i class="ph ph-instagram-logo"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide w-fit z-1">
          <div class="gallery-item position-relative">
            <img src="assets/images/gallery-4.png" alt="gallery logo" />
            <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
              <a href="#"
                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                <i class="ph ph-instagram-logo"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide w-fit z-1">
          <div class="gallery-item position-relative">
            <img src="assets/images/gallery-5.png" alt="gallery logo" />
            <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
              <a href="#"
                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                <i class="ph ph-instagram-logo"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide w-fit z-1">
          <div class="gallery-item position-relative">
            <img src="assets/images/gallery-6.png" alt="gallery logo" />
            <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
              <a href="#"
                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                <i class="ph ph-instagram-logo"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide w-fit z-1">
          <div class="gallery-item position-relative">
            <img src="assets/images/gallery-7.png" alt="gallery logo" />
            <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
              <a href="#"
                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                <i class="ph ph-instagram-logo"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide w-fit z-1">
          <div class="gallery-item position-relative">
            <img src="assets/images/gallery-8.png" alt="gallery logo" />
            <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
              <a href="#"
                class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                <i class="ph ph-instagram-logo"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="swiper-slide w-fit z-1">
          <div class="gallery-item position-relative">
            <img src="assets/images/gallery-9.png" alt="gallery logo" />
            <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
              <a href="#"
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
              Stay updated with the latest in cycling. Sign up for our
              newsletter to receive exclusive offers, product updates, and
              tips straight to your inbox. Join our biking community today!
            </p>
          </div>
          <form action="#" class="d-center flex-wrap flex-sm-nowrap cta-form mx-auto">
            <input type="email" placeholder="Enter your email address"
              class="bg-transparent text-n0 py-lg-4 py-3 px-lg-6 px-4 border border-n20-1 focus-primary" />
            <button type="submit"
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
<!-- main end -->
<?= include_once './layout/footer.php' ?>