<?php
require_once '../include/connection.php';

// Check if we're editing an existing product
$is_edit = false;
$product_id = null;
$existing_product = null;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $is_edit = true;

    // Fetch existing product data
    $product_query = "SELECT * FROM products WHERE id = $product_id";
    $product_result = mysqli_query($conn, $product_query);

    if (mysqli_num_rows($product_result) > 0) {
        $existing_product = mysqli_fetch_assoc($product_result);

        // Decode JSON fields
        if ($existing_product['image']) {
            $existing_product['image'] = json_decode($existing_product['image'], true);
        }
        if ($existing_product['specifications']) {
            $existing_product['specifications'] = json_decode($existing_product['specifications'], true);
        }
        if ($existing_product['video']) {
            $existing_product['video'] = json_decode($existing_product['video'], true);
        }

        // Fetch product variants
        $variants_query = "SELECT * FROM product_variants WHERE product_id = $product_id";
        $variants_result = mysqli_query($conn, $variants_query);
        $existing_variants = [];
        while ($variant = mysqli_fetch_assoc($variants_result)) {
            if ($variant['image']) {
                $variant['image'] = json_decode($variant['image'], true);
            }
            $existing_variants[] = $variant;
        }
    } else {
        // Product not found, redirect to products list
        header('Location: products.php');
        exit();
    }
}

