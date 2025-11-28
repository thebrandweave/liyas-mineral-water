<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Products";

// Check for error message from redirect
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Handle edit action - if page=edit is requested, redirect to edit.php with proper query string
if (isset($_GET['page']) && $_GET['page'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    header("Location: edit.php?id=" . $edit_id);
    exit;
}

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    try {
        // Check if product exists and get image path
        $checkStmt = $pdo->prepare("SELECT product_id, image FROM products WHERE product_id = ?");
        $checkStmt->execute([$product_id]);
        $product_data = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product_data) {
            // Delete product
            $deleteStmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
            $deleteStmt->execute([$product_id]);
            
            // Delete associated image file if it exists
            if (!empty($product_data['image']) && file_exists(__DIR__ . '/../' . $product_data['image'])) {
                unlink(__DIR__ . '/../' . $product_data['image']);
            }
            
            $success_message = "Product deleted successfully!";
        } else {
            $error_message = "Product not found!";
        }
    } catch (PDOException $e) {
        $error_message = "Error deleting product: " . $e->getMessage();
    }
}

// Search functionality
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
try {
    if (!empty($search)) {
        $count_query = "SELECT COUNT(*) FROM products WHERE name LIKE :search OR description LIKE :search";
        $count_stmt = $pdo->prepare($count_query);
        $count_stmt->bindValue(':search', "%$search%");
    } else {
        $count_query = "SELECT COUNT(*) FROM products";
        $count_stmt = $pdo->prepare($count_query);
    }
    
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
    error_log("Products count error: " . $e->getMessage());
}

// Fetch products
try {
    // Check if image column exists
    try {
        $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'image'");
        $hasImageColumn = $checkCol->rowCount() > 0;
    } catch (Exception $e) {
        $hasImageColumn = false;
    }
    
    // Build the base query
    if (!empty($search)) {
        if ($hasImageColumn) {
            $query = "SELECT p.product_id, p.name, p.description, p.price, p.created_at, p.image
                      FROM products p 
                      WHERE p.name LIKE :search OR p.description LIKE :search
                      ORDER BY p.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT p.product_id, p.name, p.description, p.price, p.created_at, NULL as image
                      FROM products p 
                      WHERE p.name LIKE :search OR p.description LIKE :search
                      ORDER BY p.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        }
    } else {
        if ($hasImageColumn) {
            $query = "SELECT p.product_id, p.name, p.description, p.price, p.created_at, p.image
                      FROM products p 
                      ORDER BY p.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT p.product_id, p.name, p.description, p.price, p.created_at, NULL as image
                      FROM products p 
                      ORDER BY p.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        }
    }
    
    $stmt = $pdo->prepare($query);
    
    // Only bind search parameters if they exist
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
    }
    
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    if (!isset($error_message)) {
        $error_message = "Error loading products: " . $e->getMessage();
    }
    error_log("Products fetch error: " . $e->getMessage());
}

