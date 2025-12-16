<?php
/**
 * Wishlist API
 * Handle adding/removing/listing wishlist items
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    startSecureSession();
    
    if (!isUserLoggedIn()) {
        throw new Exception('Please login to manage your wishlist');
    }
    
    $db = getDB();
    $userId = $_SESSION['user_id'];
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET: List wishlist items
    if ($method === 'GET') {
        $stmt = $db->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['success' => true, 'wishlist' => $items]);
        exit;
    }
    
    // POST: Add/Remove item
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = $input['product_id'] ?? null;
    $action = $input['action'] ?? 'toggle'; // 'add', 'remove', 'toggle'
    
    if (!$productId) {
        throw new Exception('Product ID required');
    }
    
    if ($action === 'toggle') {
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        
        if ($stmt->fetch()) {
            // Remove
            $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $status = 'removed';
        } else {
            // Add
            $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$userId, $productId]);
            $status = 'added';
        }
    }
    
    echo json_encode(['success' => true, 'status' => $status]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
