<?php
/**
 * Log Viewer
 * View registration and order logs in text format
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/logger.php';
// require_once __DIR__ . '/../includes/auth.php';

// No login required - direct access
// startSecureSession();
// requireAdminLogin();

$currentAdmin = ['full_name' => 'Admin'];

$type = isset($_GET['type']) ? $_GET['type'] : 'registrations';
$logType = in_array($type, ['registrations', 'orders']) ? $type : 'registrations';

$logContent = Logger::getLogContents($logType);
$logTitle = ucfirst($logType) . ' Log';

// Handle download
if (isset($_GET['download'])) {
    $filename = 'flexbliss_' . $logType . '_' . date('Y-m-d') . '.txt';
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $logContent;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $logTitle; ?> - Flex & Bliss Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="flex justify-between align-center">
                <div>
                    <h1>Flex & Bliss Admin Dashboard</h1>
                    <p>View log files</p>
                </div>
                <a href="../index.html" class="btn btn-secondary">View Store</a>
            </div>
        </div>
    </div>

    <div class="admin-nav">
        <div class="container">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="view_logs.php" class="active">View Logs</a></li>
            </ul>
        </div>
    </div>

    <div class="container">
        <!-- Log Type Selector -->
        <div class="flex justify-between align-center mb-3">
            <div class="flex gap-1">
                <a href="view_logs.php?type=registrations" 
                   class="btn <?php echo $logType === 'registrations' ? 'btn-primary' : 'btn-secondary'; ?>">
                    Registrations Log
                </a>
                <a href="view_logs.php?type=orders" 
                   class="btn <?php echo $logType === 'orders' ? 'btn-primary' : 'btn-secondary'; ?>">
                    Orders Log
                </a>
            </div>
            <a href="view_logs.php?type=<?php echo $logType; ?>&download=1" class="btn btn-primary">
                Download Log File
            </a>
        </div>

        <!-- Log Viewer -->
        <div class="table-container">
            <div class="table-header">
                <h2><?php echo $logTitle; ?></h2>
                <p class="text-small text-muted mt-2">
                    Location: <?php echo $logType === 'registrations' ? REGISTRATION_LOG : ORDER_LOG; ?>
                </p>
            </div>
            <div style="padding: 1.5rem;">
                <div class="log-viewer"><?php echo htmlspecialchars($logContent); ?></div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="table-container">
            <div class="table-header">
                <h2>About Log Files</h2>
            </div>
            <div style="padding: 1.5rem;">
                <p class="text-small mb-2">
                    Log files are automatically created and updated whenever a user registers or places an order.
                    You can access these files directly from the server for easy viewing and backup.
                </p>
                <p class="text-small mb-2">
                    <strong>Registrations Log:</strong> Contains all user account registrations with name, email, phone, and address.
                </p>
                <p class="text-small">
                    <strong>Orders Log:</strong> Contains complete order details including customer information, items purchased, quantities, prices, and payment method.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
