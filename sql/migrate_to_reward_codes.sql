-- Migration Script: QR Codes to Reward Codes
-- Run this script to migrate from the old QR code system to the new reward code system
-- 
-- WARNING: This will DELETE the old QR code tables and create the new codes table
-- Make sure to backup your data before running this!

-- Step 1: Drop old tables (if they exist)
DROP TABLE IF EXISTS reward_logs;
DROP TABLE IF EXISTS qr_codes;

-- Step 2: Create new codes table
CREATE TABLE IF NOT EXISTS codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reward_code VARCHAR(50) UNIQUE NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reward_code (reward_code),
    INDEX idx_is_used (is_used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration complete!
-- Now you can generate reward codes using:
-- php scripts/generate_reward_codes.php [count] [prefix]

