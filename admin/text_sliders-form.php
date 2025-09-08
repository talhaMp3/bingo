<?php
require_once '../include/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $status  = mysqli_real_escape_string($conn, $_POST['status']);

    // Get the max sort_order and increment by 1
    $result = mysqli_query($conn, "SELECT MAX(sort_order) AS max_order FROM text_sliders");
    $row = mysqli_fetch_assoc($result);
    $next_order = $row['max_order'] ? $row['max_order'] + 1 : 1;

    // Insert with auto sort_order
    $query = "INSERT INTO text_sliders (message, status, sort_order, created_at, updated_at) 
              VALUES ('$message', '$status', '$next_order', NOW(), NOW())";

    if (mysqli_query($conn, $query)) {
        $success_message = "Text slider created successfully!";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}
?>


<?php include './layout/sidebar.php'; ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a href="text_sliders.php" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i> Back to Text slider
            </a>
            <h5 class="mb-0 text-dark">Add New Text slider</h5>
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
                <!-- Left Column -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-info-circle me-2"></i>Basic Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Message*</label>
                                    <input type="text" class="form-control" id="name" name="message" placeholder="Enter message" required>
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
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-toggle-on me-2"></i>Publication Status *
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active">Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Create
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                                </button>
                                <a href="text-sliders.php" class="btn btn-outline-danger">
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