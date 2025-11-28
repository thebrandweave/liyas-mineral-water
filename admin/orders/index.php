<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

/**
 * Convert database timestamp to IST format
 */
function formatIST($dbTimestamp) {
	if (empty($dbTimestamp)) return '';
	try {
		$dt = new DateTime($dbTimestamp);
		$dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
		return $dt->format('d-m-Y H:i:s');
	} catch (Exception $e) {
		return date('d-m-Y H:i:s', strtotime($dbTimestamp));
	}
}

/**
 * Format currency
 */
function formatCurrency($amount) {
	return '₹' . number_format((float)$amount, 2);
}

/**
 * Build WHERE clause + params based on filter/search
 */
function buildFilterWhereClause($filter, $search, &$params) {
	$where_conditions = [];
	$params = [];

	if ($filter !== 'all' && in_array($filter, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
		$where_conditions[] = "status = :status";
		$params[':status'] = $filter;
	}

	if (!empty($search)) {
		$where_conditions[] = "(customer_name LIKE :search OR customer_email LIKE :search OR customer_phone LIKE :search OR order_id = :order_id)";
		$params[':search'] = "%$search%";
		$params[':order_id'] = is_numeric($search) ? (int)$search : -1;
	}

	return !empty($where_conditions)
		? "WHERE " . implode(" AND ", $where_conditions)
		: "";
}

/**
 * Bind params to prepared statement
 */
function bindFilterParams(PDOStatement $stmt, array $params) {
	foreach ($params as $key => $value) {
		$stmt->bindValue($key, $value);
	}
}

/**
 * Build redirect URL preserving filter/search/page
 */
function buildRedirectUrl($filter, $search, $page = 1, $updated = null) {
	$redirect_url = "index.php?filter=" . urlencode($filter);
	if (!empty($search)) {
		$redirect_url .= "&search=" . urlencode($search);
	}
	if ($page > 1) {
		$redirect_url .= "&page=" . (int)$page;
	}
	if ($updated !== null) {
		$redirect_url .= "&updated=" . (int)$updated;
	}
	return $redirect_url;
}

// Read filter/search/page from request
$filter = $_GET['filter'] ?? $_POST['filter'] ?? 'all';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$page   = isset($_GET['page']) ? (int)$_GET['page'] : (isset($_POST['page']) ? (int)$_POST['page'] : 1);
$per_page = 50;
$offset   = ($page - 1) * $per_page;

// Build where + params once and reuse everywhere
$params = [];
$where_clause = buildFilterWhereClause($filter, $search, $params);

// CSV EXPORT
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
	try {
		$query = "SELECT order_id, customer_name, customer_email, customer_phone, 
						 shipping_address, total_amount, status, created_at, updated_at
				  FROM orders
				  $where_clause
				  ORDER BY created_at DESC";

		$stmt = $pdo->prepare($query);
		if (!empty($params)) {
			bindFilterParams($stmt, $params);
		}
		$stmt->execute();
		$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		die("Error fetching orders: " . $e->getMessage());
	}

	if (empty($orders)) {
		die("No orders found to export with the current filters.");
	}

	$filter_name = ucfirst($filter);
	$filename = "Orders_{$filter_name}_" . date('Y-m-d_His') . ".csv";

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Pragma: no-cache');
	header('Expires: 0');

	$output = fopen('php://output', 'w');
	fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

	$headers = [
		'Order ID',
		'Customer Name',
		'Email',
		'Phone',
		'Shipping Address',
		'Total Amount',
		'Status',
		'Created At',
		'Updated At'
	];
	fputcsv($output, $headers);

	foreach ($orders as $order) {
		$row = [
			$order['order_id'],
			$order['customer_name'],
			$order['customer_email'] ?? '',
			$order['customer_phone'] ?? '',
			$order['shipping_address'],
			$order['total_amount'],
			ucfirst($order['status']),
			formatIST($order['created_at']),
			formatIST($order['updated_at'])
		];
		fputcsv($output, $row);
	}

	fclose($output);
	exit;
}

