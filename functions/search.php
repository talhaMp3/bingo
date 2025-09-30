<?php
include_once '../include/connection.php';

header('Content-Type: application/json');


if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

// Sanitize query
$searchQuery = $conn->real_escape_string($query);
$searchTerm = "%$searchQuery%";

$results = [];

// Search in products
$productSql = "SELECT 
    p.id, 
    p.name, 
    p.slug, 
    p.price, 
    p.discount_price,
    p.short_description,
    p.image,
    c.name as category_name,
    b.name as brand_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN brands b ON p.brand_id = b.id
WHERE p.status = 'active' 
AND (p.name LIKE ? OR p.short_description LIKE ? OR p.tags LIKE ?)
LIMIT 10";

$stmt = $conn->prepare($productSql);
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$productResult = $stmt->get_result();

while ($row = $productResult->fetch_assoc()) {
    $results[] = [
        'type' => 'product',
        'id' => $row['id'],
        'name' => $row['name'],
        'slug' => $row['slug'],
        'price' => $row['price'],
        'discount_price' => $row['discount_price'],
        'description' => $row['short_description'],
        'category' => $row['category_name'],
        'brand' => $row['brand_name'],
        'image' => !empty($row['image']) ? json_decode($row['image'], true)[0] : 'default-product.png',
        'url' => '/shop-details.php?slug=' . $row['slug']
    ];
}
$stmt->close();

// Search in categories
$categorySql = "SELECT 
    id, 
    name, 
    slug, 
    image,
    description
FROM categories 
WHERE status = 'active' 
AND (name LIKE ? OR description LIKE ?)
LIMIT 5";

$stmt = $conn->prepare($categorySql);
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$categoryResult = $stmt->get_result();

while ($row = $categoryResult->fetch_assoc()) {
    $results[] = [
        'type' => 'category',
        'id' => $row['id'],
        'name' => $row['name'],
        'slug' => $row['slug'],
        'description' => $row['description'],
        'image' => $row['image'],
        'url' => '/shop.php?category=' . $row['slug']
    ];
}
$stmt->close();

echo json_encode(['results' => $results]);
