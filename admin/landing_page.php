<?php
require_once '../include/connection.php';

// Fetch all landing page sections
$query = "SELECT id, banner_image, circle_image, heading, description, primary_btn_text, primary_btn_link, secondary_btn_text, secondary_btn_link, features, status, created_at FROM landing_page ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Handle Delete
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $query = "DELETE FROM landing_page WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo "Landing page section deleted successfully!";
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
    exit;
}
?>

<?php include './layout/sidebar.php'; ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 text-dark">Manage Landing Page Sections</h5>
        </div>
        <div class="d-flex align-items-center">
            <a href="landing_page_form.php" class="btn btn-dark btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Section
            </a>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid p-4">

        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-dark">
                    <i class="bi bi-list-ul me-2"></i>Landing Page Sections
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                        // Decode Features JSON if exists
                        $features = !empty($row['features']) ? json_decode($row['features'], true) : [];
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start flex-wrap">

                            <div class="d-flex align-items-start gap-3">
                                <!-- Banner & Circle Images -->
                                <div>
                                    <?php if (!empty($row['banner_image'])): ?>
                                        <img src="../assets/uploads/landing/<?php echo $row['banner_image']; ?>" alt="banner" style="height:50px; width:auto;" class="mb-1">
                                    <?php endif; ?>
                                    <?php if (!empty($row['circle_image'])): ?>
                                        <img src="../assets/uploads/landing/<?php echo $row['circle_image']; ?>" alt="circle" style="height:40px; width:40px; border-radius:50%;">
                                    <?php endif; ?>
                                </div>

                                <!-- Text Content -->
                                <div>
                                    <strong><?php echo htmlspecialchars($row['heading']); ?></strong>
                                    <div class="text-muted small">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </div>

                                    <!-- Show Features -->
                                    <?php if (!empty($features)): ?>
                                        <ul class="mt-2 mb-0 ps-3 small text-dark">
                                            <?php foreach ($features as $feature): ?>
                                                <li>
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                    <?php echo htmlspecialchars($feature['text']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <!-- Buttons Preview -->
                                    <div class="mt-2">
                                        <?php if (!empty($row['primary_btn_text'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['primary_btn_link']); ?>" target="_blank" class="btn btn-sm btn-primary">
                                                <?php echo htmlspecialchars($row['primary_btn_text']); ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($row['secondary_btn_text'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['secondary_btn_link']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <?php echo htmlspecialchars($row['secondary_btn_text']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column align-items-end gap-2">
                                <span class="badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $row['status'] == 'active' ? 'Active' : 'Inactive'; ?>
                                </span>
                                <a href="landing_page_form.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button class="btn btn-sm btn-danger delete-section" data-id="<?php echo $row['id']; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- jQuery + SweetAlert -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function() {
        // ✅ Delete with SweetAlert
        $(".delete-section").click(function() {
            let id = $(this).data("id");
            let $item = $(this).closest("li");

            Swal.fire({
                title: "Are you sure?",
                text: "This section will be deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "",
                        method: "POST",
                        data: {
                            id: id
                        },
                        success: function(response) {
                            Swal.fire("Deleted!", "Section has been deleted.", "success");
                            $item.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    });
                }
            });
        });
    });
</script>