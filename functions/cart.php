<?php
session_start();
header('Content-Type: application/json');

require_once '../include/connection.php';

$product_id = intval($_POST['product_id'] ?? 0);
$variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : null;
$quantity = intval($_POST['quantity'] ?? 1);
$action = $_POST['action'] ?? 'add_to_cart';
$user_id = $_SESSION['user_id'] ?? null;


// Check login
if (!$user_id) {
    echo json_encode([
        'success' => false,
        'login_required' => true,
        'message' => 'Please login to add items to cart.'
    ]);
    exit;
}

// Validate product
// if ($product_id <= 0 || $quantity <= 0) {
//     echo json_encode([
//         'success' => false,
//         'message' => 'Invalid product or quantity.'
//     ]);
//     exit;
// }

// Handle actions
switch ($action) {
    case 'add_to_cart':
        addToCart($conn, $user_id, $product_id, $variant_id, $quantity);
        break;

    case 'move_to_cart':
        moveWishlistToCart($conn, $user_id, $product_id, $quantity);
        break;

    case 'get_cart':
        getCart($conn, $user_id);
        break;

    case 'check_wishlist':
        checkWishlistStatus($conn, $user_id, $product_id);
        break;



    case 'remove_cart':
        $cart_id = intval($_POST['cart_id'] ?? 0);
        removeCart($conn, $cart_id);
        break;

    case 'update_qty':
        $cart_id = intval($_POST['cart_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        updateCartQty($conn, $user_id, $cart_id, $quantity);
        break;


    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

// Function to remove from cart
function removeCart($conn, $cart_id)
{
    if ($cart_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart ID.']);
        return;
    }

    $sql = "DELETE FROM cart WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cart item removed successfully',
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . $stmt->error]);
    }

    $stmt->close();
}
// Function to update quantity in cart
function updateCartQty($conn, $user_id, $cart_id, $quantity)
{
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity.']);
        return;
    }

    $sql = "UPDATE cart 
            SET qty = ?, updated_at = NOW() 
            WHERE id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'qty ' => $quantity,
            'message' => 'Cart quantity updated successfully',
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . $stmt->error]);
    }

    $stmt->close();
}

