<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bingo_cycle";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    header("Location: products.php");
    exit();
}

// Fetch product details with category and brand information
$sql = "SELECT p.*, c.name as category_name, b.name as brand_name, b.website as brand_website
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN brands b ON p.brand_id = b.id 
        WHERE p.id = ? AND p.status != 'deleted'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: products.php");
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

// Fetch product variants
$variants_sql = "SELECT * FROM product_variants WHERE product_id = ? AND status = 'active' ORDER BY id";
$variants_stmt = $conn->prepare($variants_sql);
$variants_stmt->bind_param("i", $product_id);
$variants_stmt->execute();
$variants_result = $variants_stmt->get_result();
$variants = [];
while ($variant = $variants_result->fetch_assoc()) {
    $variants[] = $variant;
}
$variants_stmt->close();

// Function to parse JSON data safely
function parseJsonSafely($json, $default = [])
{
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : $default;
}

// Parse product data
$images = parseJsonSafely($product['image']);
$specifications = parseJsonSafely($product['specifications']);
$videos = parseJsonSafely($product['video']);

// Function to get status badge class
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'active':
            return 'bg-success';
        case 'inactive':
            return 'bg-secondary';
        case 'draft':
            return 'bg-warning text-dark';
        default:
            return 'bg-secondary';
    }
}

// Function to get stock status
function getStockStatus($stock)
{
    if ($stock > 20) return ['class' => 'text-success', 'text' => 'In Stock'];
    if ($stock > 5) return ['class' => 'text-warning', 'text' => 'Low Stock'];
    if ($stock > 0) return ['class' => 'text-danger', 'text' => 'Very Low Stock'];
    return ['class' => 'text-danger', 'text' => 'Out of Stock'];
}

$stock_info = getStockStatus($product['stock_quantity']);

