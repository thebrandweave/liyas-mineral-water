<?php
/**
 * Bulk QR Code Generator
 * Generates QR codes and saves them to database and filesystem
 * 
 * Usage: php scripts/generate_qr.php [count] [base_url]
 * Example: php scripts/generate_qr.php 1000 https://mydomain.com
 */

// Load database configuration
require_once __DIR__ . '/../config/db.php';

// Check if phpqrcode library exists
$qr_library_path = __DIR__ . '/../vendor/phpqrcode/qrlib.php';
if (!file_exists($qr_library_path)) {
    // Try alternative path
    $qr_library_path = __DIR__ . '/../includes/phpqrcode/qrlib.php';
    if (!file_exists($qr_library_path)) {
        die("Error: phpqrcode library not found.\nPlease install it using: composer require endroid/qr-code\nOr download from: https://github.com/endroid/qr-code\n");
    }
}

// Try to load QR library (optional)
$qr_library_loaded = false;
if (file_exists($qr_library_path)) {
    require_once $qr_library_path;
    $qr_library_loaded = true;
} else {
    // Try Composer autoload
    $composer_autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($composer_autoload)) {
        require_once $composer_autoload;
        $qr_library_loaded = true;
    }
}

// Configuration
$count = isset($argv[1]) ? (int)$argv[1] : 1000;
$base_url = isset($argv[2]) ? rtrim($argv[2], '/') : 'https://mydomain.com';
$upload_dir = __DIR__ . '/../uploads/qrs/';

// Validate count
if ($count < 1 || $count > 10000) {
    die("Error: Count must be between 1 and 10,000\n");
}

// Create upload directory if it doesn't exist
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        die("Error: Could not create upload directory: $upload_dir\n");
    }
}

// Ensure directory is writable
if (!is_writable($upload_dir)) {
    die("Error: Upload directory is not writable: $upload_dir\n");
}

if (!$qr_library_loaded) {
    echo "⚠️  Warning: QR library not found. Will create placeholder images.\n";
    echo "   Install with: composer require endroid/qr-code\n\n";
}

echo "🚀 Starting QR Code Generation...\n";
echo "📊 Generating $count QR codes\n";
echo "🌐 Base URL: $base_url\n";
echo "📁 Output directory: $upload_dir\n\n";

try {
    $pdo = getDB();
    
    // Start transaction
    $pdo->beginTransaction();
    
    $insertStmt = $pdo->prepare("
        INSERT INTO qr_codes (code) 
        VALUES (?)
    ");
    
    $generated = 0;
    $skipped = 0;
    $errors = 0;
    
    // Progress tracking
    $start_time = microtime(true);
    $last_progress = 0;
    
    for ($i = 0; $i < $count; $i++) {
        // Generate unique random code
        $code = generateUniqueCode($pdo);
        
        // Create full URL
        $url = $base_url . '/redeem?code=' . $code;
        
        // Generate QR code image
        $filename = $code . '.png';
        $filepath = $upload_dir . $filename;
        
        try {
            // Try to generate QR code using available library
            $qr_generated = false;
            
            // Method 1: endroid/qr-code (Composer)
            if (class_exists('Endroid\QrCode\QrCode')) {
                try {
                    $qrCode = new \Endroid\QrCode\QrCode($url);
                    $qrCode->setSize(300);
                    $qrCode->setMargin(10);
                    $writer = new \Endroid\QrCode\Writer\PngWriter();
                    $result = $writer->write($qrCode);
                    file_put_contents($filepath, $result->getString());
                    $qr_generated = true;
                } catch (Exception $e) {
                    // Fall through to next method
                }
            }
            
            // Method 2: phpqrcode library (legacy)
            if (!$qr_generated && function_exists('QRcode::png')) {
                QRcode::png($url, $filepath, QR_ECLEVEL_M, 10, 2);
                $qr_generated = true;
            }
            
            // Method 3: Create placeholder if no library available
            if (!$qr_generated) {
                createPlaceholderQR($filepath, $code, $url);
            }
            
            // Insert into database
            $insertStmt->execute([$code]);
            $generated++;
            
            // Progress update every 100 codes
            if ($generated % 100 == 0) {
                $elapsed = microtime(true) - $start_time;
                $rate = $generated / $elapsed;
                $remaining = ($count - $generated) / $rate;
                echo sprintf(
                    "✓ Generated %d/%d codes (%.1f codes/sec, ~%.0f seconds remaining)\n",
                    $generated,
                    $count,
                    $rate,
                    $remaining
                );
            }
            
        } catch (PDOException $e) {
            // Check if it's a duplicate key error
            if ($e->getCode() == 23000) {
                $skipped++;
                // Delete the file if it was created
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            } else {
                $errors++;
                error_log("Error generating QR code $code: " . $e->getMessage());
            }
        } catch (Exception $e) {
            $errors++;
            error_log("Error creating QR image for $code: " . $e->getMessage());
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Summary
    $elapsed = microtime(true) - $start_time;
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ Generation Complete!\n";
    echo str_repeat("=", 50) . "\n";
    echo "📊 Statistics:\n";
    echo "   • Generated: $generated codes\n";
    echo "   • Skipped (duplicates): $skipped codes\n";
    echo "   • Errors: $errors codes\n";
    echo "   • Time elapsed: " . number_format($elapsed, 2) . " seconds\n";
    echo "   • Average rate: " . number_format($generated / $elapsed, 2) . " codes/second\n";
    echo "\n📁 Files saved to: $upload_dir\n";
    echo "💾 Database records created: $generated\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("❌ Fatal error: " . $e->getMessage() . "\n");
}

/**
 * Generate unique random code
 */
function generateUniqueCode($pdo, $length = 16) {
    $max_attempts = 100;
    $attempts = 0;
    
    do {
        // Generate random alphanumeric code
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Check if code already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM qr_codes WHERE code = ?");
        $stmt->execute([$code]);
        $exists = $stmt->fetchColumn() > 0;
        
        $attempts++;
    } while ($exists && $attempts < $max_attempts);
    
    if ($exists) {
        throw new Exception("Could not generate unique code after $max_attempts attempts");
    }
    
    return $code;
}

/**
 * Create placeholder QR code image (fallback)
 */
function createPlaceholderQR($filepath, $code, $url = '') {
    if (!function_exists('imagecreatetruecolor')) {
        // If GD library not available, create a simple text file
        file_put_contents($filepath . '.txt', "QR Code: $code\nURL: $url\n");
        return;
    }
    
    $size = 300;
    $image = imagecreatetruecolor($size, $size);
    
    // Colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 200, 200, 200);
    $blue = imagecolorallocate($image, 102, 126, 234);
    
    // Fill background
    imagefill($image, 0, 0, $white);
    
    // Draw border
    imagerectangle($image, 5, 5, $size - 6, $size - 6, $gray);
    
    // Draw QR-like pattern (simple grid)
    $grid_size = 15;
    for ($x = 20; $x < $size - 20; $x += $grid_size) {
        for ($y = 20; $y < $size - 20; $y += $grid_size) {
            if (rand(0, 1)) {
                imagefilledrectangle($image, $x, $y, $x + $grid_size - 2, $y + $grid_size - 2, $black);
            }
        }
    }
    
    // Draw code text at bottom
    $font_size = 3;
    $text = substr($code, 0, 20); // Limit text length
    $text_x = ($size - strlen($text) * imagefontwidth($font_size)) / 2;
    $text_y = $size - 30;
    imagestring($image, $font_size, $text_x, $text_y, $text, $blue);
    
    // Save image
    imagepng($image, $filepath);
    imagedestroy($image);
}

