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
 * Build WHERE clause + params based on filter/search
 * @param string $filter
 * @param string $search
 * @param array  $params (by ref)
 * @return string WHERE clause (or empty string)
 */
function buildFilterWhereClause($filter, $search, &$params) {
	$where_conditions = [];
	$params = [];

	if ($filter === 'used') {
		$where_conditions[] = "is_used = 1";
	} elseif ($filter === 'unused') {
		$where_conditions[] = "is_used = 0";
	}

	if (!empty($search)) {
		$where_conditions[] = "reward_code LIKE :search";
		$params[':search'] = "%$search%";
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
 * Build redirect URL preserving filter/search/page and deleted count
 */
function buildRedirectUrl($filter, $search, $page = 1, $deletedCount = null) {
	$redirect_url = "index.php?filter=" . urlencode($filter);
	if (!empty($search)) {
		$redirect_url .= "&search=" . urlencode($search);
	}
	if ($page > 1) {
		$redirect_url .= "&page=" . (int)$page;
	}
	if ($deletedCount !== null) {
		$redirect_url .= "&deleted=" . (int)$deletedCount;
	}
	return $redirect_url;
}

// Read filter/search/page from request
$filter = $_GET['filter'] ?? $_POST['filter'] ?? 'all'; // all, used, unused
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$page   = isset($_GET['page']) ? (int)$_GET['page'] : (isset($_POST['page']) ? (int)$_POST['page'] : 1);
$per_page = 50;
$offset   = ($page - 1) * $per_page;

// Build where + params once and reuse everywhere
$params = [];
$where_clause = buildFilterWhereClause($filter, $search, $params);

// CSV EXPORT (uses same filter/search)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
	try {
		$query = "SELECT id, reward_code, is_used, used_at, created_at,
						 customer_name, customer_phone, customer_email, customer_address
				  FROM codes
				  $where_clause
				  ORDER BY created_at DESC";

		$stmt = $pdo->prepare($query);
		if (!empty($params)) {
			bindFilterParams($stmt, $params);
		}
		$stmt->execute();
		$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		die("Error fetching codes: " . $e->getMessage());
	}

	if (empty($codes)) {
		die("No codes found to export with the current filters.");
	}

	$filter_name = $filter === 'used' ? 'Redeemed' : ($filter === 'unused' ? 'Available' : 'All');
	$filename = "Reward_Codes_{$filter_name}_" . date('Y-m-d_His') . ".csv";

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Pragma: no-cache');
	header('Expires: 0');

	$output = fopen('php://output', 'w');
	fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

	$headers = [
		'ID',
		'Reward Code',
		'Status',
		'Customer Name',
		'Phone Number',
		'Email',
		'Address',
		'Redeemed At',
		'Created At'
	];
	fputcsv($output, $headers);

	foreach ($codes as $code) {
		$row = [
			$code['id'],
			$code['reward_code'],
			$code['is_used'] ? 'Redeemed' : 'Available',
			$code['customer_name'] ?? '',
			$code['customer_phone'] ?? '',
			$code['customer_email'] ?? '',
			$code['customer_address'] ?? '',
			$code['used_at'] ? formatIST($code['used_at']) : '',
			formatIST($code['created_at'])
		];
		fputcsv($output, $row);
	}

	fclose($output);
	exit;
}

// Handle delete actions
$delete_message = '';
$delete_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// SINGLE DELETE
	if (isset($_POST['delete_code'])) {
		$code_id = isset($_POST['code_id']) ? (int)$_POST['code_id'] : 0;

		if ($code_id > 0) {
			try {
				$deleteStmt = $pdo->prepare("DELETE FROM codes WHERE id = ?");
				$deleteStmt->execute([$code_id]);
				$deleted_count = $deleteStmt->rowCount();

				header("Location: " . buildRedirectUrl($filter, $search, $page, $deleted_count));
				exit;
			} catch (PDOException $e) {
				$delete_message = "Error deleting code: " . $e->getMessage();
				$delete_type = 'error';
				error_log("Single delete error: " . $e->getMessage());
			}
		} else {
			$delete_message = 'Invalid code ID.';
			$delete_type = 'error';
		}
	}
	// BULK DELETE (selected IDs on current page)
	elseif (isset($_POST['bulk_delete'])) {
		$selected_ids = isset($_POST['selected_ids']) && is_array($_POST['selected_ids']) ? $_POST['selected_ids'] : [];
		$ids = array();
		foreach ($selected_ids as $id) {
			$id = (int)$id;
			if ($id > 0) $ids[] = $id;
		}

		if (!empty($ids)) {
			try {
				$placeholders = implode(',', array_fill(0, count($ids), '?'));
				$sql = "DELETE FROM codes WHERE id IN ($placeholders)";
				$stmt = $pdo->prepare($sql);
				$stmt->execute($ids);
				$deleted_count = $stmt->rowCount();

				header("Location: " . buildRedirectUrl($filter, $search, $page, $deleted_count));
				exit;
			} catch (PDOException $e) {
				$delete_message = "Error deleting selected codes: " . $e->getMessage();
				$delete_type = 'error';
				error_log("Bulk delete error: " . $e->getMessage());
			}
		} else {
			$delete_message = 'No codes selected for deletion.';
			$delete_type = 'error';
		}
	}
	// DELETE ALL FILTERED (ALL PAGES)
	elseif (isset($_POST['delete_all_filtered'])) {
		try {
			$sql = "DELETE FROM codes " . $where_clause;
			$stmt = $pdo->prepare($sql);
			if (!empty($params)) {
				bindFilterParams($stmt, $params);
			}
			$stmt->execute();
			$deleted_count = $stmt->rowCount();

			header("Location: " . buildRedirectUrl($filter, $search, 1, $deleted_count));
			exit;
		} catch (PDOException $e) {
			$delete_message = "Error deleting filtered codes: " . $e->getMessage();
			$delete_type = 'error';
			error_log("Delete-all-filtered error: " . $e->getMessage());
		}
	}
}

