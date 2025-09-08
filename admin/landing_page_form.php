<?php
require_once '../include/connection.php';

$id = $banner_image = $circle_image = $heading = $description = "";
$primary_btn_text = $primary_btn_link = $secondary_btn_text = $secondary_btn_link = "";
$features = "[]";
$status = "active";
$editing = false;

// ✅ If editing, load existing record
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM landing_page WHERE id = $id LIMIT 1");
    if ($row = mysqli_fetch_assoc($result)) {
        $editing = true;
        $banner_image        = $row['banner_image'];
        $circle_image        = $row['circle_image'];
        $heading             = $row['heading'];
        $description         = $row['description'];
        $primary_btn_text    = $row['primary_btn_text'];
        $primary_btn_link    = $row['primary_btn_link'];
        $secondary_btn_text  = $row['secondary_btn_text'];
        $secondary_btn_link  = $row['secondary_btn_link'];
        $features            = $row['features'];
        $status              = $row['status'];
    }
}

// ✅ Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $heading            = mysqli_real_escape_string($conn, $_POST['heading']);
    $description        = mysqli_real_escape_string($conn, $_POST['description']);
    $primary_btn_text   = mysqli_real_escape_string($conn, $_POST['primary_btn_text']);
    $primary_btn_link   = mysqli_real_escape_string($conn, $_POST['primary_btn_link']);
    $secondary_btn_text = mysqli_real_escape_string($conn, $_POST['secondary_btn_text']);
    $secondary_btn_link = mysqli_real_escape_string($conn, $_POST['secondary_btn_link']);
    $status             = mysqli_real_escape_string($conn, $_POST['status']);

    // ✅ Upload paths
    $uploadDir = "../assets/uploads/feature/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Banner Image
    if (!empty($_FILES['banner_image']['name'])) {
        $fileName = time() . "_banner_" . basename($_FILES['banner_image']['name']);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $targetFile)) {
            $banner_image = $fileName;
        }
    }

    // Circle Image
    if (!empty($_FILES['circle_image']['name'])) {
        $fileName = time() . "_circle_" . basename($_FILES['circle_image']['name']);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['circle_image']['tmp_name'], $targetFile)) {
            $circle_image = $fileName;
        }
    }

    // ✅ Handle Feature Images and Data
    $featuresArray = [];
    if (isset($_POST['features']) && is_array($_POST['features'])) {
        foreach ($_POST['features'] as $index => $feature) {
            $featureText = mysqli_real_escape_string($conn, $feature['text']);
            $featureIcon = $feature['icon']; // existing icon path from editing

            // Check if new image uploaded for this feature
            if (isset($_FILES['feature_images']['name'][$index]) && !empty($_FILES['feature_images']['name'][$index])) {
                $fileName = time() . "_feature_{$index}_" . basename($_FILES['feature_images']['name'][$index]);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['feature_images']['tmp_name'][$index], $targetFile)) {
                    $featureIcon = $fileName;
                }
            }

            if (!empty($featureText) || !empty($featureIcon)) {
                $featuresArray[] = [
                    'icon' => $featureIcon,
                    'text' => $featureText
                ];
            }
        }
    }

    $features = json_encode($featuresArray, JSON_UNESCAPED_SLASHES);

    // ✅ Update or Insert
    if ($id) {
        $query = "UPDATE landing_page SET 
            banner_image='$banner_image',
            circle_image='$circle_image',
            heading='$heading',
            description='$description',
            primary_btn_text='$primary_btn_text',
            primary_btn_link='$primary_btn_link',
            secondary_btn_text='$secondary_btn_text',
            secondary_btn_link='$secondary_btn_link',
            features='$features',
            status='$status'
            WHERE id=$id";
        if (mysqli_query($conn, $query)) {
            $success_message = "Landing Page updated successfully!";
        } else {
            $error_message = "Error updating: " . mysqli_error($conn);
        }
    } else {
        $query = "INSERT INTO landing_page 
            (banner_image, circle_image, heading, description, primary_btn_text, primary_btn_link, secondary_btn_text, secondary_btn_link, features, status, created_at) 
            VALUES ('$banner_image','$circle_image','$heading','$description','$primary_btn_text','$primary_btn_link','$secondary_btn_text','$secondary_btn_link','$features','$status',NOW())";
        if (mysqli_query($conn, $query)) {
            $success_message = "Landing Page created successfully!";
        } else {
            $error_message = "Error creating: " . mysqli_error($conn);
        }
    }
}
?>

