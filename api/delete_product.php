<?php
/**
 * Delete Product API
 * Handles product deletion including image file
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDB();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate product ID
    if (empty($input['id'])) {
        throw new Exception('Product ID is required');
    }
    
    $product_id = (int)$input['id'];
    
    // Get product data to find image path
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    // Delete product from database
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    
    // Delete image file if it's in uploads directory
    if (strpos($product['image_path'], 'uploads/products/') === 0) {
        $image_file = __DIR__ . '/../' . $product['image_path'];
        if (file_exists($image_file)) {
            unlink($image_file);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
