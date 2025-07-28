<?php
session_start();
include_once '../include/connection.php';
// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

if (!isset($user_id) || empty($user_id)) {
    // echo json_encode(['success' => false, 'message' => 'Please login to manage wishlist']);
    echo json_encode(['success' => false, 'login_required' => true, 'message' => 'Please login to manage wishlist']);

    exit;
}


// return $user_id;
// Handle AJAX requests
if (isset($_POST['action']) || isset($_GET['action'])) {
    $action = $_POST['action'] ?? $_GET['action'];

    switch ($action) {
        case 'add':
            if (isset($_POST['product_id'])) {
                $product_id = intval($_POST['product_id']);
                $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : null;
                // return print_r($variant_id);
                // exit;
                // Check if already exists
                $check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? AND variant_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("iii", $user_id, $product_id, $variant_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();

                if ($result->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => 'Product already in wishlist']);
                } else {
                    $insert_sql = "INSERT INTO wishlist (user_id, product_id, variant_id) VALUES (?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("iii", $user_id, $product_id, $variant_id);

                    if ($insert_stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Product added to wishlist successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error adding to wishlist']);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Product ID required']);
            }
            exit;

        case 'remove':
            if (isset($_POST['product_id'])) {
                $product_id = intval($_POST['product_id']);

                $delete_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("ii", $user_id, $product_id);

                if ($delete_stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Product removed from wishlist']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error removing from wishlist']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Product ID required']);
            }
            exit;

        case 'clear':
            $clear_sql = "DELETE FROM wishlist WHERE user_id = ?";
            $clear_stmt = $conn->prepare($clear_sql);
            $clear_stmt->bind_param("i", $user_id);

            if ($clear_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Wishlist cleared successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error clearing wishlist']);
            }
            exit;

        case 'count':
            $count_sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $user_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_row = $count_result->fetch_assoc();

            echo json_encode(['success' => true, 'count' => $count_row['count']]);
            exit;

        case 'check':
            if (isset($_POST['product_id'])) {
                $product_id = intval($_POST['product_id']);

                $check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ii", $user_id, $product_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();

                echo json_encode(['success' => true, 'in_wishlist' => $result->num_rows > 0]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product ID required']);
            }
            exit;
    }
}
/*
// Get wishlist items with product details
$wishlist_sql = "SELECT w.*, p.name, p.price, p.image, p.description 
                 FROM wishlist w 
                 INNER JOIN products p ON w.product_id = p.id 
                 WHERE w.user_id = ? 
                 ORDER BY w.created_at DESC";
$wishlist_stmt = $conn->prepare($wishlist_sql);
$wishlist_stmt->bind_param("i", $user_id);
$wishlist_stmt->execute();
$wishlist_result = $wishlist_stmt->get_result();
$wishlist_items = $wishlist_result->fetch_all(MYSQLI_ASSOC);*/

// Get wishlist count
$count_sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$wishlist_count = $count_row['count'];

// Helper Functions
function isInWishlist($conn, $user_id, $product_id)
{
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function getWishlistButton($conn, $product_id, $user_id = null)
{
    if (!$user_id) {
        return '<button class="btn btn-outline-danger" onclick="alert(\'Please login to add to wishlist\')">
                    <i class="fas fa-heart"></i> Add to Wishlist
                </button>';
    }

    $isInWishlist = isInWishlist($conn, $user_id, $product_id);

    if ($isInWishlist) {
        return '<button class="btn btn-danger removeFromWishlist" data-product="' . $product_id . '">
                    <i class="fas fa-heart"></i> In Wishlist
                </button>';
    } else {
        return '<button class="btn btn-outline-danger addToWishlist" data-product="' . $product_id . '">
                    <i class="far fa-heart"></i> Add to Wishlist
                </button>';
    }
}

echo "<img src='https://i.pinimg.com/originals/21/6c/c2/216cc2510f0991e66ef354ce64af0adc.gif'>";
