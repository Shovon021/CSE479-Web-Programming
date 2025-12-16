<?php
/**
 * User Dashboard
 * For registered customers to view profile, orders, and update account
 */

session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = null;
$orders = [];
$error = null;
$success = null;

try {
    $db = getDB();
    
    // Get user info
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $userId]);
        
        $success = "Profile updated successfully!";
        
        // Refresh user data
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        $_SESSION['user_name'] = $user['name'];
    }
    
    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (!password_verify($currentPassword, $user['password_hash'])) {
            $error = "Current password is incorrect";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match";
        } elseif (strlen($newPassword) < 6) {
            $error = "New password must be at least 6 characters";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            $success = "Password changed successfully!";
        }
    }
    
    // Get user's orders
    $stmt = $db->prepare("
        SELECT o.*, 
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Flex & Bliss</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 1.5rem; }
        .header-nav { display: flex; gap: 1rem; align-items: center; }
        .header-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .header-nav a:hover { background: rgba(255,255,255,0.2); }
        .header-nav .logout { background: rgba(239, 68, 68, 0.8); }
        .header-nav .logout:hover { background: rgba(239, 68, 68, 1); }
        
        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        
        /* Welcome Section */
        .welcome {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .welcome h2 {
            font-size: 1.75rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .welcome p { color: #6b7280; }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .tab {
            padding: 0.875rem 1.5rem;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.2s;
            font-size: 0.9375rem;
        }
        .tab:hover { background: #f3f4f6; color: #1f2937; }
        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        /* Tab Content */
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .card-header h3 {
            font-size: 1.25rem;
            color: #1f2937;
        }
        
        /* Profile Info */
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .profile-item {
            padding: 1rem;
            background: #f9fafb;
            border-radius: 12px;
        }
        .profile-item label {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .profile-item span {
            font-size: 1rem;
            color: #1f2937;
            font-weight: 500;
        }
        
        /* Form Styles */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 640px) {
            .form-row { grid-template-columns: 1fr; }
        }
        
        /* Buttons */
        .btn {
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9375rem;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        .orders-table th, .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .orders-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }
        .orders-table tr:hover { background: #f9fafb; }
        
        /* Status Badges */
        .status {
            display: inline-block;
            padding: 0.375rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-shipped { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        /* Messages */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 1rem; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .tabs { flex-wrap: wrap; }
            .tab { flex: 1; text-align: center; min-width: 100px; }
            .orders-table { font-size: 0.875rem; }
            .orders-table th, .orders-table td { padding: 0.75rem 0.5rem; }
            .tracking-steps { flex-direction: column; }
            .tracking-step { flex-direction: row; gap: 1rem; }
            .tracking-line { width: 4px; height: 40px; }
        }
        
        /* Order Tracking Animation */
        .order-row { cursor: pointer; transition: all 0.2s; }
        .order-row:hover { background: #f0f4ff !important; }
        .order-tracking {
            display: none;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem;
            border-radius: 12px;
            margin: 1rem 0;
            animation: slideDown 0.4s ease;
        }
        .order-tracking.active { display: block; }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .tracking-steps {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            margin: 2rem 0;
        }
        
        .tracking-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
            z-index: 2;
        }
        
        .tracking-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            transition: all 0.5s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .tracking-step.completed .tracking-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            animation: bounceIn 0.6s ease;
        }
        
        .tracking-step.active .tracking-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            animation: pulse 2s infinite;
        }
        
        .tracking-step.cancelled .tracking-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0.5); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(102, 126, 234, 0); }
        }
        
        .tracking-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 0.875rem;
            text-align: center;
        }
        
        .tracking-step.completed .tracking-label,
        .tracking-step.active .tracking-label {
            color: #1f2937;
        }
        
        .tracking-date {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }
        
        .tracking-line {
            position: absolute;
            top: 30px;
            left: 15%;
            right: 15%;
            height: 4px;
            background: #e5e7eb;
            z-index: 1;
            border-radius: 2px;
        }
        
        .tracking-line-progress {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 2px;
            transition: width 1s ease;
            width: 0%;
        }
        
        .tracking-message {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            margin-top: 1rem;
            font-weight: 500;
            color: #374151;
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-content">
        <h1>🛍️ Flex & Bliss</h1>
        <nav class="header-nav">
            <a href="index.html">🏪 Shop</a>
            <a href="?logout=1" class="logout">Logout</a>
        </nav>
    </div>
</header>

<div class="container">
    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Welcome Section -->
    <div class="welcome">
        <h2>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h2>
        <p>Manage your profile and view your order history</p>
    </div>
    
    <!-- Tabs -->
    <div class="tabs">
        <button class="tab active" onclick="showTab('profile')">👤 Profile</button>
        <button class="tab" onclick="showTab('orders')">📦 My Orders</button>
        <button class="tab" onclick="showTab('settings')">⚙️ Settings</button>
    </div>
    
    <!-- Profile Tab -->
    <div id="profile" class="tab-content active">
        <div class="card">
            <div class="card-header">
                <h3>👤 Profile Information</h3>
            </div>
            <div class="profile-grid">
                <div class="profile-item">
                    <label>Full Name</label>
                    <span><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <div class="profile-item">
                    <label>Email Address</label>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="profile-item">
                    <label>Phone Number</label>
                    <span><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></span>
                </div>
                <div class="profile-item">
                    <label>Shipping Address</label>
                    <span><?php echo htmlspecialchars($user['address'] ?? 'Not set'); ?></span>
                </div>
                <div class="profile-item">
                    <label>Member Since</label>
                    <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                </div>
                <div class="profile-item">
                    <label>Total Orders</label>
                    <span><?php echo count($orders); ?> orders</span>
                </div>
            </div>
        </div>
        
        <!-- Update Profile Form -->
        <div class="card">
            <div class="card-header">
                <h3>✏️ Update Profile</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="01XXXXXXXXX">
                    </div>
                </div>
                <div class="form-group">
                    <label>Shipping Address</label>
                    <textarea name="address" rows="3" placeholder="Enter your full shipping address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            </form>
        </div>
    </div>
    
    <!-- Orders Tab -->
    <div id="orders" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h3>📦 Order History</h3>
                <span style="color: #6b7280;"><?php echo count($orders); ?> orders</span>
            </div>
            
            <?php if (count($orders) > 0): ?>
            <div style="overflow-x: auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $index => $order): 
                            $status = $order['status'];
                            $statusIndex = array_search($status, ['pending', 'confirmed', 'shipped', 'delivered']);
                            if ($status === 'cancelled') $statusIndex = -1;
                        ?>
                        <tr class="order-row" onclick="toggleTracking(<?php echo $index; ?>)">
                            <td><strong>#<?php echo htmlspecialchars($order['order_id']); ?></strong></td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo $order['item_count']; ?> items</td>
                            <td><strong>৳<?php echo number_format($order['total_amount'], 0); ?></strong></td>
                            <td>
                                <span class="status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" style="padding: 0; border: none;">
                                <div class="order-tracking" id="tracking-<?php echo $index; ?>" data-status="<?php echo $status; ?>" data-progress="<?php echo max(0, $statusIndex); ?>">
                                    <?php if ($status === 'cancelled'): ?>
                                    <div class="tracking-message" style="background: #fee2e2; color: #991b1b;">
                                        ❌ This order has been cancelled
                                    </div>
                                    <?php else: ?>
                                    <div class="tracking-steps">
                                        <div class="tracking-line">
                                            <div class="tracking-line-progress" id="progress-<?php echo $index; ?>"></div>
                                        </div>
                                        
                                        <div class="tracking-step <?php echo $statusIndex >= 0 ? ($statusIndex > 0 ? 'completed' : 'active') : ''; ?>">
                                            <div class="tracking-icon">📝</div>
                                            <div class="tracking-label">Order Placed</div>
                                            <div class="tracking-date"><?php echo date('M j', strtotime($order['created_at'])); ?></div>
                                        </div>
                                        
                                        <div class="tracking-step <?php echo $statusIndex >= 1 ? ($statusIndex > 1 ? 'completed' : 'active') : ''; ?>">
                                            <div class="tracking-icon">✅</div>
                                            <div class="tracking-label">Confirmed</div>
                                            <div class="tracking-date"><?php echo $statusIndex >= 1 ? 'Done' : 'Pending'; ?></div>
                                        </div>
                                        
                                        <div class="tracking-step <?php echo $statusIndex >= 2 ? ($statusIndex > 2 ? 'completed' : 'active') : ''; ?>">
                                            <div class="tracking-icon">🚚</div>
                                            <div class="tracking-label">Shipped</div>
                                            <div class="tracking-date"><?php echo $statusIndex >= 2 ? 'Done' : 'Pending'; ?></div>
                                        </div>
                                        
                                        <div class="tracking-step <?php echo $statusIndex >= 3 ? 'completed' : ''; ?>">
                                            <div class="tracking-icon">📦</div>
                                            <div class="tracking-label">Delivered</div>
                                            <div class="tracking-date"><?php echo $statusIndex >= 3 ? 'Done' : 'Pending'; ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="tracking-message">
                                        <?php 
                                        $messages = [
                                            'pending' => '⏳ Your order is awaiting confirmation...',
                                            'confirmed' => '✅ Order confirmed! Preparing for shipment...',
                                            'shipped' => '🚚 Your order is on the way!',
                                            'delivered' => '🎉 Order delivered successfully!'
                                        ];
                                        echo $messages[$status] ?? 'Processing...';
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="icon">📦</div>
                <p>You haven't placed any orders yet.</p>
                <a href="index.html" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">Start Shopping</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Settings Tab -->
    <div id="settings" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h3>🔐 Change Password</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="change_password" value="1">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="6">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">🔒 Update Password</button>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>📧 Email Information</h3>
            </div>
            <p style="color: #6b7280; margin-bottom: 1rem;">Your email address cannot be changed. Contact support if you need assistance.</p>
            <div class="profile-item" style="display: inline-block;">
                <label>Current Email</label>
                <span><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active from all tabs
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabId).classList.add('active');
    
    // Add active to clicked tab
    event.target.classList.add('active');
}

function toggleTracking(index) {
    const tracking = document.getElementById('tracking-' + index);
    const isActive = tracking.classList.contains('active');
    
    // Close all other tracking sections
    document.querySelectorAll('.order-tracking').forEach(t => {
        t.classList.remove('active');
    });
    
    if (!isActive) {
        tracking.classList.add('active');
        
        // Animate progress bar
        const progress = tracking.dataset.progress;
        const progressBar = document.getElementById('progress-' + index);
        if (progressBar) {
            const progressPercent = [0, 33, 66, 100][progress] || 0;
            setTimeout(() => {
                progressBar.style.width = progressPercent + '%';
            }, 100);
        }
    }
}
</script>

</body>
</html>
