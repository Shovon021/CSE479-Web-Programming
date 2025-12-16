<?php
/**
 * Add Product API
 * Handles new product creation with image upload
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDB();
    
    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['category_id']) || empty($_POST['price'])) {
        throw new Exception('Product name, category, and price are required');
    }
    
    // Validate and handle image upload
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Product image is required');
    }
    
    $file = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    
    // Validat file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed');
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size must be less than 5MB');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_path = __DIR__ . '/../uploads/products/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to upload image');
    }
    
    // Prepare product data
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $description = !empty($_POST['description']) ? trim($_POST['description']) : '';
    $badge = !empty($_POST['badge']) && $_POST['badge'] !== 'none' ? trim($_POST['badge']) : null;
    $rating = !empty($_POST['rating']) ? (float)$_POST['rating'] : 4.5;
    $reviews = !empty($_POST['reviews']) ? (int)$_POST['reviews'] : 0;
    $in_stock = isset($_POST['in_stock']) ? (bool)$_POST['in_stock'] : true;
    $image_path = 'uploads/products/' . $filename;
    
    // Insert product into database
    $stmt = $db->prepare("
        INSERT INTO products (name, category_id, price, original_price, image_path, description, rating, reviews, badge, in_stock)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $name,
        $category_id,
        $price,
        $original_price,
        $image_path,
        $description,
        $rating,
        $reviews,
        $badge,
        $in_stock
    ]);
    
    $product_id = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added successfully',
        'product_id' => $product_id,
        'image_path' => $image_path
    ]);
    
} catch (Exception $e) {
    // Clean up uploaded file if database insert failed
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
