<?php
/**
 * Order Submission API
 * Handles order/purchase processing
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logger.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate CSRF token
    require_once __DIR__ . '/../includes/auth.php';
    if (!validateCSRFToken($data['csrf_token'] ?? '')) {
         throw new Exception('Invalid security token. Please refresh the page and try again.');
    }
    
    // Validate required fields
    if (empty($data['customer_name']) || empty($data['customer_email']) || 
        empty($data['customer_phone']) || empty($data['customer_address']) ||
        empty($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
        throw new Exception('All customer information and cart items are required');
    }
    
    // Sanitize customer info
    $customerName = trim($data['customer_name']);
    $customerEmail = filter_var(trim($data['customer_email']), FILTER_VALIDATE_EMAIL);
    $customerPhone = trim($data['customer_phone']);
    $customerAddress = trim($data['customer_address']);
    $paymentMethod = isset($data['payment_method']) ? trim($data['payment_method']) : 'bKash';
    
    if (!$customerEmail) {
        throw new Exception('Invalid email address');
    }
    
    // Calculate total
    $totalAmount = 0;
    foreach ($data['items'] as $item) {
        if (!isset($item['price']) || !isset($item['quantity'])) {
            throw new Exception('Invalid item data');
        }
        $totalAmount += floatval($item['price']) * intval($item['quantity']);
    }
    
    // Generate unique order ID
    $orderIdPrefix = 'ORD-' . date('Ymd') . '-';
    
    // Get database connection
    $db = getDB();
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Find next available order number for today
        $stmt = $db->prepare("
            SELECT order_id FROM orders 
            WHERE order_id LIKE ? 
            ORDER BY order_id DESC 
            LIMIT 1
        ");
        $stmt->execute([$orderIdPrefix . '%']);
        $lastOrder = $stmt->fetch();
        
        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder['order_id'], -5));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $orderId = $orderIdPrefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
        
        // Get current user ID if logged in
        $userId = null;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }

        // Insert order
        $stmt = $db->prepare("
            INSERT INTO orders (
                order_id, user_id, customer_name, customer_email, customer_phone, 
                customer_address, total_amount, payment_method, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $orderId, $userId, $customerName, $customerEmail, $customerPhone,
            $customerAddress, $totalAmount, $paymentMethod
        ]);
        
        $orderDbId = $db->lastInsertId();
        
        // Insert order items
        $stmt = $db->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_name, product_price, quantity, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $orderItems = [];
        foreach ($data['items'] as $item) {
            $productId = intval($item['id']);
            $productName = trim($item['name']);
            $productPrice = floatval($item['price']);
            $quantity = intval($item['quantity']);
            $subtotal = $productPrice * $quantity;
            
            $stmt->execute([
                $orderDbId, $productId, $productName, 
                $productPrice, $quantity, $subtotal
            ]);
            
            // INVENTORY: Check if product is in stock
            $checkStock = $db->prepare("SELECT in_stock FROM products WHERE id = ?");
            $checkStock->execute([$productId]);
            $productData = $checkStock->fetch();
            
            if (!$productData || !$productData['in_stock']) {
                throw new Exception("Product is out of stock: " . $productName);
            }
            
            // Note: We are using a boolean in_stock flag, not tracking quantity numbers yet.
            // If we wanted to track quantity, we would need to add a 'stock' column to products table.
            
            $orderItems[] = [
                'product_name' => $productName,
                'product_price' => $productPrice,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
        
        // Commit transaction
        $db->commit();
        
        // Log order to text file
        Logger::logOrder([
            'order_id' => $orderId,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'customer_address' => $customerAddress,
            'payment_method' => $paymentMethod,
            'total_amount' => $totalAmount,
            'status' => 'pending'
        ], $orderItems);
        
        // Success response
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $orderId,
            'total_amount' => $totalAmount
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
