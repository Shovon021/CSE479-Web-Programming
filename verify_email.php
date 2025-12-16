<?php
/**
 * Email Verification
 * Verifies user email after registration
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();

$message = '';
$messageType = '';
$token = $_GET['token'] ?? '';

if ($token) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT id, name FROM users WHERE verification_token = ? AND email_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Verify email
        $stmt = $db->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        $message = 'Email verified successfully! You can now login.';
        $messageType = 'success';
    } else {
        $message = 'Invalid or expired verification link.';
        $messageType = 'error';
    }
} else {
    $message = 'No verification token provided.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .verify-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }
        .verify-card {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            max-width: 450px;
            width: 100%;
            text-align: center;
        }
        .verify-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .verify-card h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .btn-primary {
            display: inline-block;
            padding: 0.875rem 2rem;
            background: var(--gradient-primary);
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 600;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-card">
            <div class="verify-icon"><?= $messageType === 'success' ? '✅' : '❌' ?></div>
            <h1>Email Verification</h1>
            <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <a href="login.php" class="btn-primary">Go to Login</a>
        </div>
    </div>
</body>
</html>
