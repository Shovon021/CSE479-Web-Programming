<?php
/**
 * Admin Products Page - Full CRUD
 * Add, Edit, Delete products with image upload
 */

session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$products = [];
$categories = [];
$error = null;
$success = null;
$editProduct = null;

try {
    $db = getDB();
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Add new product
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $category_id = (int)$_POST['category_id'];
            $price = (float)$_POST['price'];
            $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
            $description = trim($_POST['description']);
            $badge = $_POST['badge'] !== 'none' ? trim($_POST['badge']) : null;
            $in_stock = isset($_POST['in_stock']) ? 1 : 0;
            $rating = (float)($_POST['rating'] ?? 4.5);
            
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = __DIR__ . '/../images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image_path = 'images/' . $filename;
                }
            }
            
            if (empty($image_path)) {
                $image_path = 'https://via.placeholder.com/500x500?text=No+Image';
            }
            
            $stmt = $db->prepare("
                INSERT INTO products (name, category_id, price, original_price, image_path, description, badge, in_stock, rating, reviews)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$name, $category_id, $price, $original_price, $image_path, $description, $badge, $in_stock, $rating]);
            $success = "Product '$name' added successfully!";
        }
        
        // Update product
        if (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name']);
            $category_id = (int)$_POST['category_id'];
            $price = (float)$_POST['price'];
            $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
            $description = trim($_POST['description']);
            $badge = $_POST['badge'] !== 'none' ? trim($_POST['badge']) : null;
            $in_stock = isset($_POST['in_stock']) ? 1 : 0;
            $rating = (float)($_POST['rating'] ?? 4.5);
            
            // Handle image upload (optional for edit)
            $image_sql = '';
            $params = [$name, $category_id, $price, $original_price, $description, $badge, $in_stock, $rating];
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = __DIR__ . '/../images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image_sql = ', image_path = ?';
                    $params[] = 'images/' . $filename;
                }
            }
            
            $params[] = $id;
            $stmt = $db->prepare("
                UPDATE products SET 
                    name = ?, category_id = ?, price = ?, original_price = ?, 
                    description = ?, badge = ?, in_stock = ?, rating = ? $image_sql
                WHERE id = ?
            ");
            $stmt->execute($params);
            $success = "Product '$name' updated successfully!";
        }
    }
    
    // Handle GET actions
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $productId = (int)$_GET['delete'];
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $success = "Product deleted!";
        header('Location: products.php?msg=deleted');
        exit;
    }
    
    if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
        $productId = (int)$_GET['toggle'];
        $stmt = $db->prepare("UPDATE products SET in_stock = NOT in_stock WHERE id = ?");
        $stmt->execute([$productId]);
        header('Location: products.php?msg=updated');
        exit;
    }
    
    // Load product for editing
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([(int)$_GET['edit']]);
        $editProduct = $stmt->fetch();
    }
    
    // Get all products
    $stmt = $db->query("
        SELECT p.*, c.display_name as category_display 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC
    ");
    $products = $stmt->fetchAll() ?? [];
    
    // Get categories
    $stmt = $db->query("SELECT * FROM categories ORDER BY display_name");
    $categories = $stmt->fetchAll() ?? [];
    
    // Get counts
    $stmt = $db->query("SELECT COUNT(*) as total, SUM(CASE WHEN in_stock = 1 THEN 1 ELSE 0 END) as in_stock FROM products");
    $counts = $stmt->fetch();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Check for URL messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $success = "Product deleted successfully!";
    if ($_GET['msg'] === 'updated') $success = "Stock status updated!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Flex & Bliss Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; min-height: 100vh; }
        
        .header { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 1.5rem 0; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 1.5rem; }
        .header h1 { font-size: 1.5rem; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        
        .nav { background: white; border-bottom: 1px solid #e5e7eb; padding: 0.5rem 0; }
        .nav ul { list-style: none; display: flex; gap: 0.5rem; }
        .nav a { display: block; padding: 0.75rem 1rem; text-decoration: none; color: #374151; border-radius: 6px; font-weight: 500; }
        .nav a:hover, .nav a.active { background: #6366f1; color: white; }
        
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; font-size: 0.875rem; }
        .btn-primary { background: #6366f1; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
        
        .grid-2 { display: grid; grid-template-columns: 400px 1fr; gap: 1.5rem; margin: 1.5rem 0; }
        @media (max-width: 1024px) { .grid-2 { grid-template-columns: 1fr; } }
        
        .card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .card-header h2 { font-size: 1.1rem; color: #1f2937; }
        .card-body { padding: 1.5rem; }
        
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.875rem; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.9375rem; font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { 
            outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-hint { font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem; }
        
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-group input { width: auto; }
        
        .preview-img { width: 100%; max-width: 150px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #e5e7eb; margin-top: 0.5rem; }
        
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th, td { text-align: left; padding: 0.875rem; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem; }
        th { background: #f9fafb; font-weight: 600; color: #374151; }
        tr:hover { background: #f9fafb; }
        
        .product-img { width: 45px; height: 45px; object-fit: cover; border-radius: 6px; background: #f3f4f6; }
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        
        .error-msg { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .success-msg { background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        
        .actions { display: flex; gap: 0.375rem; }
        .stats-row { display: flex; gap: 1rem; margin-bottom: 1rem; }
        .stat-mini { background: white; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.875rem; }
        .stat-mini strong { color: #6366f1; }
    </style>
</head>
<body>

<div class="header">
    <div class="container">
        <div class="header-flex">
            <h1>📦 Product Management</h1>
            <div style="display: flex; gap: 1rem;">
                <a href="../index.html" class="btn btn-secondary">🏪 Store</a>
                <a href="index.php?logout=1" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="nav">
    <div class="container">
        <ul>
            <li><a href="index.php">📊 Dashboard</a></li>
            <li><a href="products.php" class="active">📦 Products</a></li>
        </ul>
    </div>
</div>

<div class="container" style="padding: 1.5rem 1.5rem 3rem;">
    <?php if ($error): ?>
        <div class="error-msg">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-msg">✅ <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="stats-row">
        <div class="stat-mini"><strong><?php echo $counts['total'] ?? 0; ?></strong> Total</div>
        <div class="stat-mini"><strong style="color:#10b981;"><?php echo $counts['in_stock'] ?? 0; ?></strong> In Stock</div>
        <div class="stat-mini"><strong style="color:#f59e0b;"><?php echo ($counts['total'] ?? 0) - ($counts['in_stock'] ?? 0); ?></strong> Out of Stock</div>
    </div>
    
    <div class="grid-2">
        <!-- Add/Edit Form -->
        <div class="card">
            <div class="card-header">
                <h2><?php echo $editProduct ? '✏️ Edit Product' : '➕ Add New Product'; ?></h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
                    <?php if ($editProduct): ?>
                        <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($editProduct['name'] ?? ''); ?>" placeholder="Enter product name">
                    </div>
                    
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['display_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Price (৳) *</label>
                            <input type="number" name="price" step="0.01" min="0" required value="<?php echo $editProduct['price'] ?? ''; ?>" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>Original Price (৳)</label>
                            <input type="number" name="original_price" step="0.01" min="0" value="<?php echo $editProduct['original_price'] ?? ''; ?>" placeholder="For discount display">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Product Image <?php echo $editProduct ? '' : '*'; ?></label>
                        <input type="file" name="image" accept="image/*" <?php echo $editProduct ? '' : 'required'; ?>>
                        <div class="form-hint">JPG, PNG, GIF, WEBP (max 5MB)</div>
                        <?php if ($editProduct && $editProduct['image_path']): ?>
                            <img src="../<?php echo htmlspecialchars($editProduct['image_path']); ?>" class="preview-img" alt="Current">
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="Product description..."><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Badge</label>
                            <select name="badge">
                                <option value="none">None</option>
                                <?php 
                                $badges = ['New', 'Bestseller', 'Popular', 'Trending', 'Luxury', 'Sale', 'Limited'];
                                foreach ($badges as $b): ?>
                                    <option value="<?php echo $b; ?>" <?php echo ($editProduct['badge'] ?? '') === $b ? 'selected' : ''; ?>><?php echo $b; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Rating (1-5)</label>
                            <input type="number" name="rating" min="1" max="5" step="0.1" value="<?php echo $editProduct['rating'] ?? '4.5'; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="in_stock" id="in_stock" <?php echo ($editProduct['in_stock'] ?? 1) ? 'checked' : ''; ?>>
                            <label for="in_stock" style="margin:0; font-weight:normal;">Product is in stock</label>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">
                            <?php echo $editProduct ? '💾 Update Product' : '➕ Add Product'; ?>
                        </button>
                        <?php if ($editProduct): ?>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Products List -->
        <div class="card">
            <div class="card-header">
                <h2>📋 All Products (<?php echo count($products); ?>)</h2>
            </div>
            <div class="table-scroll">
                <?php if (count($products) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo htmlspecialchars($p['image_path']); ?>" class="product-img" 
                                     onerror="this.src='https://via.placeholder.com/45'">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
                                <small style="color:#6b7280;"><?php echo htmlspecialchars($p['category_display'] ?? ''); ?></small>
                            </td>
                            <td>৳<?php echo number_format($p['price'], 0); ?></td>
                            <td>
                                <?php if ($p['in_stock']): ?>
                                    <span class="badge badge-success">In Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="?edit=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">✏️</a>
                                    <a href="?toggle=<?php echo $p['id']; ?>" class="btn <?php echo $p['in_stock'] ? 'btn-warning' : 'btn-success'; ?> btn-sm">
                                        <?php echo $p['in_stock'] ? '⬇️' : '⬆️'; ?>
                                    </a>
                                    <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Delete this product?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="padding: 2rem; text-align: center; color: #6b7280;">
                    No products yet. Add your first product!
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
