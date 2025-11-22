<?php
/**
 * Bulk Reward Code Generator
 * Generates unique reward codes and inserts them into the database
 * 
 * Usage: php scripts/generate_reward_codes.php [count] [prefix]
 * Example: php scripts/generate_reward_codes.php 1000 Liyas-
 */

// Load database configuration
require_once __DIR__ . '/../config/config.php';

// Configuration
$count = isset($argv[1]) ? (int)$argv[1] : 1000;
$prefix = isset($argv[2]) ? rtrim($argv[2], '-') . '-' : 'Liyas-';

// Validate count
if ($count < 1 || $count > 100000) {
    die("Error: Count must be between 1 and 100,000\n");
}

echo "🚀 Starting Reward Code Generation...\n";
echo "📊 Generating $count reward codes\n";
echo "🏷️  Prefix: $prefix\n\n";

try {
    // Start transaction
    $pdo->beginTransaction();
    
    $insertStmt = $pdo->prepare("INSERT INTO codes (reward_code) VALUES (?)");
    
    $generated = 0;
    $skipped = 0;
    $errors = 0;
    
    // Progress tracking
    $start_time = microtime(true);
    
    for ($i = 0; $i < $count; $i++) {
        // Generate unique random code (6-10 characters after prefix)
        $code_length = random_int(6, 10);
        $code = generateUniqueRewardCode($pdo, $prefix, $code_length);
        
        try {
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
            } else {
                $errors++;
                error_log("Error generating reward code: " . $e->getMessage());
            }
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
    echo "\n💾 Database records created: $generated\n";
    
    // Show sample codes
    $sampleStmt = $pdo->query("SELECT reward_code FROM codes ORDER BY id DESC LIMIT 5");
    $samples = $sampleStmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($samples)) {
        echo "\n📝 Sample codes generated:\n";
        foreach ($samples as $sample) {
            echo "   • $sample\n";
        }
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("❌ Fatal error: " . $e->getMessage() . "\n");
}

/**
 * Generate unique random reward code
 */
function generateUniqueRewardCode($pdo, $prefix, $length) {
    $max_attempts = 100;
    $attempts = 0;
    
    do {
        // Generate random alphanumeric code
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $suffix = '';
        for ($i = 0; $i < $length; $i++) {
            $suffix .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        $code = $prefix . $suffix;
        
        // Check if code already exists
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