// Handle form submission
if ($_POST) {
    // Handle image uploads
    $uploaded_images = [];

    if ($is_edit && !empty($existing_product['image'])) {
        // Only decode if it's a string (JSON)
        if (is_string($existing_product['image'])) {
            $uploaded_images = json_decode($existing_product['image'], true);
        } elseif (is_array($existing_product['image'])) {
            // Already an array, just assign it
            $uploaded_images = $existing_product['image'];
        }
    }
    
    // Handle removed images
    if (isset($_POST['removed_images']) && is_array($_POST['removed_images'])) {
        foreach ($_POST['removed_images'] as $removed_img) {
            $removed_img = basename($removed_img); // sanitize

            // Remove from array
            if (($key = array_search($removed_img, $uploaded_images)) !== false) {
                unset($uploaded_images[$key]);

                // Delete from server
                $file_path = '../assets/uploads/product/' . $removed_img;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
        // Reindex array
        $uploaded_images = array_values($uploaded_images);
    }

    // Handle newly uploaded images
    if (isset($_FILES['image']) && !empty($_FILES['image']['name'][0])) {
        $upload_dir = '../assets/uploads/product/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        for ($i = 0; $i < count($_FILES['image']['name']); $i++) {
            if ($_FILES['image']['error'][$i] == 0) {
                $file_name = $_FILES['image']['name'][$i];
                $file_tmp = $_FILES['image']['tmp_name'][$i];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                // Generate unique filename
                $new_filename = uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $uploaded_images[] = $new_filename;
                }
            }
        }
    }

    // Convert final array to JSON for DB
    $images_json = json_encode($uploaded_images);

    // Handle video URLs
    $video_iframes = [];
    if (isset($_POST['video_urls']) && is_array($_POST['video_urls'])) {
        foreach ($_POST['video_urls'] as $url) {
            if (!empty(trim($url))) {
                $clean_url = trim($url);
                // Convert YouTube URLs to embed format
                if (strpos($clean_url, 'youtube.com/watch?v=') !== false) {
                    $video_id = parse_url($clean_url, PHP_URL_QUERY);
                    parse_str($video_id, $query_params);
                    if (isset($query_params['v'])) {
                        $embed_url = 'https://www.youtube.com/embed/' . $query_params['v'];
                        $video_iframes[] = '<iframe src="' . $embed_url . '" frameborder="0" allowfullscreen></iframe>';
                    }
                } elseif (strpos($clean_url, 'youtu.be/') !== false) {
                    $video_id = substr(parse_url($clean_url, PHP_URL_PATH), 1);
                    $embed_url = 'https://www.youtube.com/embed/' . $video_id;
                    $video_iframes[] = '<iframe src="' . $embed_url . '" frameborder="0" allowfullscreen></iframe>';
                } else {
                    $video_iframes[] = '<iframe src="' . $clean_url . '" frameborder="0" allowfullscreen></iframe>';
                }
            }
        }
    }
    $video_json = json_encode($video_iframes);

    // Basic product information
    $category_id = (int)$_POST['category_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $slug = mysqli_real_escape_string($conn, $_POST['slug']);
    $sku = !empty($_POST['sku']) ? mysqli_real_escape_string($conn, $_POST['sku']) : 0;
    $price = (int)$_POST['price'];
    $short_description = mysqli_real_escape_string($conn, $_POST['short_description']);
    $long_description = mysqli_real_escape_string($conn, $_POST['long_description']);
    $discount_price = !empty($_POST['discount_price']) ? (int)$_POST['discount_price'] : 0;
    $stock_quantity = (int)$_POST['stock_quantity'];
    $status = $_POST['status'];

    // Additional fields
    $brand_id = (int)$_POST['brand_id'];
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // SEO fields
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description']);

    // Handle specifications
    $specifications = [];
    if (isset($_POST['spec_names']) && isset($_POST['spec_values'])) {
        for ($i = 0; $i < count($_POST['spec_names']); $i++) {
            if (!empty($_POST['spec_names'][$i]) && !empty($_POST['spec_values'][$i])) {
                $specifications[] = [
                    'name' => trim($_POST['spec_names'][$i]),
                    'value' => trim($_POST['spec_values'][$i])
                ];
            }
        }
    }
    $specifications_json = mysqli_real_escape_string($conn, json_encode($specifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // Timestamps
    $updated_by = 1;
    $updated_at = date('Y-m-d H:i:s');

    if ($is_edit) {
        // UPDATE existing product
        $product_query = "UPDATE products SET 
            category_id = '$category_id', 
            brand_id = '$brand_id', 
            name = '$name', 
            slug = '$slug', 
            sku = '$sku', 
            price = '$price', 
            short_description = '$short_description', 
            long_description = '$long_description', 
            discount_price = '$discount_price', 
            stock_quantity = '$stock_quantity', 
            image = '$images_json', 
            specifications = '$specifications_json', 
            video = '$video_json', 
            meta_title = '$meta_title', 
            meta_description = '$meta_description', 
            tags = '$tags', 
            is_featured = '$is_featured', 
            status = '$status', 
            updated_by = '$updated_by', 
            updated_at = '$updated_at'
        WHERE id = $product_id";
    } else {
        // INSERT new product
        $created_by = 1;
        $created_at = date('Y-m-d H:i:s');

        $product_query = "INSERT INTO products (
            category_id, brand_id, name, slug, sku, price, short_description, long_description, 
            discount_price, stock_quantity, image, specifications, video, meta_title, 
            meta_description, tags, is_featured, status, created_by, updated_by, created_at, updated_at
        ) VALUES (
            '$category_id', '$brand_id', '$name', '$slug', '$sku', '$price', '$short_description', 
            '$long_description', '$discount_price', '$stock_quantity', '$images_json', 
            '$specifications_json', '$video_json', '$meta_title', '$meta_description', '$tags', 
            '$is_featured', '$status', '$created_by', '$updated_by', '$created_at', '$updated_at'
        )";
    }

    if (mysqli_query($conn, $product_query)) {
        if (!$is_edit) {
            $product_id = mysqli_insert_id($conn);
        }

        // Handle product variants
        if (isset($_POST['variant_name']) && is_array($_POST['variant_name'])) {
            // If editing, delete existing variants first
            if ($is_edit) {
                $delete_variants_query = "DELETE FROM product_variants WHERE product_id = $product_id";
                mysqli_query($conn, $delete_variants_query);
            }

            for ($i = 0; $i < count($_POST['variant_name']); $i++) {
                if (!empty($_POST['variant_name'][$i])) {
                    $variant_name = mysqli_real_escape_string($conn, $_POST['variant_name'][$i]);
                    $variant_sku = mysqli_real_escape_string($conn, $_POST['variant_sku'][$i]);
                    $variant_price = (float)$_POST['variant_price'][$i];
                    $variant_discount_price = !empty($_POST['variant_discount_price'][$i]) ? (float)$_POST['variant_discount_price'][$i] : NULL;
                    $variant_stock = (int)$_POST['variant_stock'][$i];
                    $variant_status = $_POST['variant_status'][$i];

                    // Handle variant image upload
                    $variant_image = NULL;
                    if (isset($_FILES['variant_image']['name'][$i]) && $_FILES['variant_image']['error'][$i] === 0) {
                        $upload_dir = '../assets/uploads/product/';

                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        $file_name = $_FILES['variant_image']['name'][$i];
                        $file_tmp = $_FILES['variant_image']['tmp_name'][$i];
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $new_filename = uniqid() . '.' . $file_ext;
                        $upload_path = $upload_dir . $new_filename;

                        if (move_uploaded_file($file_tmp, $upload_path)) {
                            $variant_image = json_encode([$new_filename]);
                        }
                    } elseif ($is_edit && isset($existing_variants[$i]['image'])) {
                        // Keep existing image if not uploading new one
                        $variant_image = json_encode($existing_variants[$i]['image']);
                    }

                    // Insert variant into database
                    $variant_query = "INSERT INTO product_variants (
                        product_id, variant_name, sku, price, discount_price, 
                        stock_quantity, status, image, created_at, updated_at
                    ) VALUES (
                        '$product_id', '$variant_name', '$variant_sku', '$variant_price', " .
                        ($variant_discount_price ? "'$variant_discount_price'" : "NULL") . ", 
                        '$variant_stock', '$variant_status', " .
                        ($variant_image ? "'$variant_image'" : "NULL") . ", 
                        NOW(), NOW()
                    )";

                    if (!mysqli_query($conn, $variant_query)) {
                        echo "Error inserting variant: " . mysqli_error($conn);
                    }
                }
            }
        }

        $success_message = $is_edit ? "Product updated successfully!" : "Product created successfully!";
        echo "<div class='alert alert-success'>$success_message</div>";

        // If creating new product, redirect to edit page
        if (!$is_edit) {
            echo "<script>setTimeout(function() { window.location.href = 'product-form.php?id=$product_id'; }, 1000);</script>";
        }
    } else {
        $error_message = "Error: " . mysqli_error($conn);
        echo "<div class='alert alert-danger'>$error_message</div>";
    }
}

// Fetch categories for dropdown
$categories_query = "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch brands for dropdown
$brands_query = "SELECT id, name FROM brands WHERE status = 'active' ORDER BY name ASC";
$brands_result = mysqli_query($conn, $brands_query);
?>

<?php include './layout/sidebar.php'; ?>
<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a href="products.php" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
            <h5 class="mb-0 text-dark"><?php echo $is_edit ? 'Edit Product' : 'Add New Product'; ?></h5>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Welcome, Admin</span>
            <i class="bi bi-person-circle fs-4 text-secondary"></i>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid p-4">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form id="productForm" method="POST" action="" enctype="multipart/form-data">
            <?php if ($is_edit): ?>
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <?php endif; ?>

            <div class="row">
                <!-- Left Column - Main Product Info -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-info-circle me-2"></i>Basic Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Product Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo $is_edit ? htmlspecialchars($existing_product['name']) : ''; ?>"
                                        placeholder="Enter product name" required>
                                    <div class="form-text">Enter a clear, descriptive name for your product</div>
                                </div>

                                <!-- Slug -->
                                <div class="col-md-6 mb-3">
                                    <label for="slug" class="form-label">Slug *</label>
                                    <input type="text" class="form-control" id="slug" name="slug"
                                        value="<?php echo $is_edit ? htmlspecialchars($existing_product['slug']) : ''; ?>"
                                        placeholder="product-slug" required>
                                    <div class="form-text">URL-friendly version of the name</div>
                                </div>
                            </div>

                            <!-- Product Description -->
                            <div class="mb-3">
                                <label for="long_description" class="form-label">Product Description *</label>
                                <textarea class="form-control" id="long_description" name="long_description" rows="6"
                                    placeholder="Enter detailed product description" required><?php echo $is_edit ? htmlspecialchars($existing_product['long_description']) : ''; ?></textarea>
                                <div class="form-text">Provide a comprehensive description of the product features and benefits</div>
                            </div>

                            <!-- Short Description -->
                            <div class="mb-3">
                                <label for="short_description" class="form-label">Short Description</label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="3"
                                    placeholder="Enter brief product summary"><?php echo $is_edit ? htmlspecialchars($existing_product['short_description']) : ''; ?></textarea>
                                <div class="form-text">Brief summary for product listings (optional)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Images -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-images me-2"></i>Product Images
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Existing Images -->
                            <?php if ($is_edit && !empty($existing_product['image'])): ?>
                                <div class="mb-4">
                                    <h6>Existing Images:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($existing_product['image'] as $image): ?>
                                            <div class="position-relative" style="width: 100px; height: 100px;">
                                                <img src="../assets/uploads/product/<?php echo $image; ?>"
                                                    class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0"
                                                    onclick="removeExistingImage('<?php echo $image; ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Image Upload Area -->
                            <div class="image-upload-area" id="imageUploadArea">
                                <i class="bi bi-cloud-upload fs-1 text-muted mb-3"></i>
                                <h6 class="text-muted">Drag & Drop Images Here</h6>
                                <p class="text-muted mb-3">or</p>
                                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('imageInput').click()">
                                    <i class="bi bi-folder2-open me-2"></i>Browse Files
                                </button>
                                <input type="file" id="imageInput" multiple accept="image/*" name="image[]" style="display: none;">
                                <div class="form-text mt-3">Upload multiple images. URLs will be auto-generated.</div>
                            </div>

                            <!-- Image Previews -->
                            <div id="imagePreviews" class="mt-4"></div>
                        </div>
                    </div>

                    <!-- Product Video URLs -->
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-camera-video me-2"></i>Product Video URLs
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-video">
                                <i class="bi bi-plus"></i> Add Video URL
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="video-container">
                                <?php if ($is_edit && !empty($existing_product['video'])): ?>
                                    <?php foreach ($existing_product['video'] as $video): ?>
                                        <div class="video-item mb-3">
                                            <div class="row align-items-end">
                                                <div class="col-md-10">
                                                    <input type="text" class="form-control" name="video_urls[]"
                                                        value="<?php echo htmlspecialchars($video); ?>"
                                                        placeholder="https://youtube.com/watch?v=...">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-video">
                                                        <i class="bi bi-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="video-item mb-3">
                                        <div class="row align-items-end">
                                            <div class="col-md-10">
                                                <input type="text" class="form-control" name="video_urls[]"
                                                    placeholder="https://youtube.com/watch?v=...">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-video">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Product Specifications -->
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-gear me-2"></i>Product Specifications
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-spec">
                                <i class="bi bi-plus"></i> Add Specification
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="spec-container">
                                <?php if ($is_edit && !empty($existing_product['specifications'])): ?>
                                    <?php foreach ($existing_product['specifications'] as $spec): ?>
                                        <div class="spec-item mb-3">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Specification Name</label>
                                                    <input type="text" class="form-control" name="spec_names[]"
                                                        value="<?php echo htmlspecialchars($spec['name']); ?>"
                                                        placeholder="e.g., Frame Material">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Specification Value</label>
                                                    <input type="text" class="form-control" name="spec_values[]"
                                                        value="<?php echo htmlspecialchars($spec['value']); ?>"
                                                        placeholder="e.g., Aluminum Alloy">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-spec">
                                                        <i class="bi bi-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="spec-item mb-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">Specification Name</label>
                                                <input type="text" class="form-control" name="spec_names[]" placeholder="Frame Material" value="Frame Material">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Specification Value</label>
                                                <input type="text" class="form-control" name="spec_values[]" placeholder="Aluminum Alloy">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-spec">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Product Variants -->
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-collection me-2"></i>Product Variants
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-variant">
                                <i class="bi bi-plus"></i> Add Variant
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="variants-container">
                                <?php if ($is_edit && !empty($existing_variants)): ?>
                                    <?php foreach ($existing_variants as $index => $variant): ?>
                                        <div class="variant-row mb-4 p-3 border rounded">
                                            <div class="row">
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Variant Name</label>
                                                    <input type="text" name="variant_name[]" class="form-control"
                                                        value="<?php echo htmlspecialchars($variant['variant_name']); ?>"
                                                        placeholder="Size S, Red, etc.">
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Price</label>
                                                    <input type="number" step="0.01" name="variant_price[]" class="form-control"
                                                        value="<?php echo $variant['price']; ?>"
                                                        placeholder="299.99">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">SKU</label>
                                                    <input type="text" name="variant_sku[]" class="form-control"
                                                        value="<?php echo htmlspecialchars($variant['sku']); ?>"
                                                        placeholder="BIKE-001-S">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Stock</label>
                                                    <input type="number" name="variant_stock[]" class="form-control"
                                                        value="<?php echo $variant['stock_quantity']; ?>"
                                                        placeholder="10">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Discount Price</label>
                                                    <input type="number" step="0.01" name="variant_discount_price[]" class="form-control"
                                                        value="<?php echo $variant['discount_price']; ?>"
                                                        placeholder="249.99">
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Image</label>
                                                    <?php if (!empty($variant['image'])): ?>
                                                        <div class="mb-2">
                                                            <img src="../assets/uploads/product/<?php echo $variant['image'][0]; ?>"
                                                                class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file" name="variant_image[]" class="form-control">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Status</label>
                                                    <select name="variant_status[]" class="form-control">
                                                        <option value="active" <?php echo $variant['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo $variant['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm remove-variant form-control">
                                                        <i class="bi bi-trash"></i> Remove Variant
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="variant-row mb-4 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Variant Name</label>
                                                <input type="text" name="variant_name[]" class="form-control" placeholder="Size S, Red, etc.">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Price</label>
                                                <input type="number" step="0.01" name="variant_price[]" class="form-control" placeholder="299.99">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">SKU</label>
                                                <input type="text" name="variant_sku[]" class="form-control" placeholder="BIKE-001-S">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Stock</label>
                                                <input type="number" name="variant_stock[]" class="form-control" placeholder="10">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Discount Price</label>
                                                <input type="number" step="0.01" name="variant_discount_price[]" class="form-control" placeholder="249.99">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Image</label>
                                                <input type="file" name="variant_image[]" class="form-control">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Status</label>
                                                <select name="variant_status[]" class="form-control">
                                                    <option value="active">Active</option>
                                                    <option value="inactive">Inactive</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm remove-variant form-control">
                                                    <i class="bi bi-trash"></i> Remove Variant
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-search me-2"></i>SEO Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title"
                                            value="<?php echo $is_edit ? htmlspecialchars($existing_product['meta_title']) : ''; ?>"
                                            placeholder="SEO optimized title">
                                        <div class="form-text">Recommended: 50-60 characters</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3"
                                            placeholder="SEO description"><?php echo $is_edit ? htmlspecialchars($existing_product['meta_description']) : ''; ?></textarea>
                                        <div class="form-text">Recommended: 150-160 characters</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Product Details -->
                <div class="col-lg-4">
                    <!-- Pricing & Inventory -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-currency-dollar me-2"></i>Pricing & Inventory
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Regular Price -->
                            <div class="mb-3">
                                <label for="price" class="form-label">Regular Price *</label>
                                <div class="price-input">
                                    <input type="number" class="form-control" id="price" name="price"
                                        value="<?php echo $is_edit ? $existing_product['price'] : ''; ?>"
                                        placeholder="0.00" step="0.01" min="0" required>
                                </div>
                            </div>

                            <!-- Sale Price -->
                            <div class="mb-3">
                                <label for="discount_price" class="form-label">Sale Price</label>
                                <div class="price-input">
                                    <input type="number" class="form-control" id="discount_price" name="discount_price"
                                        value="<?php echo $is_edit ? $existing_product['discount_price'] : ''; ?>"
                                        placeholder="0.00" step="0.01" min="0">
                                </div>
                                <div class="form-text">Leave empty if not on sale</div>
                            </div>

                            <!-- Stock Quantity -->
                            <div class="mb-3">
                                <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity"
                                    value="<?php echo $is_edit ? $existing_product['stock_quantity'] : ''; ?>"
                                    placeholder="0" min="0" required>
                            </div>

                            <!-- SKU -->
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU</label>
                                <input type="text" class="form-control" id="sku" name="sku"
                                    value="<?php echo $is_edit ? htmlspecialchars($existing_product['sku']) : ''; ?>"
                                    placeholder="Enter product SKU">
                                <div class="form-text">Stock Keeping Unit</div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Organization -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-tags me-2"></i>Product Organization
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Category -->
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php
                                    $categories_result = mysqli_query($conn, $categories_query);
                                    while ($category = mysqli_fetch_assoc($categories_result)):
                                    ?>
                                        <option value="<?php echo $category['id']; ?>"
                                            <?php echo ($is_edit && $existing_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Brand -->
                            <div class="mb-3">
                                <label for="brand_id" class="form-label">Brand</label>
                                <select class="form-select" id="brand_id" name="brand_id">
                                    <option value="">Select Brand</option>
                                    <?php
                                    $brands_result = mysqli_query($conn, $brands_query);
                                    while ($brand = mysqli_fetch_assoc($brands_result)):
                                    ?>
                                        <option value="<?php echo $brand['id']; ?>"
                                            <?php echo ($is_edit && $existing_product['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Tags -->
                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags"
                                    value="<?php echo $is_edit ? htmlspecialchars($existing_product['tags']) : ''; ?>"
                                    placeholder="mountain, outdoor, sport">
                                <div class="form-text">Separate tags with commas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-toggle-on me-2"></i>Publication Status *
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($is_edit && $existing_product['status'] == 'active') ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo ($is_edit && $existing_product['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="inactive" <?php echo ($is_edit && $existing_product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                                        <?php echo ($is_edit && $existing_product['is_featured'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">
                                        <i class="bi bi-star me-1"></i>Featured Product
                                    </label>
                                </div>
                                <div class="form-text">Mark as featured to highlight this product</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark btn-lg">
                                    <i class="bi bi-check-circle me-2"></i><?php echo $is_edit ? 'Update Product' : 'Create Product'; ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                                </button>
                                <a href="products.php" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Function to remove existing image
    function removeExistingImage(imageName) {
        // if (confirm('Are you sure you want to remove this image?')) {
        //     // You can implement AJAX call here to remove the image from server

        //     const imageElement = document.querySelector(`img[src*="${imageName}"]`).parentElement;
        //     imageElement.remove();

        //     // Add a hidden input to track removed images
        //     const removedInput = document.createElement('input');
        //     removedInput.type = 'hidden';
        //     removedInput.name = 'removed_images[]';
        //     removedInput.value = imageName;
        //     document.getElementById('productForm').appendChild(removedInput);
        // }
        if (confirm('Are you sure you want to remove this image?')) {
            const imageElement = document.querySelector(`img[src*="${imageName}"]`).parentElement;
            imageElement.remove();

            // Track removed image in hidden input
            const removedInput = document.createElement('input');
            removedInput.type = 'hidden';
            removedInput.name = 'removed_images[]';
            removedInput.value = imageName;
            document.getElementById('productForm').appendChild(removedInput);
        }
    }

    // Rest of your existing JavaScript code remains the same...
    document.addEventListener('DOMContentLoaded', function() {
        const videoContainer = document.getElementById('video-container');
        const addVideoBtn = document.getElementById('add-video');

        addVideoBtn.addEventListener('click', function() {
            const newVideo = document.createElement('div');
            newVideo.classList.add('video-item', 'mb-3');

            newVideo.innerHTML = `
                <div class="row align-items-end">
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="video_urls[]" placeholder="https://youtube.com/watch?v=...">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-video">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
            `;

            videoContainer.appendChild(newVideo);
        });

        videoContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-video')) {
                e.target.closest('.video-item').remove();
            }
        });
    });

    // ...

    document.addEventListener('DOMContentLoaded', function() {
        const videoContainer = document.getElementById('video-container');
        const addVideoBtn = document.getElementById('add-video');

        addVideoBtn.addEventListener('click', function() {
            const newVideo = document.createElement('div');
            newVideo.classList.add('video-item', 'mb-3');

            newVideo.innerHTML = `
        <div class="row align-items-end">
            <div class="col-md-10">
                <label class="form-label">Video URL</label>
                <input type="url" class="form-control" name="video_urls[]" placeholder="https://youtube.com/watch?v=..." required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-video">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        </div>
    `;

            videoContainer.appendChild(newVideo);
        });

        videoContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-video')) {
                e.target.closest('.video-item').remove();
            }
        });
    });
</script>

<script>
    // Auto-generate slug from product name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        document.getElementById('slug').value = slug;
    });

    // Auto-generate SKU from name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const sku = name.toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .replace(/\s+/g, '-')
            .substring(0, 10) + '-' + Math.random().toString(36).substr(2, 4).toUpperCase();
        document.getElementById('sku').value = sku;
    });

    // Auto-populate meta title from product name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        if (name && !document.getElementById('meta_title').value) {
            document.getElementById('meta_title').value = name + ' - Premium Cycles';
        }
    });

    // Add Specification functionality
    document.getElementById('add-spec').addEventListener('click', function() {
        const container = document.getElementById('spec-container');
        const specItem = document.createElement('div');
        specItem.className = 'spec-item';
        specItem.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Specification Name</label>
                <input type="text" class="form-control" name="spec_names[]" placeholder="e.g., Gear System">
            </div>
            <div class="col-md-6">
                <label class="form-label">Specification Value</label>
                <input type="text" class="form-control" name="spec_values[]" placeholder="e.g., 21 Speed Shimano">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-spec">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
        container.appendChild(specItem);
    });

    // Remove specification
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-spec')) {
            e.target.closest('.spec-item').remove();
        }
    });

    // Add Variant functionality
    document.getElementById('add-variant').addEventListener('click', function() {
        const container = document.getElementById('variants-container');
        const variantRow = document.createElement('div');
        variantRow.className = 'variant-row';
        variantRow.innerHTML = `<div class="row">
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Variant Name</label>
                                                    <input type="text" name="variant_name[]" class="form-control" placeholder="Size S, Red, etc.">
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Price</label>
                                                    <input type="number" step="0.01" name="variant_price[]" class="form-control" placeholder="299.99">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">SKU</label>
                                                    <input type="text" name="variant_sku[]" class="form-control" placeholder="BIKE-001-S">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Stock</label>
                                                    <input type="number" name="variant_stock[]" class="form-control" placeholder="10">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Discount Price</label>
                                                    <input type="number" step="0.01" name="variant_discount_price[]" class="form-control" placeholder="249.99">
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="form-label">Image</label>
                                                    <input type="file" name="variant_image[]" multiple class="form-control" >
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="form-label">Status</label>
                                                    <select name="variant_status[]" class="form-control">
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                     <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm remove-variant form-control ">
                                                        <i class="bi bi-trash"></i> Remove Variant
                                                    </button>
                                                </div>
                                            </div>`;
        container.appendChild(variantRow);
    });

    // Remove variant
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-variant')) {
            e.target.closest('.variant-row').remove();
        }
    });

    // Image upload functionality
    const imageUploadArea = document.getElementById('imageUploadArea');
    const imageInput = document.getElementById('imageInput');
    const imagePreviews = document.getElementById('imagePreviews');

    // Drag and drop functionality
    imageUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        imageUploadArea.classList.add('dragover');
    });

    imageUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        imageUploadArea.classList.remove('dragover');
    });

    imageUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        imageUploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    imageInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        for (let file of files) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addImagePreview(e.target.result, file.name);
                };
                reader.readAsDataURL(file);
            }
        }
    }

    function addImagePreview(src, filename) {
        const previewContainer = document.createElement('div');
        previewContainer.className = 'image-preview-container';
        previewContainer.innerHTML = `
        <img src="${src}" class="image-preview" alt="${filename}">
        <button type="button" class="remove-image" onclick="this.parentElement.remove()">
            <i class="bi bi-x"></i>
        </button>
    `;
        imagePreviews.appendChild(previewContainer);
    }

    // Reset form function
    function resetForm() {
        if (confirm('Are you sure you want to reset the form? All data will be lost.')) {
            document.getElementById('productForm').reset();
            imagePreviews.innerHTML = '';
            // Reset specifications to default
            const specContainer = document.getElementById('spec-container');
            const variantsContainer = document.getElementById('variants-container');

            // Keep only first 2 spec items
            const specItems = specContainer.querySelectorAll('.spec-item');
            for (let i = 2; i < specItems.length; i++) {
                specItems[i].remove();
            }

            // Keep only first variant
            const variantItems = variantsContainer.querySelectorAll('.variant-row');
            for (let i = 1; i < variantItems.length; i++) {
                variantItems[i].remove();
            }
        }
    }

    // Form validation
    document.getElementById('productForm').addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('price').value);
        const discountPrice = parseFloat(document.getElementById('discount_price').value) || 0;

        if (discountPrice > 0 && discountPrice >= price) {
            e.preventDefault();
            alert('Sale price must be less than regular price');
            return false;
        }

        const stockQty = parseInt(document.getElementById('stock_quantity').value);
        const stockStatus = document.getElementById('stock_status').value;

        if (stockQty === 0 && stockStatus === 'in-stock') {
            if (!confirm('Stock quantity is 0 but status is "In Stock". Continue anyway?')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Character counter for meta description
    document.getElementById('meta_description').addEventListener('input', function() {
        const length = this.value.length;
        const formText = this.parentElement.querySelector('.form-text');
        let color = 'text-muted';
        if (length > 160) color = 'text-danger';
        else if (length > 150) color = 'text-warning';
        else if (length > 120) color = 'text-success';

        formText.className = `form-text ${color}`;
        formText.textContent = `${length}/160 characters - Recommended: 150-160 characters`;
    });

    // Character counter for meta title
    document.getElementById('meta_title').addEventListener('input', function() {
        const length = this.value.length;
        const formText = this.parentElement.querySelector('.form-text');
        let color = 'text-muted';
        if (length > 60) color = 'text-danger';
        else if (length > 50) color = 'text-success';

        formText.className = `form-text ${color}`;
        formText.textContent = `${length}/60 characters - Recommended: 50-60 characters`;
    });

    // Price formatting
    document.getElementById('price').addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });

    document.getElementById('discount_price').addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });



    // Clear draft on successful submission
    window.addEventListener('beforeunload', function() {
        if (autoSaveInterval) {
            clearInterval(autoSaveInterval);
        }
    });
</script>
</body>

</html>