// Message from redirect
if (isset($_GET['deleted'])) {
	$deleted_count = (int)$_GET['deleted'];
	if ($deleted_count > 0) {
		$delete_message = "Successfully deleted {$deleted_count} reward code(s)!";
		$delete_type = 'success';
	}
}

// Stats (global)
try {
	$stats_query = "SELECT 
		COUNT(*) as total_codes,
		SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as used_codes,
		SUM(CASE WHEN is_used = 0 THEN 1 ELSE 0 END) as unused_codes,
		SUM(CASE WHEN is_used = 1 AND used_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as recent_redeemed
	FROM codes";
	$stats_result = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);
	$total_codes = (int)($stats_result['total_codes'] ?? 0);
	$used_codes = (int)($stats_result['used_codes'] ?? 0);
	$unused_codes = (int)($stats_result['unused_codes'] ?? 0);
	$recent_redeemed = (int)($stats_result['recent_redeemed'] ?? 0);
} catch (PDOException $e) {
	$total_codes = $used_codes = $unused_codes = $recent_redeemed = 0;
	error_log("Statistics query error: " . $e->getMessage());
}

// Count filtered records (for pagination & delete-all display)
try {
	$count_query = "SELECT COUNT(*) FROM codes $where_clause";
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
	error_log("Code count error: " . $e->getMessage());
}

// Fetch paginated codes
try {
	$query = "SELECT id, reward_code, is_used, used_at, created_at,
					 customer_name, customer_phone, customer_email, customer_address
			  FROM codes
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
	$reward_codes = $stmt->fetchAll();
} catch (PDOException $e) {
	$reward_codes = [];
	error_log("Reward codes fetch error: " . $e->getMessage());
}

