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

// Get order ID
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
	header("Location: index.php");
	exit;
}

// Fetch order details
try {
	$order_query = "SELECT order_id, customer_name, customer_email, customer_phone, 
						   shipping_address, total_amount, status, created_at, updated_at
					FROM orders
					WHERE order_id = ?";
	$order_stmt = $pdo->prepare($order_query);
	$order_stmt->execute([$order_id]);
	$order = $order_stmt->fetch(PDO::FETCH_ASSOC);
	
	if (!$order) {
		header("Location: index.php");
		exit;
	}
} catch (PDOException $e) {
	error_log("Order fetch error: " . $e->getMessage());
	header("Location: index.php");
	exit;
}

// Fetch order items
try {
	$items_query = "SELECT oi.item_id, oi.quantity, oi.price_at_purchase,
						   p.product_id, p.name as product_name, p.image as product_image
					FROM order_items oi
					LEFT JOIN products p ON oi.product_id = p.product_id
					WHERE oi.order_id = ?
					ORDER BY oi.item_id";
	$items_stmt = $pdo->prepare($items_query);
	$items_stmt->execute([$order_id]);
	$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	error_log("Order items fetch error: " . $e->getMessage());
	$order_items = [];
}

// Handle status update
$update_message = '';
$update_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
	$new_status = isset($_POST['status']) ? $_POST['status'] : '';
	
	if (in_array($new_status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
		try {
			$updateStmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
			$updateStmt->execute([$new_status, $order_id]);
			
			// Refresh order data
			$order_stmt->execute([$order_id]);
			$order = $order_stmt->fetch(PDO::FETCH_ASSOC);
			
			$update_message = "Order status updated successfully!";
			$update_type = 'success';
		} catch (PDOException $e) {
			$update_message = "Error updating order status: " . $e->getMessage();
			$update_type = 'error';
			error_log("Status update error: " . $e->getMessage());
		}
	} else {
		$update_message = 'Invalid status.';
		$update_type = 'error';
	}
}

