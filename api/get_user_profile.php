<?php
/**
 * Get User Profile API
 * Returns current user data for frontend usage
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

try {
    startSecureSession();
    
    if (isUserLoggedIn()) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, phone, address FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'loggedIn' => true,
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'loggedIn' => false,
            'user' => null
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['loggedIn' => false, 'error' => $e->getMessage()]);
}
