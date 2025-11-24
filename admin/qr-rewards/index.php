<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// Get filter parameters
$filter = $_GET['filter'] ?? 'all'; // all, used, unused
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Get statistics
try {
    $total_codes = $pdo->query("SELECT COUNT(*) FROM codes")->fetchColumn();
    $used_codes = $pdo->query("SELECT COUNT(*) FROM codes WHERE is_used = 1")->fetchColumn();
    $unused_codes = $pdo->query("SELECT COUNT(*) FROM codes WHERE is_used = 0")->fetchColumn();
    $recent_redeemed = $pdo->query("SELECT COUNT(*) FROM codes WHERE is_used = 1 AND used_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
} catch (PDOException $e) {
    $total_codes = $used_codes = $unused_codes = $recent_redeemed = 0;
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

// Fetch reward codes
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
$redeem_url = $base_url . $project_root . '/redeem.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "qr-rewards";
$page_title = "Reward Codes";
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
	<title>Reward Codes - Admin Panel</title>
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
				<a href="generate.php" class="btn-download" title="Generate new reward codes">
					<i class='bx bxs-plus-circle'></i>
					<span class="text">Generate Codes</span>
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
						<form method="GET" action="" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
							<input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
							<select name="filter" style="padding: 0.5rem 1rem; border: 1px solid var(--grey); border-radius: 8px; background: var(--light); font-family: var(--opensans);">
								<option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Codes</option>
								<option value="used" <?= $filter === 'used' ? 'selected' : '' ?>>Redeemed Only</option>
								<option value="unused" <?= $filter === 'unused' ? 'selected' : '' ?>>Available Only</option>
							</select>
							<button type="submit" style="padding: 0.5rem 1.5rem; background: var(--blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-family: var(--opensans);">
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

			<!-- Reward Codes Table -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Reward Codes (<?= number_format($total_records) ?> total)</h3>
						<i class='bx bx-search' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<?php if (empty($reward_codes)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bxs-ticket' style="font-size: 4rem; color: var(--dark-grey); margin-bottom: 1rem;"></i>
							<p style="color: var(--dark-grey); font-size: 1.1rem; margin-bottom: 0.5rem;">No reward codes found</p>
							<p style="color: var(--dark-grey); font-size: 0.9rem;">Generate reward codes using: <code>php scripts/generate_reward_codes.php [count] [prefix]</code></p>
							<p style="color: var(--dark-grey); font-size: 0.9rem; margin-top: 0.5rem;">Or use the <a href="generate.php" style="color: var(--blue);">Generate Codes</a> page</p>
						</div>
					<?php else: ?>
						<table>
							<thead>
								<tr>
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
											<p><?= date('d-m-Y H:i', strtotime($code['used_at'])) ?></p>
										<?php else: ?>
											<p style="color: var(--dark-grey);">—</p>
										<?php endif; ?>
									</td>
									<td>
										<p><?= date('d-m-Y', strtotime($code['created_at'])) ?></p>
									</td>
									<td>
										<div style="display: flex; gap: 0.5rem; align-items: center;">
											<a href="<?= htmlspecialchars($redeem_url) ?>" target="_blank" title="View Redeem Page" style="color: var(--blue); text-decoration: none; padding: 0.25rem 0.5rem;">
												<i class='bx bx-link-external' ></i>
											</a>
											<button 
												onclick="copyToClipboard('<?= htmlspecialchars($code['reward_code'], ENT_QUOTES) ?>')" 
												title="Copy Code"
												style="background: none; border: none; color: var(--blue); cursor: pointer; padding: 0.25rem 0.5rem; font-size: 1.1rem;"
											>
												<i class='bx bx-copy' ></i>
											</button>
											<?php if ($code['customer_name']): ?>
											<button 
												onclick="showCustomerDetails('<?= htmlspecialchars($code['customer_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($code['customer_phone'], ENT_QUOTES) ?>', '<?= htmlspecialchars($code['customer_email'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($code['customer_address'] ?? '', ENT_QUOTES) ?>')" 
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
		let details = `Customer Details:\n\nName: ${name}\nPhone: ${phone}`;
		if (email) {
			details += `\nEmail: ${email}`;
		}
		if (address) {
			details += `\n\nAddress:\n${address}`;
		}
		alert(details);
	}
	</script>
</body>
</html>