// Handle status update
$update_message = '';
$update_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
	$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
	$new_status = isset($_POST['status']) ? $_POST['status'] : '';

	if ($order_id > 0 && in_array($new_status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
		try {
			$updateStmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
			$updateStmt->execute([$new_status, $order_id]);
			$updated_count = $updateStmt->rowCount();

			if ($updated_count > 0) {
				header("Location: " . buildRedirectUrl($filter, $search, $page, $order_id));
				exit;
			} else {
				$update_message = "Order not found or already has this status.";
				$update_type = 'error';
			}
		} catch (PDOException $e) {
			$update_message = "Error updating order status: " . $e->getMessage();
			$update_type = 'error';
			error_log("Status update error: " . $e->getMessage());
		}
	} else {
		$update_message = 'Invalid order ID or status.';
		$update_type = 'error';
	}
}

// Message from redirect
if (isset($_GET['updated'])) {
	$update_message = "Order status updated successfully!";
	$update_type = 'success';
}

// Stats (global)
try {
	$stats_query = "SELECT 
		COUNT(*) as total_orders,
		SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
		SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
		SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
		SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
		SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
		SUM(total_amount) as total_revenue
	FROM orders";
	$stats_result = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);
	$total_orders = (int)($stats_result['total_orders'] ?? 0);
	$pending_orders = (int)($stats_result['pending_orders'] ?? 0);
	$processing_orders = (int)($stats_result['processing_orders'] ?? 0);
	$shipped_orders = (int)($stats_result['shipped_orders'] ?? 0);
	$delivered_orders = (int)($stats_result['delivered_orders'] ?? 0);
	$cancelled_orders = (int)($stats_result['cancelled_orders'] ?? 0);
	$total_revenue = (float)($stats_result['total_revenue'] ?? 0);
} catch (PDOException $e) {
	$total_orders = $pending_orders = $processing_orders = $shipped_orders = $delivered_orders = $cancelled_orders = 0;
	$total_revenue = 0;
	error_log("Statistics query error: " . $e->getMessage());
}

// Count filtered records (for pagination)
try {
	$count_query = "SELECT COUNT(*) FROM orders $where_clause";
	$count_stmt = $pdo->prepare($count_query);
	if (!empty($params)) {
		bindFilterParams($count_stmt, $params);
	}
	$count_stmt->execute();
	$total_records = (int)$count_stmt->fetchColumn();
	$total_pages = $per_page > 0 ? ceil($total_records / $per_page) : 1;
} catch (PDOException $e) {
	$total_records = 0;
	$total_pages = 0;
	error_log("Order count error: " . $e->getMessage());
}

// Fetch paginated orders
try {
	$query = "SELECT order_id, customer_name, customer_email, customer_phone, 
					 shipping_address, total_amount, status, created_at, updated_at
			  FROM orders
			  $where_clause
			  ORDER BY created_at DESC
			  LIMIT :limit OFFSET :offset";
	$stmt = $pdo->prepare($query);
	if (!empty($params)) {
		bindFilterParams($stmt, $params);
	}
	$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
	$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
	$stmt->execute();
	$orders = $stmt->fetchAll();
} catch (PDOException $e) {
	$orders = [];
	error_log("Orders fetch error: " . $e->getMessage());
}

