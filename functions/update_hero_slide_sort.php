<?php
require_once '../include/connection.php';

if (isset($_POST['order'])) {
    foreach ($_POST['order'] as $item) {
        $id = intval($item['id']);
        $position = intval($item['position']);
        mysqli_query($conn, "UPDATE hero_slides SET sort_order = $position WHERE id = $id");
    }
    echo "Order updated successfully!";
} else {
    echo "No data received!";
}
