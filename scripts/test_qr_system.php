<?php
/**
 * Comprehensive QR System Test Script
 * Tests the entire QR reward system
 */

require_once __DIR__ . '/../config/config.php';

echo "🧪 QR Reward System - Comprehensive Test\n";
echo str_repeat("=", 60) . "\n\n";

$errors = [];
$success = [];

// Test 1: Check Database Connection
echo "1. Testing Database Connection...\n";
try {
    $test = $pdo->query("SELECT 1");
    $success[] = "Database connection: ✅ OK";
    echo "   ✅ Database connected successfully\n\n";
} catch (PDOException $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n\n";
    die("Cannot continue without database connection.\n");
}

// Test 2: Check if QR tables exist
echo "2. Checking QR Tables...\n";
try {
    $tables = ['qr_codes', 'reward_logs'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $success[] = "Table '$table': ✅ Exists";
            echo "   ✅ Table '$table' exists\n";
        } else {
            $errors[] = "Table '$table' does not exist";
            echo "   ❌ Table '$table' does not exist\n";
            echo "   💡 Run: mysql -u root -p liyas_international < sql/qr_tables_only.sql\n";
        }
    }
    echo "\n";
} catch (PDOException $e) {
    $errors[] = "Error checking tables: " . $e->getMessage();
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Check existing QR codes
echo "3. Checking Existing QR Codes...\n";
try {
    $total = $pdo->query("SELECT COUNT(*) FROM qr_codes")->fetchColumn();
    $used = $pdo->query("SELECT COUNT(*) FROM qr_codes WHERE is_used = 1")->fetchColumn();
    $unused = $pdo->query("SELECT COUNT(*) FROM qr_codes WHERE is_used = 0")->fetchColumn();
    
    echo "   📊 Statistics:\n";
    echo "      • Total QR codes: $total\n";
    echo "      • Redeemed: $used\n";
    echo "      • Available: $unused\n";
    
    if ($total == 0) {
        echo "   ⚠️  No QR codes found. Generate some first!\n";
        echo "   💡 Run: php scripts/generate_qr.php 10 http://localhost/liyas-mineral-water\n\n";
    } else {
        $success[] = "QR codes found: $total total";
        echo "   ✅ QR codes found in database\n\n";
    }
} catch (PDOException $e) {
    $errors[] = "Error checking QR codes: " . $e->getMessage();
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Check uploads directory
echo "4. Checking Uploads Directory...\n";
$upload_dir = __DIR__ . '/../uploads/qrs/';
if (is_dir($upload_dir)) {
    if (is_writable($upload_dir)) {
        $success[] = "Uploads directory: ✅ Writable";
        echo "   ✅ Directory exists and is writable\n";
        
        $files = glob($upload_dir . '*.png');
        echo "   📁 QR images found: " . count($files) . "\n";
    } else {
        $errors[] = "Uploads directory is not writable";
        echo "   ❌ Directory exists but is not writable\n";
        echo "   💡 Fix: chmod 755 uploads/qrs/\n";
    }
} else {
    $errors[] = "Uploads directory does not exist";
    echo "   ❌ Directory does not exist: $upload_dir\n";
    echo "   💡 Creating directory...\n";
    if (mkdir($upload_dir, 0755, true)) {
        echo "   ✅ Directory created\n";
    } else {
        echo "   ❌ Failed to create directory\n";
    }
}
echo "\n";

// Test 5: Test QR code generation (if no codes exist)
if (isset($total) && $total == 0) {
    echo "5. Generating Test QR Codes...\n";
    echo "   Would you like to generate 10 test codes? (This will be done automatically)\n";
    echo "   Generating 10 test codes now...\n";
    
    // Generate 10 test codes
    $base_url = "http://localhost/liyas-mineral-water";
    $generated = 0;
    
    for ($i = 0; $i < 10; $i++) {
        $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 16));
        $url = $base_url . '/public/index.php?code=' . $code;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO qr_codes (code) VALUES (?)");
            $stmt->execute([$code]);
            $generated++;
            
            // Create placeholder QR image
            $filepath = $upload_dir . $code . '.png';
            if (function_exists('imagecreatetruecolor')) {
                $size = 200;
                $image = imagecreatetruecolor($size, $size);
                $white = imagecolorallocate($image, 255, 255, 255);
                $black = imagecolorallocate($image, 0, 0, 0);
                imagefill($image, 0, 0, $white);
                imagestring($image, 5, 20, 90, substr($code, 0, 10), $black);
                imagepng($image, $filepath);
                imagedestroy($image);
            }
        } catch (PDOException $e) {
            if ($e->getCode() != 23000) { // Ignore duplicate errors
                echo "   ⚠️  Error generating code: " . $e->getMessage() . "\n";
            }
        }
    }
    
    if ($generated > 0) {
        $success[] = "Generated $generated test QR codes";
        echo "   ✅ Generated $generated test QR codes\n";
        echo "   📁 QR images saved to: $upload_dir\n\n";
    }
}