// Basic setup
$admin_name   = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "orders";
$page_title   = "Orders";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<!-- Favicon -->
	<link rel="icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="shortcut icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="apple-touch-icon" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="32x32" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="16x16" href="../../assets/images/logo/logo-bg.jpg">
	
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/admin-style.css">
	<title>Orders - Admin Panel</title>
	<style>
		.modal-overlay {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.45);
			backdrop-filter: blur(5px);
			-webkit-backdrop-filter: blur(5px);
			z-index: 10000;
			align-items: center;
			justify-content: center;
			animation: fadeIn 0.2s ease-out;
		}
		.modal-overlay.active { display: flex; }
		@keyframes fadeIn {
			from { opacity: 0; }
			to   { opacity: 1; }
		}
		.modal-dialog {
			background: white;
			border-radius: 16px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
			max-width: 480px;
			width: 90%;
			max-height: 90vh;
			overflow-y: auto;
			animation: slideUp 0.25s ease-out;
			position: relative;
			z-index: 10001;
		}
		@keyframes slideUp {
			from { opacity: 0; transform: translateY(30px) scale(0.95); }
			to   { opacity: 1; transform: translateY(0) scale(1); }
		}
		.modal-header {
			padding: 1.25rem 1.5rem;
			border-bottom: 1px solid var(--grey);
			display: flex;
			align-items: center;
			justify-content: space-between;
		}
		.modal-header h3 {
			font-size: 1.1rem;
			font-weight: 600;
			color: var(--dark);
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}
		.modal-header .close-btn {
			background: none;
			border: none;
			font-size: 1.5rem;
			color: var(--dark-grey);
			cursor: pointer;
			width: 32px;
			height: 32px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 8px;
			transition: all 0.15s;
		}
		.modal-header .close-btn:hover {
			background: var(--grey);
			color: var(--dark);
		}
		.modal-body {
			padding: 1.25rem 1.5rem 0.75rem;
			text-align: center;
		}
		.modal-body p {
			color: var(--dark);
			font-size: 0.95rem;
			line-height: 1.5;
			margin-bottom: 0.75rem;
		}
		.modal-footer {
			padding: 0.9rem 1.5rem 1.25rem;
			border-top: 1px solid var(--grey);
			display: flex;
			gap: 0.75rem;
			justify-content: flex-end;
		}
		.modal-btn {
			padding: 0.6rem 1.3rem;
			border: none;
			border-radius: 999px;
			font-size: 0.9rem;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.15s;
			font-family: var(--opensans);
			display: inline-flex;
			align-items: center;
			gap: 0.45rem;
		}
		.modal-btn-cancel {
			background: var(--grey);
			color: var(--dark);
		}
		.modal-btn-cancel:hover {
			background: var(--dark-grey);
			color: #fff;
		}
		.modal-btn-primary {
			background: var(--blue);
			color: white;
		}
		.modal-btn-primary:hover {
			background: #2563eb;
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
		}
		body.modal-active {
			overflow: hidden;
		}
		body.modal-active #content {
			filter: blur(2px);
			transition: filter 0.2s;
		}
		.alert {
			margin: 1rem 0;
			padding: 0.75rem 1rem;
			border-radius: 8px;
			font-size: 0.95rem;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}
		.alert-success {
			background: #dcfce7;
			color: #166534;
			border: 1px solid #bbf7d0;
		}
		.alert-error {
			background: #fee2e2;
			color: #b91c1c;
			border: 1px solid #fecaca;
		}
		.status {
			padding: 0.4rem 0.8rem;
			border-radius: 999px;
			font-size: 0.85rem;
			font-weight: 600;
			display: inline-block;
		}
		.status.pending { background: #fef3c7; color: #92400e; }
		.status.processing { background: #dbeafe; color: #1e40af; }
		.status.shipped { background: #e0e7ff; color: #3730a3; }
		.status.delivered { background: #d1fae5; color: #065f46; }
		.status.cancelled { background: #fee2e2; color: #991b1b; }
	</style>
</head>
<body>
	<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

	<section id="content">
		<nav>
			<i class='bx bx-menu bx-sm'></i>
			<a href="#" class="nav-link"><?= $page_title ?></a>
			<form action="index.php" method="GET">
				<div class="form-input">
					<input type="search" name="search" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
					<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification">
				<i class='bx bxs-bell bx-tada-hover'></i>
				<span class="num"><?= $pending_orders ?></span>
			</a>
			<a href="#" class="profile">
				<i class='bx bx-user-circle' style="font-size: 2rem; color: var(--dark-grey);"></i>
			</a>
		</nav>

		<main>
			<div class="head-title">
				<div class="left">
					<h1>Orders</h1>
					<ul class="breadcrumb">
						<li><a href="../index.php">Dashboard</a></li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li><a class="active" href="#">Orders</a></li>
					</ul>
				</div>
			</div>

			<?php if (!empty($update_message)): ?>
				<div class="alert <?= $update_type === 'success' ? 'alert-success' : 'alert-error' ?>">
					<i class='bx <?= $update_type === 'success' ? 'bx-check-circle' : 'bx-error-circle' ?>'></i>
					<span><?= htmlspecialchars($update_message) ?></span>
				</div>
			<?php endif; ?>

			<ul class="box-info">
				<li>
					<i class='bx bxs-cart-alt'></i>
					<span class="text">
						<h3><?= number_format($total_orders) ?></h3>
						<p>Total Orders</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-time'></i>
					<span class="text">
						<h3><?= number_format($pending_orders) ?></h3>
						<p>Pending</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-package'></i>
					<span class="text">
						<h3><?= number_format($processing_orders) ?></h3>
						<p>Processing</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-truck'></i>
					<span class="text">
						<h3><?= number_format($shipped_orders) ?></h3>
						<p>Shipped</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-check-circle'></i>
					<span class="text">
						<h3><?= number_format($delivered_orders) ?></h3>
						<p>Delivered</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-wallet'></i>
					<span class="text">
						<h3><?= formatCurrency($total_revenue) ?></h3>
						<p>Total Revenue</p>
					</span>
				</li>
			</ul>

			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Filter Orders</h3>
						<i class='bx bx-filter'></i>
					</div>
					<div style="padding: 1rem;">
						<form method="GET" action="" id="filterForm" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
							<input type="hidden" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>">
							<select name="filter" id="filterSelect" style="padding: 0.5rem 1rem; border: 1px solid var(--grey); border-radius: 8px; background: var(--light); font-family: var(--opensans);">
								<option value="all"   <?= $filter === 'all'   ? 'selected' : '' ?>>All Orders</option>
								<option value="pending"  <?= $filter === 'pending'  ? 'selected' : '' ?>>Pending</option>
								<option value="processing"<?= $filter === 'processing'? 'selected' : '' ?>>Processing</option>
								<option value="shipped"<?= $filter === 'shipped'? 'selected' : '' ?>>Shipped</option>
								<option value="delivered"<?= $filter === 'delivered'? 'selected' : '' ?>>Delivered</option>
								<option value="cancelled"<?= $filter === 'cancelled'? 'selected' : '' ?>>Cancelled</option>
							</select>
							<button type="submit" style="padding: 0.5rem 1.5rem; background: var(--blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-family: var(--opensans);">
								<i class='bx bx-filter'></i> Apply Filter
							</button>
							<button type="button" id="exportBtn"
							   style="padding: 0.5rem 1.5rem; background: var(--green); color: white; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; font-family: var(--opensans);"
							   title="Export filtered results to Excel">
								<i class='bx bx-download'></i> Export Excel
							</button>
							<?php if ($filter !== 'all' || !empty($search)): ?>
								<a href="index.php" style="padding: 0.5rem 1.5rem; background: var(--dark-grey); color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
									<i class='bx bx-x'></i> Clear
								</a>
							<?php endif; ?>
						</form>
					</div>
				</div>
			</div>

			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Orders (<?= number_format($total_records) ?> total)</h3>
					</div>

					<?php if (empty($orders)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bxs-cart-alt' style="font-size: 4rem; color: var(--dark-grey); margin-bottom: 1rem;"></i>
							<p style="color: var(--dark-grey); font-size: 1.1rem;">No orders found</p>
						</div>
					<?php else: ?>
						<table>
							<thead>
								<tr>
									<th>Order ID</th>
									<th>Customer</th>
									<th>Contact</th>
									<th>Shipping Address</th>
									<th>Amount</th>
									<th>Status</th>
									<th>Created At</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($orders as $order): ?>
								<tr>
									<td><p><strong>#<?= $order['order_id'] ?></strong></p></td>
									<td>
										<p style="font-weight: 600;"><?= htmlspecialchars($order['customer_name']) ?></p>
										<?php if ($order['customer_email']): ?>
											<p style="color: var(--dark-grey); font-size: 0.85rem;"><?= htmlspecialchars($order['customer_email']) ?></p>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($order['customer_phone']): ?>
											<p><?= htmlspecialchars($order['customer_phone']) ?></p>
										<?php else: ?>
											<p style="color: var(--dark-grey);">—</p>
										<?php endif; ?>
									</td>
									<td>
										<p style="max-width: 250px; word-break: break-word; font-size: 0.9rem;">
											<?= htmlspecialchars($order['shipping_address']) ?>
										</p>
									</td>
									<td>
										<p style="font-weight: 600; color: var(--green);"><?= formatCurrency($order['total_amount']) ?></p>
									</td>
									<td>
										<span class="status <?= htmlspecialchars($order['status']) ?>">
											<?= ucfirst($order['status']) ?>
										</span>
									</td>
									<td>
										<p><?= formatIST($order['created_at']) ?> <small style="color: var(--dark-grey); font-size: 0.85rem;">IST</small></p>
									</td>
									<td>
										<div style="display: flex; gap: 0.5rem; align-items: center;">
											<a href="view.php?id=<?= $order['order_id'] ?>" 
											   style="background: var(--blue); color: white; padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.3rem;"
											   title="View Details">
												<i class='bx bx-show'></i> View
											</a>
											<button 
												type="button"
												onclick="openStatusModal(<?= $order['order_id'] ?>, '<?= htmlspecialchars($order['status'], ENT_QUOTES) ?>')" 
												title="Update Status"
												style="background: var(--green); color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.3rem;">
												<i class='bx bx-edit'></i> Status
											</button>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<?php if ($total_pages > 1): ?>
						<div style="padding: 1.5rem; border-top: 1px solid var(--grey); display: flex; justify-content: center; gap: 0.5rem; align-items: center;">
							<?php if ($page > 1): ?>
								<a href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--opensans);">
									<i class='bx bx-chevron-left'></i> Previous
								</a>
							<?php endif; ?>
							
							<span style="padding: 0.5rem 1rem; color: var(--dark); font-family: var(--opensans);">
								Page <?= $page ?> of <?= $total_pages ?>
							</span>
							
							<?php if ($page < $total_pages): ?>
								<a href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--opensans);">
									Next <i class='bx bx-chevron-right'></i>
								</a>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</main>
	</section>

	<!-- Status Update Modal -->
	<div id="statusModal" class="modal-overlay">
		<div class="modal-dialog">
			<div class="modal-header">
				<h3>
					<i class='bx bx-edit' style="color: var(--blue);"></i>
					Update Order Status
				</h3>
				<button type="button" class="close-btn" onclick="closeStatusModal()">
					<i class='bx bx-x'></i>
				</button>
			</div>
			<div class="modal-body">
				<form method="POST" action="" id="statusForm">
					<input type="hidden" name="order_id" id="statusOrderId">
					<input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
					<input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
					<input type="hidden" name="page" value="<?= $page ?>">
					
					<label for="statusSelect" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark);">Select Status:</label>
					<select name="status" id="statusSelect" style="width: 100%; padding: 0.75rem; border: 1px solid var(--grey); border-radius: 8px; font-family: var(--opensans); font-size: 0.95rem; margin-bottom: 1rem;">
						<option value="pending">Pending</option>
						<option value="processing">Processing</option>
						<option value="shipped">Shipped</option>
						<option value="delivered">Delivered</option>
						<option value="cancelled">Cancelled</option>
					</select>
					
					<p style="font-size: 0.85rem; color: #6b7280; margin-top: 0.5rem;">
						This will update the order status immediately.
					</p>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="modal-btn modal-btn-cancel" onclick="closeStatusModal()">
					<i class='bx bx-x'></i> Cancel
				</button>
				<button type="button" class="modal-btn modal-btn-primary" onclick="submitStatusForm()">
					<i class='bx bx-check'></i> Update Status
				</button>
			</div>
		</div>
	</div>
	
	<script src="../assets/js/admin-script.js"></script>
	<script>
		document.getElementById('exportBtn').addEventListener('click', function(e) {
			e.preventDefault();
			const filterSelect = document.getElementById('filterSelect');
			const searchInput = document.getElementById('searchInput');
			let filter = filterSelect ? filterSelect.value : 'all';
			let search = searchInput ? searchInput.value : '';
			let exportUrl = '?export=csv&filter=' + encodeURIComponent(filter);
			if (search) {
				exportUrl += '&search=' + encodeURIComponent(search);
			}
			window.location.href = exportUrl;
		});

		function openStatusModal(orderId, currentStatus) {
			const modal = document.getElementById('statusModal');
			const orderIdInput = document.getElementById('statusOrderId');
			const statusSelect = document.getElementById('statusSelect');
			
			if (orderIdInput) orderIdInput.value = orderId;
			if (statusSelect) statusSelect.value = currentStatus;
			if (modal) {
				modal.classList.add('active');
				document.body.classList.add('modal-active');
			}
		}

		function closeStatusModal() {
			const modal = document.getElementById('statusModal');
			if (modal) {
				modal.classList.remove('active');
				document.body.classList.remove('modal-active');
			}
		}

		function submitStatusForm() {
			const form = document.getElementById('statusForm');
			if (form) {
				const statusInput = document.createElement('input');
				statusInput.type = 'hidden';
				statusInput.name = 'update_status';
				statusInput.value = '1';
				form.appendChild(statusInput);
				form.submit();
			}
		}

		const statusModalOverlay = document.getElementById('statusModal');
		if (statusModalOverlay) {
			statusModalOverlay.addEventListener('click', function(e) {
				if (e.target === statusModalOverlay) {
					closeStatusModal();
				}
			});
		}

		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				const statusModal = document.getElementById('statusModal');
				if (statusModal && statusModal.classList.contains('active')) {
					closeStatusModal();
				}
			}
		});
	</script>
</body>
</html>

