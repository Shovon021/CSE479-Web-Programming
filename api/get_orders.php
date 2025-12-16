<?php
/**
 * Get Orders API
 * Retrieves orders for admin view
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $db = getDB();
    
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $status = isset($_GET['status']) ? trim($_GET['status']) : null;
    
    // Build query
    $query = "SELECT * FROM orders";
    $params = [];
    
    if ($status && in_array($status, ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])) {
        $query .= " WHERE status = ?";
        $params[] = $status;
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    // Get order items for each order
    foreach ($orders as &$order) {
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM orders";
    if ($status) {
        $countQuery .= " WHERE status = ?";
        $stmt = $db->prepare($countQuery);
        $stmt->execute([$status]);
    } else {
        $stmt = $db->query($countQuery);
    }
    $total = $stmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
