           <?php
            require_once './include/connection.php';
            // SOLUTION 1: Use GROUP_CONCAT instead of JSON_ARRAYAGG (Compatible with older MySQL versions)
            $query = "
  SELECT 
    p.id AS product_id,
    p.name AS product_name,
    p.slug,
    p.price AS base_price,
    p.discount_price AS base_discount_price,
    p.stock_quantity AS base_stock,
    p.image AS product_images,
    p.short_description,
    p.long_description,
    p.status AS product_status,
    c.id AS category_id,
    c.name AS category_name,
    c.slug AS category_slug,
    CONCAT('[', GROUP_CONCAT(
        JSON_OBJECT(
            'id', v.id,
            'name', v.variant_name,
            'price', v.price,
            'discount_price', v.discount_price,
            'stock', v.stock_quantity,
            'images', v.image,
            'status', v.status
        )
    ), ']') AS variants_json
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN product_variants v 
    ON p.id = v.product_id AND v.status = 'active'
WHERE p.status = 'active'
GROUP BY p.id, p.name, p.slug, p.price, p.discount_price, p.stock_quantity, 
        p.image, p.short_description, p.long_description, p.status, 
        c.id, c.name, c.slug
ORDER BY p.created_at DESC;

      ";

            $result = mysqli_query($conn, $query);
            while ($product = mysqli_fetch_assoc($result)) {
                // Decode product images
                $images = json_decode($product['product_images'], true);
                $mainImage = !empty($images) ? $images[0] : 'default.png';

                // Initialize variants array
                $variants = [];

                if (!empty($product['variants_json'])) {
                    $decodedVariants = json_decode($product['variants_json'], true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $variants = $decodedVariants;
                    } else {
                        error_log("JSON decode failed for product {$product['product_id']}: " . json_last_error_msg());
                    }
                }

                // foreach ($variants as $variant) {
                //     echo "- " . $variant['name'] . " (Price: " . $variant['price'] . ", Stock: " . $variant['stock'] . ")\n";
                // }


                // Debug
                // echo "<pre>";
                // echo "Product: " . $product['product_name'] . "\n";
                // echo "Variants: ";
                // // print_r($variants);
                // foreach ($variants as $variant) {
                //     echo "- " . $variant['name'] . " (Price: " . $variant['price'] . ", Stock: " . $variant['stock'] . ")\n";
                // }
                // echo "</pre>";
            }

            ?>