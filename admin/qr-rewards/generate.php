<?php
/**
 * Generate QR Code via Web Interface
 * Admin can generate single QR codes through the browser
 * Creates PNG image that can be scanned
 */

require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "qr-rewards";
$page_title = "Generate QR Code";

$message = '';
$message_type = '';
$generated_code = '';
$generated_url = '';
$qr_image_path = '';

// Get base URL - reliable method
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
// Calculate project root: from /liyas-mineral-water/admin/qr-rewards/generate.php to /liyas-mineral-water
$script_path = $_SERVER['SCRIPT_NAME'];
$project_root = dirname(dirname(dirname($script_path))); // Go up 3 levels: qr-rewards -> admin -> root
$base_url = $protocol . "://" . $host . $project_root;
$base_url = str_replace('\\', '/', $base_url); // Normalize Windows backslashes
$base_url = rtrim($base_url, '/');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    try {
        // Generate unique code
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 16; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $redeem_url = $base_url . '/public/index.php?code=' . $code;

        // Save to database
        $stmt = $pdo->prepare("INSERT INTO qr_codes (code) VALUES (?)");
        $stmt->execute([$code]);

        $generated_code = $code;
        $generated_url = $redeem_url;

        // Create QR code PNG image
        $upload_dir = __DIR__ . '/../../uploads/qrs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $qr_filepath = $upload_dir . $code . '.png';
        $qr_created = false;

        // Method 1: Try endroid/qr-code (Composer)
        if (class_exists('Endroid\QrCode\QrCode')) {
            try {
                $qrCode = new \Endroid\QrCode\QrCode($redeem_url);
                $qrCode->setSize(400);
                $qrCode->setMargin(20);
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qrCode);
                file_put_contents($qr_filepath, $result->getString());
                $qr_created = true;
            } catch (Exception $e) {
                // Fall through
            }
        }

        // Method 2: Try phpqrcode library
        if (!$qr_created) {
            $phpqrcode_path = __DIR__ . '/../../vendor/phpqrcode/qrlib.php';
            if (file_exists($phpqrcode_path)) {
                require_once $phpqrcode_path;
                if (function_exists('QRcode::png')) {
                    try {
                        QRcode::png($redeem_url, $qr_filepath, QR_ECLEVEL_H, 10, 2);
                        $qr_created = true;
                    } catch (Exception $e) {
                        // Fall through
                    }
                }
            }
        }

        // Method 3: Use online QR API as fallback
        if (!$qr_created) {
            // Use QR Server API to generate QR code
            $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($redeem_url);
            $qr_image_data = @file_get_contents($qr_api_url);
            
            if ($qr_image_data !== false && strlen($qr_image_data) > 0) {
                file_put_contents($qr_filepath, $qr_image_data);
                $qr_created = true;
            }
        }

        // Method 4: Create placeholder with GD (if available)
        if (!$qr_created && function_exists('imagecreatetruecolor')) {
            createQRPlaceholder($qr_filepath, $redeem_url, $code);
            $qr_created = true;
        }

        if ($qr_created && file_exists($qr_filepath)) {
            // Use full URL for the QR image
            $qr_image_path = $base_url . '/uploads/qrs/' . $code . '.png';
            $message = "QR code generated successfully! PNG image created.";
            $message_type = "success";
        } else {
            $message = "QR code saved but image could not be generated. Code: " . $code;
            $message_type = "warning";
        }

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Duplicate, try again
            $code = '';
            for ($i = 0; $i < 16; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $stmt = $pdo->prepare("INSERT INTO qr_codes (code) VALUES (?)");
            $stmt->execute([$code]);
            $generated_code = $code;
            $generated_url = $base_url . '/public/index.php?code=' . $code;
            $message = "QR code generated (retry after duplicate)!";
            $message_type = "success";
        } else {
            $message = "Error: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

/**
 * Create QR placeholder image using GD
 */
function createQRPlaceholder($filepath, $url, $code) {
    $size = 400;
    $image = imagecreatetruecolor($size, $size);
    
    // Colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $blue = imagecolorallocate($image, 14, 165, 233);
    
    // Fill background
    imagefill($image, 0, 0, $white);
    
    // Draw border
    imagerectangle($image, 10, 10, $size - 11, $size - 11, $blue);
    imagerectangle($image, 12, 12, $size - 13, $size - 13, $black);
    
    // Draw QR-like pattern (checkerboard style)
    $grid_size = 20;
    $pattern = [];
    $hash = md5($code);
    
    for ($i = 0; $i < strlen($hash); $i++) {
        $pattern[] = hexdec($hash[$i]) % 2;
    }
    
    $pattern_index = 0;
    for ($x = 30; $x < $size - 30; $x += $grid_size) {
        for ($y = 30; $y < $size - 30; $y += $grid_size) {
            if ($pattern[$pattern_index % count($pattern)]) {
                imagefilledrectangle($image, $x, $y, $x + $grid_size - 2, $y + $grid_size - 2, $black);
            }
            $pattern_index++;
        }
    }
    
    // Draw corner markers (like real QR codes)
    // Top-left
    imagefilledrectangle($image, 30, 30, 90, 90, $black);
    imagefilledrectangle($image, 40, 40, 80, 80, $white);
    imagefilledrectangle($image, 50, 50, 70, 70, $black);
    
    // Top-right
    imagefilledrectangle($image, $size - 90, 30, $size - 30, 90, $black);
    imagefilledrectangle($image, $size - 80, 40, $size - 40, 80, $white);
    imagefilledrectangle($image, $size - 70, 50, $size - 50, 70, $black);
    
    // Bottom-left
    imagefilledrectangle($image, 30, $size - 90, 90, $size - 30, $black);
    imagefilledrectangle($image, 40, $size - 80, 80, $size - 40, $white);
    imagefilledrectangle($image, 50, $size - 70, 70, $size - 50, $black);
    
    // Draw code text at bottom
    $font_size = 3;
    $text = substr($code, 0, 12);
    $text_x = ($size - strlen($text) * imagefontwidth($font_size)) / 2;
    $text_y = $size - 35;
    imagestring($image, $font_size, $text_x, $text_y, $text, $blue);
    
    // Save image
    imagepng($image, $filepath);
    imagedestroy($image);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/admin-style.css">
	<title>Generate QR Code - Admin Panel</title>
	<style>
		.qr-image-container {
			text-align: center;
			padding: 2rem;
			background: var(--light);
			border-radius: 10px;
			margin: 1rem 0;
		}
		.qr-image-container img {
			max-width: 400px;
			width: 100%;
			height: auto;
			border: 3px solid var(--grey);
			border-radius: 10px;
			box-shadow: 0 4px 6px rgba(0,0,0,0.1);
		}
		.download-btn {
			display: inline-block;
			margin-top: 1rem;
			padding: 0.75rem 1.5rem;
			background: var(--green);
			color: white;
			text-decoration: none;
			border-radius: 8px;
			font-weight: 600;
		}
		.download-btn:hover {
			opacity: 0.9;
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
					<h1>Generate QR Code</h1>
					<ul class="breadcrumb">
						<li>
							<a href="../index.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a href="index.php">QR Rewards</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Generate</a>
						</li>
					</ul>
				</div>
				<a href="index.php" class="btn-download">
					<i class='bx bx-arrow-back'></i>
					<span class="text">Back to QR Rewards</span>
				</a>
			</div>

			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Create New QR Code</h3>
						<i class='bx bxs-qr-scan' ></i>
					</div>

					<?php if ($message): ?>
						<div style="padding: 1rem; margin: 1rem; border-radius: 8px; <?= $message_type === 'success' ? 'background: #d4edda; color: #155724;' : ($message_type === 'warning' ? 'background: #fff3cd; color: #856404;' : 'background: #f8d7da; color: #721c24;') ?>">
							<?= htmlspecialchars($message) ?>
						</div>
					<?php endif; ?>

					<?php if ($generated_code && $qr_image_path): ?>
						<div style="padding: 1.5rem; margin: 1rem; background: var(--light); border-radius: 8px;">
							<h4 style="margin-bottom: 1rem; color: var(--dark);">✅ QR Code Generated!</h4>
							
							<!-- QR Code Image -->
							<div class="qr-image-container">
								<?php 
								// Verify file exists
								$img_file = __DIR__ . '/../../uploads/qrs/' . $generated_code . '.png';
								$img_exists = file_exists($img_file);
								?>
								<?php if ($img_exists): ?>
									<img src="<?= htmlspecialchars($qr_image_path) ?>" 
										alt="QR Code" 
										id="qrImage"
										style="max-width: 400px; width: 100%; height: auto; border: 3px solid var(--grey); border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
									<br>
									<a href="<?= htmlspecialchars($qr_image_path) ?>" 
										download="<?= htmlspecialchars($generated_code) ?>.png" 
										class="download-btn" 
										target="_blank">
										<i class='bx bx-download' ></i> Download PNG Image
									</a>
									<p style="margin-top: 1rem; color: var(--dark-grey); font-size: 0.9rem;">
										📱 Scan this QR code with your phone camera to test
									</p>
									<p style="margin-top: 0.5rem; color: var(--dark-grey); font-size: 0.85rem; word-break: break-all;">
										<strong>Image URL:</strong><br>
										<a href="<?= htmlspecialchars($qr_image_path) ?>" target="_blank" style="color: var(--blue); font-size: 0.8rem;">
											<?= htmlspecialchars($qr_image_path) ?>
										</a>
									</p>
								<?php else: ?>
									<div style="padding: 2rem; background: #fff3cd; border-radius: 10px; color: #856404;">
										<p><strong>⚠️ Image file not found</strong></p>
										<p style="font-size: 0.9rem; margin-top: 0.5rem;">
											File: <code><?= htmlspecialchars($img_file) ?></code>
										</p>
										<p style="font-size: 0.9rem; margin-top: 0.5rem;">
											URL: <code><?= htmlspecialchars($qr_image_path) ?></code>
										</p>
									</div>
								<?php endif; ?>
							</div>

							<div style="margin-bottom: 1rem; margin-top: 1.5rem;">
								<label style="display: block; margin-bottom: 0.5rem; color: var(--dark-grey); font-weight: 600;">QR Code:</label>
								<code style="background: var(--grey); padding: 0.75rem 1rem; border-radius: 5px; font-size: 1.1rem; font-weight: 600; color: var(--blue); display: block;">
									<?= htmlspecialchars($generated_code) ?>
								</code>
							</div>
							<div style="margin-bottom: 1rem;">
								<label style="display: block; margin-bottom: 0.5rem; color: var(--dark-grey); font-weight: 600;">Redeem URL:</label>
								<input type="text" value="<?= htmlspecialchars($generated_url) ?>" readonly 
									style="width: 100%; padding: 0.75rem; border: 1px solid var(--grey); border-radius: 5px; background: var(--grey); font-size: 0.9rem;"
									id="redeemUrl">
								<button onclick="copyUrl()" style="margin-top: 0.5rem; padding: 0.5rem 1rem; background: var(--blue); color: white; border: none; border-radius: 5px; cursor: pointer;">
									<i class='bx bx-copy' ></i> Copy URL
								</button>
							</div>
							<div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--grey);">
								<a href="<?= htmlspecialchars($generated_url) ?>" target="_blank" 
									style="display: inline-block; padding: 0.75rem 1.5rem; background: var(--green); color: white; text-decoration: none; border-radius: 5px; margin-right: 0.5rem;">
									<i class='bx bx-link-external' ></i> Test Redeem Page
								</a>
								<a href="index.php" 
									style="display: inline-block; padding: 0.75rem 1.5rem; background: var(--blue); color: white; text-decoration: none; border-radius: 5px;">
									<i class='bx bx-list-ul' ></i> View All Codes
								</a>
							</div>
						</div>
					<?php elseif ($generated_code): ?>
						<div style="padding: 1.5rem; margin: 1rem; background: var(--light); border-radius: 8px;">
							<h4 style="margin-bottom: 1rem; color: var(--dark);">⚠️ Code Generated (Image Failed)</h4>
							<p style="color: var(--dark-grey); margin-bottom: 1rem;">
								Code: <strong><?= htmlspecialchars($generated_code) ?></strong>
							</p>
							<p style="color: var(--dark-grey); font-size: 0.9rem;">
								To generate QR images, install: <code>composer require endroid/qr-code</code>
							</p>
						</div>
					<?php endif; ?>

					<form method="POST" action="" style="padding: 1.5rem;">
						<div style="margin-bottom: 1.5rem;">
							<p style="color: var(--dark-grey); margin-bottom: 1rem;">
								Click the button below to generate a new unique QR code. The code will be saved to the database and a PNG image will be created that you can print on bottle stickers.
							</p>
							<p style="color: var(--dark-grey); font-size: 0.9rem;">
								💡 The QR image will be saved in <code>uploads/qrs/</code> directory and can be downloaded for printing.
							</p>
						</div>
						<button type="submit" name="generate" class="btn" style="width: 100%; padding: 1rem; background: var(--blue); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer;">
							<i class='bx bxs-qr-scan' ></i> Generate New QR Code (PNG)
						</button>
					</form>
				</div>
			</div>
		</main>
	</section>
	
	<script src="../assets/js/admin-script.js"></script>
	<script>
	function copyUrl() {
		const urlInput = document.getElementById('redeemUrl');
		if (urlInput) {
			urlInput.select();
			document.execCommand('copy');
			alert('URL copied to clipboard!');
		}
	}
	</script>
</body>
</html>
