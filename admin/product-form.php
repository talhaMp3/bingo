    <?php
    require_once '../include/connection.php';

    // Handle form submission
    if ($_POST) {
        // Handle image uploads
        $uploaded_images = [];
        if (isset($_FILES['image']) && !empty($_FILES['image']['name'][0])) {
            $upload_dir = '../assets/uploads/product/';

            // Create directory if it doesn't exist
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
                        $uploaded_images[] = 'asset/uploads/product/' . $new_filename;
                        $uploaded_images[] =  $new_filename;
                    }
                }
            }
        }

        // Convert images array to JSON
        $images_json = json_encode($uploaded_images);

        // Handle video URLs
        $video_urls = [];
        if (isset($_POST['video_urls']) && is_array($_POST['video_urls'])) {
            foreach ($_POST['video_urls'] as $url) {
                if (!empty(trim($url))) {
                    $video_urls[] = trim($url);
                }
            }
        }
        $video_json = json_encode($video_urls);

        // Basic product information
        $category_id = (int)$_POST['category_id'];
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $slug = mysqli_real_escape_string($conn, $_POST['slug']);
        $sku = mysqli_real_escape_string($conn, $_POST['sku']);
        $price = (float)$_POST['price'];
        $short_description = mysqli_real_escape_string($conn, $_POST['short_description']);
        $long_description = mysqli_real_escape_string($conn, $_POST['long_description']);
        $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : 0;
        $stock_quantity = (int)$_POST['stock_quantity'];
        $status = $_POST['status'];

        // New fields
        $brand_id = (int)$_POST['brand_id'];
        $tags = mysqli_real_escape_string($conn, $_POST['tags']);
        $featured = isset($_POST['is_featured']) ? 1 : 0;
        $stock_status = $_POST['stock_status'];

        // SEO fields
        $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
        $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description']);

        // Handle specifications - convert array to JSON
        $specifications = [];
        if (isset($_POST['spec_names']) && isset($_POST['spec_values'])) {
            for ($i = 0; $i < count($_POST['spec_names']); $i++) {
                if (!empty($_POST['spec_names'][$i]) && !empty($_POST['spec_values'][$i])) {
                    $specifications[] = [
                        'name' => $_POST['spec_names'][$i],
                        'value' => $_POST['spec_values'][$i]
                    ];
                }
            }
        }
        $specifications_json = mysqli_real_escape_string($conn, json_encode($specifications));

        // Timestamps
        $created_by = 1; // Assuming admin user ID is 1
        $updated_by = 1;
        $created_at = date('Y-m-d H:i:s');
        $updated_at = time();

        // Insert product with all fields
        $product_query = "INSERT INTO products (
        category_id, name, slug, sku, price, short_description, long_description, 
        discount_price, stock_quantity, image, specifications, video, meta_title, 
        meta_description, status, brand_id, tags, is_featured,created_by, updated_by, created_at, updated_at
    ) VALUES (
        '$category_id', '$name', '$slug', '$sku', '$price', '$short_description', 
        '$long_description', '$discount_price', '$stock_quantity', '$images_json', 
        '$specifications_json', '$video_json', '$meta_title', '$meta_description', '$status', 
        '$brand_id', '$tags', '$featured',
        '$created_by', '$updated_by', '$created_at', '$updated_at'
    )";


        if (mysqli_query($conn, $product_query)) {
            $product_id = mysqli_insert_id($conn);

if (isset($_POST['variant_name']) && is_array($_POST['variant_name'])) {
    for ($i = 0; $i < count($_POST['variant_name']); $i++) {
        if (!empty($_POST['variant_name'][$i])) {
            $variant_name = mysqli_real_escape_string($conn, $_POST['variant_name'][$i]);
            $variant_sku = mysqli_real_escape_string($conn, $_POST['variant_sku'][$i]);
            $variant_price = (float)$_POST['variant_price'][$i];
            $variant_discount_price = !empty($_POST['variant_discount_price'][$i]) ? (float)$_POST['variant_discount_price'][$i] : 0;
            $variant_stock = (int)$_POST['variant_stock'][$i];
            $variant_status = $_POST['variant_status'][$i];

            // 👇 Upload the image file
            $variant_image = '';
            if (isset($_FILES['variant_image']['name'][$i]) && $_FILES['variant_image']['error'][$i] === 0) {
                 $upload_dir = '../assets/uploads/product/';

                // Create directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_name = $_FILES['variant_image']['name'][$i];
                $file_tmp = $_FILES['variant_image']['tmp_name'][$i];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $variant_image = 'asset/uploads/variant/' . $new_filename;
                    $variant_image =  $new_filename;
                }
            }

            // ✅ Now insert into DB
            $variant_query = "INSERT INTO product_variants (
                product_id, variant_name, sku, price, discount_price, 
                stock_quantity, status, image
            ) VALUES (
                '$product_id', '$variant_name', '$variant_sku', '$variant_price', 
                '$variant_discount_price', '$variant_stock', '$variant_status', '$variant_image'
            )";

            mysqli_query($conn, $variant_query);
        }
    }
}

            $success_message = "Product created successfully!";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }

    // Fetch categories for dropdown
    $categories_query = "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC";
    $categories_result = mysqli_query($conn, $categories_query);

    // Fetch brands for dropdown
    $brands_query = "SELECT id, name FROM brands WHERE status = 'active' ORDER BY name ASC";
    $brands_result = mysqli_query($conn, $brands_query);
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Product - Cycle Selling Admin</title>
        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

        <style>
            /* Custom monochrome styling */
            :root {
                --sidebar-width: 250px;
                --navbar-height: 60px;
            }

            body {
                background-color: #f8f9fa;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            /* Sidebar Styles */
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: var(--sidebar-width);
                background-color: #212529;
                color: white;
                z-index: 1000;
                transition: all 0.3s;
            }

            .sidebar .nav-link {
                color: #adb5bd;
                padding: 12px 20px;
                border-radius: 0;
                transition: all 0.3s;
            }

            .sidebar .nav-link:hover,
            .sidebar .nav-link.active {
                background-color: #495057;
                color: white;
            }

            .sidebar .nav-link i {
                margin-right: 10px;
                width: 20px;
            }

            /* Main content area */
            .main-content {
                margin-left: var(--sidebar-width);
                padding-top: var(--navbar-height);
                min-height: 100vh;
            }

            /* Top navbar */
            .top-navbar {
                position: fixed;
                top: 0;
                left: var(--sidebar-width);
                right: 0;
                height: var(--navbar-height);
                background-color: white;
                border-bottom: 1px solid #dee2e6;
                z-index: 999;
            }

            /* Form styling */
            .form-label {
                font-weight: 600;
                color: #495057;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: #6c757d;
                box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
            }

            /* Image upload area */
            .image-upload-area {
                border: 2px dashed #dee2e6;
                border-radius: 8px;
                padding: 40px;
                text-align: center;
                background-color: #f8f9fa;
                transition: all 0.3s;
                cursor: pointer;
            }

            .image-upload-area:hover {
                border-color: #6c757d;
                background-color: #e9ecef;
            }

            .image-upload-area.dragover {
                border-color: #495057;
                background-color: #e9ecef;
            }

            /* Image preview */
            .image-preview {
                max-width: 200px;
                max-height: 200px;
                border-radius: 8px;
                border: 1px solid #dee2e6;
            }

            .image-preview-container {
                position: relative;
                display: inline-block;
                margin: 10px;
            }

            .remove-image {
                position: absolute;
                top: -10px;
                right: -10px;
                background: #dc3545;
                color: white;
                border: none;
                border-radius: 50%;
                width: 25px;
                height: 25px;
                font-size: 12px;
                cursor: pointer;
            }

            /* Price input styling */
            .price-input {
                position: relative;
            }

            .price-input::before {
                content: '$';
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #6c757d;
                font-weight: 600;
                z-index: 10;
            }

            .price-input input {
                padding-left: 25px;
            }

            /* Specifications section */
            .spec-item {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                padding: 15px;
                margin-bottom: 10px;
            }

            /* Variant section */
            .variant-row {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                padding: 15px;
                margin-bottom: 15px;
            }

            /* Responsive design */
            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                }

                .main-content {
                    margin-left: 0;
                }

                .top-navbar {
                    left: 0;
                }
            }
        </style>
    </head>

    <body>
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="p-3">
                <h4 class="text-white mb-4">
                    <i class="bi bi-bicycle"></i> Cycle Admin
                </h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.html">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.html">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="products.html">
                        <i class="bi bi-box-seam"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#orders">
                        <i class="bi bi-cart-check"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#customers">
                        <i class="bi bi-people"></i> Customers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#settings">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <a href="products.html" class="btn btn-outline-secondary btn-sm me-3">
                        <i class="bi bi-arrow-left"></i> Back to Products
                    </a>
                    <h5 class="mb-0 text-dark">Add New Product</h5>
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
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name" required>
                                            <div class="form-text">Enter a clear, descriptive name for your product</div>
                                        </div>

                                        <!-- Slug -->
                                        <div class="col-md-6 mb-3">
                                            <label for="slug" class="form-label">Slug *</label>
                                            <input type="text" class="form-control" id="slug" name="slug" placeholder="product-slug" required>
                                            <div class="form-text">URL-friendly version of the name</div>
                                        </div>
                                    </div>

                                    <!-- Product Description -->
                                    <div class="mb-3">
                                        <label for="long_description" class="form-label">Product Description *</label>
                                        <textarea class="form-control" id="long_description" name="long_description" rows="6" placeholder="Enter detailed product description" required></textarea>
                                        <div class="form-text">Provide a comprehensive description of the product features and benefits</div>
                                    </div>

                                    <!-- Short Description -->
                                    <div class="mb-3">
                                        <label for="short_description" class="form-label">Short Description</label>
                                        <textarea class="form-control" id="short_description" name="short_description" rows="3" placeholder="Enter brief product summary"></textarea>
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
                                    <!-- Video URL -->


                                    <!-- Image Upload Area (Visual Enhancement) -->
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
                                        <!-- Default video URL field -->
                                        <div class="video-item mb-3">
                                            <div class="row align-items-end">
                                                <div class="col-md-10">
                                                    <!-- <label class="form-label">Video URL</label> -->
                                                    <input type="text" class="form-control" name="video_urls[]" placeholder="https://youtube.com/watch?v=..." required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-video">
                                                        <i class="bi bi-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
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
                                        <!-- Default specifications for bikes -->
                                        <div class="spec-item">
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
                                        <div class="spec-item">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Specification Name</label>
                                                    <input type="text" class="form-control" name="spec_names[]" placeholder="Wheel Size" value="Wheel Size">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Specification Value</label>
                                                    <input type="text" class="form-control" name="spec_values[]" placeholder="26 inches">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-spec">
                                                        <i class="bi bi-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
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
                                        <div class="variant-row">
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
                                            </div>
                                        </div>
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
                                                <input type="text" class="form-control" id="meta_title" name="meta_title" placeholder="SEO optimized title">
                                                <div class="form-text">Recommended: 50-60 characters</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="meta_description" class="form-label">Meta Description</label>
                                                <textarea class="form-control" id="meta_description" name="meta_description" rows="3" placeholder="SEO description"></textarea>
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
                                            <input type="number" class="form-control" id="price" name="price" placeholder="0.00" step="0.01" min="0" required>
                                        </div>
                                    </div>

                                    <!-- Sale Price -->
                                    <div class="mb-3">
                                        <label for="discount_price" class="form-label">Sale Price</label>
                                        <div class="price-input">
                                            <input type="number" class="form-control" id="discount_price" name="discount_price" placeholder="0.00" step="0.01" min="0">
                                        </div>
                                        <div class="form-text">Leave empty if not on sale</div>
                                    </div>

                                    <!-- Stock Quantity -->
                                    <div class="mb-3">
                                        <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" placeholder="0" min="0" required>
                                    </div>

                                    <!-- SKU -->
                                    <div class="mb-3">
                                        <label for="sku" class="form-label">SKU</label>
                                        <input type="text" class="form-control" id="sku" name="sku" placeholder="Enter product SKU">
                                        <div class="form-text">Stock Keeping Unit</div>
                                    </div>

                                    <!-- Stock Status -->
                                    <div class="mb-3">
                                        <label for="stock_status" class="form-label">Stock Status *</label>
                                        <select class="form-select" id="stock_status" name="stock_status" required>
                                            <option value="in-stock">In Stock</option>
                                            <option value="out-of-stock">Out of Stock</option>
                                            <option value="on-backorder">On Backorder</option>
                                        </select>
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
                                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Brand -->
                                    <div class="mb-3">
                                        <label for="brand" class="form-label">Brand</label>
                                        <!-- <input type="text" class="form-control" id="brand" name="brand" placeholder="Enter brand name"> -->
                                        <select class="form-select" id="brand_id" name="brand_id" required>
                                            <option value="">Select Brand</option>
                                            <?php while ($brand = mysqli_fetch_assoc($brands_result)): ?>
                                                <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Tags -->
                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags</label>
                                        <input type="text" class="form-control" id="tags" name="tags" placeholder="mountain, outdoor, sport">
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
                                    <!-- Category -->
                                    <div class="mb-3">
                                        <label for="status" class="form-label"></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="active">Published</option>
                                            <option value="draft">Draft</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>

                                    <!-- Brand -->

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="0">
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
                                            <i class="bi bi-check-circle me-2"></i>Create Product
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                            <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                                        </button>
                                        <a href="products.html" class="btn btn-outline-danger">
                                            <i class="bi bi-x-circle me-2"></i>Cancel
                                        </a>
                                    </div>
                                </div>
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
                variantRow.innerHTML = `
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
                                            </div>
    `;
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