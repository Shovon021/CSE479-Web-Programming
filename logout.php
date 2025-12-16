<?php
/**
 * User Logout Handler
 * Flex & Bliss E-commerce Website
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();

// Logout user
logoutUser();

// Destroy the entire session
session_unset();
session_destroy();

// Redirect to homepage
header('Location: index.html');
exit;
