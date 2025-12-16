<?php
/**
 * Coupon Validation API
 * Validates coupon codes and returns discount info
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $code = trim(strtoupper($input['code'] ?? ''));
    $cartTotal = floatval($input['cart_total'] ?? 0);
    
    if (empty($code)) {
        throw new Exception('Coupon code required');
    }
    
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT * FROM coupons 
        WHERE code = ? AND active = 1 
        AND (expires_at IS NULL OR expires_at > NOW())
        AND (max_uses IS NULL OR used_count < max_uses)
    ");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$coupon) {
        throw new Exception('Invalid or expired coupon code');
    }
    
    if ($cartTotal < $coupon['min_order']) {
        throw new Exception('Minimum order amount is ৳' . number_format($coupon['min_order']));
    }
    
    // Calculate discount
    if ($coupon['discount_type'] === 'percentage') {
        $discount = ($cartTotal * $coupon['discount_value']) / 100;
        $discountText = $coupon['discount_value'] . '% off';
    } else {
        $discount = $coupon['discount_value'];
        $discountText = '৳' . number_format($coupon['discount_value']) . ' off';
    }
    
    $newTotal = max(0, $cartTotal - $discount);
    
    echo json_encode([
        'success' => true,
        'coupon' => [
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => $coupon['discount_value'],
            'discount_amount' => round($discount, 2),
            'discount_text' => $discountText,
            'new_total' => round($newTotal, 2)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
