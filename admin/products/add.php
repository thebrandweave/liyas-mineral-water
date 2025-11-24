<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Add Product";

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?: null;
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $featured = isset($_POST['featured']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO products (category_id, name, description, price, stock, is_active, featured)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$category_id, $name, $desc, $price, $stock, $is_active, $featured]);
        $product_id = $pdo->lastInsertId();

        // Upload directory
        $uploadDir = $ROOT_PATH . '/uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to create upload directory: " . $uploadDir);
            }
        }

        // Handle media uploads
        if (!empty($_FILES['media']['name'][0])) {
            foreach ($_FILES['media']['tmp_name'] as $i => $tmp) {
                if (!is_uploaded_file($tmp)) continue;
                
                // Check for upload errors
                if ($_FILES['media']['error'][$i] !== UPLOAD_ERR_OK) {
                    error_log("Upload error code: " . $_FILES['media']['error'][$i]);
                    continue;
                }
                
                $original = basename($_FILES['media']['name'][$i]);
                $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
                $filename = uniqid('media_') . '.' . $ext;
                $dest = $uploadDir . $filename;
                $type = in_array($ext, ['mp4', 'mov', 'webm']) ? 'video' : 'image';
                
                if (move_uploaded_file($tmp, $dest)) {
                    // Verify file was actually moved
                    if (file_exists($dest)) {
                        $pdo->prepare("
                            INSERT INTO products_media (product_id, file_path, file_type, alt_text, is_primary)
                            VALUES (?, ?, ?, ?, ?)
                        ")->execute([$product_id, $filename, $type, $original, $i === 0]);
                    } else {
                        error_log("File move failed: " . $dest);
                    }
                } else {
                    error_log("move_uploaded_file failed. Source: " . $tmp . " Dest: " . $dest);
                }
            }
        }

        header("Location: index.php?success=1");
        exit;
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
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
	<title>Add Product - Admin Panel</title>
	<style>
		.form-group {
			margin-bottom: 1.5rem;
		}
		.form-group label {
			display: block;
			margin-bottom: 0.5rem;
			color: var(--dark-grey);
			font-weight: 600;
			font-family: var(--opensans);
		}
		.form-group input,
		.form-group select,
		.form-group textarea {
			width: 100%;
			padding: 0.75rem 1rem;
			border: 1px solid var(--grey);
			border-radius: 8px;
			background: var(--light);
			font-size: 1rem;
			font-family: var(--opensans);
		}
		.form-group input:focus,
		.form-group select:focus,
		.form-group textarea:focus {
			outline: none;
			border-color: var(--blue);
		}
		.form-group textarea {
			resize: vertical;
			min-height: 100px;
		}
		.form-group-inline {
			display: flex;
			gap: 2rem;
			align-items: center;
			margin-bottom: 1.5rem;
		}
		.form-group-inline label {
			display: flex;
			align-items: center;
			gap: 0.5rem;
			cursor: pointer;
			font-weight: 500;
		}
		.form-group-inline input[type="checkbox"] {
			width: auto;
			cursor: pointer;
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
		.btn-submit {
			width: 100%;
			padding: 1rem;
			background: var(--blue);
			color: white;
			border: none;
			border-radius: 8px;
			font-size: 1rem;
			font-weight: 600;
			cursor: pointer;
			font-family: var(--opensans);
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0.5rem;
		}
		.btn-submit:hover {
			background: var(--blue-dark);
		}
		.form-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 1.5rem;
		}
		@media (max-width: 768px) {
			.form-grid {
				grid-template-columns: 1fr;
			}
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
					<h1>Add New Product</h1>
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
							<a class="active" href="#">Add Product</a>
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

					<?php if ($message): ?>
						<div class="alert <?= $message_type === 'success' ? 'alert-success' : 'alert-error' ?>">
							<?= htmlspecialchars($message) ?>
						</div>
					<?php endif; ?>

					<form method="post" enctype="multipart/form-data" style="padding: 1.5rem;">
						<div class="form-group">
							<label for="name">Product Name *</label>
							<input type="text" name="name" id="name" required>
						</div>

						<div class="form-group">
							<label for="category_id">Category</label>
							<select name="category_id" id="category_id">
								<option value="">-- None --</option>
								<?php foreach ($categories as $c): ?>
									<option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group">
							<label for="description">Description</label>
							<textarea name="description" id="description" rows="4"></textarea>
						</div>

						<div class="form-grid">
							<div class="form-group">
								<label for="price">Price (₹) *</label>
								<input type="number" step="0.01" name="price" id="price" required>
							</div>

							<div class="form-group">
								<label for="stock">Stock Quantity</label>
								<input type="number" name="stock" id="stock" value="0" min="0">
							</div>
						</div>

						<div class="form-group-inline">
							<label for="is_active">
								<input type="checkbox" name="is_active" id="is_active" checked>
								Active
							</label>
							<label for="featured">
								<input type="checkbox" name="featured" id="featured">
								Featured
							</label>
						</div>

						<div class="form-group">
							<label for="media">Upload Media</label>
							<input type="file" name="media[]" id="media" multiple accept="image/*,video/*">
							<small style="color: var(--dark-grey); font-size: 0.85rem; display: block; margin-top: 0.5rem;">
								You can upload multiple images or videos. First file will be set as primary.
							</small>
						</div>

						<button type="submit" class="btn-submit">
							<i class='bx bxs-save' ></i>
							Save Product
						</button>
					</form>
				</div>
			</div>
		</main>
	</section>
	
	<script src="../admin-script.js"></script>
</body>
</html>
