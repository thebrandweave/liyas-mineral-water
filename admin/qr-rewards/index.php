<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

/**
 * Convert database timestamp to IST format
 * @param string $dbTimestamp Database timestamp
 * @return string Formatted date in IST
 */
function formatIST($dbTimestamp) {
    if (empty($dbTimestamp)) {
        return '';
    }
    try {
        // Create DateTime from database value
        // MySQL timestamps are typically stored without timezone, so we interpret as server timezone
        // Since we set MySQL timezone to IST, the value should be in IST
        $dt = new DateTime($dbTimestamp);
        // Ensure it's displayed in IST
        $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
        return $dt->format('d-m-Y H:i:s');
    } catch (Exception $e) {
        // Fallback
        return date('d-m-Y H:i:s', strtotime($dbTimestamp));
    }
}

// Check if export is requested
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Get filter parameters for export
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    // Build query based on filters
    $where_conditions = [];
    $params = [];
    
    if ($filter === 'used') {
        $where_conditions[] = "c.is_used = 1";
    } elseif ($filter === 'unused') {
        $where_conditions[] = "c.is_used = 0";
    }
    
    if (!empty($search)) {
        $where_conditions[] = "c.reward_code LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Fetch all matching codes (no pagination for export)
    try {
        $query = "SELECT c.id, c.reward_code, c.is_used, c.used_at, c.created_at,
                         c.customer_name, c.customer_phone, c.customer_email, c.customer_address
                  FROM codes c 
                  $where_clause 
                  ORDER BY c.created_at DESC";
        
        $stmt = $pdo->prepare($query);
        
        // Bind parameters
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching codes: " . $e->getMessage());
    }
    
    // Check if there are codes to export
    if (empty($codes)) {
        die("No codes found to export with the current filters.");
    }
    
    // Generate filename based on filter
    $filter_name = $filter === 'used' ? 'Redeemed' : ($filter === 'unused' ? 'Available' : 'All');
    $filename = "Reward_Codes_{$filter_name}_" . date('Y-m-d_His') . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write CSV headers
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
    
    // Write data rows
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
    
    // Close output stream
    fclose($output);
    exit;
}

