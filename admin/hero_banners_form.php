<?php
require_once '../include/connection.php';


$id = $image = $link = $alt_text = $status = "";
$editing = false;


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM hero_banners WHERE id = $id LIMIT 1");
    if ($row = mysqli_fetch_assoc($result)) {
        $editing  = true;
        $image    = $row['image'];
        $link     = $row['link'];
        $alt_text = $row['alt_text'];
        $status   = $row['status'];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = isset($_POST['id']) ? intval($_POST['id']) : null;
    $link     = mysqli_real_escape_string($conn, $_POST['link']);
    $alt_text = mysqli_real_escape_string($conn, $_POST['alt_text']);
    $status   = mysqli_real_escape_string($conn, $_POST['status']);

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "../assets/uploads/banners/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image =  $fileName;
        }
    }

    if ($id) {
        // ✅ Update existing banner
        $query = "UPDATE hero_banners 
                  SET image = '$image', link = '$link', alt_text = '$alt_text', status = '$status'
                  WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $success_message = "Hero banner updated successfully!";
        } else {
            $error_message = "Error updating banner: " . mysqli_error($conn);
        }
    } else {
        // ✅ Get next sort_order
        $result = mysqli_query($conn, "SELECT MAX(sort_order) AS max_order FROM hero_banners");
        $row = mysqli_fetch_assoc($result);
        $next_order = $row['max_order'] ? $row['max_order'] + 1 : 1;

        // ✅ Insert new banner
        $query = "INSERT INTO hero_banners (image, link, alt_text, status, sort_order, created_at)
                  VALUES ('$image', '$link', '$alt_text', '$status', '$next_order', NOW())";
        if (mysqli_query($conn, $query)) {
            $success_message = "Hero banner created successfully!";
        } else {
            $error_message = "Error creating banner: " . mysqli_error($conn);
        }
    }
}
?>

<?php include './layout/sidebar.php'; ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a href="hero_banners.php" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i> Back to Banners
            </a>
            <h5 class="mb-0 text-dark"><?php echo $editing ? "Edit Hero Banner" : "Add New Hero Banner"; ?></h5>
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
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-image me-2"></i>Banner Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="image" class="form-label">Banner Image *</label>
                                <input type="file" class="form-control" id="image" name="image" <?php echo $editing ? "" : "required"; ?>>
                                <?php if ($editing && $image): ?>
                                    <div class="mt-2">
                                        <img src="../<?php echo $image; ?>" alt="Current Banner" style="max-height: 120px;">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="link" class="form-label">Link *</label>
                                <input type="text" class="form-control" id="link" name="link"
                                    value="<?php echo htmlspecialchars($link); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="alt_text" class="form-label">Alt Text</label>
                                <input type="text" class="form-control" id="alt_text" name="alt_text"
                                    value="<?php echo htmlspecialchars($alt_text); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Status -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-toggle-on me-2"></i>Publication Status *
                            </h5>
                        </div>
                        <div class="card-body">
                            <select class="form-select" name="status" required>
                                <option value="active" <?php echo ($status == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark btn-lg">
                                    <i class="bi bi-check-circle me-2"></i><?php echo $editing ? "Update" : "Create"; ?>
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                                </button>
                                <a href="hero_banners.php" class="btn btn-outline-danger">
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