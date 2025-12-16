<?php
/**
 * Cancel Order API
 * Allows users to cancel pending orders and restores stock
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    startSecureSession();
    
    if (!isUserLoggedIn()) {
        throw new Exception('Please login to cancel orders');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = $input['order_id'] ?? null;
    
    if (!$orderId) {
        throw new Exception('Order ID required');
    }
    
    $db = getDB();
    $userId = $_SESSION['user_id'];
    
    // Get order and verify ownership
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    if ($order['status'] !== 'pending') {
        throw new Exception('Only pending orders can be cancelled');
    }
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Get order items to restore stock
        $stmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Restore stock for each item
        $stockStmt = $db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        foreach ($items as $item) {
            $stockStmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Update order status
        $stmt = $db->prepare("UPDATE orders SET status = 'cancelled', status_updated_at = NOW() WHERE id = ?");
        $stmt->execute([$orderId]);
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order cancelled successfully. Stock has been restored.'
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
