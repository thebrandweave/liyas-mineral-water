<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// Fetch products with categories
try {
    $stmt = $pdo->query("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    error_log("Products fetch error: " . $e->getMessage());
}

// Get statistics
try {
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $active_products = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
    $low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn();
    $featured_products = $pdo->query("SELECT COUNT(*) FROM products WHERE featured = 1")->fetchColumn();
} catch (PDOException $e) {
    $total_products = $active_products = $low_stock = $featured_products = 0;
}

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Products";
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
	<title>Products - Admin Panel</title>
	<style>
		/* --- ANIMATED BUTTON STYLES (STABLE) --- */
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
			overflow: hidden;
			position: relative;
			text-decoration: none !important;
		}

		/* CRITICAL FIX: Prevent mouse events on children to stop flickering */
		.button .svgIcon, 
		.button::before {
			pointer-events: none; 
		}

		.svgIcon { width: 17px; transition-duration: .3s; }
		.svgIcon path { fill: white; }

		.button:hover {
			width: 140px; /* Width when expanded */
			border-radius: 50px;
			transition-duration: .3s;
			align-items: center;
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
		}

		.button:hover::before {
			font-size: 13px;
			opacity: 1;
			transform: translateY(30px);
			transition-duration: .3s;
		}

		/* Table Actions */
		.table-edit-btn:hover { background-color: #3b82f6; } /* Blue */
		.table-edit-btn::before { content: "Edit"; }
		
		.table-delete-btn:hover { background-color: #ef4444; } /* Red */
		.table-delete-btn::before { content: "Delete"; }
		.table-delete-btn .svgIcon { width: 12px; }

		/* --- TABLE LAYOUT STABILITY --- */
		.actions { display: flex; gap: 0.5rem; align-items: center; }

		/* CRITICAL FIX: Fixed width for Action column prevents table jumping */
		.action-column {
			min-width: 180px; 
			width: 180px;
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
					<input type="search" name="search" placeholder="Search products..." value="">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification">
				<i class='bx bxs-bell bx-tada-hover' ></i>
				<span class="num"><?= $low_stock ?></span>
			</a>
			<a href="#" class="profile">
				<img src="https://i.pravatar.cc/36?u=<?= urlencode($admin_name) ?>" alt="Profile">
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
				<a href="add.php" class="btn-download" title="Add new product">
					<i class='bx bxs-plus-circle'></i>
					<span class="text">Add Product</span>
				</a>
			</div>

			<!-- Statistics Cards -->
			<ul class="box-info">
				<li>
					<i class='bx bxs-box' ></i>
					<span class="text">
						<h3><?= number_format($total_products) ?></h3>
						<p>Total Products</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-check-circle' ></i>
					<span class="text">
						<h3><?= number_format($active_products) ?></h3>
						<p>Active</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-star' ></i>
					<span class="text">
						<h3><?= number_format($featured_products) ?></h3>
						<p>Featured</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-error-circle' ></i>
					<span class="text">
						<h3><?= number_format($low_stock) ?></h3>
						<p>Low Stock</p>
					</span>
				</li>
			</ul>

			<!-- Products Table -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>All Products (<?= number_format($total_products) ?> total)</h3>
						<i class='bx bx-search' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<?php if (empty($products)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bxs-box' style="font-size: 4rem; color: var(--dark-grey); margin-bottom: 1rem;"></i>
							<p style="color: var(--dark-grey); font-size: 1.1rem; margin-bottom: 0.5rem;">No products found</p>
							<p style="color: var(--dark-grey); font-size: 0.9rem; margin-top: 0.5rem;">
								<a href="add.php" style="color: var(--blue);">Add your first product</a>
							</p>
						</div>
					<?php else: ?>
						<table>
							<thead>
								<tr>
									<th>ID</th>
									<th>Name</th>
									<th>Category</th>
									<th>Price</th>
									<th>Stock</th>
									<th>Status</th>
									<th>Featured</th>
									<th class="action-column">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($products as $p): ?>
								<tr>
									<td>
										<p><?= $p['product_id'] ?></p>
									</td>
									<td>
										<p style="font-weight: 600;"><?= htmlspecialchars($p['name']) ?></p>
									</td>
									<td>
										<p><?= htmlspecialchars($p['category_name'] ?? '—') ?></p>
									</td>
									<td>
										<p style="font-weight: 600; color: var(--blue);">₹<?= number_format($p['price'], 2) ?></p>
									</td>
									<td>
										<p style="<?= $p['stock'] < 10 ? 'color: var(--red); font-weight: 600;' : '' ?>">
											<?= $p['stock'] ?>
										</p>
									</td>
									<td>
										<?php if ($p['is_active']): ?>
											<span class="status completed">Active</span>
										<?php else: ?>
											<span class="status pending">Inactive</span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($p['featured']): ?>
											<span class="status completed">⭐ Featured</span>
										<?php else: ?>
											<span style="color: var(--dark-grey);">—</span>
										<?php endif; ?>
									</td>
									<td class="actions action-column">
										<a href="edit.php?id=<?= $p['product_id'] ?>" class="button table-edit-btn" title="Edit Product">
											<svg class="svgIcon" viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.8-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"></path></svg>
										</a>
										
										<a href="delete.php?id=<?= $p['product_id'] ?>" 
										   class="button table-delete-btn" 
										   title="Delete Product"
										   onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
											<svg class="svgIcon" viewBox="0 0 448 512"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
										</a>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->
	
	<script src="../admin-script.js"></script>
</body>
</html>
