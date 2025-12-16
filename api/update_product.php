<?php
/**
 * Update Product API
 * Handles product updates with optional image replacement
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDB();
    
    // Validate product ID
    if (empty($_POST['id'])) {
        throw new Exception('Product ID is required');
    }
    
    $product_id = (int)$_POST['id'];
    
    // Get current product data
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $current_product = $stmt->fetch();
    
    if (!$current_product) {
        throw new Exception('Product not found');
    }
    
    // Handle image upload if provided
    $image_path = $current_product['image_path'];
    $old_image_path = null;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        // Validate file type
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
        
        // Store old image path for deletion
        if (strpos($current_product['image_path'], 'uploads/products/') === 0) {
            $old_image_path = __DIR__ . '/../' . $current_product['image_path'];
        }
        
        $image_path = 'uploads/products/' . $filename;
    }
    
    // Prepare update data
    $name = isset($_POST['name']) ? trim($_POST['name']) : $current_product['name'];
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : $current_product['category_id'];
    $price = isset($_POST['price']) ? (float)$_POST['price'] : $current_product['price'];
    $original_price = isset($_POST['original_price']) ? (float)$_POST['original_price'] : $current_product['original_price'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : $current_product['description'];
    $badge = isset($_POST['badge']) && $_POST['badge'] !== 'none' ? trim($_POST['badge']) : null;
    $rating = isset($_POST['rating']) ? (float)$_POST['rating'] : $current_product['rating'];
    $reviews = isset($_POST['reviews']) ? (int)$_POST['reviews'] : $current_product['reviews'];
    $in_stock = isset($_POST['in_stock']) ? (bool)$_POST['in_stock'] : $current_product['in_stock'];
    
    // Update product in database
    $stmt = $db->prepare("
        UPDATE products 
        SET name = ?, category_id = ?, price = ?, original_price = ?, image_path = ?, 
            description = ?, rating = ?, reviews = ?, badge = ?, in_stock = ?
        WHERE id = ?
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
        $in_stock,
        $product_id
    ]);
    
    // Delete old image if new one was uploaded
    if ($old_image_path && file_exists($old_image_path)) {
        unlink($old_image_path);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Product updated successfully',
        'product_id' => $product_id
    ]);
    
} catch (Exception $e) {
    // Clean up uploaded file if update failed
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
