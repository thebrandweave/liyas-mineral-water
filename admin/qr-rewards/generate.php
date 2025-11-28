<?php
/**
 * Generate Reward Codes via Web Interface
 * Admin can generate reward codes through the browser
 */

require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "qr-rewards";
$page_title = "Generate Reward Codes";

$message = '';
$message_type = '';
$generated_count = 0;
$sample_codes = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $count = isset($_POST['count']) ? (int)$_POST['count'] : 100;
    $prefix = isset($_POST['prefix']) ? trim($_POST['prefix']) : 'Liyas-';
    $prefix = rtrim($prefix, '-') . '-';
    
    // Validate
    if ($count < 1 || $count > 10000) {
        $message = 'Count must be between 1 and 10,000';
        $message_type = 'error';
    } else {
        try {
            $pdo->beginTransaction();
            
            $insertStmt = $pdo->prepare("INSERT INTO codes (reward_code) VALUES (?)");
            $generated = 0;
            $errors = 0;
            
            for ($i = 0; $i < $count; $i++) {
                $code_length = random_int(6, 10);
                $code = generateUniqueRewardCode($pdo, $prefix, $code_length);
                
                try {
                    $insertStmt->execute([$code]);
                    $generated++;
                    
                    // Store first 5 for display
                    if ($generated <= 5) {
                        $sample_codes[] = $code;
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() != 23000) { // Not duplicate
                        $errors++;
                    }
                }
            }
            
            $pdo->commit();
            $generated_count = $generated;
            $message = "Successfully generated $generated reward codes!";
            $message_type = 'success';
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

/**
 * Generate unique random reward code
 */
function generateUniqueRewardCode($pdo, $prefix, $length) {
    $max_attempts = 100;
    $attempts = 0;
    
    do {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $suffix = '';
        for ($i = 0; $i < $length; $i++) {
            $suffix .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        $code = $prefix . $suffix;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM codes WHERE reward_code = ?");
        $stmt->execute([$code]);
        $exists = $stmt->fetchColumn() > 0;
        
        $attempts++;
    } while ($exists && $attempts < $max_attempts);
    
    if ($exists) {
        throw new Exception("Could not generate unique code after $max_attempts attempts");
    }
    
    return $code;
}
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
	<title>Generate Reward Codes - Admin Panel</title>
	<style>
		.form-group {
			margin-bottom: 1.5rem;
		}
		.form-group label {
			display: block;
			margin-bottom: 0.5rem;
			color: var(--dark-grey);
			font-weight: 600;
		}
		.form-group input {
			width: 100%;
			padding: 0.75rem 1rem;
			border: 1px solid var(--grey);
			border-radius: 8px;
			background: var(--light);
			font-size: 1rem;
			font-family: var(--opensans);
		}
		.form-group input:focus {
			outline: none;
			border-color: var(--blue);
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
		.sample-codes {
			background: var(--light);
			padding: 1rem;
			border-radius: 8px;
			margin-top: 1rem;
		}
		.sample-codes code {
			display: block;
			margin: 0.5rem 0;
			padding: 0.5rem;
			background: white;
			border-radius: 4px;
			font-family: monospace;
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
				<i class='bx bx-user-circle' style="font-size: 2rem; color: var(--dark-grey);"></i>
			</a>
		</nav>

		<main>
			<div class="head-title">
				<div class="left">
					<h1>Generate Reward Codes</h1>
					<ul class="breadcrumb">
						<li>
							<a href="../index.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a href="index.php">Reward Codes</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Generate</a>
						</li>
					</ul>
				</div>
				<a href="index.php" class="btn-download">
					<i class='bx bx-arrow-back'></i>
					<span class="text">Back to Codes</span>
				</a>
			</div>

			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Create New Reward Codes</h3>
						<i class='bx bxs-ticket' ></i>
					</div>

					<?php if ($message): ?>
						<div class="alert <?= $message_type === 'success' ? 'alert-success' : 'alert-error' ?>">
							<?= htmlspecialchars($message) ?>
						</div>
					<?php endif; ?>

					<?php if ($generated_count > 0 && !empty($sample_codes)): ?>
						<div class="sample-codes">
							<h4 style="margin-bottom: 1rem;">Sample codes generated:</h4>
							<?php foreach ($sample_codes as $sample): ?>
								<code><?= htmlspecialchars($sample) ?></code>
							<?php endforeach; ?>
							<p style="margin-top: 1rem; color: var(--dark-grey); font-size: 0.9rem;">
								<a href="index.php" style="color: var(--blue);">View all codes →</a>
							</p>
						</div>
					<?php endif; ?>

					<form method="POST" action="" style="padding: 1.5rem;">
						<div class="form-group">
							<label for="count">Number of Codes to Generate</label>
							<input 
								type="number" 
								name="count" 
								id="count" 
								value="100" 
								min="1" 
								max="10000" 
								required
							>
							<small style="color: var(--dark-grey); font-size: 0.85rem;">Between 1 and 10,000 codes</small>
						</div>

						<div class="form-group">
							<label for="prefix">Code Prefix</label>
							<input 
								type="text" 
								name="prefix" 
								id="prefix" 
								value="Liyas" 
								placeholder="Liyas"
								maxlength="20"
								required
							>
							<small style="color: var(--dark-grey); font-size: 0.85rem;">Example: "Liyas" will create codes like "Liyas-ABC123XYZ"</small>
						</div>

						<button type="submit" name="generate" class="btn" style="width: 100%; padding: 1rem; background: var(--blue); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer;">
							<i class='bx bxs-plus-circle' ></i> Generate Codes
						</button>
					</form>

					<div style="padding: 1.5rem; margin-top: 1rem; background: var(--light); border-radius: 8px;">
						<h4 style="margin-bottom: 0.5rem;">💡 How it works:</h4>
						<ul style="color: var(--dark-grey); font-size: 0.9rem; line-height: 1.8;">
							<li>All bottles use ONE common QR code that redirects to the redeem page</li>
							<li>Each bottle has a UNIQUE reward code printed on the sticker</li>
							<li>Users manually enter the reward code on the redeem page</li>
							<li>Codes are 6-10 random characters after the prefix</li>
							<li>Example format: <code>Liyas-SFA123Fcg</code></li>
						</ul>
					</div>
				</div>
			</div>
		</main>
	</section>
	
	<script src="../assets/js/admin-script.js"></script>
</body>
</html>
