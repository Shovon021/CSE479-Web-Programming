<?php
/**
 * Forgot Password Page
 * Sends password reset email to user
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (!isValidEmail($email)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            $db = getDB();
            
            // Check if user exists
            $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Always show success message (security: don't reveal if email exists)
            $message = 'If an account with that email exists, you will receive a password reset link.';
            $messageType = 'success';
            
            if ($user) {
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Delete old tokens for this email
                $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);
                
                // Insert new token
                $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expiresAt]);
                
                // Build reset link
                $resetLink = SITE_URL . "/reset_password.php?token=" . $token;
                
                // Send email (using native mail for now)
                $subject = "Password Reset - " . SITE_NAME;
                $body = "
                    <html>
                    <body style='font-family: Arial, sans-serif; color: #333;'>
                        <h2>Hello {$user['name']},</h2>
                        <p>You requested a password reset for your account.</p>
                        <p>Click the button below to reset your password:</p>
                        <p style='margin: 20px 0;'>
                            <a href='{$resetLink}' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                                Reset Password
                            </a>
                        </p>
                        <p>Or copy this link: <a href='{$resetLink}'>{$resetLink}</a></p>
                        <p style='color: #666; font-size: 12px;'>This link expires in 1 hour. If you didn't request this, ignore this email.</p>
                    </body>
                    </html>
                ";
                
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: " . SITE_NAME . " <noreply@" . parse_url(SITE_URL, PHP_URL_HOST) . ">\r\n";
                
                // Try to send email
                @mail($email, $subject, $body, $headers);
                
                // Log the attempt
                error_log("Password reset requested for: " . $email);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?= SITE_NAME ?></title>
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
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>🔐 Forgot Password</h1>
                <p>Enter your email and we'll send you a reset link</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                
                <button type="submit" class="btn-primary">Send Reset Link</button>
            </form>
            
            <div class="auth-footer">
                Remember your password? <a href="login.php">Login</a>
            </div>
        </div>
    </div>
</body>
</html>
