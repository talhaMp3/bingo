<?php
require_once '../include/connection.php';

// Fetch all hero slides ordered by sort_order
$query = "SELECT * FROM hero_slides ORDER BY sort_order ASC";
$result = mysqli_query($conn, $query);

// Handle Delete
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $query = "DELETE FROM hero_slides WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo "Hero slide deleted successfully!";
    } else {
        echo "Error deleting slide: " . mysqli_error($conn);
    }
    exit;
}
?>

<?php include './layout/sidebar.php'; ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 text-dark">Manage Hero Slides</h5>
        </div>
        <div class="d-flex align-items-center">
            <a href="hero_slide_form.php" class="btn btn-dark btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Slide
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
                    <i class="bi bi-list-ul me-2"></i>Hero Slides List (Drag to Reorder)
                </h5>
            </div>
            <div class="card-body">
                <ul id="sortable" class="list-group">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center"
                            data-id="<?php echo $row['id']; ?>">

                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-list me-2"></i>
                                <img src="../assets/uploads/hero_slide/<?php echo $row['image']; ?>" alt="slide image"
                                    style="height:50px; width:auto;">
                                <div>
                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                    <div class="text-muted small">
                                        <?php echo htmlspecialchars($row['subtitle']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $row['status'] == 'active' ? 'Active' : 'Inactive'; ?>
                                </span>
                                <a href="hero_section_form.php?id=<?php echo $row['id']; ?>"
                                    class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button class="btn btn-sm btn-danger delete-slide" data-id="<?php echo $row['id']; ?>">
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

<!-- jQuery + jQuery UI (Sortable) + SweetAlert -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function() {
        // ✅ Drag & Drop Sort
        $("#sortable").sortable({
            update: function(event, ui) {
                let order = [];
                $("#sortable li").each(function(index) {
                    order.push({
                        id: $(this).data("id"),
                        position: index + 1
                    });
                });

                $.ajax({
                    url: "../functions/update_hero_slide_sort.php",
                    method: "POST",
                    data: {
                        order: order
                    },
                    success: function(response) {
                        console.log(response);
                    }
                });
            }
        });

        // ✅ Delete with SweetAlert
        $(".delete-slide").click(function() {
            let id = $(this).data("id");
            let $item = $(this).closest("li");

            Swal.fire({
                title: "Are you sure?",
                text: "This hero slide will be deleted!",
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
                            Swal.fire("Deleted!", "Hero slide has been deleted.", "success");
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