// Test 6: Test redemption flow
echo "6. Testing Redemption Flow...\n";
try {
    // Get an unused code
    $stmt = $pdo->query("SELECT code FROM qr_codes WHERE is_used = 0 LIMIT 1");
    $test_code = $stmt->fetchColumn();
    
    if ($test_code) {
        echo "   📝 Testing with code: $test_code\n";
        
        // Simulate redemption
        $pdo->beginTransaction();
        $update = $pdo->prepare("UPDATE qr_codes SET is_used = 1, scanned_at = NOW() WHERE code = ? AND is_used = 0");
        $update->execute([$test_code]);
        
        if ($update->rowCount() > 0) {
            $pdo->commit();
            echo "   ✅ Redemption test: SUCCESS\n";
            $success[] = "Redemption flow: ✅ Working";
            
            // Test double redemption
            $update2 = $pdo->prepare("UPDATE qr_codes SET is_used = 1 WHERE code = ? AND is_used = 0");
            $update2->execute([$test_code]);
            if ($update2->rowCount() == 0) {
                echo "   ✅ Double redemption prevention: WORKING\n";
                $success[] = "Double redemption prevention: ✅ Working";
            } else {
                echo "   ❌ Double redemption prevention: FAILED\n";
                $errors[] = "Double redemption prevention failed";
            }
        } else {
            $pdo->rollBack();
            echo "   ❌ Redemption test: FAILED\n";
            $errors[] = "Redemption flow failed";
        }
    } else {
        echo "   ⚠️  No unused codes available for testing\n";
    }
    echo "\n";
} catch (PDOException $e) {
    $errors[] = "Redemption test error: " . $e->getMessage();
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 7: Check file paths
echo "7. Checking File Paths...\n";
$paths = [
    'Admin QR Page' => __DIR__ . '/../admin/qr-rewards/index.php',
    'Public Redeem Page' => __DIR__ . '/../public/index.php',
    'QR Check API' => __DIR__ . '/../public/qr-check.php',
];

foreach ($paths as $name => $path) {
    if (file_exists($path)) {
        $success[] = "$name: ✅ Exists";
        echo "   ✅ $name: Found\n";
    } else {
        $errors[] = "$name file missing";
        echo "   ❌ $name: Not found at $path\n";
    }
}
echo "\n";

// Summary
echo str_repeat("=", 60) . "\n";
echo "📊 TEST SUMMARY\n";
echo str_repeat("=", 60) . "\n";
echo "✅ Successful: " . count($success) . "\n";
echo "❌ Errors: " . count($errors) . "\n\n";

if (count($success) > 0) {
    echo "✅ SUCCESSFUL TESTS:\n";
    foreach ($success as $msg) {
        echo "   • $msg\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ ERRORS FOUND:\n";
    foreach ($errors as $msg) {
        echo "   • $msg\n";
    }
    echo "\n";
}

if (count($errors) == 0) {
    echo "🎉 All tests passed! System is ready to use.\n\n";
    echo "🌐 Access Points:\n";
    echo "   • Admin Panel: http://localhost/liyas-mineral-water/admin/qr-rewards/index.php\n";
    echo "   • Public Redeem: http://localhost/liyas-mineral-water/public/index.php\n";
    echo "   • API Endpoint: http://localhost/liyas-mineral-water/public/qr-check.php?code=YOUR_CODE\n";
} else {
    echo "⚠️  Please fix the errors above before using the system.\n";
}

echo "\n";

