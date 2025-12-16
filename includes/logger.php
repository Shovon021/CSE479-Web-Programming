<?php
/**
 * Logging Utility
 * Writes registration and purchase data to text files
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class Logger {
    
    /**
     * Log user registration
     */
    public static function logRegistration($data) {
        // 1. Log to Database
        try {
            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO activity_logs (type, message, user_id, ip_address, user_agent) 
                VALUES ('registration', ?, ?, ?, ?)
            ");
            $message = "New registration: {$data['name']} ({$data['email']})";
            $stmt->execute([
                $message, 
                $data['id'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            // Silently fail DB log to ensure file log still happens
        }

        // 2. Log to File (keep existing text log)
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = "\n========================================\n";
        $logEntry .= "REGISTRATION #" . ($data['id'] ?? 'N/A') . "\n";
        $logEntry .= "========================================\n";
        $logEntry .= "Timestamp: {$timestamp}\n";
        $logEntry .= "Name: " . ($data['name'] ?? 'N/A') . "\n";
        $logEntry .= "Email: " . ($data['email'] ?? 'N/A') . "\n";
        $logEntry .= "Phone: " . ($data['phone'] ?? 'N/A') . "\n";
        $logEntry .= "Address: " . ($data['address'] ?? 'N/A') . "\n";
        $logEntry .= "========================================\n";
        
        return file_put_contents(REGISTRATION_LOG, $logEntry, FILE_APPEND);
    }
    
    /**
     * Log order/purchase
     */
    public static function logOrder($orderData, $items) {
        // 1. Log to Database
        try {
            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO activity_logs (type, message, user_id, ip_address, user_agent) 
                VALUES ('order', ?, ?, ?, ?)
            ");
            
            $itemCount = 0;
            foreach ($items as $item) $itemCount += ($item['quantity'] ?? 0);
            
            $message = "New Order #{$orderData['order_id']} by {$orderData['customer_name']}. Total: {$orderData['total_amount']}. Items: {$itemCount}";
            
            // Get current user ID if logged in
            $userId = null;
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            }
            
            $stmt->execute([
                $message, 
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            // Silently fail DB log
        }

        // 2. Log to File
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = "\n========================================\n";
        $logEntry .= "ORDER #" . ($orderData['order_id'] ?? 'N/A') . "\n";
        $logEntry .= "========================================\n";
        $logEntry .= "Timestamp: {$timestamp}\n";
        $logEntry .= "Customer Name: " . ($orderData['customer_name'] ?? 'N/A') . "\n";
        $logEntry .= "Customer Email: " . ($orderData['customer_email'] ?? 'N/A') . "\n";
        $logEntry .= "Customer Phone: " . ($orderData['customer_phone'] ?? 'N/A') . "\n";
        $logEntry .= "Shipping Address: " . ($orderData['customer_address'] ?? 'N/A') . "\n";
        $logEntry .= "Payment Method: " . ($orderData['payment_method'] ?? 'bKash') . "\n";
        $logEntry .= "---\n";
        $logEntry .= "ORDER ITEMS:\n";
        
        foreach ($items as $item) {
            $productName = $item['product_name'] ?? 'Unknown Product';
            $quantity = $item['quantity'] ?? 0;
            $price = $item['product_price'] ?? 0;
            $subtotal = $item['subtotal'] ?? 0;
            
            $logEntry .= "- {$productName} (Qty: {$quantity}) - ৳" . number_format($price, 2) . " each = ৳" . number_format($subtotal, 2) . "\n";
        }
        
        $logEntry .= "---\n";
        $logEntry .= "TOTAL AMOUNT: ৳" . number_format($orderData['total_amount'] ?? 0, 2) . "\n";
        $logEntry .= "Status: " . ucfirst($orderData['status'] ?? 'pending') . "\n";
        $logEntry .= "========================================\n";
        
        return file_put_contents(ORDER_LOG, $logEntry, FILE_APPEND);
    }
    
    /**
     * Get log file contents
     */
    public static function getLogContents($type = 'registrations') {
        $logFile = ($type === 'registrations') ? REGISTRATION_LOG : ORDER_LOG;
        
        if (file_exists($logFile)) {
            return file_get_contents($logFile);
        }
        
        return "No log entries found.";
    }
}
