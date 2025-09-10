<?php
session_start();
require_once '../include/connection.php';

// Enhanced error handling and security
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: ../login.php');
//     exit();
// }

// Initialize variables
$edit_mode = false;
$category_data = [];
$errors = [];
$success_message = '';

// Check if editing existing category
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $category_id = (int)$_GET['edit'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($category_data = mysqli_fetch_assoc($result)) {
        // echo "<pre>";
        // print_r($category_data);
        // exit;
        // Category found
    } else {
        $errors[] = "Category not found.";
    }
}

// Get parent categories (exclude current category if editing)
// $parent_query = "SELECT id, name,parent_id FROM categories ";

$parent_query = "SELECT id, name, parent_id FROM categories";
if ($edit_mode) {
    $parent_query .= " WHERE id != $category_id";
}
$categories = mysqli_query($conn, $parent_query);

// Form validation function
function validateForm($data, $files)
{
    $errors = [];

    // Required fields validation
    if (empty(trim($data['name']))) {
        $errors[] = "Category name is required.";
    } elseif (strlen(trim($data['name'])) > 100) {
        $errors[] = "Category name must be less than 100 characters.";
    }

    if (empty($data['status'])) {
        $errors[] = "Status is required.";
    } elseif (!in_array($data['status'], ['active', 'inactive'])) {
        $errors[] = "Invalid status selected.";
    }

    // Optional field validation
    if (!empty($data['description']) && strlen($data['description']) > 500) {
        $errors[] = "Description must be less than 500 characters.";
    }

    if (!empty($data['meta_title']) && strlen($data['meta_title']) > 60) {
        $errors[] = "Meta title should be less than 60 characters for SEO.";
    }

    if (!empty($data['meta_description']) && strlen($data['meta_description']) > 160) {
        $errors[] = "Meta description should be less than 160 characters for SEO.";
    }

    if (!empty($data['slug']) && !preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
        $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens.";
    }

    // Image validation
    if (!empty($files['image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($files['image']['type'], $allowed_types)) {
            $errors[] = "Invalid image type. Please upload JPEG, PNG, GIF, or WebP.";
        }

        if ($files['image']['size'] > $max_size) {
            $errors[] = "Image size must be less than 5MB.";
        }
    }

    return $errors;
}

// Function to generate slug
function generateSlug($text)
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
}

// Function to check if slug exists
function slugExists($conn, $slug, $exclude_id = null)
{
    $query = "SELECT id FROM categories WHERE slug = ?";
    $params = [$slug];
    $types = "s";

    if ($exclude_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
        $types .= "i";
    }

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_num_rows($result) > 0;
}

// Handle form submission
if (isset($_POST['submit'])) {
    $errors = validateForm($_POST, $_FILES);

    if (empty($errors)) {
        // Sanitize inputs
        $name = trim($_POST['name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $description = trim($_POST['description']);
        $status = $_POST['status'];
        $meta_title = trim($_POST['meta_title']);
        $meta_description = trim($_POST['meta_description']);
        $slug = !empty($_POST['slug']) ? $_POST['slug'] : generateSlug($name);

        // Check if slug already exists
        $exclude_id = $edit_mode ? $category_id : null;
        if (slugExists($conn, $slug, $exclude_id)) {
            $slug = $slug . '-' . time();
        }

        // Handle image upload
        $image_path = $edit_mode ? $category_data['image'] : '';
        if (!empty($_FILES['image']['name'])) {
            $targetDir = '../assets/uploads/categories/';
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = uniqid() . '_' . time() . '.' . $imageFileType;
            $targetFile = $targetDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                // Delete old image if updating
                if ($edit_mode && !empty($category_data['image']) && file_exists('../' . $category_data['image'])) {
                    unlink('../' . $category_data['image']);
                }
                $image_path =  $filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }

        if (empty($errors)) {
            mysqli_begin_transaction($conn);

            try {
                if ($edit_mode) {
                    // Update existing category
                    $query = "UPDATE categories SET name=?, slug=?, description=?, image=?, parent_id=?, status=?, meta_title=?, meta_description=?, updated_at=NOW() WHERE id=?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssssisssi", $name, $slug, $description, $image_path, $parent_id, $status, $meta_title, $meta_description, $category_id);
                } else {
                    // Insert new category
                    $query = "INSERT INTO categories (name, slug, description, image, parent_id, status, meta_title, meta_description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssssisss", $name, $slug, $description, $image_path, $parent_id, $status, $meta_title, $meta_description);
                }

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_commit($conn);
                    $success_message = $edit_mode ? "Category updated successfully!" : "Category added successfully!";

                    // Redirect after successful operation
                    $_SESSION['success_message'] = $success_message;
                    header('Location: categories.php');
                    exit();
                } else {
                    throw new Exception("Database error: " . mysqli_error($conn));
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $errors[] = $e->getMessage();
            }
        }
    }
}

