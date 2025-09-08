<?php
require_once '../include/connection.php';

// Handle delete request (AJAX)
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $query = "DELETE FROM text_sliders WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

// Fetch all text sliders ordered by sort_order
$query = "SELECT * FROM text_sliders ORDER BY sort_order ASC";
$result = mysqli_query($conn, $query);
?>

<?php include './layout/sidebar.php'; ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 text-dark">Manage Text Sliders</h5>
        </div>
        <div class="d-flex align-items-center">
            <a href="text_sliders-form.php" class="btn btn-dark btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Slider
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
                    <i class="bi bi-list-ul me-2"></i>Sliders List (Drag to Reorder)
                </h5>
            </div>
            <div class="card-body">
                <ul id="sortable" class="list-group">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" data-id="<?php echo $row['id']; ?>">
                            <span><i class="bi bi-list me-2"></i> <?php echo htmlspecialchars($row['message']); ?></span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                                <button class="btn btn-sm btn-danger delete-slider" data-id="<?php echo $row['id']; ?>">
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

<!-- jQuery + jQuery UI (Sortable) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function() {
        // Sortable (drag & drop)
        $("#sortable").sortable({
            update: function(event, ui) {
                let order = [];
                $("#sortable li").each(function(index) {
                    order.push({
                        id: $(this).data("id"),
                        position: index + 1
                    });
                });

                // Send new order to PHP
                $.ajax({
                    url: "../functions/update_sort_order.php",
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

        // Delete button with SweetAlert2
        $(".delete-slider").click(function() {
            let id = $(this).data("id");
            let $item = $(this).closest("li");

            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "", // same page
                        method: "POST",
                        data: {
                            delete_id: id
                        },
                        success: function(response) {
                            if (response.trim() === "success") {
                                Swal.fire(
                                    "Deleted!",
                                    "The slider has been deleted.",
                                    "success"
                                );
                                $item.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                Swal.fire(
                                    "Error!",
                                    "Something went wrong. Try again.",
                                    "error"
                                );
                            }
                        }
                    });
                }
            });
        });
    });
</script>