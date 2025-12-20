<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Add Product";

$error = '';

// Fetch categories for the dropdown
try {
    $cat_stmt = $pdo->query("SELECT category_id, name FROM categories ORDER BY name ASC");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $stock = trim($_POST['stock'] ?? 0);
    $discount = trim($_POST['discount'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    // Generate URL Slug
    $url_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    if (empty($name)) {
        $error = "Product name is required.";
    } elseif (empty($price) || !is_numeric($price)) {
        $error = "Valid price is required.";
    } else {
        $image_path = null;
        
        // Handle image upload logic
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name)) {
                $image_path = 'admin/uploads/products/' . $file_name;
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, url_slug, description, price, discount, stock, status, category_id, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $url_slug, $description ?: null, $price, $discount, $stock, $status, $category_id, $image_path]);
                
                quickLog($pdo, 'create', 'product', $pdo->lastInsertId(), "Created product: {$name}");
                header("Location: index.php?added=1");
                exit;
            } catch (PDOException $e) {
                $error = "Error adding product: " . $e->getMessage();
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <title>Add Product - Liyas Admin</title>
    <style>
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; }
        @media (max-width: 768px) { .grid-3 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb">
                    <i class='bx bx-home'></i><span>Products</span> / <span>Add</span>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="header-btn"><i class='bx bx-arrow-back'></i> Back</a>
                </div>
            </div>
            
            <div class="content-area">
                <div class="table-card">
                    <div class="table-header"><div class="table-title">Product Details</div></div>
                    <div style="padding: 2rem;">
                        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="form-modern">
                            <div class="grid-3">
                                <div class="form-group">
                                    <label>Product Name *</label>
                                    <input type="text" name="name" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label>Category *</label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid-3">
                                <div class="form-group">
                                    <label>Price (₹) *</label>
                                    <input type="number" name="price" step="0.01" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label>Discount (%)</label>
                                    <input type="number" name="discount" step="0.01" class="form-input" value="0.00">
                                </div>
                                <div class="form-group">
                                    <label>Stock Quantity</label>
                                    <input type="number" name="stock" class="form-input" value="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-textarea" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Product Image</label>
                                <input type="file" name="image" class="form-input" accept="image/*">
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-action btn-add noselect">
                                    <span class="text">Save Product</span>
                                    <span class="icon"><i class='bx bx-check'></i></span>
                                </button>
                                <a href="index.php" class="table-btn" style="margin-left:1rem;">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>