// Get statistics
try {
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
} catch (PDOException $e) {
    $total_products = 0;
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
	
	<!-- Favicon -->
	<link rel="icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="shortcut icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="apple-touch-icon" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="32x32" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="16x16" href="../../assets/images/logo/logo-bg.jpg">
	
	<title>Products - Admin Panel</title>
	<style>
		.button {
			width: 50px;
			height: 50px;
			border-radius: 50%;
			background-color: rgb(20, 20, 20);
			border: none;
			font-weight: 600;
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.164);
			cursor: pointer;
			transition-duration: .3s;
			overflow: visible;
			position: relative;
			text-decoration: none !important;
			will-change: width, border-radius, background-color;
			backface-visibility: hidden;
			transform: translateZ(0);
			flex-shrink: 0;
			margin: 0;
		}

		/* CRITICAL: Prevent flickering by disabling pointer events on all children */
		.button *,
		.button::before {
			pointer-events: none !important; 
		}

		.svgIcon { 
			width: 17px; 
			transition-duration: .3s;
			pointer-events: none !important;
			will-change: width, transform;
			backface-visibility: hidden;
		}
		.svgIcon path { 
			fill: white; 
			pointer-events: none !important;
		}

		.button.action-btn.edit:hover {
			width: 100px;
			border-radius: 50px;
			transition-duration: .3s;
			align-items: center;
			background-color: #3b82f6;
			z-index: 10;
		}
		
		.button.action-btn.delete:hover {
			width: 110px;
			border-radius: 50px;
			transition-duration: .3s;
			align-items: center;
			background-color: #dc2626;
			z-index: 10;
		}
		
		.button.action-btn.add:hover {
			width: 140px;
			border-radius: 50px;
			transition-duration: .3s;
			align-items: center;
			background-color: #22c55e;
		}

		.button:hover .svgIcon {
			width: 20px;
			transition-duration: .3s;
			transform: translateY(60%);
		}

		.button::before {
			position: absolute;
			top: -20px;
			color: white;
			transition-duration: .3s;
			font-size: 2px;
			opacity: 0;
			pointer-events: none !important;
			will-change: font-size, opacity, transform;
			backface-visibility: hidden;
		}

		.button:hover::before {
			font-size: 13px;
			opacity: 1;
			transform: translateY(30px);
			transition-duration: .3s;
		}

		.action-btn.add:hover { background-color: #22c55e; }
		.action-btn.add::before { content: "Add Product"; }
		
		.action-btn.edit:hover { background-color: #3b82f6; }
		.action-btn.edit::before { content: "Edit"; }
		
		.action-btn.delete:hover { background-color: #dc2626; }
		.action-btn.delete::before { content: "Delete"; }

		.product-image {
			width: 50px;
			height: 50px;
			object-fit: cover;
			border-radius: 8px;
		}

		.alert {
			padding: 1rem;
			border-radius: 8px;
			margin-bottom: 1rem;
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

		/* Prevent table from shifting when buttons expand */
		table {
			table-layout: fixed;
			width: 100%;
		}
		
		table th:last-child,
		table td:last-child {
			width: 200px;
			min-width: 200px;
			max-width: 200px;
			overflow: visible;
			position: relative;
		}
		
		/* Fixed container for action buttons */
		.action-buttons-wrapper {
			position: relative;
			width: 200px;
			height: 50px;
			margin: 0 auto;
			overflow: visible;
		}
		
		.action-buttons {
			position: relative;
			display: flex;
			gap: 0.5rem;
			align-items: center;
			justify-content: center;
			width: 100%;
			height: 100%;
		}
		
		.action-buttons .button {
			position: relative;
			flex-shrink: 0;
		}
		
		/* Ensure buttons expand upward/outward without affecting layout */
		.action-buttons .button:hover {
			z-index: 100;
		}

	</style>
</head>
<body>
	<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu bx-sm' ></i>
			<a href="#" class="nav-link"><?= $page_title ?></a>
			<form action="index.php" method="GET">
				<div class="form-input">
					<input type="search" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
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
				<i class='bx bx-user-circle' style="font-size: 2rem; color: var(--dark-grey);"></i>
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Products</h1>
					<ul class="breadcrumb">
						<li>
							<a href="../index.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Products</a>
						</li>
					</ul>
				</div>
				<a href="add.php" class="button action-btn add" title="Add new product">
					<svg class="svgIcon" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"></path></svg>
				</a>
			</div>

			<!-- Messages -->
			<?php if (isset($success_message)): ?>
				<div class="alert alert-success">
					<?= htmlspecialchars($success_message) ?>
				</div>
			<?php endif; ?>
			<?php if (isset($error_message)): ?>
				<div class="alert alert-error">
					<?= htmlspecialchars($error_message) ?>
				</div>
			<?php endif; ?>

			<!-- Statistics Cards -->
			<ul class="box-info">
				<li>
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">
						<h3><?= number_format($total_products) ?></h3>
						<p>Total Products</p>
					</span>
				</li>
			</ul>

			<!-- Products Table -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Products (<?= number_format($total_records) ?> total)</h3>
						<i class='bx bx-search' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<?php if (empty($products)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bxs-shopping-bag-alt' style="font-size: 4rem; color: var(--dark-grey); margin-bottom: 1rem;"></i>
							<p style="color: var(--dark-grey); font-size: 1.1rem; margin-bottom: 0.5rem;">No products found</p>
							<p style="color: var(--dark-grey); font-size: 0.9rem;">Get started by <a href="add.php" style="color: var(--blue);">adding your first product</a></p>
						</div>
					<?php else: ?>
						<table>
							<thead>
								<tr>
									<th>Image</th>
									<th>Product Name</th>
									<th>Description</th>
									<th>Price</th>
									<th>Created</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($products as $product): ?>
								<tr>
									<td>
										<?php if (!empty($product['image'])): ?>
											<img src="../../<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
										<?php else: ?>
											<img src="https://via.placeholder.com/50?text=No+Image" alt="No image" class="product-image">
										<?php endif; ?>
									</td>
									<td>
										<p style="font-weight: 600;"><?= htmlspecialchars($product['name']) ?></p>
									</td>
									<td>
										<p style="color: var(--dark-grey); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
											<?= htmlspecialchars($product['description'] ?? 'No description') ?>
										</p>
									</td>
									<td>
										<p style="font-weight: 600; color: var(--green);"><?= number_format($product['price'], 2) ?></p>
									</td>
									<td>
										<p><?= date('d-m-Y', strtotime($product['created_at'])) ?></p>
									</td>
									<td>
										<div class="action-buttons-wrapper">
											<div class="action-buttons">
												<a href="javascript:void(0);" onclick="window.location.href='edit.php?id=<?= (int)$product['product_id'] ?>'; return false;" class="button action-btn edit" title="Edit Product">
													<svg class="svgIcon" viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"></path></svg>
												</a>
												<button 
													onclick="confirmDelete(<?= $product['product_id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>')" 
													class="button action-btn delete" 
													title="Delete Product"
												>
													<svg class="svgIcon" viewBox="0 0 448 512"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
												</button>
											</div>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<!-- Pagination -->
						<?php if ($total_pages > 1): ?>
						<div style="padding: 1.5rem; border-top: 1px solid var(--grey); display: flex; justify-content: center; gap: 0.5rem; align-items: center;">
							<?php if ($page > 1): ?>
								<a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--opensans);">
									<i class='bx bx-chevron-left' ></i> Previous
								</a>
							<?php endif; ?>
							
							<span style="padding: 0.5rem 1rem; color: var(--dark); font-family: var(--opensans);">
								Page <?= $page ?> of <?= $total_pages ?>
							</span>
							
							<?php if ($page < $total_pages): ?>
								<a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--opensans);">
									Next <i class='bx bx-chevron-right' ></i>
								</a>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->
	
	<script src="../assets/js/admin-script.js"></script>
	<script>
	function confirmDelete(productId, productName) {
		if (confirm(`Are you sure you want to delete "${productName}"?\n\nThis action cannot be undone!`)) {
			window.location.href = `index.php?delete=${productId}`;
		}
	}
	
	// Ensure edit links work properly
	document.addEventListener('DOMContentLoaded', function() {
		const editLinks = document.querySelectorAll('.btn-edit');
		editLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				// Allow the link to work normally
				console.log('Edit link clicked:', this.href);
				// Don't prevent default - let the link work
			});
			
			// Also check the href attribute
			console.log('Edit link href:', link.getAttribute('href'));
		});
	});
	</script>
</body>
</html>