// Get filter parameters (before delete handling to preserve them)
$filter = $_GET['filter'] ?? $_POST['filter'] ?? 'all'; // all, used, unused
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : (isset($_POST['page']) ? (int)$_POST['page'] : 1);
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Handle single code delete action
$delete_message = '';
$delete_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_code'])) {
    $code_id = isset($_POST['code_id']) ? (int)$_POST['code_id'] : 0;
    
    if ($code_id > 0) {
        try {
            $deleteStmt = $pdo->prepare("DELETE FROM codes WHERE id = ?");
            $deleteStmt->execute([$code_id]);
            
            $deleted_count = $deleteStmt->rowCount();
            if ($deleted_count > 0) {
                $delete_message = "Successfully deleted reward code!";
                $delete_type = 'success';
            } else {
                $delete_message = "Code not found or already deleted.";
                $delete_type = 'error';
            }
            
            // Redirect to preserve filter and search after deletion
            $redirect_url = "index.php?filter=" . urlencode($filter);
            if (!empty($search)) {
                $redirect_url .= "&search=" . urlencode($search);
            }
            if ($page > 1) {
                $redirect_url .= "&page=" . $page;
            }
            header("Location: $redirect_url&deleted=1");
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

// Handle bulk delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    // Check if deleting all matching filter (for select all functionality)
    $delete_all_matching = isset($_POST['delete_all_matching']) && $_POST['delete_all_matching'] === '1';
    $selected_ids = $_POST['selected_ids'] ?? [];
    
    if ($delete_all_matching) {
        // Delete all codes matching the current filter
        try {
            // Build the same WHERE clause used for filtering
            $delete_where_conditions = [];
            $delete_params = [];
            
            if ($filter === 'used') {
                $delete_where_conditions[] = "is_used = 1";
            } elseif ($filter === 'unused') {
                $delete_where_conditions[] = "is_used = 0";
            }
            
            if (!empty($search)) {
                $delete_where_conditions[] = "reward_code LIKE :search";
                $delete_params[':search'] = "%$search%";
            }
            
            // Build WHERE clause
            $delete_where_clause = !empty($delete_where_conditions) ? "WHERE " . implode(" AND ", $delete_where_conditions) : "";
            
            // Delete all matching codes (no alias needed in DELETE FROM)
            $deleteQuery = "DELETE FROM codes $delete_where_clause";
            $deleteStmt = $pdo->prepare($deleteQuery);
            
            if (!empty($delete_params)) {
                foreach ($delete_params as $key => $value) {
                    $deleteStmt->bindValue($key, $value);
                }
            }
            
            $deleteStmt->execute();
            $deleted_count = $deleteStmt->rowCount();
            
            $delete_message = "Successfully deleted $deleted_count reward code(s)!";
            $delete_type = 'success';
            
            // Redirect to preserve filter and search after deletion
            $redirect_url = "index.php?filter=" . urlencode($filter);
            if (!empty($search)) {
                $redirect_url .= "&search=" . urlencode($search);
            }
            header("Location: $redirect_url&deleted=$deleted_count");
            exit;
        } catch (PDOException $e) {
            $delete_message = "Error deleting codes: " . $e->getMessage();
            $delete_type = 'error';
            error_log("Bulk delete error: " . $e->getMessage());
        }
    } elseif (!empty($selected_ids)) {
        // Delete specific selected codes
        try {
            // Convert all IDs to integers for safety
            $ids = array_map('intval', $selected_ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            // Delete selected codes
            $deleteStmt = $pdo->prepare("DELETE FROM codes WHERE id IN ($placeholders)");
            $deleteStmt->execute($ids);
            
            $deleted_count = $deleteStmt->rowCount();
            $delete_message = "Successfully deleted $deleted_count reward code(s)!";
            $delete_type = 'success';
            
            // Redirect to preserve filter and search after deletion
            $redirect_url = "index.php?filter=" . urlencode($filter);
            if (!empty($search)) {
                $redirect_url .= "&search=" . urlencode($search);
            }
            if ($page > 1) {
                $redirect_url .= "&page=" . $page;
            }
            header("Location: $redirect_url&deleted=$deleted_count");
            exit;
        } catch (PDOException $e) {
            $delete_message = "Error deleting codes: " . $e->getMessage();
            $delete_type = 'error';
            error_log("Bulk delete error: " . $e->getMessage());
        }
    } else {
        $delete_message = 'Please select at least one code to delete.';
        $delete_type = 'error';
    }
}

// Check for success message from redirect
if (isset($_GET['deleted'])) {
    $delete_message = "Successfully deleted " . (int)$_GET['deleted'] . " reward code(s)!";
    $delete_type = 'success';
}

// Get statistics - optimized single query instead of 4 separate queries
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

// Build query
$where_conditions = [];
$params = [];

if ($filter === 'used') {
    $where_conditions[] = "c.is_used = 1";
} elseif ($filter === 'unused') {
    $where_conditions[] = "c.is_used = 0";
}

if (!empty($search)) {
    $where_conditions[] = "c.reward_code LIKE :search";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
try {
    $count_query = "SELECT COUNT(*) FROM codes c $where_clause";
    $count_stmt = $pdo->prepare($count_query);
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $count_stmt->bindValue($key, $value);
        }
    }
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
    error_log("Code count error: " . $e->getMessage());
}

// No need to fetch all IDs - we use delete_all_matching flag for bulk delete
// This significantly improves performance by avoiding fetching thousands of IDs

// Fetch reward codes (paginated for display)
try {
    $query = "SELECT c.id, c.reward_code, c.is_used, c.used_at, c.created_at,
                     c.customer_name, c.customer_phone, c.customer_email, c.customer_address
              FROM codes c 
              $where_clause 
              ORDER BY c.created_at DESC 
              LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    // Bind search parameter if exists
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reward_codes = $stmt->fetchAll();
} catch (PDOException $e) {
    $reward_codes = [];
    error_log("Reward codes fetch error: " . $e->getMessage());
}

// Get base URL for redeem page
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$project_root = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
$redeem_url = $base_url . $project_root . '/redeem';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "qr-rewards";
$page_title = "Reward Codes";
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
	<title>Reward Codes - Admin Panel</title>
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

		/* Form Actions */
		.action-btn:hover { background-color: #22c55e; } /* Green */
		.action-btn.add::before { content: "Generate Codes"; }

		/* Custom Modal Dialog */
		.modal-overlay {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			backdrop-filter: blur(5px);
			-webkit-backdrop-filter: blur(5px);
			z-index: 10000;
			align-items: center;
			justify-content: center;
			animation: fadeIn 0.2s ease-out;
		}

		.modal-overlay.active {
			display: flex;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
			}
			to {
				opacity: 1;
			}
		}

		.modal-dialog {
			background: white;
			border-radius: 16px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
			max-width: 500px;
			width: 90%;
			max-height: 90vh;
			overflow-y: auto;
			animation: slideUp 0.3s ease-out;
			position: relative;
			z-index: 10001;
		}

		@keyframes slideUp {
			from {
				opacity: 0;
				transform: translateY(30px) scale(0.95);
			}
			to {
				opacity: 1;
				transform: translateY(0) scale(1);
			}
		}

		.modal-header {
			padding: 1.5rem;
			border-bottom: 1px solid var(--grey);
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.modal-header h3 {
			font-size: 1.25rem;
			font-weight: 600;
			color: var(--dark);
			display: flex;
			align-items: center;
			gap: 0.75rem;
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
			transition: all 0.2s;
		}

		.modal-header .close-btn:hover {
			background: var(--grey);
			color: var(--dark);
		}

		.modal-body {
			padding: 1.5rem;
		}

		.modal-body .warning-icon {
			width: 60px;
			height: 60px;
			border-radius: 50%;
			background: #fef3c7;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 1rem;
			font-size: 2rem;
		}

		.modal-body .warning-icon i {
			color: #f59e0b;
		}

		.modal-body .customer-icon {
			width: 60px;
			height: 60px;
			border-radius: 50%;
			background: #dbeafe;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 1.5rem;
			font-size: 2rem;
		}

		.modal-body .customer-icon i {
			color: var(--blue);
		}

		.customer-details-item {
			padding: 0.75rem 0;
			border-bottom: 1px solid var(--grey);
		}

		.customer-details-item:last-child {
			border-bottom: none;
		}

		.customer-details-label {
			font-weight: 600;
			color: var(--dark-grey);
			font-size: 0.875rem;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-bottom: 0.25rem;
		}

		.customer-details-value {
			color: var(--dark);
			font-size: 1rem;
			word-break: break-word;
		}

		.modal-body p {
			color: var(--dark);
			font-size: 1rem;
			line-height: 1.6;
			margin-bottom: 1rem;
			text-align: center;
		}

		.modal-body .delete-details {
			background: var(--light);
			border-radius: 12px;
			padding: 1rem;
			margin: 1rem 0;
			border-left: 4px solid #dc2626;
		}

		.modal-body .delete-details ul {
			list-style: none;
			padding: 0;
			margin: 0;
		}

		.modal-body .delete-details li {
			padding: 0.5rem 0;
			color: var(--dark);
			font-size: 0.95rem;
		}

		.modal-body .delete-details li strong {
			color: #dc2626;
		}

		.modal-footer {
			padding: 1.5rem;
			border-top: 1px solid var(--grey);
			display: flex;
			gap: 1rem;
			justify-content: flex-end;
		}

		.modal-btn {
			padding: 0.75rem 1.5rem;
			border: none;
			border-radius: 8px;
			font-size: 0.95rem;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.2s;
			font-family: var(--opensans);
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
		}

		.modal-btn-cancel {
			background: var(--grey);
			color: var(--dark);
		}

		.modal-btn-cancel:hover {
			background: var(--dark-grey);
		}

		.modal-btn-delete {
			background: #dc2626;
			color: white;
		}

		.modal-btn-delete:hover {
			background: #b91c1c;
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
		}

		/* Blur background when modal is active */
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
	<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu bx-sm' ></i>
			<a href="#" class="nav-link"><?= $page_title ?></a>
			<form action="index.php" method="GET">
				<div class="form-input">
					<input type="search" name="search" placeholder="Search reward codes..." value="<?= htmlspecialchars($search) ?>">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification">
				<i class='bx bxs-bell bx-tada-hover' ></i>
				<span class="num"><?= $recent_redeemed ?></span>
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
					<h1>Reward Codes</h1>
					<ul class="breadcrumb">
						<li>
							<a href="../index.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Reward Codes</a>
						</li>
					</ul>
				</div>
				<a href="generate.php" class="button action-btn add" title="Generate new reward codes">
					<svg class="svgIcon" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"></path></svg>
				</a>
			</div>

			<!-- Statistics Cards -->
			<ul class="box-info">
				<li>
					<i class='bx bxs-ticket' ></i>
					<span class="text">
						<h3><?= number_format($total_codes) ?></h3>
						<p>Total Codes</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-check-circle' ></i>
					<span class="text">
						<h3><?= number_format($used_codes) ?></h3>
						<p>Redeemed</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-time' ></i>
					<span class="text">
						<h3><?= number_format($unused_codes) ?></h3>
						<p>Available</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-calendar-check' ></i>
					<span class="text">
						<h3><?= number_format($recent_redeemed) ?></h3>
						<p>Redeemed (7 days)</p>
					</span>
				</li>
			</ul>

			<!-- Filters -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Filter Reward Codes</h3>
						<i class='bx bx-filter' ></i>
					</div>
					<div style="padding: 1rem;">
						<form method="GET" action="" id="filterForm" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
							<input type="hidden" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>">
							<select name="filter" id="filterSelect" style="padding: 0.5rem 1rem; border: 1px solid var(--grey); border-radius: 8px; background: var(--light); font-family: var(--opensans);">
								<option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Codes</option>
								<option value="used" <?= $filter === 'used' ? 'selected' : '' ?>>Redeemed Only</option>
								<option value="unused" <?= $filter === 'unused' ? 'selected' : '' ?>>Available Only</option>
							</select>
							<button type="submit" style="padding: 0.5rem 1.5rem; background: var(--blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-family: var(--opensans);">
								<i class='bx bx-filter' ></i> Apply Filter
							</button>
							<button type="button" id="exportBtn"
							   style="padding: 0.5rem 1.5rem; background: var(--green); color: white; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; font-family: var(--opensans);"
							   title="Export filtered results to Excel">
								<i class='bx bx-download' ></i> Export Excel
							</button>
							<?php if ($filter !== 'all' || !empty($search)): ?>
							<a href="index.php" style="padding: 0.5rem 1.5rem; background: var(--dark-grey); color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
								<i class='bx bx-x' ></i> Clear
							</a>
							<?php endif; ?>
						</form>
					</div>
				</div>
			</div>

			<!-- Bulk Delete Messages -->
			<?php if ($delete_message): ?>
				<div class="table-data" style="margin-bottom: 1rem;">
					<div class="order">
						<div class="head">
							<h3>Delete Status</h3>
						</div>
						<div style="padding: 1rem;">
							<div style="padding: 1rem; border-radius: 8px; <?= $delete_type === 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' ?>">
								<?= htmlspecialchars($delete_message) ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Reward Codes Table -->
			<div class="table-data">
				<div class="order">
					<div class="head" style="display: flex; justify-content: space-between; align-items: center;">
						<h3>Reward Codes (<?= number_format($total_records) ?> total)</h3>
						<div style="display: flex; gap: 0.5rem; align-items: center;">
							<button 
								id="selectAllBtn" 
								style="padding: 0.5rem 1rem; background: var(--blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-family: var(--opensans); font-size: 0.9rem;"
								title="Select All Codes (All Pages)"
							>
								<i class='bx bx-check-square' ></i> Select All (<?= number_format($total_records) ?>)
							</button>
							<button 
								id="bulkDeleteBtn" 
								style="padding: 0.5rem 1rem; background: #dc2626; color: white; border: none; border-radius: 8px; cursor: pointer; font-family: var(--opensans); font-size: 0.9rem; display: none;"
								title="Delete Selected Codes"
							>
								<i class='bx bx-trash' ></i> Delete Selected (<span id="selectedCount">0</span>)
							</button>
						</div>
					</div>
					<?php if (empty($reward_codes)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bxs-ticket' style="font-size: 4rem; color: var(--dark-grey); margin-bottom: 1rem;"></i>
							<p style="color: var(--dark-grey); font-size: 1.1rem; margin-bottom: 0.5rem;">No reward codes found</p>
							<p style="color: var(--dark-grey); font-size: 0.9rem;">Generate reward codes using: <code>php scripts/generate_reward_codes.php [count] [prefix]</code></p>
							<p style="color: var(--dark-grey); font-size: 0.9rem; margin-top: 0.5rem;">Or use the <a href="generate.php" style="color: var(--blue);">Generate Codes</a> page</p>
						</div>
					<?php else: ?>
						<form method="POST" action="" id="bulkDeleteForm">
							<input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
							<input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
							<input type="hidden" name="page" value="<?= $page ?>">
							<!-- No hidden checkboxes needed - using delete_all_matching flag for performance -->
							<table>
								<thead>
									<tr>
										<th style="width: 50px;">
											<input 
												type="checkbox" 
												id="selectAllCheckbox" 
												title="Select/Deselect All"
												style="cursor: pointer; width: 18px; height: 18px;"
											>
										</th>
										<th>ID</th>
										<th>Reward Code</th>
										<th>Status</th>
										<th>Customer Name</th>
										<th>Phone</th>
										<th>Redeemed At</th>
										<th>Created At</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($reward_codes as $code): ?>
									<tr>
										<td>
											<input 
												type="checkbox" 
												name="selected_ids[]" 
												value="<?= $code['id'] ?>" 
												class="code-checkbox"
												style="cursor: pointer; width: 18px; height: 18px;"
											>
										</td>
										<td>
											<p><?= $code['id'] ?></p>
										</td>
									<td>
										<p style="font-family: monospace; font-weight: 600; color: var(--blue);">
											<?= htmlspecialchars($code['reward_code']) ?>
										</p>
									</td>
									<td>
										<?php if ($code['is_used']): ?>
											<span class="status completed">Redeemed</span>
										<?php else: ?>
											<span class="status pending">Available</span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($code['customer_name']): ?>
											<p style="font-weight: 600;"><?= htmlspecialchars($code['customer_name']) ?></p>
										<?php else: ?>
											<p style="color: var(--dark-grey);">—</p>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($code['customer_phone']): ?>
											<p><?= htmlspecialchars($code['customer_phone']) ?></p>
										<?php else: ?>
											<p style="color: var(--dark-grey);">—</p>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($code['used_at']): ?>
											<p><?= formatIST($code['used_at']) ?> <small style="color: var(--dark-grey); font-size: 0.85rem;">IST</small></p>
										<?php else: ?>
											<p style="color: var(--dark-grey);">—</p>
										<?php endif; ?>
									</td>
									<td>
										<p><?= formatIST($code['created_at']) ?> <small style="color: var(--dark-grey); font-size: 0.85rem;">IST</small></p>
									</td>
									<td>
										<div style="display: flex; gap: 0.5rem; align-items: center;">
											<?php if ($code['customer_name']): ?>
											<button 
												type="button"
												onclick="event.preventDefault(); event.stopPropagation(); showCustomerDetails('<?= htmlspecialchars($code['customer_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($code['customer_phone'], ENT_QUOTES) ?>', '<?= htmlspecialchars($code['customer_email'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($code['customer_address'] ?? '', ENT_QUOTES) ?>')" 
												title="View Customer Details"
												style="background: none; border: none; color: var(--blue); cursor: pointer; padding: 0.25rem 0.5rem; font-size: 1.1rem;"
											>
												<i class='bx bx-user' ></i>
											</button>
											<?php endif; ?>
											<form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this reward code? This action cannot be undone.');">
												<input type="hidden" name="delete_code" value="1">
												<input type="hidden" name="code_id" value="<?= $code['id'] ?>">
												<input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
												<input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
												<input type="hidden" name="page" value="<?= $page ?>">
												<button 
													type="submit"
													title="Delete Code"
													style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0.25rem 0.5rem; font-size: 1.1rem;"
												>
													<i class='bx bx-trash' ></i>
												</button>
											</form>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<input type="hidden" name="bulk_delete" value="1">
						</form>

						<!-- Pagination -->
						<?php if ($total_pages > 1): ?>
						<div style="padding: 1.5rem; border-top: 1px solid var(--grey); display: flex; justify-content: center; gap: 0.5rem; align-items: center;">
							<?php if ($page > 1): ?>
								<a href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--opensans);">
									<i class='bx bx-chevron-left' ></i> Previous
								</a>
							<?php endif; ?>
							
							<span style="padding: 0.5rem 1rem; color: var(--dark); font-family: var(--opensans);">
								Page <?= $page ?> of <?= $total_pages ?>
							</span>
							
							<?php if ($page < $total_pages): ?>
								<a href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--opensans);">
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

	<!-- Custom Modal Dialog for Delete -->
	<div id="deleteModal" class="modal-overlay">
		<div class="modal-dialog">
			<div class="modal-header">
				<h3>
					<i class='bx bx-error-circle' style="color: #dc2626;"></i>
					Confirm Deletion
				</h3>
				<button type="button" class="close-btn" onclick="closeDeleteModal()">
					<i class='bx bx-x' ></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="warning-icon">
					<i class='bx bx-error-circle' ></i>
				</div>
				<p id="modalMessage">Are you sure you want to delete the selected reward codes?</p>
				<div id="modalDetails" class="delete-details" style="display: none;">
					<ul id="modalDetailsList"></ul>
				</div>
				<p style="color: #dc2626; font-weight: 600; margin-top: 1rem;">
					⚠️ WARNING: This action cannot be undone!
				</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">
					<i class='bx bx-x' ></i> Cancel
				</button>
				<button type="button" class="modal-btn modal-btn-delete" id="confirmDeleteBtn">
					<i class='bx bx-trash' ></i> Delete
				</button>
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
					<i class='bx bx-x' ></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="customer-icon">
					<i class='bx bx-user' ></i>
				</div>
				<div id="customerDetailsContent" style="text-align: left;">
					<!-- Customer details will be inserted here -->
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="modal-btn modal-btn-cancel" onclick="closeCustomerDetailsModal()">
					<i class='bx bx-x' ></i> Close
				</button>
			</div>
		</div>
	</div>
	
	<script src="../assets/js/admin-script.js"></script>
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
		}, function(err) {
			// Fallback for older browsers
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
		
		// Build customer details HTML
		let detailsHTML = '<div class="customer-details-item">';
		detailsHTML += '<div class="customer-details-label">Name</div>';
		detailsHTML += '<div class="customer-details-value">' + escapeHtml(name) + '</div>';
		detailsHTML += '</div>';
		
		detailsHTML += '<div class="customer-details-item">';
		detailsHTML += '<div class="customer-details-label">Phone</div>';
		detailsHTML += '<div class="customer-details-value">' + escapeHtml(phone) + '</div>';
		detailsHTML += '</div>';
		
		if (email) {
			detailsHTML += '<div class="customer-details-item">';
			detailsHTML += '<div class="customer-details-label">Email</div>';
			detailsHTML += '<div class="customer-details-value">' + escapeHtml(email) + '</div>';
			detailsHTML += '</div>';
		}
		
		if (address) {
			detailsHTML += '<div class="customer-details-item">';
			detailsHTML += '<div class="customer-details-label">Address</div>';
			detailsHTML += '<div class="customer-details-value">' + escapeHtml(address) + '</div>';
			detailsHTML += '</div>';
		}
		
		content.innerHTML = detailsHTML;
		
		// Show modal
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

	// Export button functionality
	document.getElementById('exportBtn').addEventListener('click', function(e) {
		e.preventDefault();
		
		// Get current filter and search values from the form
		const filterSelect = document.getElementById('filterSelect');
		const searchInput = document.getElementById('searchInput');
		
		let filter = filterSelect ? filterSelect.value : 'all';
		let search = searchInput ? searchInput.value : '';
		
		// Build export URL with export parameter
		let exportUrl = '?export=csv&filter=' + encodeURIComponent(filter);
		if (search) {
			exportUrl += '&search=' + encodeURIComponent(search);
		}
		
		console.log('Exporting with URL:', exportUrl);
		
		// Trigger download
		window.location.href = exportUrl;
	});

	// Bulk selection functionality - Optimized version
	const selectAllCheckbox = document.getElementById('selectAllCheckbox');
	const selectAllBtn = document.getElementById('selectAllBtn');
	const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
	const selectedCountSpan = document.getElementById('selectedCount');
	const bulkDeleteForm = document.getElementById('bulkDeleteForm');
	const totalRecords = <?= $total_records ?>;
	
	// Cache code checkboxes (only query once)
	let codeCheckboxes = document.querySelectorAll('.code-checkbox');
	
	// Track if all codes are selected (across all pages)
	let allSelected = false;

	// Optimized update function - uses cached selectors
	function updateSelectionUI() {
		// Only query checked boxes when needed
		const visibleChecked = document.querySelectorAll('.code-checkbox:checked');
		const visibleCount = visibleChecked.length;
		
		// Total count is either visible checked or total records if all selected
		const totalCount = allSelected ? totalRecords : visibleCount;
		
		// Update UI efficiently
		selectedCountSpan.textContent = totalCount.toLocaleString();
		
		if (totalCount > 0) {
			bulkDeleteBtn.style.display = 'inline-flex';
		} else {
			bulkDeleteBtn.style.display = 'none';
		}
		
		// Update select all checkbox state (for current page only)
		if (selectAllCheckbox && codeCheckboxes.length > 0) {
			selectAllCheckbox.checked = visibleCount === codeCheckboxes.length && allSelected;
			selectAllCheckbox.indeterminate = (visibleCount > 0 && visibleCount < codeCheckboxes.length) || (allSelected && visibleCount < codeCheckboxes.length);
		}
		
		// Update select all button text
		if (selectAllBtn) {
			if (allSelected) {
				selectAllBtn.innerHTML = '<i class="bx bx-x"></i> Deselect All';
				selectAllBtn.style.background = 'var(--dark-grey)';
			} else {
				selectAllBtn.innerHTML = '<i class="bx bx-check-square"></i> Select All (' + totalRecords.toLocaleString() + ')';
				selectAllBtn.style.background = 'var(--blue)';
			}
		}
	}

	// Select all checkbox (current page only) - optimized
	if (selectAllCheckbox) {
		selectAllCheckbox.addEventListener('change', function() {
			const isChecked = this.checked;
			// Use forEach with cached checkboxes
			codeCheckboxes.forEach(checkbox => {
				checkbox.checked = isChecked;
			});
			allSelected = false; // Reset all selected flag when manually selecting page
			updateSelectionUI();
		});
	}

	// Select all button (all pages matching filter) - optimized
	if (selectAllBtn) {
		selectAllBtn.addEventListener('click', function(e) {
			e.preventDefault();
			
			if (allSelected) {
				// Deselect all
				allSelected = false;
				codeCheckboxes.forEach(checkbox => {
					checkbox.checked = false;
				});
			} else {
				// Select all (across all pages) - just mark current page as checked
				allSelected = true;
				codeCheckboxes.forEach(checkbox => {
					checkbox.checked = true;
				});
			}
			updateSelectionUI();
		});
	}

	// Individual checkbox change - use event delegation for better performance
	const tableContainer = document.querySelector('.table-data .order');
	if (tableContainer) {
		tableContainer.addEventListener('change', function(e) {
			if (e.target.classList.contains('code-checkbox')) {
				// If any checkbox is unchecked, reset all selected flag
				if (!e.target.checked) {
					allSelected = false;
				}
				updateSelectionUI();
			}
		});
	}

	// Show delete modal - optimized
	function showDeleteModal() {
		// Get count based on selection mode
		let count;
		
		if (allSelected) {
			// All codes matching filter are selected
			count = totalRecords;
		} else {
			// Only visible checkboxes are selected
			const checked = document.querySelectorAll('.code-checkbox:checked');
			count = checked.length;
		}
		
		if (count === 0) {
			alert('Please select at least one code to delete.');
			return false;
		}
		
		// Get details about what's being deleted (only from visible rows if not all selected)
		let checkedCodes = [];
		if (allSelected) {
			// If all selected, we need to fetch stats differently
			// For now, just show the total count
			checkedCodes = [];
		} else {
			const checked = document.querySelectorAll('.code-checkbox:checked');
			checkedCodes = Array.from(checked).map(cb => {
				const row = cb.closest('tr');
				const codeCell = row.querySelector('td:nth-child(3) p');
				const statusCell = row.querySelector('td:nth-child(4) span');
				return {
					code: codeCell ? codeCell.textContent.trim() : '',
					status: statusCell ? statusCell.textContent.trim() : ''
				};
			});
		}
		
		const usedCount = checkedCodes.filter(c => c.status === 'Redeemed').length;
		const unusedCount = checkedCodes.filter(c => c.status === 'Available').length;
		
		// Update modal message
		const modalMessage = document.getElementById('modalMessage');
		if (allSelected) {
			modalMessage.textContent = `Are you sure you want to delete ALL ${count.toLocaleString()} reward code(s) matching the current filter?`;
		} else {
			modalMessage.textContent = `Are you sure you want to delete ${count.toLocaleString()} reward code(s)?`;
		}
		
		// Update modal details
		const modalDetails = document.getElementById('modalDetails');
		const modalDetailsList = document.getElementById('modalDetailsList');
		modalDetailsList.innerHTML = '';
		
		if (allSelected) {
			// Show filter info when all selected
			const filterText = '<?= $filter === "used" ? "Redeemed" : ($filter === "unused" ? "Available" : "All") ?>';
			modalDetails.style.display = 'block';
			modalDetailsList.innerHTML = `<li>All codes matching: <strong>${filterText}</strong> filter</li>`;
		} else if (usedCount > 0 && unusedCount > 0) {
			modalDetails.style.display = 'block';
			modalDetailsList.innerHTML = `
				<li><strong>${usedCount}</strong> Redeemed code(s)</li>
				<li><strong>${unusedCount}</strong> Available code(s)</li>
			`;
		} else if (usedCount > 0) {
			modalDetails.style.display = 'block';
			modalDetailsList.innerHTML = `<li><strong>${usedCount}</strong> Redeemed code(s)</li>`;
		} else if (unusedCount > 0) {
			modalDetails.style.display = 'block';
			modalDetailsList.innerHTML = `<li><strong>${unusedCount}</strong> Available code(s)</li>`;
		} else {
			modalDetails.style.display = 'none';
		}
		
		// Show modal
		const modal = document.getElementById('deleteModal');
		modal.classList.add('active');
		document.body.classList.add('modal-active');
		
		return true;
	}

	// Close delete modal
	function closeDeleteModal() {
		const modal = document.getElementById('deleteModal');
		modal.classList.remove('active');
		document.body.classList.remove('modal-active');
	}

	// Confirm delete and submit form
	function confirmDelete() {
		closeDeleteModal();
		
		// Prepare form with all selected IDs
		// Remove all existing selected_ids inputs and delete_all_matching flag
		const existingInputs = bulkDeleteForm.querySelectorAll('input[name="selected_ids[]"], input[name="delete_all_matching"]');
		existingInputs.forEach(input => input.remove());
		
		// Add selected IDs to form
		if (allSelected) {
			// When all selected, send a flag instead of all IDs (to avoid POST limits)
			const flagInput = document.createElement('input');
			flagInput.type = 'hidden';
			flagInput.name = 'delete_all_matching';
			flagInput.value = '1';
			bulkDeleteForm.appendChild(flagInput);
		} else {
			// Add visible checked checkboxes only
			const checked = document.querySelectorAll('.code-checkbox:checked');
			checked.forEach(checkbox => {
				const input = document.createElement('input');
				input.type = 'hidden';
				input.name = 'selected_ids[]';
				input.value = checkbox.value;
				bulkDeleteForm.appendChild(input);
			});
		}
		
		bulkDeleteForm.submit();
	}

	// Bulk delete button - show modal
	if (bulkDeleteBtn) {
		bulkDeleteBtn.addEventListener('click', function(e) {
			e.preventDefault();
			showDeleteModal();
		});
	}

	// Confirm delete button in modal
	const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
	if (confirmDeleteBtn) {
		confirmDeleteBtn.addEventListener('click', confirmDelete);
	}

	// Close modal when clicking overlay
	const modalOverlay = document.getElementById('deleteModal');
	if (modalOverlay) {
		modalOverlay.addEventListener('click', function(e) {
			if (e.target === modalOverlay) {
				closeDeleteModal();
			}
		});
	}

	// Close modal on Escape key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') {
			const deleteModal = document.getElementById('deleteModal');
			const customerModal = document.getElementById('customerDetailsModal');
			
			if (deleteModal && deleteModal.classList.contains('active')) {
				closeDeleteModal();
			} else if (customerModal && customerModal.classList.contains('active')) {
				closeCustomerDetailsModal();
			}
		}
	});

	// Close customer details modal when clicking outside
	const customerModalOverlay = document.getElementById('customerDetailsModal');
	if (customerModalOverlay) {
		customerModalOverlay.addEventListener('click', function(e) {
			if (e.target === customerModalOverlay) {
				closeCustomerDetailsModal();
			}
		});
	}

	// Initialize UI on page load
	updateSelectionUI();
	</script>
</body>
</html>
</html>