<?php
session_start();
require_once '../include/connection.php';

// Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: ../login.php');
//     exit();
// }

// Function to build category tree with subcategory counts
function buildCategoryTree($conn, $parent_id = null, $level = 0)
{
    $tree = [];

    // Get categories for current level
    $query = "SELECT c.*, 
                     (SELECT COUNT(*) FROM categories WHERE parent_id = c.id) as subcategory_count
                     --  ,(SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
              FROM categories c 
              WHERE " . ($parent_id === null ? "parent_id IS NULL" : "parent_id = ?") . "
              ORDER BY c.name ASC";

    $stmt = mysqli_prepare($conn, $query);
    if ($parent_id !== null) {
        mysqli_stmt_bind_param($stmt, "i", $parent_id);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $row['level'] = $level;
        $row['children'] = buildCategoryTree($conn, $row['id'], $level + 1);
        $tree[] = $row;
    }

    return $tree;
}

// Function to flatten tree for table display
function flattenTree($tree, &$flat = [])
{
    foreach ($tree as $item) {
        $flat[] = $item;
        if (!empty($item['children'])) {
            flattenTree($item['children'], $flat);
        }
    }
    return $flat;
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build category tree
$category_tree = buildCategoryTree($conn);
$flat_categories = flattenTree($category_tree);

// Filter categories based on search and status
$filtered_categories = [];
foreach ($flat_categories as $category) {
    $include = true;

    // Search filter
    if (!empty($search)) {
        $search_lower = strtolower($search);
        if (
            strpos(strtolower($category['name']), $search_lower) === false &&
            strpos(strtolower($category['description']), $search_lower) === false
        ) {
            $include = false;
        }
    }

    // Status filter
    if (!empty($status_filter) && $category['status'] !== $status_filter) {
        $include = false;
    }

    if ($include) {
        $filtered_categories[] = $category;
    }
}

// Pagination
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_categories = count($filtered_categories);
$total_pages = ceil($total_categories / $per_page);
$offset = ($page - 1) * $per_page;
$current_categories = array_slice($filtered_categories, $offset, $per_page);

// Handle delete action
if (isset($_POST['delete_category'])) {
    $category_id = (int)$_POST['category_id'];

    // Check if category has subcategories
    $check_query = "SELECT COUNT(*) as count FROM categories WHERE parent_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $category_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_row = mysqli_fetch_assoc($check_result);

    if ($check_row['count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete category with subcategories. Please delete subcategories first.";
    } else {
        // Delete the category
        $delete_query = "DELETE FROM categories WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "i", $category_id);

        if (mysqli_stmt_execute($delete_stmt)) {
            $_SESSION['success_message'] = "Category deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting category: " . mysqli_error($conn);
        }
    }

    // Redirect to avoid form resubmission
    header('Location: categories.php');
    exit();
}
// Handle bulk status update - CORRECTED VERSION
if (isset($_POST['bulk_action']) && isset($_POST['selected_categories'])) {
    $action = $_POST['bulk_action'];
    $selected_categories = $_POST['selected_categories'];

    // Convert comma-separated string to array and sanitize
    if (is_string($selected_categories)) {
        $selected_ids = array_map('intval', explode(',', $selected_categories));
    } else {
        $selected_ids = array_map('intval', $selected_categories);
    }

    // Remove any invalid IDs (0 or negative)
    $selected_ids = array_filter($selected_ids, function ($id) {
        return $id > 0;
    });

    if (!empty($selected_ids) && ($action === 'activate' || $action === 'deactivate')) {
        $new_status = $action === 'activate' ? 'active' : 'inactive';

        // Create placeholders for prepared statement
        $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
        $update_query = "UPDATE categories SET status = ? WHERE id IN ($placeholders)";

        $stmt = mysqli_prepare($conn, $update_query);

        if ($stmt) {
            // Prepare parameters: status first, then all IDs
            $params = array_merge([$new_status], $selected_ids);

            // Create type string: 's' for status, 'i' for each ID
            $types = 's' . str_repeat('i', count($selected_ids));

            // Bind parameters
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            if (mysqli_stmt_execute($stmt)) {
                $count = count($selected_ids);
                $_SESSION['success_message'] = "$count categories updated successfully.";
            } else {
                $_SESSION['error_message'] = "Error updating categories: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error_message'] = "Error preparing statement: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "Invalid action or no categories selected.";
    }

    header('Location: categories.php');
    exit();
}
include_once('./layout/sidebar.php');
?>
<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar border-bottom">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 text-dark">Manage Categories</h5>
            <span class="badge bg-secondary ms-2"><?= count($flat_categories) ?> Total</span>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
            <div class="dropdown">
                <button class="btn btn-link text-secondary" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-4"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid p-4">

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="text-dark mb-1">Categories Management</h4>
                <p class="text-muted mb-0">Manage your product categories in tree structure</p>
            </div>
            <div>
                <a href="category-form.php" class="btn btn-dark">
                    <i class="bi bi-plus-circle me-2"></i>Add Category
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Categories</h6>
                                <h3 class="mb-0"><?= count($flat_categories) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-tags fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Active Categories</h6>
                                <h3 class="mb-0"><?= count(array_filter($flat_categories, function ($cat) {
                                                        return $cat['status'] === 'active';
                                                    })) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-check-circle fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Inactive Categories</h6>
                                <h3 class="mb-0"><?= count(array_filter($flat_categories, function ($cat) {
                                                        return $cat['status'] === 'inactive';
                                                    })) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-x-circle fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Parent Categories</h6>
                                <h3 class="mb-0"><?= count(array_filter($flat_categories, function ($cat) {
                                                        return $cat['parent_id'] === null;
                                                    })) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-diagram-3 fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bulk-actions" id="bulkActions">
            <form method="POST" class="d-flex align-items-center" id="bulkActionsForm">
                <span class="me-3">With selected:</span>
                <select name="bulk_action" class="form-select me-3" style="width: auto;">
                    <option value="">Choose action</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                </select>
                <button type="submit" class="btn btn-dark btn-sm">Apply</button>
                <!-- Hidden field to store selected categories -->
                <input type="hidden" name="selected_categories" id="selectedCategories" value="">
            </form>
        </div>

        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search categories..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-dark">Filter</button>
                        <a href="categories.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-diagram-3 me-2"></i>Category
                        </h5>
                    </div>
                    <div class="col-auto">
                        <div class="form-check">
                            <label class="form-check-label" for="selectAll">
                                Select All
                            </label>
                            <select name="view_perpage" id="view_perpage">
                                <option value="10">10</option>
                                <option value="50"></option>
                                <option value=""></option>
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="masterCheckbox">
                                </th>
                                <th>Category Name</th>
                                <th>Products</th>
                                <th>Subcategories</th>
                                <th>Created Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($current_categories)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No categories found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($current_categories as $category): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input category-checkbox" value="<?= $category['id'] ?>">
                                        </td>
                                        <td>
                                            <div class="category-level-<?= min($category['level'], 4) ?>">
                                                <div class="d-flex align-items-center">
                                                    <?php if ($category['level'] > 0): ?>
                                                        <i class="bi bi-arrow-return-right category-tree-icon"></i>
                                                    <?php endif; ?>

                                                    <?php if (!empty($category['image'])): ?>
                                                        <img src="../<?= htmlspecialchars($category['image']) ?>"
                                                            alt="<?= htmlspecialchars($category['name']) ?>"
                                                            class="category-image">
                                                    <?php else: ?>
                                                        <i class="bi bi-tag category-tree-icon fs-4"></i>
                                                    <?php endif; ?>

                                                    <div>
                                                        <strong><?= htmlspecialchars($category['name']) ?></strong>
                                                        <?php if ($category['subcategory_count'] > 0): ?>
                                                            <span class="subcategory-count"><?= $category['subcategory_count'] ?> sub</span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($category['description'])): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars(substr($category['description'], 0, 60)) ?><?= strlen($category['description']) > 60 ? '...' : '' ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <!-- <span class="badge bg-info"><?= $category['product_count'] ?></span> -->
                                            <span class="badge bg-info"><?= rand(20, 89) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($category['subcategory_count'] > 0): ?>
                                                <span class="badge bg-secondary"><?= $category['subcategory_count'] ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M j, Y', strtotime($category['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge <?= $category['status'] === 'active' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                <?= ucfirst($category['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="category-form.php?edit=<?= $category['id'] ?>"
                                                    class="btn btn-sm btn-outline-dark"
                                                    title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="category-form.php?parent_id=<?= $category['id'] ?>"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Add Subcategory">
                                                    <i class="bi bi-plus"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    title="Delete"
                                                    onclick="confirmDelete(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>', <?= $category['subcategory_count'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_categories) ?> of <?= $total_categories ?> entries
                        </span>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link bg-dark text-white border-0" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link <?= $i === $page ? 'bg-secondary' : 'bg-dark' ?> text-white border-0" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link bg-dark text-white border-0" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>

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
                <p id="deleteMessage"></p>
                <div class="alert alert-warning" id="subcategoryWarning" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This category has subcategories. You must delete all subcategories first.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="category_id" id="deleteCategoryId">
                    <button type="submit" name="delete_category" class="btn btn-danger" id="deleteButton">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Checkbox functionality
    document.addEventListener('DOMContentLoaded', function() {
        const masterCheckbox = document.getElementById('masterCheckbox');
        const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCategories = document.getElementById('selectedCategories');
        const bulkActionsForm = document.getElementById('bulkActionsForm');

        // Master checkbox functionality
        masterCheckbox.addEventListener('change', function() {
            categoryCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        // Individual checkbox functionality
        categoryCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateMasterCheckbox();
                updateBulkActions();
            });
        });

        function updateMasterCheckbox() {
            const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
            masterCheckbox.checked = checkedCount === categoryCheckboxes.length && checkedCount > 0;
            masterCheckbox.indeterminate = checkedCount > 0 && checkedCount < categoryCheckboxes.length;
        }

        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.category-checkbox:checked');
            const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

            if (selectedIds.length > 0) {
                bulkActions.style.display = 'block';
                selectedCategories.value = selectedIds.join(',');
            } else {
                bulkActions.style.display = 'none';
                selectedCategories.value = '';
            }
        }

        // Bulk actions form submission with validation
        bulkActionsForm.addEventListener('submit', function(e) {
            const action = document.querySelector('select[name="bulk_action"]').value;
            const selectedIds = selectedCategories.value;

            if (!action) {
                e.preventDefault();
                alert('Please select an action.');
                return;
            }

            if (!selectedIds) {
                e.preventDefault();
                alert('Please select at least one category.');
                return;
            }

            const selectedCount = selectedIds.split(',').length;
            const actionText = action === 'activate' ? 'activate' : 'deactivate';

            if (!confirm(`Are you sure you want to ${actionText} ${selectedCount} selected categories?`)) {
                e.preventDefault();
            }
        });
    });
    // Delete confirmation function
    function confirmDelete(categoryId, categoryName, subcategoryCount) {
        document.getElementById('deleteCategoryId').value = categoryId;
        document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${categoryName}"? This action cannot be undone.`;

        const subcategoryWarning = document.getElementById('subcategoryWarning');
        const deleteButton = document.getElementById('deleteButton');

        if (subcategoryCount > 0) {
            subcategoryWarning.style.display = 'block';
            deleteButton.disabled = true;
            deleteButton.textContent = 'Cannot Delete';
        } else {
            subcategoryWarning.style.display = 'none';
            deleteButton.disabled = false;
            deleteButton.textContent = 'Delete Category';
        }

        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Auto-refresh every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);
</script>

</body>

</html>