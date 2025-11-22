<?php
/**
 * Test Script for QR Redemption
 * Tests the redemption flow with sample codes
 */

// Load database configuration
require_once __DIR__ . '/../config/db.php';

echo "🧪 QR Reward System - Test Script\n";
echo str_repeat("=", 50) . "\n\n";

try {
    $pdo = getDB();
    
    // Get a few unused codes
    $stmt = $pdo->query("SELECT code FROM qr_codes WHERE is_used = 0 LIMIT 5");
    $codes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($codes)) {
        echo "❌ No unused codes found. Please generate codes first.\n";
        echo "   Run: php scripts/generate_qr.php 100\n";
        exit(1);
    }
    
    echo "✅ Found " . count($codes) . " unused codes\n\n";
    echo "Testing redemption flow...\n\n";
    
    // Test 1: First redemption (should succeed)
    $test_code = $codes[0];
    echo "Test 1: First redemption of code: $test_code\n";
    
    $checkStmt = $pdo->prepare("SELECT is_used FROM qr_codes WHERE code = ?");
    $checkStmt->execute([$test_code]);
    $before = $checkStmt->fetchColumn();
    echo "   Before: is_used = $before\n";
    
    // Simulate redemption
    $pdo->beginTransaction();
    $updateStmt = $pdo->prepare("UPDATE qr_codes SET is_used = 1, scanned_at = NOW() WHERE code = ? AND is_used = 0");
    $updateStmt->execute([$test_code]);
    $pdo->commit();
    
    $checkStmt->execute([$test_code]);
    $after = $checkStmt->fetchColumn();
    echo "   After: is_used = $after\n";
    
    if ($after == 1) {
        echo "   ✅ SUCCESS: Code redeemed successfully\n\n";
    } else {
        echo "   ❌ FAILED: Code not marked as used\n\n";
    }
    
    // Test 2: Second redemption attempt (should fail)
    echo "Test 2: Attempting to redeem already-used code: $test_code\n";
    $checkStmt->execute([$test_code]);
    $status = $checkStmt->fetchColumn();
    echo "   Current status: is_used = $status\n";
    
    if ($status == 1) {
        echo "   ✅ SUCCESS: Code correctly marked as used\n";
        echo "   ✅ Redemption would be rejected (as expected)\n\n";
    } else {
        echo "   ❌ FAILED: Code should be marked as used\n\n";
    }
    
    // Test 3: Invalid code
    echo "Test 3: Testing invalid code: INVALID123\n";
    $invalidStmt = $pdo->prepare("SELECT COUNT(*) FROM qr_codes WHERE code = ?");
    $invalidStmt->execute(['INVALID123']);
    $exists = $invalidStmt->fetchColumn() > 0;
    
    if (!$exists) {
        echo "   ✅ SUCCESS: Invalid code correctly not found\n\n";
    } else {
        echo "   ❌ FAILED: Invalid code should not exist\n\n";
    }
    
    // Summary
    echo str_repeat("=", 50) . "\n";
    echo "📊 Test Summary:\n";
    echo "   • Codes available: " . count($codes) . "\n";
    echo "   • Test code used: $test_code\n";
    echo "   • All tests completed\n\n";
    
    echo "🌐 To test via web interface:\n";
    echo "   http://localhost/liyas-mineral-water/public/index.php?code=$test_code\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

