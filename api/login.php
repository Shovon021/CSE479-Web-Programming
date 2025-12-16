<?php
/**
 * User Login API
 * Handles user authentication via JSON requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }

    if (!isValidEmail($email)) {
        throw new Exception('Invalid email address');
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && verifyPassword($password, $user['password_hash'])) {
        // Start session and log user in
        loginUser($user['id'], [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => $_SESSION['user']
        ]);
    } else {
        throw new Exception('Invalid email or password');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
