<?php
/**
 * Product Reviews API
 * Submit and retrieve product reviews
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET: Fetch reviews for a product
    if ($method === 'GET') {
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
        
        if (!$productId) {
            throw new Exception('Product ID required');
        }
        
        $stmt = $db->prepare("
            SELECT r.*, u.name as user_name 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.product_id = ? 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$productId]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate average rating
        $stmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE product_id = ?");
        $stmt->execute([$productId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'average_rating' => round($stats['avg_rating'] ?? 0, 1),
            'total_reviews' => (int)$stats['total']
        ]);
        exit;
    }
    
    // POST: Submit a review
    startSecureSession();
    
    if (!isUserLoggedIn()) {
        throw new Exception('Please login to submit a review');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = $input['product_id'] ?? null;
    $rating = $input['rating'] ?? null;
    $title = trim($input['title'] ?? '');
    $comment = trim($input['comment'] ?? '');
    
    if (!$productId || !$rating) {
        throw new Exception('Product ID and rating required');
    }
    
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Rating must be between 1 and 5');
    }
    
    $userId = $_SESSION['user_id'];
    
    // Check if user already reviewed this product
    $stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    
    if ($stmt->fetch()) {
        // Update existing review
        $stmt = $db->prepare("UPDATE reviews SET rating = ?, title = ?, comment = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$rating, $title, $comment, $userId, $productId]);
        $message = 'Review updated successfully';
    } else {
        // Insert new review
        $stmt = $db->prepare("INSERT INTO reviews (product_id, user_id, rating, title, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$productId, $userId, $rating, $title, $comment]);
        $message = 'Review submitted successfully';
    }
    
    // Update product's aggregate rating
    $stmt = $db->prepare("
        UPDATE products SET 
            rating = (SELECT AVG(rating) FROM reviews WHERE product_id = ?),
            reviews = (SELECT COUNT(*) FROM reviews WHERE product_id = ?)
        WHERE id = ?
    ");
    $stmt->execute([$productId, $productId, $productId]);
    
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