include './layout/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .product-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }

        .thumbnail-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .thumbnail-image:hover {
            opacity: 0.7;
        }

        .thumbnail-image.active {
            border: 2px solid #007bff;
        }

        .specification-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .variant-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: box-shadow 0.3s;
        }

        .variant-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .price-display {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .original-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 1rem;
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Product Details</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center">
                <span class="text-muted me-3">Welcome, Admin</span>
                <i class="bi bi-person-circle fs-4 text-secondary"></i>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="text-dark mb-1"><?php echo htmlspecialchars($product['name']); ?></h4>
                    <p class="text-muted mb-0">Product ID: #<?php echo $product['id']; ?></p>
                </div>
                <div>
                    <a href="products.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i>Back to Products
                    </a>
                    <a href="product-form.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Edit Product
                    </a>
                </div>
            </div>

            <!-- Product Details -->
            <div class="row">
                <!-- Left Column - Images -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <!-- Main Image -->
                            <div class="text-center mb-3">
                                <?php if (!empty($images)): ?>
                                    <img id="mainImage" src="../assets/uploads/product/<?php echo htmlspecialchars($images[0]); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        class="img-fluid product-image">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                                        <i class="bi bi-image fs-1 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Thumbnail Images -->
                            <?php if (count($images) > 1): ?>
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    <?php foreach ($images as $index => $image): ?>
                                        <img src="../assets/uploads/product/<?php echo htmlspecialchars($image); ?>"
                                            alt="Thumbnail <?php echo $index + 1; ?>"
                                            class="thumbnail-image <?php echo $index === 0 ? 'active' : ''; ?>"
                                            onclick="changeMainImage(this, <?php echo $index; ?>)">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Product Info -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Category:</strong></div>
                                <div class="col-sm-8">
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Brand:</strong></div>
                                <div class="col-sm-8">
                                    <?php if (!empty($product['brand_name'])): ?>
                                        <?php if (!empty($product['brand_website'])): ?>
                                            <a href="<?php echo htmlspecialchars($product['brand_website']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($product['brand_name']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($product['brand_name']); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No brand</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>SKU:</strong></div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($product['sku']); ?></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Price:</strong></div>
                                <div class="col-sm-8">
                                    <div class="price-display text-primary">
                                        ₹<?php echo number_format($product['discount_price'] > 0 ? $product['discount_price'] : $product['price'], 2); ?>
                                        <?php if ($product['discount_price'] > 0 && $product['discount_price'] < $product['price']): ?>
                                            <span class="original-price ms-2">₹<?php echo number_format($product['price'], 2); ?></span>
                                            <span class="badge bg-success ms-2">
                                                <?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>% OFF
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Stock:</strong></div>
                                <div class="col-sm-8">
                                    <span class="<?php echo $stock_info['class']; ?> fw-bold">
                                        <?php echo $product['stock_quantity']; ?> units - <?php echo $stock_info['text']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Status:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge <?php echo getStatusBadgeClass($product['status']); ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Featured:</strong></div>
                                <div class="col-sm-8">
                                    <?php if ($product['is_featured']): ?>
                                        <span class="badge bg-warning text-dark">Featured Product</span>
                                    <?php else: ?>
                                        <span class="text-muted">Not Featured</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Tags:</strong></div>
                                <div class="col-sm-8">
                                    <?php if (!empty($product['tags'])): ?>
                                        <?php
                                        $tags = explode(',', $product['tags']);
                                        foreach ($tags as $tag):
                                        ?>
                                            <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No tags</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4"><strong>Created:</strong></div>
                                <div class="col-sm-8">
                                    <?php echo date('M j, Y g:i A', strtotime($product['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Product Description</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Short Description</h6>
                                    <p><?php echo nl2br(htmlspecialchars($product['short_description'] ?? 'No short description available.')); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Long Description</h6>
                                    <p><?php echo nl2br(htmlspecialchars($product['long_description'] ?? 'No detailed description available.')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications Section -->
            <?php if (!empty($specifications)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Specifications</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table specification-table">
                                        <tbody>
                                            <?php foreach ($specifications as $spec): ?>
                                                <tr>
                                                    <th style="width: 30%;"><?php echo htmlspecialchars($spec['name']); ?></th>
                                                    <td><?php echo htmlspecialchars($spec['value']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Product Variants Section -->
            <?php if (!empty($variants)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Product Variants (<?php echo count($variants); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($variants as $variant): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="variant-card">
                                                <h6><?php echo htmlspecialchars($variant['variant_name']); ?></h6>
                                                <p class="text-muted mb-2">SKU: <?php echo htmlspecialchars($variant['sku']); ?></p>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <span class="fw-bold text-primary">₹<?php echo number_format($variant['discount_price'] ?: $variant['price'], 2); ?></span>
                                                        <?php if ($variant['discount_price'] > 0 && $variant['discount_price'] < $variant['price']): ?>
                                                            <small class="text-muted text-decoration-line-through ms-1">
                                                                ₹<?php echo number_format($variant['price'], 2); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span class="badge <?php echo getStatusBadgeClass($variant['status']); ?>">
                                                        <?php echo ucfirst($variant['status']); ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted">Stock: <?php echo $variant['stock_quantity']; ?> units</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Videos Section -->
            <?php if (!empty($videos)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Product Videos</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($videos as $index => $video): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="video-container">
                                                <?php echo $video; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- SEO Information -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">SEO Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Meta Title</h6>
                                    <p><?php echo htmlspecialchars($product['meta_title'] ?? 'No meta title set'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Meta Description</h6>
                                    <p><?php echo htmlspecialchars($product['meta_description'] ?? 'No meta description set'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Image gallery functionality
        function changeMainImage(thumbnail, index) {
            const mainImage = document.getElementById('mainImage');
            mainImage.src = thumbnail.src;

            // Update active thumbnail
            document.querySelectorAll('.thumbnail-image').forEach(img => img.classList.remove('active'));
            thumbnail.classList.add('active');
        }
    </script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>