// Basic setup
$admin_name   = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "qr-rewards";
$page_title   = "Reward Codes";
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
	<title>Reward Codes - Liyas Admin</title>
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
			overflow: hidden;
			position: relative;
			text-decoration: none !important;
		}
		.button .svgIcon, 
		.button::before {
			pointer-events: none; 
		}
		.svgIcon { width: 17px; transition-duration: .3s; }
		.svgIcon path { fill: white; }
		.button:hover {
			width: 140px;
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
		.action-btn:hover { background-color: #22c55e; }
		.action-btn.add::before { content: "Generate Codes"; }

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
		.warning-icon {
			width: 60px;
			height: 60px;
			border-radius: 999px;
			background: #fef3c7;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 1rem;
			font-size: 2rem;
		}
		.warning-icon i { color: #f59e0b; }
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
		.modal-btn-delete {
			background: #dc2626;
			color: white;
		}
		.modal-btn-delete:hover {
			background: #b91c1c;
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
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
	</style>
</head>
<body>
	<div class="container">
		<?php include '../includes/sidebar.php'; ?>
		
		<div class="main-content">
			<div class="header">
				<div class="breadcrumb">
					<i class='bx bx-home'></i>
					<span>QR Rewards</span>
				</div>
				<div class="header-actions">
					<form action="index.php" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
						<input type="search" name="search" placeholder="Search reward codes..." value="<?= htmlspecialchars($search) ?>" style="padding: 0.5rem 0.75rem; border: 1px solid var(--border-light); border-radius: 6px; font-size: 14px; font-family: inherit;">
						<button type="submit" class="header-btn" style="padding: 0.5rem;">
							<i class='bx bx-search'></i>
						</button>
					</form>
				</div>
			</div>
			
			<div class="content-area">
				<?php if (!empty($delete_message)): ?>
					<div class="alert <?= $delete_type === 'success' ? 'alert-success' : 'alert-error' ?>">
						<?= htmlspecialchars($delete_message) ?>
					</div>
				<?php endif; ?>

				<div class="table-card" style="margin-bottom: 1.5rem;">
					<div class="table-header">
						<div class="table-title">Filter Reward Codes</div>
						<div class="table-actions">
							<form method="GET" action="" id="filterForm" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
								<input type="hidden" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>">
								<select name="filter" id="filterSelect" class="form-select" style="padding: 0.5rem 0.75rem; border: 1px solid var(--border-light); border-radius: 6px; font-size: 14px; font-family: inherit;">
									<option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Codes</option>
									<option value="used" <?= $filter === 'used' ? 'selected' : '' ?>>Redeemed Only</option>
									<option value="unused" <?= $filter === 'unused' ? 'selected' : '' ?>>Available Only</option>
								</select>
								<button type="submit" class="table-btn">
									<i class='bx bx-filter'></i> Apply
								</button>
								<button type="button" id="exportBtn" class="btn-action btn-add noselect">
									<span class="text">Export</span>
									<span class="icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
									</span>
								</button>
								<a href="generate.php" class="btn-action btn-add noselect">
									<span class="text">Generate</span>
									<span class="icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
									</span>
								</a>
								<?php if ($filter !== 'all' || !empty($search)): ?>
									<a href="index.php" class="table-btn">
										<i class='bx bx-x'></i> Clear
									</a>
								<?php endif; ?>
							</form>
						</div>
					</div>
				</div>

				<div class="table-card">
					<div class="table-header">
						<div class="table-title">
							All Reward Codes
							<i class='bx bx-chevron-down'></i>
						</div>
						<?php if (!empty($reward_codes)): ?>
						<div class="table-actions">
							<button type="button" id="deleteSelectedBtn" class="btn-action btn-delete noselect">
								<span class="text">Delete Selected</span>
								<span class="icon">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"></path></svg>
								</span>
							</button>
							<button type="button" id="deselectAllBtn" class="btn btn-secondary">
								<i class='bx bx-x-circle'></i> Deselect All
							</button>
						</div>
						<?php endif; ?>
					</div>
					
					<?php if (empty($reward_codes)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bx-qr' style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
							<p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 0.5rem;">No reward codes found</p>
							<p style="color: var(--text-muted); font-size: 0.9rem;">Get started by <a href="generate.php" style="color: var(--blue);">generating reward codes</a></p>
						</div>
					<?php else: ?>
						<table>
							<thead>
								<tr>
									<th style="width: 40px;"><input type="checkbox" id="selectAll" style="cursor:pointer;"></th>
									<th>ID</th>
									<th>Reward Code</th>
									<th>Status</th>
									<th>Customer</th>
									<th>Phone</th>
									<th>Redeemed At</th>
									<th>Created</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($reward_codes as $code): ?>
								<tr>
									<td>
										<input type="checkbox" class="row-checkbox" value="<?= $code['id'] ?>" style="width:16px;height:16px;cursor:pointer;">
									</td>
									<td><?= $code['id'] ?></td>
									<td>
										<strong style="font-family: monospace; color: var(--blue);">
											<?= htmlspecialchars($code['reward_code']) ?>
										</strong>
									</td>
									<td>
										<?php if ($code['is_used']): ?>
											<span class="badge badge-completed">Redeemed</span>
										<?php else: ?>
											<span class="badge badge-pending">Available</span>
										<?php endif; ?>
									</td>
									<td><?= htmlspecialchars($code['customer_name'] ?? '—') ?></td>
									<td><?= htmlspecialchars($code['customer_phone'] ?? '—') ?></td>
									<td>
										<?php if ($code['used_at']): ?>
											<span style="font-size: 13px;"><?= formatIST($code['used_at']) ?></span>
											<br><small style="color: var(--text-muted);">IST</small>
										<?php else: ?>
											<span style="color: var(--text-muted);">—</span>
										<?php endif; ?>
									</td>
									<td>
										<span style="font-size: 13px;"><?= formatIST($code['created_at']) ?></span>
										<br><small style="color: var(--text-muted);">IST</small>
									</td>
									<td style="white-space: nowrap;">
										<?php if ($code['customer_name']): ?>
										<button type="button" onclick="showCustomerDetails('<?= htmlspecialchars($code['customer_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($code['customer_phone'], ENT_QUOTES) ?>', '<?= htmlspecialchars($code['customer_email'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($code['customer_address'] ?? '', ENT_QUOTES) ?>')" class="btn-action btn-view noselect" style="margin-right: 0.5rem;">
											<span class="text">View</span>
											<span class="icon">
												<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
											</span>
										</button>
										<?php endif; ?>
										<form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this reward code?');">
											<input type="hidden" name="delete_code" value="1">
											<input type="hidden" name="code_id" value="<?= $code['id'] ?>">
											<input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
											<input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
											<input type="hidden" name="page" value="<?= $page ?>">
											<button type="submit" class="btn-action btn-delete noselect">
												<span class="text">Delete</span>
												<span class="icon">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"></path></svg>
												</span>
											</button>
										</form>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<?php if ($total_pages > 1): ?>
							<div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-light); display: flex; justify-content: center; gap: 0.75rem; align-items: center;">
								<?php if ($page > 1): ?>
									<a href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" class="table-btn">
										<i class='bx bx-chevron-left'></i> Previous
									</a>
								<?php endif; ?>
								
								<span style="padding: 0.5rem 1rem; color: var(--text-secondary);">
									Page <?= $page ?> of <?= $total_pages ?>
								</span>
								
								<?php if ($page < $total_pages): ?>
									<a href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" class="table-btn">
										Next <i class='bx bx-chevron-right'></i>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Customer Details Modal -->
	<div id="customerDetailsModal" class="modal-overlay">
		<div class="modal-dialog">
			<div class="modal-header">
				<h3>
					<i class='bx bx-user' style="color: var(--blue);"></i>
					Customer Details
				</h3>
				<button type="button" class="close-btn" onclick="closeCustomerDetailsModal()">
					<i class='bx bx-x'></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="warning-icon" style="background:#dbeafe;">
					<i class='bx bx-user' style="color:var(--blue);"></i>
				</div>
				<div id="customerDetailsContent" style="text-align: left;"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" onclick="closeCustomerDetailsModal()">
					<i class='bx bx-x'></i> Close
				</button>
			</div>
		</div>
	</div>

	<!-- Bulk Delete Confirm Modal -->
	<div id="bulkDeleteModal" class="modal-overlay">
		<div class="modal-dialog">
			<div class="modal-header">
				<h3>
					<i class='bx bx-trash' style="color:#dc2626;"></i>
					Confirm Deletion
				</h3>
				<button type="button" class="close-btn" id="bulkDeleteCloseIcon">
					<i class='bx bx-x'></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="warning-icon">
					<i class='bx bx-error'></i>
				</div>
				<p id="bulkDeleteMessage">
					You are about to delete selected codes. This action cannot be undone.
				</p>
				<p style="font-size:0.85rem; color:#6b7280; margin-top:0.25rem;">
					Please confirm to proceed.
				</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" id="bulkDeleteCancelBtn">
					<i class='bx bx-x'></i> Cancel
				</button>
				<button type="button" class="btn-action btn-delete noselect" id="bulkDeleteConfirmBtn">
					<span class="text">Delete</span>
					<span class="icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"></path></svg>
					</span>
				</button>
			</div>
		</div>
	</div>
	
	<script>
	function copyToClipboard(text) {
		navigator.clipboard.writeText(text).then(function() {
			const notification = document.querySelector('.notification');
			if (notification) {
				const originalNum = notification.querySelector('.num').textContent;
				notification.querySelector('.num').textContent = '✓';
				setTimeout(() => {
					notification.querySelector('.num').textContent = originalNum;
				}, 2000);
			}
		}, function() {
			const textarea = document.createElement('textarea');
			textarea.value = text;
			document.body.appendChild(textarea);
			textarea.select();
			document.execCommand('copy');
			document.body.removeChild(textarea);
			alert('Code copied to clipboard!');
		});
	}

	function showCustomerDetails(name, phone, email, address) {
		const modal = document.getElementById('customerDetailsModal');
		const content = document.getElementById('customerDetailsContent');
		let html = '';

		html += '<div class="customer-details-item">';
		html += '<div class="customer-details-label">Name</div>';
		html += '<div class="customer-details-value">' + escapeHtml(name) + '</div>';
		html += '</div>';

		html += '<div class="customer-details-item">';
		html += '<div class="customer-details-label">Phone</div>';
		html += '<div class="customer-details-value">' + escapeHtml(phone) + '</div>';
		html += '</div>';

		if (email) {
			html += '<div class="customer-details-item">';
			html += '<div class="customer-details-label">Email</div>';
			html += '<div class="customer-details-value">' + escapeHtml(email) + '</div>';
			html += '</div>';
		}
		if (address) {
			html += '<div class="customer-details-item">';
			html += '<div class="customer-details-label">Address</div>';
			html += '<div class="customer-details-value">' + escapeHtml(address) + '</div>';
			html += '</div>';
		}

		content.innerHTML = html;
		modal.classList.add('active');
		document.body.classList.add('modal-active');
	}

	function closeCustomerDetailsModal() {
		const modal = document.getElementById('customerDetailsModal');
		modal.classList.remove('active');
		document.body.classList.remove('modal-active');
	}

	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

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

	const customerModalOverlay = document.getElementById('customerDetailsModal');
	if (customerModalOverlay) {
		customerModalOverlay.addEventListener('click', function(e) {
			if (e.target === customerModalOverlay) {
				closeCustomerDetailsModal();
			}
		});
	}

	function updateSelectAllState() {
		const all = document.querySelectorAll('.row-checkbox');
		const checked = document.querySelectorAll('.row-checkbox:checked');
		const selectAll = document.getElementById('selectAll');
		if (!selectAll) return;
		if (all.length === 0) {
			selectAll.checked = false;
			selectAll.indeterminate = false;
			return;
		}
		selectAll.checked = (checked.length === all.length);
		selectAll.indeterminate = (checked.length > 0 && checked.length < all.length);
	}

	const selectAllCheckbox = document.getElementById('selectAll');
	if (selectAllCheckbox) {
		selectAllCheckbox.addEventListener('change', function() {
			const checkboxes = document.querySelectorAll('.row-checkbox');
			checkboxes.forEach(cb => { cb.checked = this.checked; });
			updateSelectAllState();
		});
	}

	document.addEventListener('change', function(e) {
		if (e.target.classList && e.target.classList.contains('row-checkbox')) {
			updateSelectAllState();
		}
	});

	const deselectAllBtn = document.getElementById('deselectAllBtn');
	if (deselectAllBtn) {
		deselectAllBtn.addEventListener('click', function() {
			const all = document.querySelectorAll('.row-checkbox');
			all.forEach(cb => { cb.checked = false; });
			const selectAll = document.getElementById('selectAll');
			if (selectAll) {
				selectAll.checked = false;
				selectAll.indeterminate = false;
			}
		});
	}

	let bulkDeleteMode = null; // "all" or "selected"
	let bulkSelectedIds = [];

	const bulkModal = document.getElementById('bulkDeleteModal');
	const bulkMessageEl = document.getElementById('bulkDeleteMessage');
	const bulkConfirmBtn = document.getElementById('bulkDeleteConfirmBtn');
	const bulkCancelBtn = document.getElementById('bulkDeleteCancelBtn');
	const bulkCloseIcon = document.getElementById('bulkDeleteCloseIcon');

	function openBulkDeleteModal(message) {
		if (bulkMessageEl) bulkMessageEl.textContent = message;
		if (bulkModal) bulkModal.classList.add('active');
		document.body.classList.add('modal-active');
	}

	function closeBulkDeleteModal() {
		if (bulkModal) bulkModal.classList.remove('active');
		document.body.classList.remove('modal-active');
		bulkDeleteMode = null;
		bulkSelectedIds = [];
	}

	if (bulkCancelBtn) bulkCancelBtn.addEventListener('click', closeBulkDeleteModal);
	if (bulkCloseIcon) bulkCloseIcon.addEventListener('click', closeBulkDeleteModal);
	if (bulkModal) {
		bulkModal.addEventListener('click', function(e) {
			if (e.target === bulkModal) closeBulkDeleteModal();
		});
	}

	const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
	if (deleteSelectedBtn) {
		deleteSelectedBtn.addEventListener('click', function() {
			const selectAll = document.getElementById('selectAll');
			const checked = document.querySelectorAll('.row-checkbox:checked');
			const totalFiltered = <?= (int)$total_records ?>;

			if ((!selectAll || !selectAll.checked) && checked.length === 0) {
				alert('No reward codes selected.');
				return;
			}

			if (selectAll && selectAll.checked && totalFiltered > 0) {
				bulkDeleteMode = 'all';
				bulkSelectedIds = [];
				openBulkDeleteModal('You are about to delete ALL filtered reward codes (' + totalFiltered + ' code(s)). This action cannot be undone.');
			} else {
				bulkDeleteMode = 'selected';
				bulkSelectedIds = Array.from(checked).map(cb => cb.value);
				openBulkDeleteModal('You are about to delete ' + bulkSelectedIds.length + ' selected reward code(s). This action cannot be undone.');
			}
		});
	}

	if (bulkConfirmBtn) {
		bulkConfirmBtn.addEventListener('click', function() {
			if (!bulkDeleteMode) {
				closeBulkDeleteModal();
				return;
			}

			const form = document.createElement('form');
			form.method = 'POST';
			form.action = '';

			const filterInput = document.createElement('input');
			filterInput.type = 'hidden';
			filterInput.name = 'filter';
			filterInput.value = '<?= htmlspecialchars($filter, ENT_QUOTES) ?>';
			form.appendChild(filterInput);

			const searchInput = document.createElement('input');
			searchInput.type = 'hidden';
			searchInput.name = 'search';
			searchInput.value = '<?= htmlspecialchars($search, ENT_QUOTES) ?>';
			form.appendChild(searchInput);

			const pageInput = document.createElement('input');
			pageInput.type = 'hidden';
			pageInput.name = 'page';
			pageInput.value = '<?= (int)$page ?>';
			form.appendChild(pageInput);

			if (bulkDeleteMode === 'all') {
				const allInput = document.createElement('input');
				allInput.type = 'hidden';
				allInput.name = 'delete_all_filtered';
				allInput.value = '1';
				form.appendChild(allInput);
			} else if (bulkDeleteMode === 'selected') {
				const bulkInput = document.createElement('input');
				bulkInput.type = 'hidden';
				bulkInput.name = 'bulk_delete';
				bulkInput.value = '1';
				form.appendChild(bulkInput);

				bulkSelectedIds.forEach(id => {
					const hidden = document.createElement('input');
					hidden.type = 'hidden';
					hidden.name = 'selected_ids[]';
					hidden.value = id;
					form.appendChild(hidden);
				});
			}

			document.body.appendChild(form);
			form.submit();
		});
	}
	</script>
</body>
</html>
