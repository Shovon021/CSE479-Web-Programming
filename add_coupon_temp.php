<?php
require_once __DIR__ . '/includes/db.php';

try {
    $db = getDB();
    
    $stmt = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order, max_uses) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE active = 1");
    
    // Code: ARMANSIR479
    // Type: Percentage
    // Value: 15%
    // Min Order: 0
    // Max Uses: 1000
    
    $result = $stmt->execute(['ARMANSIR479', 'percentage', 15.00, 0, 1000]);
    
    if ($result) {
        echo "Coupon ARMANSIR479 added successfully!";
    } else {
        echo "Failed to add coupon.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