<?php include './layout/sidebar.php'; ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a href="landing_page_list.php" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i> Back to Landing Pages
            </a>
            <h5 class="mb-0 text-dark"><?php echo $editing ? "Edit Landing Page" : "Add New Landing Page"; ?></h5>
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
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            <?php endif; ?>

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark"><i class="bi bi-info-circle me-2"></i>Landing Page Content</h5>
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label">Banner Image *</label>
                                <input type="file" class="form-control" name="banner_image" accept="image/*" <?php echo $editing ? "" : "required"; ?>>
                                <?php if ($editing && $banner_image): ?>
                                    <img src="../<?php echo $banner_image; ?>" class="mt-2" style="max-height:120px;">
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Circle Image</label>
                                <input type="file" class="form-control" name="circle_image" accept="image/*">
                                <?php if ($editing && $circle_image): ?>
                                    <img src="../<?php echo $circle_image; ?>" class="mt-2" style="max-height:120px;">
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Heading *</label>
                                <textarea name="heading" class="form-control" required><?php echo htmlspecialchars($heading); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Primary Button Text</label>
                                    <input type="text" class="form-control" name="primary_btn_text" value="<?php echo htmlspecialchars($primary_btn_text); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Primary Button Link</label>
                                    <input type="text" class="form-control" name="primary_btn_link" value="<?php echo htmlspecialchars($primary_btn_link); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Secondary Button Text</label>
                                    <input type="text" class="form-control" name="secondary_btn_text" value="<?php echo htmlspecialchars($secondary_btn_text); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Secondary Button Link</label>
                                    <input type="text" class="form-control" name="secondary_btn_link" value="<?php echo htmlspecialchars($secondary_btn_link); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Features</label>
                                <div id="features-repeater">

                                    <?php
                                    $featuresArr = json_decode($features, true);
                                    if (!empty($featuresArr)) {
                                        foreach ($featuresArr as $index => $feat) { ?>
                                            <div class="feature-item border rounded p-3 mb-3">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Feature Image</label>
                                                        <input type="file" name="feature_images[<?php echo $index; ?>]"
                                                            class="form-control" accept="image/*">
                                                        <?php if (!empty($feat['icon'])): ?>
                                                            <img src="../<?php echo $feat['icon']; ?>"
                                                                class="mt-2" style="max-height:60px;">
                                                            <input type="hidden" name="features[<?php echo $index; ?>][icon]"
                                                                value="<?php echo htmlspecialchars($feat['icon']); ?>">
                                                        <?php else: ?>
                                                            <input type="hidden" name="features[<?php echo $index; ?>][icon]" value="">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Feature Text</label>
                                                        <input type="text" name="features[<?php echo $index; ?>][text]"
                                                            class="form-control"
                                                            placeholder="Feature description"
                                                            value="<?php echo htmlspecialchars($feat['text']); ?>">
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-danger btn-sm mt-2 remove-feature">
                                                    <i class="bi bi-trash"></i> Remove Feature
                                                </button>
                                            </div>
                                        <?php }
                                    } else { ?>
                                        <!-- Default empty row -->
                                        <div class="feature-item border rounded p-3 mb-3">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small">Feature Image</label>
                                                    <input type="file" name="feature_images[0]" class="form-control" accept="image/*">
                                                    <input type="hidden" name="features[0][icon]" value="">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Feature Text</label>
                                                    <input type="text" name="features[0][text]" class="form-control" placeholder="Feature description">
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-danger btn-sm mt-2 remove-feature">
                                                <i class="bi bi-trash"></i> Remove Feature
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>

                                <button type="button" class="btn btn-outline-primary mt-2" id="add-feature">
                                    <i class="bi bi-plus-circle"></i> Add Feature
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark"><i class="bi bi-toggle-on me-2"></i>Status</h5>
                        </div>
                        <div class="card-body">
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($status == "active") ? "selected" : ""; ?>>Active</option>
                                <option value="inactive" <?php echo ($status == "inactive") ? "selected" : ""; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body d-grid gap-2">
                            <button type="submit" class="btn btn-dark btn-lg">
                                <i class="bi bi-check-circle me-2"></i><?php echo $editing ? "Update" : "Create"; ?>
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                            </button>
                            <a href="landing_page_list.php" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let featureRepeater = document.getElementById("features-repeater");
        let addBtn = document.getElementById("add-feature");

        // Add new feature row
        addBtn.addEventListener("click", function() {
            let index = featureRepeater.querySelectorAll(".feature-item").length;
            let newItem = document.createElement("div");
            newItem.classList.add("feature-item", "border", "rounded", "p-3", "mb-3");
            newItem.innerHTML = `
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small">Feature Image</label>
                    <input type="file" name="feature_images[${index}]" class="form-control" accept="image/*">
                    <input type="hidden" name="features[${index}][icon]" value="">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Feature Text</label>
                    <input type="text" name="features[${index}][text]" class="form-control" placeholder="Feature description">
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-feature">
                <i class="bi bi-trash"></i> Remove Feature
            </button>
        `;
            featureRepeater.appendChild(newItem);
        });

        // Remove feature row
        featureRepeater.addEventListener("click", function(e) {
            if (e.target.closest(".remove-feature")) {
                e.target.closest(".feature-item").remove();
                // Re-index remaining features
                reindexFeatures();
            }
        });

        // Re-index feature items after removal
        function reindexFeatures() {
            let features = featureRepeater.querySelectorAll(".feature-item");
            features.forEach((item, index) => {
                // Update file input name
                let fileInput = item.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.setAttribute('name', `feature_images[${index}]`);
                }

                // Update hidden input name
                let hiddenInput = item.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    hiddenInput.setAttribute('name', `features[${index}][icon]`);
                }

                // Update text input name
                let textInput = item.querySelector('input[type="text"]');
                if (textInput) {
                    textInput.setAttribute('name', `features[${index}][text]`);
                }
            });
        }

        // Image preview functionality
        featureRepeater.addEventListener("change", function(e) {
            if (e.target.type === "file" && e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    // Remove existing preview
                    let existingPreview = e.target.parentElement.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }

                    // Add new preview
                    let preview = document.createElement('img');
                    preview.src = event.target.result;
                    preview.className = 'mt-2 image-preview';
                    preview.style.maxHeight = '60px';
                    preview.style.maxWidth = '100px';
                    preview.style.objectFit = 'cover';
                    preview.style.border = '1px solid #ddd';
                    preview.style.borderRadius = '4px';
                    e.target.parentElement.appendChild(preview);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    });
</script>

<style>
    .feature-item {
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }

    .feature-item:hover {
        background-color: #e9ecef;
        border-color: #adb5bd !important;
    }

    .image-preview {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>