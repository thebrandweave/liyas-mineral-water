<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Edit Product";

$error = '';
$success = '';
$product = null;

// Get product ID - check both GET and check for empty string
$product_id = 0;
if (isset($_GET['id']) && $_GET['id'] !== '') {
    $product_id = (int)$_GET['id'];
} elseif (isset($_REQUEST['id']) && $_REQUEST['id'] !== '') {
    // Fallback to REQUEST in case GET is being filtered
    $product_id = (int)$_REQUEST['id'];
}

$product = null;

// Debug: Log what we received
error_log("Edit page - GET: " . var_export($_GET, true) . ", REQUEST: " . var_export($_REQUEST, true) . ", Parsed product_id: " . $product_id);

if (!$product_id || $product_id <= 0) {
    $error = "Invalid product ID. Please select a valid product to edit. Received: " . htmlspecialchars($_GET['id'] ?? 'not set');
} else {
    // Fetch product only if we have a valid ID
    try {
        // Check if image column exists
        try {
            $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'image'");
            $hasImageColumn = $checkCol->rowCount() > 0;
        } catch (Exception $e) {
            $hasImageColumn = false;
        }
        
        if ($hasImageColumn) {
            $stmt = $pdo->prepare("SELECT product_id, name, description, price, image, created_at FROM products WHERE product_id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT product_id, name, description, price, created_at FROM products WHERE product_id = ?");
        }
        
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            // Debug: Check if product exists with different query
            $debugStmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE product_id = ?");
            $debugStmt->execute([$product_id]);
            $debugResult = $debugStmt->fetch(PDO::FETCH_ASSOC);
            $error = "Product not found! Product ID: " . $product_id . " (Found in DB: " . ($debugResult['count'] > 0 ? 'Yes' : 'No') . ")";
        } else {
            // Set image to null if column doesn't exist
            if (!$hasImageColumn) {
                $product['image'] = null;
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching product: " . $e->getMessage());
        $error = "Error loading product: " . $e->getMessage();
        $product = null;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    
    // Validation
    if (empty($name)) {
        $error = "Product name is required.";
    } elseif (empty($price) || !is_numeric($price) || $price <= 0) {
        $error = "Valid price is required.";
    } else {
        $image_path = $product['image'] ?? null; // Keep existing image by default
        
        // Handle image upload if new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowed_types)) {
                $error = "Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.";
            } elseif ($file['size'] > $max_size) {
                $error = "File size too large. Maximum size is 5MB.";
            } else {
                $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
                $target_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    // Delete old image if it exists
                    if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                        unlink(__DIR__ . '/../' . $image_path);
                    }
                    $image_path = 'admin/uploads/products/' . $file_name;
                } else {
                    $error = "Failed to upload image. Please try again.";
                }
            }
        }
        
        // Handle image deletion if delete_image is checked
        if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
            if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                unlink(__DIR__ . '/../' . $image_path);
            }
            $image_path = null;
        }
        
        if (empty($error)) {
            try {
                // Check if image column exists, if not, update without it
                try {
                    $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'image'");
                    $hasImageColumn = $checkCol->rowCount() > 0;
                } catch (Exception $e) {
                    $hasImageColumn = false;
                }
                
                if ($hasImageColumn) {
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE product_id = ?");
                    $stmt->execute([$name, $description ?: null, $price, $image_path, $product_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE product_id = ?");
                    $stmt->execute([$name, $description ?: null, $price, $product_id]);
                }
                
                $success = "Product updated successfully!";
                
                // Refresh product data
                $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Delete uploaded file if database update fails
                if ($image_path && $image_path !== ($product['image'] ?? null) && file_exists(__DIR__ . '/../' . $image_path)) {
                    unlink(__DIR__ . '/../' . $image_path);
                }
                $error = "Error updating product: " . $e->getMessage();
            }
        }
    }
}

