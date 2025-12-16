<?php
/**
 * Database Configuration for XAMPP Localhost
 * Flex & Bliss E-commerce Website
 */

// Database credentials for XAMPP localhost
define('DB_HOST', 'localhost');
define('DB_NAME', 'flexbliss_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL has no password

// Application settings
define('SITE_NAME', 'Flex & Bliss');
define('SITE_URL', 'http://localhost/FinalWeb(HTML)/');

// Environment: 'development' or 'production'
define('ENVIRONMENT', 'development');

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Error reporting based on environment
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Log file paths
define('LOG_DIR', __DIR__ . '/../logs/');
define('REGISTRATION_LOG', LOG_DIR . 'registrations.txt');
define('ORDER_LOG', LOG_DIR . 'orders.txt');

// Ensure log directory exists
if (!file_exists(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}

// Ensure log files exist
if (!file_exists(REGISTRATION_LOG)) {
    file_put_contents(REGISTRATION_LOG, "FLEX & BLISS - USER REGISTRATIONS LOG\n");
    file_put_contents(REGISTRATION_LOG, "=====================================\n\n", FILE_APPEND);
}

if (!file_exists(ORDER_LOG)) {
    file_put_contents(ORDER_LOG, "FLEX & BLISS - ORDERS LOG\n");
    file_put_contents(ORDER_LOG, "==========================\n\n", FILE_APPEND);
}
