<?php
/**
 * Reset Password Page
 * Validates token and allows password reset
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();

$message = '';
$messageType = '';
$validToken = false;
$token = $_GET['token'] ?? '';

$db = getDB();

// Validate token
if ($token) {
    $stmt = $db->prepare("
        SELECT * FROM password_resets 
        WHERE token = ? AND used = 0 AND expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reset) {
        $validToken = true;
    } else {
        $message = 'Invalid or expired reset link. Please request a new one.';
        $messageType = 'error';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (strlen($password) < 8) {
            $message = 'Password must be at least 8 characters.';
            $messageType = 'error';
        } elseif ($password !== $confirmPassword) {
            $message = 'Passwords do not match.';
            $messageType = 'error';
        } else {
            // Update password
            $hashedPassword = hashPassword($password);
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
            $stmt->execute([$hashedPassword, $reset['email']]);
            
            // Mark token as used
            $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->execute([$reset['id']]);
            
            $message = 'Password reset successful! You can now login.';
            $messageType = 'success';
            $validToken = false; // Hide form
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }
        .auth-card {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            max-width: 420px;
            width: 100%;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h1 {
            font-family: var(--font-heading);
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        .auth-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>🔑 Reset Password</h1>
                <p>Enter your new password below</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($validToken): ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" required minlength="8" placeholder="Enter new password">
                        <div class="password-strength">Minimum 8 characters</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required placeholder="Confirm new password">
                    </div>
                    
                    <button type="submit" class="btn-primary">Reset Password</button>
                </form>
            <?php else: ?>
                <div class="auth-footer">
                    <a href="forgot_password.php">Request new reset link</a> | <a href="login.php">Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