// Show success message if redirected from add page
if (isset($_GET['added'])) {
    $success = "Product added successfully! You can now edit it or add images.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Google Font: Poppins -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/prody-admin.css">
	
	<!-- Favicon -->
	<link rel="icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="shortcut icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="apple-touch-icon" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="32x32" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="16x16" href="../../assets/images/logo/logo-bg.jpg">
	
	<title>Edit Product - Liyas Admin</title>
	<style>
		/* Centered modal with blurred background for edit product form */
		.modal-overlay {
			position: fixed;
			inset: 0;
			background: rgba(15, 23, 42, 0.35);
			backdrop-filter: blur(4px);
			-webkit-backdrop-filter: blur(4px);
			display: flex;
			align-items: center;
			justify-content: center;
			z-index: 999;
			padding: 1.5rem;
		}

		.modal-card {
			max-width: 720px;
			width: 100%;
		}

		@media (max-width: 768px) {
			.modal-overlay {
				align-items: flex-start;
				padding-top: 4rem;
			}
		}
	</style>
</head>
<body>
	<div class="container">
		<?php include '../includes/sidebar.php'; ?>
		
		<div class="main-content">
			<div class="header">
				<div class="breadcrumb">
					<i class='bx bx-home'></i>
					<span>Products</span>
					<span>/</span>
					<span>Edit Product</span>
				</div>
				<div class="header-actions">
					<a href="index.php" class="header-btn">
						<i class='bx bx-arrow-back'></i>
						<span>Back</span>
					</a>
				</div>
			</div>
			
			<div class="content-area">
				<?php if ($error): ?>
					<div class="alert alert-error">
						<?= htmlspecialchars($error) ?>
					</div>
				<?php endif; ?>

				<?php if ($success): ?>
					<div class="alert alert-success">
						<?= htmlspecialchars($success) ?>
					</div>
				<?php endif; ?>

				<?php if ($product): ?>
					<div class="modal-overlay">
						<div class="form-card modal-card">
						<div class="form-header">
							<h2>Edit Product #<?= $product['product_id'] ?></h2>
						</div>

						<form method="POST" action="" enctype="multipart/form-data" class="form-modern">
							<div class="form-group">
								<label for="name">Product Name <span style="color: var(--red);">*</span></label>
								<input 
									type="text" 
									name="name" 
									id="name" 
									class="form-input"
									value="<?= htmlspecialchars($product['name']) ?>" 
									required
									placeholder="e.g., Liyas Mineral Water 500ml"
								>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Enter a descriptive name for your product</small>
							</div>

							<div class="form-group">
								<label for="description">Description</label>
								<textarea 
									name="description" 
									id="description" 
									class="form-textarea"
									placeholder="Describe your product..."
								><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Provide details about the product (optional)</small>
							</div>

							<div class="form-group">
								<label for="price">Price <span style="color: var(--red);">*</span></label>
								<input 
									type="number" 
									name="price" 
									id="price" 
									class="form-input"
									value="<?= htmlspecialchars($product['price']) ?>" 
									step="0.01" 
									min="0.01"
									required
									placeholder="0.00"
								>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Enter the price</small>
							</div>

							<div class="form-group">
								<label for="image">Product Image</label>
								<?php if (!empty($product['image'])): ?>
									<div style="margin-bottom: 1rem;">
										<img src="../../<?= htmlspecialchars($product['image']) ?>" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid var(--border-light); margin-bottom: 0.5rem;">
										<br>
										<label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
											<input type="checkbox" name="delete_image" value="1">
											<span style="color: var(--red);">Delete current image</span>
										</label>
									</div>
								<?php endif; ?>
								<input 
									type="file" 
									name="image" 
									id="image" 
									class="form-input"
									accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
								>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;"><?= !empty($product['image']) ? 'Upload a new image to replace the current one' : 'Upload a product image (JPEG, PNG, GIF, or WebP - Max 5MB)' ?></small>
								<div id="image-preview" style="margin-top: 1rem; display: none;">
									<img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid var(--border-light);">
								</div>
							</div>

							<div class="form-actions">
								<button type="submit" name="update" class="btn-action btn-edit noselect">
									<span class="text">Update Product</span>
									<span class="icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
									</span>
								</button>
								<a href="index.php" class="btn btn-secondary">
									<i class='bx bx-x'></i> Cancel
								</a>
							</div>
						</form>
						</div>
					</div>
				<?php else: ?>
					<div class="alert alert-error">
						Product not found.
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	
	<script>
		// Image preview
		const imageInput = document.getElementById('image');
		if (imageInput) {
			imageInput.addEventListener('change', function(e) {
				const file = e.target.files[0];
				if (file) {
					const reader = new FileReader();
					reader.onload = function(e) {
						document.getElementById('preview-img').src = e.target.result;
						document.getElementById('image-preview').style.display = 'block';
					};
					reader.readAsDataURL(file);
				} else {
					document.getElementById('image-preview').style.display = 'none';
				}
			});
		}
	</script>
</body>
</html>