include('./layout/sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'Edit' : 'Add' ?> Category - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }

        .char-counter {
            font-size: 0.875rem;
            color: #6c757d;
            text-align: right;
        }

        .char-counter.warning {
            color: #fd7e14;
        }

        .char-counter.danger {
            color: #dc3545;
        }
    </style>
</head>

<body>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar border-bottom">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <a href="categories.php" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="bi bi-arrow-left"></i> Back to Categories
                </a>
                <h5 class="mb-0 text-dark"><?= $edit_mode ? 'Edit' : 'Add' ?> Category</h5>
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
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="error-message">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Success Message -->
                    <?php if (!empty($success_message)): ?>
                        <div class="success-message">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Page Header -->
                    <div class="mb-4">
                        <h4 class="text-dark mb-1">Category Information</h4>
                        <p class="text-muted mb-0">Fill in the details below to <?= $edit_mode ? 'edit' : 'add' ?> a category</p>
                    </div>

                    <!-- Category Form -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-tags me-2"></i>Category Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" novalidate>
                                <div class="row">
                                    <div class="col-md-8">
                                        <!-- Category Name -->
                                        <div class="mb-4">
                                            <label for="categoryName" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                            <input type="text"
                                                name="name"
                                                id="categoryName"
                                                class="form-control"
                                                placeholder="Enter category name"
                                                value="<?= htmlspecialchars($category_data['name'] ?? '') ?>"
                                                maxlength="100"
                                                required>
                                            <div class="form-text">Enter a unique name for this category (max 100 characters)</div>
                                            <div class="char-counter" id="nameCounter">0/100</div>
                                        </div>

                                        <?php $selected_parent_id = $_GET['parent_id'] ?? ($category_data['parent_id'] ?? ''); ?>

                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">Parent Category</label>
                                            <select name="parent_id" class="form-select">
                                                <option value="">-- None (Top Level) --</option>
                                                <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                                                    <option value="<?= $row['id'] ?>" <?= ($selected_parent_id == $row['id']) ? 'selected' : '' ?>>
                                                        <?php
                                                        if (is_null($row['parent_id'])) {
                                                            echo htmlspecialchars($row['name']) . ' ' . '(P)';
                                                        } else {
                                                            echo htmlspecialchars($row['name']);
                                                        }
                                                        ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <div class="form-text">Select a parent category to create a subcategory</div>
                                        </div>


                                        <!-- Category Description -->
                                        <div class="mb-4">
                                            <label for="categoryDescription" class="form-label fw-semibold">Description</label>
                                            <textarea name="description"
                                                id="categoryDescription"
                                                class="form-control"
                                                rows="4"
                                                maxlength="500"
                                                placeholder="Enter category description"><?= htmlspecialchars($category_data['description'] ?? '') ?></textarea>
                                            <div class="form-text">Provide a brief description of this category (max 500 characters)</div>
                                            <div class="char-counter" id="descCounter">0/500</div>
                                        </div>

                                        <!-- Category Status -->
                                        <div class="mb-4">
                                            <label for="categoryStatus" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                            <select name="status" id="categoryStatus" class="form-select" required>
                                                <option value="">Select status</option>
                                                <option value="active" <?= ($category_data['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>
                                                    <i class="bi bi-check-circle"></i> Active
                                                </option>
                                                <option value="inactive" <?= ($category_data['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>
                                                    <i class="bi bi-x-circle"></i> Inactive
                                                </option>
                                            </select>
                                            <div class="form-text">Active categories will be visible to customers</div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <!-- Category Image -->
                                        <div class="mb-4">
                                            <label for="categoryImage" class="form-label fw-semibold">Category Image</label>
                                            <input type="file"
                                                class="form-control"
                                                name="image"
                                                id="categoryImage"
                                                accept="image/jpeg,image/png,image/gif,image/webp">
                                            <div class="form-text">Upload an image (JPEG, PNG, GIF, WebP - max 5MB)</div>

                                            <!-- Current Image Preview -->
                                            <?php if ($edit_mode && !empty($category_data['image'])): ?>
                                                <div class="mt-3">
                                                    <label class="form-label small text-muted">Current Image:</label>
                                                    <div>
                                                        <img src="../<?= htmlspecialchars($category_data['image']) ?>"
                                                            alt="Current category image"
                                                            class="preview-image">
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Image Preview -->
                                            <div id="imagePreview" class="mt-3" style="display: none;">
                                                <label class="form-label small text-muted">New Image Preview:</label>
                                                <div>
                                                    <img id="previewImg" src="" alt="Image preview" class="preview-image">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEO Settings -->
                                <div class="card bg-light mb-4">
                                    <div class="card-header bg-transparent">
                                        <h6 class="mb-0 text-dark">
                                            <i class="bi bi-search me-2"></i>SEO Settings
                                            <small class="text-muted">(Optional but recommended)</small>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="metaTitle" class="form-label fw-semibold">Meta Title</label>
                                                    <input type="text"
                                                        name="meta_title"
                                                        id="metaTitle"
                                                        class="form-control"
                                                        maxlength="60"
                                                        placeholder="Enter meta title"
                                                        value="<?= htmlspecialchars($category_data['meta_title'] ?? '') ?>">
                                                    <div class="form-text">Recommended: 50-60 characters</div>
                                                    <div class="char-counter" id="metaTitleCounter">0/60</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="categorySlug" class="form-label fw-semibold">URL Slug</label>
                                                    <input type="text"
                                                        name="slug"
                                                        id="categorySlug"
                                                        class="form-control"
                                                        pattern="[a-z0-9-]+"
                                                        placeholder="category-url-slug"
                                                        value="<?= htmlspecialchars($category_data['slug'] ?? '') ?>">
                                                    <div class="form-text">URL-friendly version (lowercase, hyphens only)</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label for="metaDescription" class="form-label fw-semibold">Meta Description</label>
                                            <textarea name="meta_description"
                                                id="metaDescription"
                                                class="form-control"
                                                rows="3"
                                                maxlength="160"
                                                placeholder="Enter meta description"><?= htmlspecialchars($category_data['meta_description'] ?? '') ?></textarea>
                                            <div class="form-text">Recommended: 150-160 characters</div>
                                            <div class="char-counter" id="metaDescCounter">0/160</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="categories.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-2"></i>Cancel
                                        </a>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary me-2" onclick="previewCategory()">
                                            <i class="bi bi-eye me-2"></i>Preview
                                        </button>
                                        <button type="submit" name="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-2"></i>
                                            <?= $edit_mode ? 'Update' : 'Save' ?> Category
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="card mt-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 text-dark">
                                <i class="bi bi-info-circle me-2"></i>Help & Tips
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-semibold">Category Guidelines:</h6>
                                    <ul class="text-muted mb-0">
                                        <li>Category names should be unique and descriptive</li>
                                        <li>Use clear, concise names that customers will understand</li>
                                        <li>Inactive categories won't be visible to customers</li>
                                        <li>Parent categories help organize your catalog</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-semibold">SEO Best Practices:</h6>
                                    <ul class="text-muted mb-0">
                                        <li>Meta titles should be descriptive and under 60 characters</li>
                                        <li>Meta descriptions should summarize the category content</li>
                                        <li>Use relevant keywords naturally in your content</li>
                                        <li>Category images should be at least 300x300 pixels</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Category Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Preview content will be inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Character counters
            const counters = [{
                    input: 'categoryName',
                    counter: 'nameCounter',
                    max: 100
                },
                {
                    input: 'categoryDescription',
                    counter: 'descCounter',
                    max: 500
                },
                {
                    input: 'metaTitle',
                    counter: 'metaTitleCounter',
                    max: 60
                },
                {
                    input: 'metaDescription',
                    counter: 'metaDescCounter',
                    max: 160
                }
            ];

            counters.forEach(item => {
                const input = document.getElementById(item.input);
                const counter = document.getElementById(item.counter);

                if (input && counter) {
                    function updateCounter() {
                        const length = input.value.length;
                        counter.textContent = `${length}/${item.max}`;

                        // Color coding
                        counter.className = 'char-counter';
                        if (length > item.max * 0.8) {
                            counter.classList.add('warning');
                        }
                        if (length > item.max * 0.95) {
                            counter.classList.add('danger');
                        }
                    }

                    input.addEventListener('input', updateCounter);
                    updateCounter(); // Initialize
                }
            });

            // Auto-generate slug from category name
            const categoryName = document.getElementById('categoryName');
            const categorySlug = document.getElementById('categorySlug');

            if (categoryName && categorySlug) {
                categoryName.addEventListener('input', function() {
                    // Only auto-generate if slug is empty (not editing)
                    if (categorySlug.value === '') {
                        const name = this.value;
                        const slug = name.toLowerCase()
                            .replace(/[^a-z0-9 -]/g, '')
                            .replace(/\s+/g, '-')
                            .replace(/-+/g, '-')
                            .replace(/^-|-$/g, '');
                        categorySlug.value = slug;
                    }
                });
            }

            // Image preview
            const imageInput = document.getElementById('categoryImage');
            const previewDiv = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');

            if (imageInput && previewDiv && previewImg) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewDiv.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewDiv.style.display = 'none';
                    }
                });
            }

            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    const categoryName = document.getElementById('categoryName');
                    const categoryStatus = document.getElementById('categoryStatus');

                    // Remove previous validation classes
                    document.querySelectorAll('.is-invalid').forEach(el => {
                        el.classList.remove('is-invalid');
                    });

                    // Validate required fields
                    if (!categoryName.value.trim()) {
                        categoryName.classList.add('is-invalid');
                        isValid = false;
                    }

                    if (!categoryStatus.value) {
                        categoryStatus.classList.add('is-invalid');
                        isValid = false;
                    }

                    // Validate slug format
                    const slug = document.getElementById('categorySlug');
                    if (slug.value && !/^[a-z0-9-]+$/.test(slug.value)) {
                        slug.classList.add('is-invalid');
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fix the highlighted fields before submitting.');
                    }
                });
            }
        });

        // Preview function
        function previewCategory() {
            const name = document.getElementById('categoryName').value;
            const description = document.getElementById('categoryDescription').value;
            const status = document.getElementById('categoryStatus').value;
            const metaTitle = document.getElementById('metaTitle').value;
            const metaDescription = document.getElementById('metaDescription').value;

            if (!name.trim()) {
                alert('Please enter a category name first.');
                return;
            }

            const previewContent = `
        <div class="category-preview">
            <h4>${name}</h4>
            <p class="text-muted mb-2">Status: <span class="badge ${status === 'active' ? 'bg-success' : 'bg-secondary'}">${status}</span></p>
            ${description ? `<p class="mb-3">${description}</p>` : ''}
            
            <hr>
            
            <h6>SEO Information:</h6>
            <p><strong>Meta Title:</strong> ${metaTitle || 'Not set'}</p>
            <p><strong>Meta Description:</strong> ${metaDescription || 'Not set'}</p>
        </div>
    `;

            document.getElementById('previewContent').innerHTML = previewContent;
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        }
    </script>

</body>

</html>