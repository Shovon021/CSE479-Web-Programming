<?php
/**
 * Real-time Search API
 * Returns search results with images and all product info
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDB();
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (strlen($query) < 2) {
        echo json_encode(['success' => true, 'products' => []]);
        exit;
    }
    
    $searchTerm = "%$query%";
    
    $stmt = $db->prepare("
        SELECT 
            p.id, 
            p.name, 
            p.price, 
            p.original_price,
            p.image_path,
            p.description,
            p.rating,
            p.reviews,
            p.badge,
            p.in_stock,
            c.name as category
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE (p.name LIKE ? OR p.description LIKE ? OR c.display_name LIKE ?)
        ORDER BY p.in_stock DESC, p.name ASC
        LIMIT 8
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format products for frontend
    foreach ($products as &$product) {
        $product['price'] = (float)$product['price'];
        $product['original_price'] = $product['original_price'] ? (float)$product['original_price'] : null;
        $product['in_stock'] = (bool)$product['in_stock'];
        $product['rating'] = (float)$product['rating'];
        $product['reviews'] = (int)$product['reviews'];
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($products),
        'products' => $products
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