// Basic setup
$admin_name   = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "orders";
$page_title   = "Order #" . $order['order_id'];
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
	
	<!-- Google Font: Poppins -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/prody-admin.css">
	<title>Order #<?= $order['order_id'] ?> - Liyas Admin</title>
	<style>
		.order-details-card {
			background: white;
			border-radius: 12px;
			padding: 1.5rem;
			margin-bottom: 1.5rem;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		}
		.order-details-card h3 {
			font-size: 1.1rem;
			font-weight: 600;
			color: var(--dark);
			margin-bottom: 1rem;
			padding-bottom: 0.75rem;
			border-bottom: 2px solid var(--grey);
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}
		.detail-row {
			display: grid;
			grid-template-columns: 150px 1fr;
			gap: 1rem;
			padding: 0.75rem 0;
			border-bottom: 1px solid var(--grey);
		}
		.detail-row:last-child {
			border-bottom: none;
		}
		.detail-label {
			font-weight: 600;
			color: var(--dark-grey);
			font-size: 0.9rem;
		}
		.detail-value {
			color: var(--dark);
			font-size: 0.95rem;
		}
		.status {
			padding: 0.4rem 0.8rem;
			border-radius: 999px;
			font-size: 0.85rem;
			font-weight: 600;
			display: inline-block;
		}
		.status.pending,
		.badge-pending { background: var(--yellow-light); color: #92400e; }
		.status.processing,
		.badge-processing { background: var(--blue-light); color: var(--blue-dark); }
		.status.shipped,
		.badge-shipped { background: #e0e7ff; color: #3730a3; }
		.status.delivered,
		.badge-completed { background: var(--green-light); color: #065f46; }
		.status.cancelled,
		.badge-cancelled { background: #fee2e2; color: #991b1b; }
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
		.product-item {
			display: flex;
			align-items: center;
			gap: 1rem;
			padding: 1rem;
			border-bottom: 1px solid var(--grey);
		}
		.product-item:last-child {
			border-bottom: none;
		}
		.product-image {
			width: 60px;
			height: 60px;
			object-fit: cover;
			border-radius: 8px;
			background: var(--light);
		}
		.product-info {
			flex: 1;
		}
		.product-name {
			font-weight: 600;
			color: var(--dark);
			margin-bottom: 0.25rem;
		}
		.product-meta {
			font-size: 0.85rem;
			color: var(--dark-grey);
		}
		.product-price {
			font-weight: 600;
			color: var(--green);
			text-align: right;
		}
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
	</style>
</head>
<body>
	<div class="container">
		<?php include '../includes/sidebar.php'; ?>
		
		<div class="main-content">
			<div class="header">
				<div class="breadcrumb">
					<i class='bx bx-home'></i>
					<span>Orders</span>
					<span>/</span>
					<span>Order #<?= $order['order_id'] ?></span>
				</div>
				<div class="header-actions">
					<a href="index.php" class="header-btn">
						<i class='bx bx-arrow-back'></i>
						<span>Back</span>
					</a>
				</div>
			</div>
			
			<div class="content-area">
				<?php if (!empty($update_message)): ?>
					<div class="alert <?= $update_type === 'success' ? 'alert-success' : 'alert-error' ?>">
						<?= htmlspecialchars($update_message) ?>
					</div>
				<?php endif; ?>

				<!-- Order Information -->
				<div class="form-card" style="margin-bottom: 1.5rem;">
					<div class="form-header">
						<h2>Order Information</h2>
					</div>
					<div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light);">
						<div style="font-weight: 600; color: var(--text-secondary); font-size: 14px;">Order ID:</div>
						<div><strong>#<?= $order['order_id'] ?></strong></div>
					</div>
					<div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light);">
						<div style="font-weight: 600; color: var(--text-secondary); font-size: 14px;">Status:</div>
						<div>
							<span class="badge badge-<?= htmlspecialchars($order['status']) ?>">
								<?= ucfirst($order['status']) ?>
							</span>
							<button type="button" onclick="openStatusModal()" class="btn" style="margin-left: 1rem; padding: 0.5rem 1rem; font-size: 13px;">
								<i class='bx bx-edit'></i> Update
							</button>
						</div>
					</div>
					<div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light);">
						<div style="font-weight: 600; color: var(--text-secondary); font-size: 14px;">Total Amount:</div>
						<div style="font-size: 1.2rem; font-weight: 600; color: var(--green);">
							<?= formatCurrency($order['total_amount']) ?>
						</div>
					</div>
					<div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light);">
						<div style="font-weight: 600; color: var(--text-secondary); font-size: 14px;">Created At:</div>
						<div><?= formatIST($order['created_at']) ?> <small style="color: var(--text-muted);">IST</small></div>
					</div>
					<div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0;">
						<div style="font-weight: 600; color: var(--text-secondary); font-size: 14px;">Last Updated:</div>
						<div><?= formatIST($order['updated_at']) ?> <small style="color: var(--text-muted);">IST</small></div>
					</div>
				</div>

				<!-- Customer Information -->
				<div class="form-card" style="margin-bottom: 1.5rem;">
					<div class="form-header">
						<h2>Customer Information</h2>
					</div>
					<div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light);">
						<div style="font-weight: 600; color: var(--text-secondary); font-size: 14px;">Name:</div>
						<div><?= htmlspecialchars($order['customer_name']) ?></div>
					</div>
					<?php if ($order['customer_email']): ?>
					<div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light);">
						<div style="font-weight: 600; color: var(--text-secondary); font-size: 14px;">Email:</div>
						<div><?= htmlspecialchars($order['customer_email']) ?></div>
					</div>
					<?php endif; ?>
					<?php if ($order['customer_phone']): ?>
					<div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light);">
						<div style="font-weight: 600; color: var(--text-secondary); font-size: 14px;">Phone:</div>
						<div><?= htmlspecialchars($order['customer_phone']) ?></div>
					</div>
					<?php endif; ?>
					<div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0;">
						<div style="font-weight: 600; color: var(--text-secondary); font-size: 14px;">Shipping Address:</div>
						<div style="white-space: pre-line;"><?= htmlspecialchars($order['shipping_address']) ?></div>
					</div>
				</div>

				<!-- Order Items -->
				<div class="table-card">
					<div class="table-header">
						<div class="table-title">
							Order Items (<?= count($order_items) ?>)
						</div>
					</div>
					
					<?php if (empty($order_items)): ?>
						<div style="padding: 2rem; text-align: center;">
							<p style="color: var(--text-secondary);">No items found in this order.</p>
						</div>
					<?php else: ?>
						<div style="padding: 1rem;">
							<?php 
							$subtotal = 0;
							foreach ($order_items as $item): 
								$item_total = (float)$item['price_at_purchase'] * (int)$item['quantity'];
								$subtotal += $item_total;
							?>
								<div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-light);">
									<?php if ($item['product_image']): ?>
										<img src="../../<?= htmlspecialchars($item['product_image']) ?>" 
											 alt="<?= htmlspecialchars($item['product_name'] ?? 'Product') ?>" 
											 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; background: var(--bg-main);"
											 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\'%3E%3Crect fill=\'%23e5e7eb\' width=\'60\' height=\'60\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-size=\'12\'%3ENo Image%3C/text%3E%3C/svg%3E';">
									<?php else: ?>
										<div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); background: var(--bg-main); border-radius: 8px;">
											<i class='bx bx-image' style="font-size: 1.5rem;"></i>
										</div>
									<?php endif; ?>
									<div style="flex: 1;">
										<div style="font-weight: 600; margin-bottom: 0.25rem;">
											<?= htmlspecialchars($item['product_name'] ?? 'Product #' . ($item['product_id'] ?? 'N/A')) ?>
										</div>
										<div style="font-size: 13px; color: var(--text-muted);">
											Quantity: <?= $item['quantity'] ?> × <?= formatCurrency($item['price_at_purchase']) ?>
										</div>
									</div>
									<div style="font-weight: 600; color: var(--green); text-align: right;">
										<?= formatCurrency($item_total) ?>
									</div>
								</div>
							<?php endforeach; ?>
							
							<div style="padding: 1rem; border-top: 2px solid var(--border-light); margin-top: 1rem;">
								<div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.1rem; font-weight: 600;">
									<span>Total Amount:</span>
									<span style="color: var(--green);"><?= formatCurrency($order['total_amount']) ?></span>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

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
					<input type="hidden" name="update_status" value="1">
					
					<label for="statusSelect" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark);">Select Status:</label>
					<select name="status" id="statusSelect" style="width: 100%; padding: 0.75rem; border: 1px solid var(--grey); border-radius: 8px; font-family: var(--opensans); font-size: 0.95rem; margin-bottom: 1rem;">
						<option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
						<option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
						<option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
						<option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
						<option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
	
	<script>
		function openStatusModal() {
			const modal = document.getElementById('statusModal');
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

