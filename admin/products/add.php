<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Add Product";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    
    // Validation
    if (empty($name)) {
        $error = "Product name is required.";
    } elseif (empty($price) || !is_numeric($price) || $price <= 0) {
        $error = "Valid price is required.";
    } else {
        $image_path = null;
        
        // Handle image upload
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
                    $image_path = 'admin/uploads/products/' . $file_name;
                } else {
                    $error = "Failed to upload image. Please try again.";
                }
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $description ?: null, $price, $image_path]);
                
                $product_id = $pdo->lastInsertId();
                $success = "Product added successfully!";
                
                // Redirect to products list
                header("Location: index.php?added=1");
                exit;
            } catch (PDOException $e) {
                // Delete uploaded file if database insert fails
                if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                    unlink(__DIR__ . '/../' . $image_path);
                }
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
	<link rel="preload" href="https://cal.com/fonts/CalSans-SemiBold.woff2" as="font" type="font/woff2" crossorigin>
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/prody-admin.css">
	
	<!-- Favicon -->
	<link rel="icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="shortcut icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="apple-touch-icon" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="32x32" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="16x16" href="../../assets/images/logo/logo-bg.jpg">
	
	<title>Add Product - Liyas Admin</title>
	<style>
		/* Centered modal with blurred background for add product form */
		.modal-overlay {
			position: fixed;
			inset: 0;
			background: rgba(15, 23, 42, 0.35);
			backdrop-filter: blur(4px);
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
					<span>Add Product</span>
				</div>
				<div class="header-actions">
					<a href="index.php" class="header-btn">
						<i class='bx bx-arrow-back'></i>
						<span>Back</span>
					</a>
				</div>
			</div>
			
			<div class="content-area">
				<div class="modal-overlay">
					<div class="form-card modal-card">
						<div class="form-header">
							<h2>Add New Product</h2>
						</div>

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

						<form method="POST" action="" enctype="multipart/form-data" class="form-modern">
							<div class="form-group">
								<label for="name">Product Name <span style="color: var(--red);">*</span></label>
								<input 
									type="text" 
									name="name" 
									id="name" 
									class="form-input"
									value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
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
								><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Provide details about the product (optional)</small>
							</div>

							<div class="form-group">
								<label for="price">Price <span style="color: var(--red);">*</span></label>
								<input 
									type="number" 
									name="price" 
									id="price" 
									class="form-input"
									value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" 
									step="0.01" 
									min="0.01"
									required
									placeholder="0.00"
								>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Enter the price</small>
							</div>

							<div class="form-group">
								<label for="image">Product Image</label>
								<input 
									type="file" 
									name="image" 
									id="image" 
									class="form-input"
									accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
								>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Upload a product image (JPEG, PNG, GIF, or WebP - Max 5MB)</small>
								<div id="image-preview" style="margin-top: 1rem; display: none;">
									<img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid var(--border-light);">
								</div>
							</div>

							<div class="form-actions">
								<button type="submit" class="btn btn-primary">
									<i class='bx bx-save'></i> Add Product
								</button>
								<a href="index.php" class="btn btn-secondary">
									<i class='bx bx-x'></i> Cancel
								</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script>
		// Image preview
		document.getElementById('image').addEventListener('change', function(e) {
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
	</script>
</body>
</html>

