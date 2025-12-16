<?php
/**
 * Admin Logout Handler
 * Flex & Bliss E-commerce Website
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

startSecureSession();

// Logout admin
logoutAdmin();

// Destroy the entire session
session_unset();
session_destroy();

// Redirect to admin login
header('Location: login.php');
exit;
