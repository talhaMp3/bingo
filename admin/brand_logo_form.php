<?php
require_once '../include/connection.php';

// Initialize
$id = $brand_name = $logo = $link = $status = "";
$edit_mode = false;

// ✅ If Edit Mode
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM brand_logos WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $brand_name = $row['brand_name'];
        $logo = $row['logo'];
        $link = $row['link'];
        $status = $row['status'];
        $edit_mode = true;
    }
}

// ✅ Handle Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $brand_name = mysqli_real_escape_string($conn, $_POST['brand_name']);
    $link = mysqli_real_escape_string($conn, $_POST['link']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $logo = $_POST['existing_logo'] ?? "";

    // File upload
    if (!empty($_FILES['logo']['name'])) {
        $target_dir = "../assets/uploads/brands/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES["logo"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $logo =  $file_name;
        }
    }

    if ($id > 0) {
        // Update
        $query = "UPDATE brand_logos SET brand_name='$brand_name', logo='$logo', link='$link', status='$status' WHERE id=$id";
        $success_message = mysqli_query($conn, $query) ? "Brand updated successfully!" : "Error: " . mysqli_error($conn);
    } else {
        // Insert
        $maxOrderQuery = mysqli_query($conn, "SELECT MAX(sort_order) AS max_order FROM brand_logos");
        $row = mysqli_fetch_assoc($maxOrderQuery);
        $sort_order = $row['max_order'] + 1;

        $query = "INSERT INTO brand_logos (brand_name, logo, link, status, sort_order) 
                  VALUES ('$brand_name', '$logo', '$link', '$status', '$sort_order')";
        $success_message = mysqli_query($conn, $query) ? "Brand created successfully!" : "Error: " . mysqli_error($conn);
    }
}
?>

<?php include './layout/sidebar.php'; ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a href="brand_logos.php" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i> Back to Brand Logos
            </a>
            <h5 class="mb-0 text-dark"><?= $edit_mode ? "Edit Brand Logo" : "Add New Brand Logo" ?></h5>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid p-4">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="existing_logo" value="<?= $logo ?>">

            <div class="row">
                <!-- LEFT COLUMN -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Brand Name *</label>
                                <input type="text" name="brand_name" class="form-control" value="<?= htmlspecialchars($brand_name) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Brand Link</label>
                                <input type="text" name="link" class="form-control" value="<?= htmlspecialchars($link) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Logo</label>
                                <input type="file" name="logo" class="form-control">
                                <?php if ($logo): ?>
                                    <div class="mt-2">
                                        <img src="../assets/uploads/brands/<?php echo $logo; ?>" height="50" alt="Brand Logo">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark"><i class="bi bi-toggle-on me-2"></i>Publication Status *</h5>
                        </div>
                        <div class="card-body">
                            <select name="status" class="form-select" required>
                                <option value="active" <?= $status == "active" ? "selected" : "" ?>>Active</option>
                                <option value="inactive" <?= $status == "inactive" ? "selected" : "" ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark btn-lg">
                                    <i class="bi bi-check-circle me-2"></i><?= $edit_mode ? "Update" : "Create" ?>
                                </button>
                                <a href="brand_logos.php" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- row end -->
        </form>
    </div>
</div>