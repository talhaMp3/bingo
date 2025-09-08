<?php
require_once '../include/connection.php';

if (isset($_POST['order'])) {
    foreach ($_POST['order'] as $item) {
        $id = intval($item['id']);
        $position = intval($item['position']);
        mysqli_query($conn, "UPDATE hero_banners SET sort_order = $position WHERE id = $id");
    }
    echo "Sort order updated!";
}
