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
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/admin-style.css">
	<title>Edit Product - Admin Panel</title>
	<style>
		.form-container {
			background: var(--light);
			padding: 2rem;
			border-radius: 12px;
			margin-top: 1rem;
		}

		.form-group {
			margin-bottom: 1.5rem;
		}

		.form-group label {
			display: block;
			margin-bottom: 0.5rem;
			color: var(--dark);
			font-weight: 600;
			font-size: 0.9rem;
		}

		.form-group input[type="text"],
		.form-group input[type="number"],
		.form-group textarea {
			width: 100%;
			padding: 0.75rem 1rem;
			border: 1px solid var(--grey);
			border-radius: 8px;
			background: white;
			font-size: 1rem;
			font-family: var(--opensans);
			transition: border-color 0.2s;
		}

		.form-group input:focus,
		.form-group textarea:focus {
			outline: none;
			border-color: var(--blue);
		}

		.form-group textarea {
			resize: vertical;
			min-height: 120px;
		}

		.form-group small {
			display: block;
			margin-top: 0.25rem;
			color: var(--dark-grey);
			font-size: 0.85rem;
		}

		.alert {
			padding: 1rem;
			border-radius: 8px;
			margin-bottom: 1.5rem;
		}

		.alert-success {
			background: #d4edda;
			color: #155724;
			border: 1px solid #c3e6cb;
		}

		.alert-error {
			background: #f8d7da;
			color: #721c24;
			border: 1px solid #f5c6cb;
		}

		.btn-group {
			display: flex;
			gap: 1rem;
			margin-top: 2rem;
		}

		.btn {
			padding: 0.75rem 2rem;
			border: none;
			border-radius: 8px;
			font-size: 1rem;
			font-weight: 600;
			cursor: pointer;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			transition: all 0.2s;
		}

		.btn-primary {
			background: var(--blue);
			color: white;
		}

		.btn-primary:hover {
			background: #2563eb;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
		}

		.btn-secondary {
			background: var(--grey);
			color: var(--dark);
		}

		.btn-secondary:hover {
			background: #d1d5db;
		}

		.product-info {
			background: #f8f9fa;
			padding: 1rem;
			border-radius: 8px;
			margin-bottom: 1.5rem;
		}

		.product-info p {
			margin: 0.5rem 0;
			color: var(--dark-grey);
		}

		.product-info strong {
			color: var(--dark);
		}
	</style>
</head>
<body>
	<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

	<section id="content">
		<nav>
			<i class='bx bx-menu bx-sm' ></i>
			<a href="#" class="nav-link"><?= $page_title ?></a>
			<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification">
				<i class='bx bxs-bell bx-tada-hover' ></i>
				<span class="num">0</span>
			</a>
			<a href="#" class="profile">
				<img src="https://i.pravatar.cc/36?u=<?= urlencode($admin_name) ?>" alt="Profile">
			</a>
		</nav>

		<main>
			<div class="head-title">
				<div class="left">
					<h1>Edit Product</h1>
					<ul class="breadcrumb">
						<li>
							<a href="../index.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a href="index.php">Products</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Edit Product</a>
						</li>
					</ul>
				</div>
				<a href="index.php" class="btn-download">
					<i class='bx bx-arrow-back'></i>
					<span class="text">Back to Products</span>
				</a>
			</div>

			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Product Information</h3>
						<i class='bx bxs-shopping-bag-alt' ></i>
					</div>

					<div class="form-container">
						<?php if ($error): ?>
							<div class="alert alert-error">
								<i class='bx bx-error-circle' ></i> <?= htmlspecialchars($error) ?>
								<br><small style="margin-top: 0.5rem; display: block;">
									<strong>Debug Info:</strong><br>
									URL Parameter 'id': <?= htmlspecialchars(var_export($_GET['id'] ?? 'not set', true)) ?><br>
									Parsed product_id: <?= $product_id ?><br>
									Full URL: <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'unknown') ?><br>
									Query String: <?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? 'none') ?>
								</small>
							</div>
						<?php endif; ?>

						<?php if ($success): ?>
							<div class="alert alert-success">
								<i class='bx bx-check-circle' ></i> <?= htmlspecialchars($success) ?>
							</div>
						<?php endif; ?>

						<?php if ($product): ?>
							<div class="product-info">
								<p><strong>Product ID:</strong> #<?= $product['product_id'] ?></p>
								<p><strong>Created:</strong> <?= date('d-m-Y H:i', strtotime($product['created_at'])) ?></p>
							</div>

							<form method="POST" action="" enctype="multipart/form-data">
								<div class="form-group">
									<label for="name">Product Name <span style="color: #dc2626;">*</span></label>
									<input 
										type="text" 
										name="name" 
										id="name" 
										value="<?= htmlspecialchars($product['name']) ?>" 
										required
										placeholder="e.g., Liyas Mineral Water 500ml"
									>
									<small>Enter a descriptive name for your product</small>
								</div>

								<div class="form-group">
									<label for="description">Description</label>
									<textarea 
										name="description" 
										id="description" 
										placeholder="Describe your product..."
									><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
									<small>Provide details about the product (optional)</small>
								</div>

								<div class="form-group">
									<label for="price">Price <span style="color: #dc2626;">*</span></label>
									<input 
										type="number" 
										name="price" 
										id="price" 
										value="<?= htmlspecialchars($product['price']) ?>" 
										step="0.01" 
										min="0.01"
										required
										placeholder="0.00"
									>
									<small>Enter the price in USD</small>
								</div>

								<div class="form-group">
									<label for="image">Product Image</label>
									<?php if (!empty($product['image'])): ?>
										<div style="margin-bottom: 1rem;">
											<img src="../../<?= htmlspecialchars($product['image']) ?>" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid var(--grey); margin-bottom: 0.5rem;">
											<br>
											<label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
												<input type="checkbox" name="delete_image" value="1">
												<span style="color: #dc2626;">Delete current image</span>
											</label>
										</div>
									<?php endif; ?>
									<input 
										type="file" 
										name="image" 
										id="image" 
										accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
									>
									<small><?= !empty($product['image']) ? 'Upload a new image to replace the current one' : 'Upload a product image (JPEG, PNG, GIF, or WebP - Max 5MB)' ?></small>
									<div id="image-preview" style="margin-top: 1rem; display: none;">
										<img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid var(--grey);">
									</div>
								</div>

								<div class="btn-group">
									<button type="submit" name="update" class="btn btn-primary">
										<i class='bx bx-save' ></i> Update Product
									</button>
									<a href="index.php" class="btn btn-secondary">
										<i class='bx bx-x' ></i> Cancel
									</a>
								</div>
							</form>
						<?php else: ?>
							<div class="alert alert-error">
								Product not found.
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</main>
	</section>
	
	<script src="../assets/js/admin-script.js"></script>
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

