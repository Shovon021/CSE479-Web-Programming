<?php
/**
 * API: Get all products from database
 * Returns JSON for frontend to display products
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDB();
    
    // Get all products with category info
    $stmt = $db->query("
        SELECT 
            p.id,
            p.name,
            c.name as category,
            c.display_name as categoryDisplay,
            p.price,
            p.original_price as originalPrice,
            p.image_path as image,
            p.description,
            p.rating,
            p.reviews,
            p.badge,
            p.in_stock as inStock
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id ASC
    ");
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert in_stock to boolean
    foreach ($products as &$product) {
        $product['inStock'] = (bool)$product['inStock'];
        $product['price'] = (float)$product['price'];
        $product['originalPrice'] = $product['originalPrice'] ? (float)$product['originalPrice'] : null;
        $product['rating'] = (float)$product['rating'];
        $product['reviews'] = (int)$product['reviews'];
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($products),
        'products' => $products
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
