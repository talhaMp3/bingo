<?php
include '../include/connection.php';

// Handle delete request
if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];

    // Update status to 'deleted' instead of actually deleting
    $delete_sql = "UPDATE products SET status = 'deleted' WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $product_id);

    if ($delete_stmt->execute()) {
        $success_message = "Product deleted successfully!";
    } else {
        $error_message = "Error deleting product: " . $conn->error;
    }
    $delete_stmt->close();
}

// Pagination settings
$products_per_page = 5;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $products_per_page;

// Search and filter parameters
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build WHERE clause for search and filters
$where_conditions = ["p.status != 'deleted'"];
$params = [];
$types = '';

if (!empty($search_term)) {
    $where_conditions[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($category_filter) && $category_filter !== 'All Categories') {
    $where_conditions[] = "c.name = ?";
    $params[] = $category_filter;
    $types .= 's';
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN brands b ON p.brand_id = b.id 
              WHERE $where_clause";

$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $products_per_page);
$count_stmt->close();

// Get products with category and brand information
$sql = "SELECT p.id, p.name, p.price, p.discount_price, p.stock_quantity, p.status, p.image,
               c.name as category_name, b.name as brand_name, p.short_description
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN brands b ON p.brand_id = b.id 
        WHERE $where_clause
        ORDER BY p.id DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $products_per_page;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get all categories for filter dropdown
$categories_sql = "SELECT DISTINCT name FROM categories WHERE status = 'active' ORDER BY name";
$categories_result = $conn->query($categories_sql);

// Function to get stock status class
function getStockStatusClass($stock)
{
    if ($stock > 20) return 'stock-high';
    if ($stock > 5) return 'stock-medium';
    return 'stock-low';
}

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

// Function to parse image JSON and get first image
function getProductImage($imageJson)
{
    $images = json_decode($imageJson, true);
    if (is_array($images) && !empty($images)) {
        return $images[0];
    }
    return 'default-product.png';
}

include './layout/sidebar.php';
?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <h5 class="mb-0 text-dark">Manage Products</h5>
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
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="text-dark mb-1">Products Management</h4>
                <p class="text-muted mb-0">Manage your product inventory</p>
            </div>
            <div>
                <a href="product-form.php" class="btn btn-dark">
                    <i class="bi bi-plus-circle me-2"></i>Add Product
                </a>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0 text-dark">All Products</h5>
                    </div>
                    <div class="col-auto">
                        <form method="GET" class="d-flex gap-2">
                            <select class="form-select form-select-sm" name="category" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php while ($category = $categories_result->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($category['name']); ?>"
                                        <?php echo $category_filter === $category['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search"
                                    placeholder="Search products..."
                                    value="<?php echo htmlspecialchars($search_term); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <?php if (!empty($search_term) || !empty($category_filter)): ?>
                                <a href="?" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <div class="product-img d-flex align-items-center justify-content-center">
                                                <?php
                                                $product_image = getProductImage($row['image']);
                                                if ($product_image !== 'default-product.png'):
                                                ?>
                                                    <img src="../assets/uploads/product/<?php echo htmlspecialchars($product_image); ?>"
                                                        alt="Product" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                                <?php else: ?>
                                                    <i class="bi bi-bicycle text-secondary"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                                <br><small class="text-muted">
                                                    <?php echo htmlspecialchars(substr($row['short_description'] ?? '', 0, 50)); ?>
                                                    <?php echo strlen($row['short_description'] ?? '') > 50 ? '...' : ''; ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td>
                                            <?php if ($row['discount_price'] > 0 && $row['discount_price'] < $row['price']): ?>
                                                <strong>₹<?php echo number_format($row['discount_price'], 2); ?></strong>
                                                <br><small class="text-muted text-decoration-line-through">₹<?php echo number_format($row['price'], 2); ?></small>
                                            <?php else: ?>
                                                <strong>₹<?php echo number_format($row['price'], 2); ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="<?php echo getStockStatusClass($row['stock_quantity']); ?>">
                                                <?php echo $row['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($row['status']); ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="product-form.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-outline-secondary btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="product-view.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-outline-info btn-action" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger btn-action"
                                                title="Delete"
                                                onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                                            No products found
                                            <?php if (!empty($search_term) || !empty($category_filter)): ?>
                                                matching your criteria
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($total_products > 0): ?>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $products_per_page, $total_products); ?>
                            of <?php echo $total_products; ?> entries
                        </span>
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Previous Button -->
                                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?>">
                                            Previous
                                        </a>
                                    </li>

                                    <!-- Page Numbers -->
                                    <?php
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $current_page + 2);

                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?>">
                                            Next
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the product "<span id="productName"></span>"?</p>
                <p class="text-danger small">This action will mark the product as deleted and it won't be visible in the store.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="product_id" id="deleteProductId" value="">
                    <button type="submit" name="delete_product" class="btn btn-danger">Delete Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for stock status -->
<style>
    .stock-high {
        color: #28a745;
        font-weight: bold;
    }

    .stock-medium {
        color: #ffc107;
        font-weight: bold;
    }

    .stock-low {
        color: #dc3545;
        font-weight: bold;
    }

    .btn-action {
        margin-right: 2px;
    }

    .product-img {
        width: 50px;
        height: 50px;
        background: #f8f9fa;
        border-radius: 5px;
    }
</style>

<!-- JavaScript for delete confirmation -->
<script>
    function confirmDelete(productId, productName) {
        document.getElementById('deleteProductId').value = productId;
        document.getElementById('productName').textContent = productName;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>