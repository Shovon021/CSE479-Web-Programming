<?php
/**
 * Related Products API
 * Returns 4 random products from the same category
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDB();
    
    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$productId) {
        throw new Exception('Product ID required');
    }
    
    // Get category of current product
    $stmt = $db->prepare("SELECT category_id FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $currentProduct = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentProduct) {
        throw new Exception('Product not found');
    }
    
    // Get 4 related products (same category, different ID)
    $stmt = $db->prepare("
        SELECT id, name, price, original_price, image_path, rating, reviews, badge 
        FROM products 
        WHERE category_id = ? AND id != ? AND in_stock = 1
        ORDER BY RAND() 
        LIMIT 4
    ");
    $stmt->execute([$currentProduct['category_id'], $productId]);
    $related = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fallback if no related items (get random popular items)
    if (count($related) < 4) {
        $needed = 4 - count($related);
        $excludeIds = [$productId];
        foreach ($related as $p) $excludeIds[] = $p['id'];
        
        $placeholders = str_repeat('?,', count($excludeIds) - 1) . '?';
        $stmt = $db->prepare("
             SELECT id, name, price, original_price, image_path, rating, reviews, badge 
             FROM products 
             WHERE id NOT IN ($placeholders) AND in_stock = 1
             ORDER BY rating DESC 
             LIMIT ?
        ");
        $params = array_merge($excludeIds, [$needed]);
        $stmt->execute($params);
        $fallback = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $related = array_merge($related, $fallback);
    }
    
    echo json_encode([
        'success' => true,
        'products' => $related
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
