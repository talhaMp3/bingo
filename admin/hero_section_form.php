<?php
require_once '../include/connection.php';

// Init variables
$id = $_GET['id'] ?? null;
$banner_image = $circle_image = $heading = $description = "";
$primary_btn_text = $primary_btn_link = $secondary_btn_text = $secondary_btn_link = $features = "";
$status = "active"; // default

// If editing, fetch existing
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM landing_page WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows) {
        $row = $result->fetch_assoc();
        extract($row);
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $heading            = mysqli_real_escape_string($conn, $_POST['heading']);
    $description        = mysqli_real_escape_string($conn, $_POST['description']);
    $primary_btn_text   = mysqli_real_escape_string($conn, $_POST['primary_btn_text']);
    $primary_btn_link   = mysqli_real_escape_string($conn, $_POST['primary_btn_link']);
    $secondary_btn_text = mysqli_real_escape_string($conn, $_POST['secondary_btn_text']);
    $secondary_btn_link = mysqli_real_escape_string($conn, $_POST['secondary_btn_link']);
    $features           = mysqli_real_escape_string($conn, $_POST['features']);
    $status             = $_POST['status'];

    // Uploads
    function uploadImage($fileInput, $currentFile, $folder)
    {
        if (!empty($_FILES[$fileInput]['name'])) {
            $targetDir = "../assets/uploads/landing/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $fileName = time() . "_" . basename($_FILES[$fileInput]["name"]);
            $targetFilePath = $targetDir . $fileName;
            if (move_uploaded_file($_FILES[$fileInput]["tmp_name"], $targetFilePath)) {
                return $fileName;
            }
        }
        return $currentFile;
    }

    $banner_image = uploadImage('banner_image', $banner_image, "landing");
    $circle_image = uploadImage('circle_image', $circle_image, "landing");

    if ($id) {
        // UPDATE
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
            $success_message = "Landing section updated successfully!";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    } else {
        $query = "INSERT INTO landing_page
            (banner_image, circle_image, heading, description, primary_btn_text, primary_btn_link, secondary_btn_text, secondary_btn_link, features, status, created_at)
            VALUES ('$banner_image','$circle_image','$heading','$description','$primary_btn_text','$primary_btn_link','$secondary_btn_text','$secondary_btn_link','$features','$status',NOW())";
        if (mysqli_query($conn, $query)) {
            $success_message = "Landing section created successfully!";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<?php include './layout/sidebar.php'; ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a href="landing_page.php" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i> Back to Landing Page
            </a>
            <h5 class="mb-0 text-dark">
                <?= $id ? "Edit Landing Page" : "Add Landing Page" ?>
            </h5>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid p-4">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark"><i class="bi bi-info-circle me-2"></i>Landing Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Heading *</label>
                                    <input type="text" class="form-control" name="heading" value="<?= htmlspecialchars($heading) ?>" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Primary Button Text</label>
                                    <input type="text" class="form-control" name="primary_btn_text" value="<?= htmlspecialchars($primary_btn_text) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Primary Button Link</label>
                                    <input type="text" class="form-control" name="primary_btn_link" value="<?= htmlspecialchars($primary_btn_link) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Secondary Button Text</label>
                                    <input type="text" class="form-control" name="secondary_btn_text" value="<?= htmlspecialchars($secondary_btn_text) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Secondary Button Link</label>
                                    <input type="text" class="form-control" name="secondary_btn_link" value="<?= htmlspecialchars($secondary_btn_link) ?>">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Features (comma separated)</label>
                                    <textarea class="form-control" name="features" rows="2"><?= htmlspecialchars($features) ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Banner Image <?= $id ? "(leave blank to keep)" : "*" ?></label>
                                    <input type="file" class="form-control" name="banner_image" <?= $id ? "" : "required" ?>>
                                    <?php if ($id && $banner_image): ?>
                                        <img src="../assets/uploads/landing/<?= htmlspecialchars($banner_image) ?>" class="img-thumbnail mt-2" width="150">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Circle Image <?= $id ? "(leave blank to keep)" : "" ?></label>
                                    <input type="file" class="form-control" name="circle_image">
                                    <?php if ($id && $circle_image): ?>
                                        <img src="../assets/uploads/landing/<?= htmlspecialchars($circle_image) ?>" class="img-thumbnail mt-2" width="150">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Status -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark"><i class="bi bi-toggle-on me-2"></i>Status</h5>
                        </div>
                        <div class="card-body">
                            <select class="form-select" name="status" required>
                                <option value="active" <?= $status == 'active' ? "selected" : "" ?>>Active</option>
                                <option value="inactive" <?= $status == 'inactive' ? "selected" : "" ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark btn-lg">
                                    <i class="bi bi-check-circle me-2"></i><?= $id ? "Update" : "Create" ?>
                                </button>
                                <a href="landing_page.php" class="btn btn-outline-danger">
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