<?php
/**
 * Simple Admin Dashboard for Flex & Bliss
 * Direct access with simple password protection
 */

// Simple login settings - change these!
$ADMIN_USER = 'shovon';
$ADMIN_PASS = 'shovon021';

session_start();

// Handle login
if (isset($_POST['login'])) {
    if ($_POST['username'] === $ADMIN_USER && $_POST['password'] === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $login_error = 'Invalid username or password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle reset actions
if (isset($_GET['reset']) && isset($_SESSION['admin_logged_in'])) {
    require_once __DIR__ . '/../includes/db.php';
    try {
        $db = getDB();
        $action = $_GET['reset'];
        
        if ($action === 'orders') {
            $db->exec("DELETE FROM order_items");
            $db->exec("DELETE FROM orders");
            $reset_message = "All orders have been deleted!";
        } elseif ($action === 'users') {
            $db->exec("DELETE FROM users");
            $reset_message = "All users have been deleted!";
        } elseif ($action === 'logs') {
            // Clear log files
            $logPath = __DIR__ . '/../logs/';
            if (file_exists($logPath . 'registrations.log')) {
                file_put_contents($logPath . 'registrations.log', '');
            }
            if (file_exists($logPath . 'orders.log')) {
                file_put_contents($logPath . 'orders.log', '');
            }
            $reset_message = "All logs have been cleared!";
        }
    } catch (Exception $e) {
        $reset_error = $e->getMessage();
    }
}

// Handle order status update
if (isset($_POST['update_status']) && isset($_SESSION['admin_logged_in'])) {
    require_once __DIR__ . '/../includes/db.php';
    try {
        $db = getDB();
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];
        
        // Validate status
        $valid_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        if (in_array($new_status, $valid_statuses)) {
            $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            $status_message = "Order status updated to " . ucfirst($new_status) . "!";
        }
    } catch (Exception $e) {
        $status_error = $e->getMessage();
    }
}

// Check if logged in
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Get stats if logged in
$stats = [];
$recent_orders = [];
$recent_users = [];

if ($is_logged_in) {
    require_once __DIR__ . '/../includes/db.php';
    try {
        $db = getDB();
        
        // Get statistics
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'] ?? 0;
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders");
        $stats['total_orders'] = $stmt->fetch()['count'] ?? 0;
        
        $stmt = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
        $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $stmt->fetch()['count'] ?? 0;
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM products");
        $stats['total_products'] = $stmt->fetch()['count'] ?? 0;
        
        // Recent orders
        $stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
        $recent_orders = $stmt->fetchAll() ?? [];
        
        // Recent users
        $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
        $recent_users = $stmt->fetchAll() ?? [];
        
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Flex & Bliss</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; min-height: 100vh; }
        
        /* Header */
        .header { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 1.5rem 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }
        .header h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
        .header p { opacity: 0.9; font-size: 0.875rem; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; }
        
        /* Navigation */
        .nav { background: white; border-bottom: 1px solid #e5e7eb; padding: 0.5rem 0; }
        .nav ul { list-style: none; display: flex; gap: 0.5rem; }
        .nav a { display: block; padding: 0.75rem 1rem; text-decoration: none; color: #374151; border-radius: 6px; font-weight: 500; }
        .nav a:hover, .nav a.active { background: #6366f1; color: white; }
        
        /* Buttons */
        .btn { display: inline-block; padding: 0.625rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; font-size: 0.875rem; }
        .btn-primary { background: #6366f1; color: white; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-secondary:hover { background: #4b5563; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin: 2rem 0; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-card h3 { font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #1f2937; }
        .stat-card.primary { border-left: 4px solid #6366f1; }
        .stat-card.success { border-left: 4px solid #10b981; }
        .stat-card.warning { border-left: 4px solid #f59e0b; }
        .stat-card.danger { border-left: 4px solid #ef4444; }
        
        /* Tables */
        .table-container { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; overflow: hidden; }
        .table-header { padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .table-header h2 { font-size: 1.25rem; color: #1f2937; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; color: #374151; }
        tr:hover { background: #f9fafb; }
        
        /* Status badges */
        .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-confirmed { background: #d1fae5; color: #065f46; }
        .badge-shipped { background: #dbeafe; color: #1e40af; }
        .badge-delivered { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        
        /* Login Form */
        .login-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .login-box { background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.25); width: 100%; max-width: 400px; }
        .login-box h1 { text-align: center; margin-bottom: 0.5rem; color: #1f2937; }
        .login-box p { text-align: center; color: #6b7280; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; }
        .form-group input { width: 100%; padding: 0.875rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.2); }
        .login-btn { width: 100%; padding: 1rem; background: #6366f1; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; }
        .login-btn:hover { background: #4f46e5; }
        .error-msg { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; }
        .success-msg { background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; }
        
        /* Reset Section */
        .reset-section { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 1.5rem; margin: 2rem 0; }
        .reset-section h3 { color: #991b1b; margin-bottom: 1rem; }
        .reset-buttons { display: flex; gap: 1rem; flex-wrap: wrap; }
        
        /* Main Content */
        main { padding: 2rem 0; }
        
        .empty-state { text-align: center; padding: 3rem; color: #6b7280; }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .table-header { flex-direction: column; align-items: flex-start; }
            th, td { padding: 0.75rem 0.5rem; font-size: 0.875rem; }
        }
    </style>
</head>
<body>

<?php if (!$is_logged_in): ?>
<!-- LOGIN PAGE -->
<div class="login-container">
    <div class="login-box">
        <h1>🛍️ Flex & Bliss</h1>
        <p>Admin Dashboard Login</p>
        
        <?php if (isset($login_error)): ?>
            <div class="error-msg"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password">
            </div>
            <button type="submit" name="login" class="login-btn">Login to Dashboard</button>
        </form>
        
        <p style="margin-top: 2rem; font-size: 0.875rem; text-align: center; color: #9ca3af;">
            <a href="../index.html" style="color: #6366f1;">← Back to Store</a>
        </p>
    </div>
</div>

<?php else: ?>
<!-- DASHBOARD -->
<div class="header">
    <div class="container">
        <div class="header-flex">
            <div>
                <h1>Flex & Bliss Admin Dashboard</h1>
                <p>Welcome, Admin! Manage your store</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="../index.html" class="btn btn-secondary">View Store</a>
                <a href="?logout=1" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="nav">
    <div class="container">
        <ul>
            <li><a href="index.php" class="active">Dashboard</a></li>
            <li><a href="products.php">Products</a></li>
        </ul>
    </div>
</div>

<main>
    <div class="container">
        <?php if (isset($reset_message)): ?>
            <div class="success-msg"><?php echo $reset_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($reset_error)): ?>
            <div class="error-msg"><?php echo $reset_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($status_message)): ?>
            <div class="success-msg"><?php echo $status_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($status_error)): ?>
            <div class="error-msg"><?php echo $status_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($db_error)): ?>
            <div class="error-msg">
                <strong>Database Error:</strong> <?php echo htmlspecialchars($db_error); ?>
                <br><small>Make sure MySQL is running and database is imported.</small>
            </div>
        <?php else: ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <h3>Total Products</h3>
                <div class="stat-value"><?php echo number_format($stats['total_products'] ?? 0); ?></div>
            </div>
            <div class="stat-card success">
                <h3>Total Users</h3>
                <div class="stat-value"><?php echo number_format($stats['total_users'] ?? 0); ?></div>
            </div>
            <div class="stat-card warning">
                <h3>Total Orders</h3>
                <div class="stat-value"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
            </div>
            <div class="stat-card danger">
                <h3>Total Revenue</h3>
                <div class="stat-value">৳<?php echo number_format($stats['total_revenue'] ?? 0); ?></div>
            </div>
        </div>
        
        <!-- Reset Section -->
        <div class="reset-section">
            <h3>⚠️ Danger Zone - Reset Data</h3>
            <p style="margin-bottom: 1rem; color: #7f1d1d;">These actions cannot be undone. Use with caution!</p>
            <div class="reset-buttons">
                <a href="?reset=orders" class="btn btn-danger" onclick="return confirm('Delete all orders? This cannot be undone!')">🗑️ Reset All Orders</a>
                <a href="?reset=users" class="btn btn-danger" onclick="return confirm('Delete all users? This cannot be undone!')">👥 Reset All Users</a>
                <a href="?reset=logs" class="btn btn-secondary" onclick="return confirm('Clear all log files?')">📋 Clear Logs</a>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="table-container">
            <div class="table-header">
                <h2>Recent Orders</h2>
                <span><?php echo count($recent_orders); ?> orders shown (click row for details)</span>
            </div>
            <?php if (count($recent_orders) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr class="order-row" onclick="toggleOrderDetails('order-<?php echo $order['id']; ?>')" style="cursor: pointer;">
                        <td><strong><?php echo htmlspecialchars($order['order_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <tr class="order-details" id="order-<?php echo $order['id']; ?>" style="display: none; background: #f9fafb;">
                        <td colspan="5" style="padding: 1.5rem;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                                <div>
                                    <h4 style="color: #6366f1; margin-bottom: 0.75rem;">📋 Order Details</h4>
                                    <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                                    <p><strong>Status:</strong> <span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
                                    <p><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'bKash'); ?></p>
                                    <p><strong>Total:</strong> ৳<?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                                <div>
                                    <h4 style="color: #10b981; margin-bottom: 0.75rem;">👤 Customer Information</h4>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></p>
                                </div>
                                <div>
                                    <h4 style="color: #f59e0b; margin-bottom: 0.75rem;">📍 Shipping Address</h4>
                                    <p><?php echo nl2br(htmlspecialchars($order['customer_address'] ?? 'No address provided')); ?></p>
                                </div>
                            </div>
                            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                <h4 style="color: #374151; margin-bottom: 0.75rem;">🔄 Update Order Status</h4>
                                <form method="POST" style="display: flex; gap: 0.5rem; flex-wrap: wrap;" onclick="event.stopPropagation();">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <?php if ($order['status'] === 'pending'): ?>
                                    <button type="submit" name="new_status" value="confirmed" class="btn btn-success" style="padding: 0.5rem 1rem;">✓ Confirm Order</button>
                                    <?php endif; ?>
                                    <?php if ($order['status'] === 'confirmed'): ?>
                                    <button type="submit" name="new_status" value="shipped" class="btn btn-primary" style="padding: 0.5rem 1rem;">📦 Mark Shipped</button>
                                    <?php endif; ?>
                                    <?php if ($order['status'] === 'shipped'): ?>
                                    <button type="submit" name="new_status" value="delivered" class="btn btn-success" style="padding: 0.5rem 1rem;">✅ Mark Delivered</button>
                                    <?php endif; ?>
                                    <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'delivered'): ?>
                                    <button type="submit" name="new_status" value="cancelled" class="btn btn-danger" style="padding: 0.5rem 1rem;" onclick="return confirm('Cancel this order?');">✕ Cancel</button>
                                    <?php endif; ?>
                                    <?php if ($order['status'] === 'cancelled' || $order['status'] === 'delivered'): ?>
                                    <button type="submit" name="new_status" value="pending" class="btn btn-secondary" style="padding: 0.5rem 1rem;">↩ Reset to Pending</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <script>
            function toggleOrderDetails(id) {
                const row = document.getElementById(id);
                if (row) {
                    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
                }
            }
            </script>
            <?php else: ?>
            <div class="empty-state">No orders yet</div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Users -->
        <div class="table-container">
            <div class="table-header">
                <h2>Recent Registrations</h2>
                <span><?php echo count($recent_users); ?> users shown</span>
            </div>
            <?php if (count($recent_users) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $user): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No registered users yet</div>
            <?php endif; ?>
        </div>
        
        <?php endif; ?>
    </div>
</main>

<?php endif; ?>

</body>
</html>
