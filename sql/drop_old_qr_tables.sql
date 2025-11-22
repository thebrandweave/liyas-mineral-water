-- Drop Old QR Code System Tables
-- This script removes the old QR code tables that are no longer needed
-- after migrating to the new reward code system
--
-- WARNING: This will permanently delete the old QR code tables and all their data!
-- Make sure you have backed up any important data before running this script.
--
-- Run this script only after:
-- 1. You have migrated to the new reward code system
-- 2. You have verified the new system is working correctly
-- 3. You have backed up your database

-- Drop reward_logs table first (has foreign key to qr_codes)
DROP TABLE IF EXISTS reward_logs;

-- Drop qr_codes table
DROP TABLE IF EXISTS qr_codes;

-- Verify tables are dropped (optional - uncomment to check)
-- SHOW TABLES LIKE 'qr_%';
-- SHOW TABLES LIKE 'reward_%';

-- Success message
SELECT 'Old QR code tables dropped successfully!' AS message;

