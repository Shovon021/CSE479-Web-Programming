<?php
/**
 * Get Categories API
 * Returns all product categories
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDB();
    
    $stmt = $db->query("SELECT * FROM categories ORDER BY display_name");
    $categories = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch categories',
        'message' => $e->getMessage()
    ]);
}
