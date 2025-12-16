<?php
/**
 * Database Connection Test Endpoint
 * Tests the connection between frontend -> backend -> database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    // Test database connection
    $db = getDB();
    
    // Test query - get product count
    $stmt = $db->query("SELECT COUNT(*) as count FROM products");
    $productCount = $stmt->fetch()['count'];
    
    // Test query - get user count
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    // Test query - get order count
    $stmt = $db->query("SELECT COUNT(*) as count FROM orders");
    $orderCount = $stmt->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful!',
        'database' => DB_NAME,
        'stats' => [
            'products' => (int)$productCount,
            'users' => (int)$userCount,
            'orders' => (int)$orderCount
        ],
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => ENVIRONMENT
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
}
