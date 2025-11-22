<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// Use existing $pdo from config.php (already connected to liyas_international database)

// Get filter parameters
$filter = $_GET['filter'] ?? 'all'; // all, used, unused
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Get statistics
try {
    $total_qr = $pdo->query("SELECT COUNT(*) FROM qr_codes")->fetchColumn();
    $used_qr = $pdo->query("SELECT COUNT(*) FROM qr_codes WHERE is_used = 1")->fetchColumn();
    $unused_qr = $pdo->query("SELECT COUNT(*) FROM qr_codes WHERE is_used = 0")->fetchColumn();
    $recent_redeemed = $pdo->query("SELECT COUNT(*) FROM qr_codes WHERE is_used = 1 AND scanned_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
} catch (PDOException $e) {
    $total_qr = $used_qr = $unused_qr = $recent_redeemed = 0;
}

// Build query
$where_conditions = [];
$params = [];

if ($filter === 'used') {
    $where_conditions[] = "q.is_used = 1";
} elseif ($filter === 'unused') {
    $where_conditions[] = "q.is_used = 0";
}

if (!empty($search)) {
    $where_conditions[] = "q.code LIKE :search";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
try {
    $count_query = "SELECT COUNT(*) FROM qr_codes q $where_clause";
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
    error_log("QR count error: " . $e->getMessage());
}

// Fetch QR codes with customer data
try {
    $query = "SELECT q.id, q.code, q.is_used, q.scanned_at, q.created_at,
                     r.customer_name, 
                     r.customer_phone, 
                     r.customer_address,
                     r.redeemed_at
              FROM qr_codes q 
              LEFT JOIN reward_logs r ON q.id = r.qr_code_id 
              $where_clause 
              ORDER BY q.created_at DESC 
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
    $qr_codes = $stmt->fetchAll();
} catch (PDOException $e) {
    $qr_codes = [];
    error_log("QR codes fetch error: " . $e->getMessage());
}

// Get base URL for QR code links
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$redeem_url = $base_url . '/public/index.php?code=';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "qr-rewards";
$page_title = "QR Rewards";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/admin-style.css">
	<title>QR Rewards - Admin Panel</title>
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
					<input type="search" name="search" placeholder="Search QR codes..." value="<?= htmlspecialchars($search) ?>">
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
					<h1>QR Rewards</h1>
					<ul class="breadcrumb">
						<li>
							<a href="../index.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">QR Rewards</a>
						</li>
					</ul>
				</div>
				<a href="generate.php" class="btn-download" title="Generate new QR code">
					<i class='bx bxs-qr-scan'></i>
					<span class="text">Generate Code</span>
				</a>
			</div>

			<!-- Statistics Cards -->
			<ul class="box-info">
				<li>
					<i class='bx bxs-qr-scan' ></i>
					<span class="text">
						<h3><?= number_format($total_qr) ?></h3>
						<p>Total QR Codes</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-check-circle' ></i>
					<span class="text">
						<h3><?= number_format($used_qr) ?></h3>
						<p>Redeemed</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-time' ></i>
					<span class="text">
						<h3><?= number_format($unused_qr) ?></h3>
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
						<h3>Filter QR Codes</h3>
						<i class='bx bx-filter' ></i>
					</div>
					<div style="padding: 1rem;">
						<form method="GET" action="" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
							<input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
							<select name="filter" style="padding: 0.5rem 1rem; border: 1px solid var(--grey); border-radius: 8px; background: var(--light); font-family: var(--lato);">
								<option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Codes</option>
								<option value="used" <?= $filter === 'used' ? 'selected' : '' ?>>Redeemed Only</option>
								<option value="unused" <?= $filter === 'unused' ? 'selected' : '' ?>>Available Only</option>
							</select>
							<button type="submit" style="padding: 0.5rem 1.5rem; background: var(--blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-family: var(--lato);">
								<i class='bx bx-filter' ></i> Apply Filter
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

			<!-- QR Codes Table -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>QR Codes (<?= number_format($total_records) ?> total)</h3>
						<i class='bx bx-search' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<?php if (empty($qr_codes)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bxs-qr-scan' style="font-size: 4rem; color: var(--dark-grey); margin-bottom: 1rem;"></i>
							<p style="color: var(--dark-grey); font-size: 1.1rem; margin-bottom: 0.5rem;">No QR codes found</p>
							<p style="color: var(--dark-grey); font-size: 0.9rem;">Generate QR codes using: <code>php scripts/generate_qr.php</code></p>
						</div>
					<?php else: ?>
						<table>
							<thead>
								<tr>
									<th>ID</th>
									<th>QR Code</th>
									<th>Status</th>
									<th>Customer Name</th>
									<th>Phone</th>
									<th>Redeemed At</th>
									<th>Created At</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($qr_codes as $qr): ?>
								<tr>
									<td>
										<p><?= $qr['id'] ?></p>
									</td>
									<td>
										<p style="font-family: monospace; font-weight: 600; color: var(--blue);">
											<?= htmlspecialchars($qr['code']) ?>
										</p>
									</td>
									<td>
										<?php if ($qr['is_used']): ?>
											<span class="status completed">Redeemed</span>
										<?php else: ?>
											<span class="status pending">Available</span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($qr['customer_name']): ?>
											<p style="font-weight: 600;"><?= htmlspecialchars($qr['customer_name']) ?></p>
										<?php else: ?>
											<p style="color: var(--dark-grey);">—</p>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($qr['customer_phone']): ?>
											<p><?= htmlspecialchars($qr['customer_phone']) ?></p>
										<?php else: ?>
											<p style="color: var(--dark-grey);">—</p>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($qr['scanned_at']): ?>
											<p><?= date('d-m-Y H:i', strtotime($qr['scanned_at'])) ?></p>
										<?php else: ?>
											<p style="color: var(--dark-grey);">—</p>
										<?php endif; ?>
									</td>
									<td>
										<p><?= date('d-m-Y', strtotime($qr['created_at'])) ?></p>
									</td>
									<td>
										<div style="display: flex; gap: 0.5rem; align-items: center;">
											<a href="<?= $redeem_url . urlencode($qr['code']) ?>" target="_blank" title="View Redeem Page" style="color: var(--blue); text-decoration: none; padding: 0.25rem 0.5rem;">
												<i class='bx bx-link-external' ></i>
											</a>
											<button 
												onclick="copyToClipboard('<?= $redeem_url . $qr['code'] ?>')" 
												title="Copy Redeem URL"
												style="background: none; border: none; color: var(--blue); cursor: pointer; padding: 0.25rem 0.5rem; font-size: 1.1rem;"
											>
												<i class='bx bx-copy' ></i>
											</button>
											<?php if (file_exists('../../uploads/qrs/' . $qr['code'] . '.png')): ?>
											<a 
												href="../../uploads/qrs/<?= htmlspecialchars($qr['code']) ?>.png" 
												target="_blank"
												title="View QR Image"
												style="color: var(--green); text-decoration: none; padding: 0.25rem 0.5rem;"
											>
												<i class='bx bx-image' ></i>
											</a>
											<?php endif; ?>
											<?php if ($qr['customer_name']): ?>
											<button 
												onclick="showCustomerDetails('<?= htmlspecialchars($qr['customer_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($qr['customer_phone'], ENT_QUOTES) ?>', '<?= htmlspecialchars($qr['customer_address'], ENT_QUOTES) ?>')" 
												title="View Customer Details"
												style="background: none; border: none; color: var(--blue); cursor: pointer; padding: 0.25rem 0.5rem; font-size: 1.1rem;"
											>
												<i class='bx bx-user' ></i>
											</button>
											<?php endif; ?>
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
								<a href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--lato);">
									<i class='bx bx-chevron-left' ></i> Previous
								</a>
							<?php endif; ?>
							
							<span style="padding: 0.5rem 1rem; color: var(--dark); font-family: var(--lato);">
								Page <?= $page ?> of <?= $total_pages ?>
							</span>
							
							<?php if ($page < $total_pages): ?>
								<a href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--lato);">
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
	function copyToClipboard(text) {
		navigator.clipboard.writeText(text).then(function() {
			// Show success message (you can customize this)
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
			alert('Redeem URL copied to clipboard!');
		});
	}

	function showCustomerDetails(name, phone, address) {
		const details = `Customer Details:\n\nName: ${name}\nPhone: ${phone}\nAddress: ${address}`;
		alert(details);
	}
	</script>
</body>
</html>
