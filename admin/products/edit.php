<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id=?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

// Fetch categories + media
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$media = $pdo->prepare("SELECT * FROM products_media WHERE product_id=? ORDER BY is_primary DESC, uploaded_at ASC");
$media->execute([$id]);
$mediaFiles = $media->fetchAll();

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Edit Product";

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?: null;
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $featured = isset($_POST['featured']) ? 1 : 0;

    try {
        $pdo->prepare("
            UPDATE products 
            SET category_id=?, name=?, description=?, price=?, stock=?, is_active=?, featured=? 
            WHERE product_id=?
        ")->execute([$category_id, $name, $desc, $price, $stock, $is_active, $featured, $id]);

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
	<title>Edit Product - Admin Panel</title>
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
		.media-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
			gap: 1rem;
			margin-top: 1rem;
		}
		.media-item {
			position: relative;
			border: 1px solid var(--grey);
			border-radius: 8px;
			overflow: hidden;
			background: var(--light);
		}
		.media-item img,
		.media-item video {
			width: 100%;
			height: auto;
			display: block;
		}
		.media-item .media-info {
			padding: 0.75rem;
			text-align: center;
		}
		.media-item .media-info small {
			display: block;
			color: var(--dark-grey);
			font-size: 0.85rem;
			margin-bottom: 0.25rem;
		}
		.media-item .media-info .primary-badge {
			display: inline-block;
			background: var(--blue);
			color: white;
			padding: 0.25rem 0.5rem;
			border-radius: 4px;
			font-size: 0.75rem;
			margin-bottom: 0.5rem;
		}
		.media-item .media-actions {
			margin-top: 0.5rem;
		}
		.media-item .media-actions a {
			color: var(--red);
			text-decoration: none;
			font-size: 0.85rem;
			display: inline-flex;
			align-items: center;
			gap: 0.25rem;
		}
		.media-item .media-actions a:hover {
			text-decoration: underline;
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

					<?php if ($message): ?>
						<div class="alert <?= $message_type === 'success' ? 'alert-success' : 'alert-error' ?>">
							<?= htmlspecialchars($message) ?>
						</div>
					<?php endif; ?>

					<form method="post" style="padding: 1.5rem;">
						<div class="form-group">
							<label for="name">Product Name *</label>
							<input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required>
						</div>

						<div class="form-group">
							<label for="category_id">Category</label>
							<select name="category_id" id="category_id">
								<option value="">-- None --</option>
								<?php foreach ($categories as $c): ?>
									<option value="<?= $c['category_id'] ?>" <?= $product['category_id'] == $c['category_id'] ? 'selected' : '' ?>>
										<?= htmlspecialchars($c['name']) ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group">
							<label for="description">Description</label>
							<textarea name="description" id="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
						</div>

						<div class="form-grid">
							<div class="form-group">
								<label for="price">Price (₹) *</label>
								<input type="number" step="0.01" name="price" id="price" value="<?= $product['price'] ?>" required>
							</div>

							<div class="form-group">
								<label for="stock">Stock Quantity</label>
								<input type="number" name="stock" id="stock" value="<?= $product['stock'] ?>" min="0">
							</div>
						</div>

						<div class="form-group-inline">
							<label for="is_active">
								<input type="checkbox" name="is_active" id="is_active" <?= $product['is_active'] ? 'checked' : '' ?>>
								Active
							</label>
							<label for="featured">
								<input type="checkbox" name="featured" id="featured" <?= $product['featured'] ? 'checked' : '' ?>>
								Featured
							</label>
						</div>

						<button type="submit" class="btn-submit">
							<i class='bx bxs-save' ></i>
							Update Product
						</button>
					</form>
				</div>
			</div>

			<?php if (!empty($mediaFiles)): ?>
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Product Media</h3>
						<i class='bx bxs-image' ></i>
					</div>
					<div style="padding: 1.5rem;">
						<div class="media-grid">
							<?php foreach ($mediaFiles as $m): ?>
							<div class="media-item">
								<?php if ($m['file_type'] === 'image'): ?>
									<img src="../../uploads/<?= htmlspecialchars($m['file_path']) ?>" alt="<?= htmlspecialchars($m['alt_text']) ?>">
								<?php else: ?>
									<video controls>
										<source src="../../uploads/<?= htmlspecialchars($m['file_path']) ?>" type="video/<?= pathinfo($m['file_path'], PATHINFO_EXTENSION) ?>">
									</video>
								<?php endif; ?>
								<div class="media-info">
									<?php if ($m['is_primary']): ?>
										<span class="primary-badge">Primary</span>
									<?php endif; ?>
									<small><?= htmlspecialchars($m['alt_text']) ?></small>
									<div class="media-actions">
										<a href="delete_media.php?id=<?= $m['media_id'] ?>&product_id=<?= $id ?>" 
										   onclick="return confirm('Delete this file permanently?')">
											<i class='bx bx-trash' ></i> Delete
										</a>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</main>
	</section>
	
	<script src="../admin-script.js"></script>
</body>
</html>
