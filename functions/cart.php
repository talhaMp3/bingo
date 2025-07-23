<?php
// Get parameters from request
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'add_to_cart'; // Default action

// Set content type to JSON
header('Content-Type: application/json');

// Validate required parameters
if ($product_id <= 0) {
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

if ($user_id <= 0) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// Handle different actions
switch ($action) {
    case 'add_to_cart':
        addToCart($conn, $user_id, $product_id, $quantity);
        break;

    case 'add_to_wishlist':
        addToWishlist($conn, $user_id, $product_id);
        break;

    case 'remove_from_wishlist':
        removeFromWishlist($conn, $user_id, $product_id);
        break;

    case 'move_to_cart':
        moveWishlistToCart($conn, $user_id, $product_id, $quantity);
        break;

    case 'get_wishlist':
        getWishlist($conn, $user_id);
        break;

    case 'check_wishlist':
        checkWishlistStatus($conn, $user_id, $product_id);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

// Function to add product to cart
function addToCart($conn, $user_id, $product_id, $quantity)
{
    if ($quantity <= 0) {
        echo json_encode(['error' => 'Invalid quantity']);
        return;
    }

    $sql = "INSERT INTO cart (user_id, product_id, quantity, created_at) 
            VALUES (?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE 
            quantity = quantity + ?, 
            updated_at = NOW()";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => 'Product added to cart successfully',
                'action' => 'add_to_cart',
                'product_id' => $product_id,
                'quantity' => $quantity
            ]);
        } else {
            echo json_encode(['error' => 'Product already in cart with same quantity']);
        }
    } else {
        echo json_encode(['error' => 'Failed to add product to cart: ' . $conn->error]);
    }

    $stmt->close();
}

// Function to add product to wishlist
function addToWishlist($conn, $user_id, $product_id)
{
    // Check if product already exists in wishlist
    $check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'error' => 'Product already exists in wishlist',
            'action' => 'add_to_wishlist',
            'product_id' => $product_id
        ]);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();

    // Add to wishlist
    $sql = "INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => 'Product added to wishlist successfully',
            'action' => 'add_to_wishlist',
            'product_id' => $product_id
        ]);
    } else {
        echo json_encode(['error' => 'Failed to add product to wishlist: ' . $conn->error]);
    }

    $stmt->close();
}

// Function to remove product from wishlist
function removeFromWishlist($conn, $user_id, $product_id)
{
    $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => 'Product removed from wishlist successfully',
                'action' => 'remove_from_wishlist',
                'product_id' => $product_id
            ]);
        } else {
            echo json_encode(['error' => 'Product not found in wishlist']);
        }
    } else {
        echo json_encode(['error' => 'Failed to remove product from wishlist: ' . $conn->error]);
    }

    $stmt->close();
}

// Function to move product from wishlist to cart
function moveWishlistToCart($conn, $user_id, $product_id, $quantity)
{
    if ($quantity <= 0) {
        echo json_encode(['error' => 'Invalid quantity']);
        return;
    }

    // Start transaction
    $conn->autocommit(false);

    try {
        // Check if product exists in wishlist
        $check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows == 0) {
            throw new Exception('Product not found in wishlist');
        }
        $check_stmt->close();

        // Add to cart
        $cart_sql = "INSERT INTO cart (user_id, product_id, quantity, created_at) 
                     VALUES (?, ?, ?, NOW()) 
                     ON DUPLICATE KEY UPDATE 
                     quantity = quantity + ?, 
                     updated_at = NOW()";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);

        if (!$cart_stmt->execute()) {
            throw new Exception('Failed to add product to cart');
        }
        $cart_stmt->close();

        // Remove from wishlist
        $wishlist_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $wishlist_stmt = $conn->prepare($wishlist_sql);
        $wishlist_stmt->bind_param("ii", $user_id, $product_id);

        if (!$wishlist_stmt->execute()) {
            throw new Exception('Failed to remove product from wishlist');
        }
        $wishlist_stmt->close();

        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);

        echo json_encode([
            'success' => 'Product moved from wishlist to cart successfully',
            'action' => 'move_to_cart',
            'product_id' => $product_id,
            'quantity' => $quantity
        ]);
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $conn->autocommit(true);

        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Function to get user's wishlist
function getWishlist($conn, $user_id)
{
    $sql = "SELECT w.product_id, w.created_at, p.name, p.price, p.image_url 
            FROM wishlist w 
            LEFT JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = ? 
            ORDER BY w.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $wishlist = [];

        while ($row = $result->fetch_assoc()) {
            $wishlist[] = $row;
        }

        echo json_encode([
            'success' => 'Wishlist retrieved successfully',
            'action' => 'get_wishlist',
            'wishlist' => $wishlist,
            'count' => count($wishlist)
        ]);
    } else {
        echo json_encode(['error' => 'Failed to retrieve wishlist: ' . $conn->error]);
    }

    $stmt->close();
}

// Function to check if product is in wishlist
function checkWishlistStatus($conn, $user_id, $product_id)
{
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $in_wishlist = $result->num_rows > 0;

        echo json_encode([
            'success' => 'Wishlist status checked',
            'action' => 'check_wishlist',
            'product_id' => $product_id,
            'in_wishlist' => $in_wishlist
        ]);
    } else {
        echo json_encode(['error' => 'Failed to check wishlist status: ' . $conn->error]);
    }

    $stmt->close();
}
