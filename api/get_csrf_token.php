<?php
/**
 * CSRF Token Endpoint
 * Returns a CSRF token for AJAX requests from static pages
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Only allow from same origin
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_domains = [
    'http://localhost',
];

// In production, checking origin is recommended
// if (ENVIRONMENT === 'production' && !in_array($origin, $allowed_domains)) {
//     http_response_code(403);
//     exit();
// }

try {
    startSecureSession();
    $token = generateCSRFToken();
    
    echo json_encode([
        'success' => true,
        'csrf_token' => $token
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to generate token'
    ]);
}
