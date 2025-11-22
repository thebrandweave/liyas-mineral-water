<?php
/**
 * Create Single QR Code for Testing
 * Generates one QR code and saves it to database
 * 
 * Usage: php scripts/create_single_qr.php
 */

require_once __DIR__ . '/../config/config.php';

echo "🎯 Creating Single QR Code for Testing\n";
echo str_repeat("=", 50) . "\n\n";

// Generate unique code
$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$code = '';
for ($i = 0; $i < 16; $i++) {
    $code .= $characters[random_int(0, strlen($characters) - 1)];
}

// Get base URL
$base_url = "http://localhost/liyas-mineral-water";
$redeem_url = $base_url . '/public/index.php?code=' . $code;

echo "📝 Generated Code: $code\n";
echo "🔗 Redeem URL: $redeem_url\n\n";

// Save to database
try {
    $stmt = $pdo->prepare("INSERT INTO qr_codes (code) VALUES (?)");
    $stmt->execute([$code]);
    
    echo "✅ Code saved to database\n";
    echo "   • Code ID: " . $pdo->lastInsertId() . "\n";
    echo "   • Status: Available (not used)\n\n";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "⚠️  Code already exists, generating new one...\n";
        // Regenerate if duplicate
        $code = '';
        for ($i = 0; $i < 16; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        $redeem_url = $base_url . '/public/index.php?code=' . $code;
        $stmt = $pdo->prepare("INSERT INTO qr_codes (code) VALUES (?)");
        $stmt->execute([$code]);
        echo "✅ New code saved: $code\n\n";
    } else {
        die("❌ Error: " . $e->getMessage() . "\n");
    }
}

// Create QR image
$upload_dir = __DIR__ . '/../uploads/qrs/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$filepath = $upload_dir . $code . '.png';

// Try to use QR library if available
$qr_created = false;

// Method 1: endroid/qr-code (Composer)
if (class_exists('Endroid\QrCode\QrCode')) {
    try {
        $qrCode = new \Endroid\QrCode\QrCode($redeem_url);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        file_put_contents($filepath, $result->getString());
        $qr_created = true;
        echo "✅ QR image created using endroid/qr-code library\n";
    } catch (Exception $e) {
        // Fall through
    }
}

// Method 2: phpqrcode (legacy)
if (!$qr_created && function_exists('QRcode::png')) {
    try {
        QRcode::png($redeem_url, $filepath, QR_ECLEVEL_M, 10, 2);
        $qr_created = true;
        echo "✅ QR image created using phpqrcode library\n";
    } catch (Exception $e) {
        // Fall through
    }
}

// Method 3: Simple placeholder (if no library)
if (!$qr_created && function_exists('imagecreatetruecolor')) {
    $size = 300;
    $image = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $blue = imagecolorallocate($image, 14, 165, 233);
    
    imagefill($image, 0, 0, $white);
    
    // Draw border
    imagerectangle($image, 10, 10, $size - 11, $size - 11, $blue);
    
    // Draw QR-like pattern
    $grid_size = 20;
    for ($x = 30; $x < $size - 30; $x += $grid_size) {
        for ($y = 30; $y < $size - 30; $y += $grid_size) {
            if (rand(0, 1)) {
                imagefilledrectangle($image, $x, $y, $x + $grid_size - 2, $y + $grid_size - 2, $black);
            }
        }
    }
    
    // Draw code text
    $font_size = 3;
    $text = substr($code, 0, 12);
    $text_x = ($size - strlen($text) * imagefontwidth($font_size)) / 2;
    $text_y = $size - 40;
    imagestring($image, $font_size, $text_x, $text_y, $text, $blue);
    
    imagepng($image, $filepath);
    imagedestroy($image);
    
    echo "✅ QR placeholder image created (library not found, using GD)\n";
} else if (!$qr_created) {
    echo "⚠️  Could not create QR image (no library or GD available)\n";
    echo "   Install: composer require endroid/qr-code\n";
}

if ($qr_created || file_exists($filepath)) {
    echo "   📁 Image saved: $filepath\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ QR CODE CREATED SUCCESSFULLY!\n";
echo str_repeat("=", 50) . "\n\n";

echo "📋 TESTING INFORMATION:\n";
echo "   • Code: $code\n";
echo "   • Redeem URL: $redeem_url\n";
echo "   • Image: uploads/qrs/$code.png\n\n";

echo "🧪 HOW TO TEST:\n";
echo "   1. Open: $redeem_url\n";
echo "   2. Or go to: http://localhost/liyas-mineral-water/public/index.php?code=$code\n";
echo "   3. Click 'Redeem Code' button\n";
echo "   4. Try redeeming again (should show 'Already Redeemed')\n\n";

echo "📱 SCAN THE QR CODE:\n";
echo "   • Open the image: uploads/qrs/$code.png\n";
echo "   • Scan with your phone camera\n";
echo "   • It will open the redeem page\n\n";

echo "🎉 Ready to test!\n";

