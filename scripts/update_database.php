<?php
/**
 * Update Database Schema
 * Adds customer data fields to reward_logs table
 */

require_once __DIR__ . '/../config/config.php';

echo "🔄 Updating Database Schema...\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Check if columns already exist
    $stmt = $pdo->query("SHOW COLUMNS FROM reward_logs LIKE 'customer_name'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Customer data columns already exist\n";
        exit(0);
    }

    // Add columns
    $pdo->exec("ALTER TABLE reward_logs 
        ADD COLUMN customer_name VARCHAR(150) NULL AFTER qr_code_id,
        ADD COLUMN customer_phone VARCHAR(20) NULL AFTER customer_name,
        ADD COLUMN customer_address TEXT NULL AFTER customer_phone");

    // Add indexes
    try {
        $pdo->exec("CREATE INDEX idx_customer_phone ON reward_logs(customer_phone)");
    } catch (PDOException $e) {
        // Index might already exist
    }

    try {
        $pdo->exec("CREATE INDEX idx_customer_name ON reward_logs(customer_name)");
    } catch (PDOException $e) {
        // Index might already exist
    }

    echo "✅ Database updated successfully!\n";
    echo "   • Added customer_name column\n";
    echo "   • Added customer_phone column\n";
    echo "   • Added customer_address column\n";
    echo "   • Added indexes for better performance\n\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✅ Columns already exist\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