function addToCart($conn, $user_id, $product_id, $variant_id, $quantity)
{
    $price = null;

    // 🔹 Check if the item already exists in the cart
    $sql = "SELECT id FROM cart WHERE user_id = ? AND product_id = ? AND variant_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $product_id, $variant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $update_sql = "UPDATE cart 
                       SET qty = qty + ?, updated_at = NOW() 
                       WHERE user_id = ? AND product_id = ? AND variant_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iiii", $quantity, $user_id, $product_id, $variant_id);

        if ($update_stmt->execute()) {
            $cart_count = getCartCount($conn, $user_id);
            echo json_encode([
                'success' => true,
                'message' => 'Product quantity updated in cart ',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating cart']);
        }
        return;
    } else {
        // 🔹 Fetch price based on variant or product
        if (!empty($variant_id)) {
            $price_query = "SELECT discount_price FROM product_variants WHERE id = ?";
            $stmt_price = $conn->prepare($price_query);
            $stmt_price->bind_param("i", $variant_id);
        } else {
            $price_query = "SELECT discount_price FROM products WHERE id = ?";
            $stmt_price = $conn->prepare($price_query);
            $stmt_price->bind_param("i", $product_id);
        }

        $stmt_price->execute();
        $stmt_price->bind_result($price);
        $stmt_price->fetch();
        $stmt_price->close();

        if (!isset($price)) {
            echo json_encode(['success' => false, 'message' => 'Price not found for this product.']);
            return;
        }

        // 🔹 Insert into cart
        $sql = "INSERT INTO cart (user_id, product_id, variant_id, qty, price, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    qty = qty + VALUES(qty),
                    price = VALUES(price),
                    updated_at = NOW()";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiid", $user_id, $product_id, $variant_id, $quantity, $price);

        if ($stmt->execute()) {
            $cart_count = getCartCount($conn);
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart!',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }

        $stmt->close();
    }
}


// 🔹 Helper function to get cart count from session user
function getCartCount($conn)
{
    if (!isset($_SESSION['user_id'])) {
        return 0; // no user logged in
    }

    $user_id = $_SESSION['user_id'];
    $total = 0;

    $sql = "SELECT SUM(qty) as total FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    return $total ?? 0;
}


// // 🔹 Helper function to get cart count
// function getCartCount($conn, $user_id)
// {
//     $total = 0;
//     $sql = "SELECT SUM(qty) as total FROM cart WHERE user_id = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("i", $user_id);
//     $stmt->execute();
//     $stmt->bind_result($total);
//     $stmt->fetch();
//     $stmt->close();

//     return $total ?? 0;
// }


// function addToCart($conn, $user_id, $product_id, $variant_id, $quantity)
// {
//     $price = null;

//     $sql = "SELECT id FROM cart WHERE user_id = ? AND product_id = ? AND variant_id = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("iii", $user_id, $product_id, $variant_id);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     if ($result->num_rows > 0) {

//         $update_sql = "UPDATE cart SET qty = qty + ?, updated_at = NOW() WHERE user_id = ? AND product_id = ? AND variant_id = ?";
//         $update_stmt = $conn->prepare($update_sql);
//         $update_stmt->bind_param("iiii", $quantity, $user_id, $product_id, $variant_id);

//         if ($update_stmt->execute()) {
//             echo json_encode(['success' => true, 'message' => 'Product quantity updated in cart']);
//         } else {
//             echo json_encode(['success' => false, 'message' => 'Error updating cart']);
//         }
//         return;
//     } else {

//         // Step 1: Fetch price based on variant or product
//         if (!empty($variant_id)) {
//             $price_query = "SELECT discount_price FROM product_variants WHERE id = ?";
//             $stmt_price = $conn->prepare($price_query);
//             $stmt_price->bind_param("i", $variant_id);
//         } else {
//             $price_query = "SELECT discount_price FROM products WHERE id = ?";
//             $stmt_price = $conn->prepare($price_query);
//             $stmt_price->bind_param("i", $product_id);
//         }

//         $stmt_price->execute();
//         $stmt_price->bind_result($price);
//         $stmt_price->fetch();
//         $stmt_price->close();

//         if (!isset($price)) {
//             echo json_encode([
//                 'success' => true,
//                 'message' => 'Price not found for this product.'
//             ]);
//             echo json_encode(['success' => false, 'message' => '']);
//             return;
//         }

//         // Step 2: Insert into cart with price
//         $sql = "INSERT INTO cart (user_id, product_id, variant_id, qty, price, created_at)
//             VALUES (?, ?, ?, ?, ?, NOW())
//             ON DUPLICATE KEY UPDATE 
//                 qty = qty + VALUES(qty),
//                 price = VALUES(price),
//                 updated_at = NOW()";

//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("iiiid", $user_id, $product_id, $variant_id, $quantity, $price);


//         if ($stmt->execute()) {
//             echo json_encode([
//                 'success' => true,
//                 'message' => 'Product added to cart!'
//             ]);
//         } else {
//             echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
//         }


//         $stmt->close();
//     }
// }



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

// Function to get user's cart
/*
function getCart($conn, $user_id)
{
    $sql = "SELECT 
                c.id as cart_id,
                c.product_id,
                c.variant_id,
                c.qty,
                COALESCE(v.variant_name, p.name) as name,
                COALESCE(v.discount_price, p.discount_price) as discount_price,
                COALESCE(v.image, p.image) as image,
                p.name as product_name,
                v.variant_name
            FROM cart c
            LEFT JOIN products p ON c.product_id = p.id
            LEFT JOIN product_variants v ON c.variant_id = v.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $cart = [];

        while ($row = $result->fetch_assoc()) {
            $cart[] = $row;
        }

        echo json_encode([
            'success' => 'cart retrieved successfully',
            'action' => 'get_cart',
            'cart' => $cart,
            'count' => count($cart)
        ]);
    } else {
        echo json_encode(['error' => 'Failed to retrieve cart: ' . $conn->error]);
    }

    $stmt->close();
}
    */

// Function to get user's cart
function getCart($conn, $user_id)
{
    $sql = "SELECT 
                c.id as cart_id,
                c.product_id,
                c.variant_id,
                c.qty,
                COALESCE(v.variant_name, p.name) as name,
                COALESCE(v.discount_price, p.discount_price) as discount_price,
                COALESCE(v.image, p.image) as image,
                p.name as product_name,
                v.variant_name
            FROM cart c
            LEFT JOIN products p ON c.product_id = p.id
            LEFT JOIN product_variants v ON c.variant_id = v.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $cart = [];
        $overall_total = 0;

        while ($row = $result->fetch_assoc()) {
            // Convert discount_price to float
            $price = (float)$row['discount_price'];
            $qty   = (int)$row['qty'];
            $item_total = $price * $qty;

            // Add item_total to the row
            $row['item_total'] = number_format($item_total, 2, '.', '');

            // Add to overall total
            $overall_total += $item_total;

            $cart[] = $row;
        }

        echo json_encode([
            'success'       => 'cart retrieved successfully',
            'action'        => 'get_cart',
            'cart'          => $cart,
            'count'         => count($cart),
            'overall_total' => number_format($overall_total, 2, '.', '')
        ]);
    } else {
        echo json_encode(['error' => 'Failed to retrieve cart: ' . $conn->error]);
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
