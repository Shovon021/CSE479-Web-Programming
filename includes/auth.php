<?php
/**
 * Authentication & Security Helper Functions
 * Flex & Bliss E-commerce Website
 */

// Start secure session
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Secure session settings
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }
        
        session_start();
        
        // Regenerate session ID periodically to prevent fixation
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) {
            // Regenerate session ID every 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Session timeout (8 hours max, 30 min idle)
        $maxLifetime = 28800; // 8 hours
        $idleTimeout = 1800;  // 30 minutes
        
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > $idleTimeout)) {
            session_unset();
            session_destroy();
            session_start();
        }
        
        if (isset($_SESSION['session_start']) && 
            (time() - $_SESSION['session_start'] > $maxLifetime)) {
            session_unset();
            session_destroy();
            session_start();
        }
        
        $_SESSION['last_activity'] = time();
        if (!isset($_SESSION['session_start'])) {
            $_SESSION['session_start'] = time();
        }
    }
}

// Generate CSRF Token
function generateCSRFToken() {
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF Token
function validateCSRFToken($token) {
    startSecureSession();
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Get CSRF input field HTML
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

// Hash password securely
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Check if user is logged in
function isUserLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    startSecureSession();
    return $_SESSION['user_id'] ?? null;
}

// Get current user data
function getCurrentUser() {
    startSecureSession();
    return $_SESSION['user'] ?? null;
}

// Login user
function loginUser($userId, $userData) {
    startSecureSession();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user'] = $userData;
    $_SESSION['logged_in_at'] = time();
}

// Logout user
function logoutUser() {
    startSecureSession();
    unset($_SESSION['user_id']);
    unset($_SESSION['user']);
    unset($_SESSION['logged_in_at']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    startSecureSession();
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Get current admin ID
function getCurrentAdminId() {
    startSecureSession();
    return $_SESSION['admin_id'] ?? null;
}

// Login admin
function loginAdmin($adminId, $adminData) {
    startSecureSession();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $adminId;
    $_SESSION['admin'] = $adminData;
    $_SESSION['admin_logged_in_at'] = time();
}

// Logout admin
function logoutAdmin() {
    startSecureSession();
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin']);
    unset($_SESSION['admin_logged_in_at']);
}

// Require user login - redirect if not logged in
function requireUserLogin($redirectUrl = '/login.php') {
    if (!isUserLoggedIn()) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Require admin login - redirect if not admin
function requireAdminLogin($redirectUrl = 'login.php') {
    if (!isAdminLoggedIn()) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Validate password strength
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    return $errors;
}

// Sanitize string input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
