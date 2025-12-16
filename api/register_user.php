<?php
/**
 * User Registration API
 * Handles new user account creation
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
    
    // Validate required fields
    if (empty($data['name']) || empty($data['email'])) {
        throw new Exception('Name and email are required');
    }
    
    // Sanitize inputs
    $name = trim($data['name']);
    $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $address = isset($data['address']) ? trim($data['address']) : '';
    
    if (!$email) {
        throw new Exception('Invalid email address');
    }
    
    // Get database connection
    $db = getDB();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        throw new Exception('Email address already registered');
    }
    
    // Insert new user
    $stmt = $db->prepare("
        INSERT INTO users (name, email, phone, address) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$name, $email, $phone, $address]);
    $userId = $db->lastInsertId();
    
    // Log registration to text file
    Logger::logRegistration([
        'id' => $userId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address
    ]);
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully!',
        'user_id' => $userId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
