-- Update reward_logs table to store customer information
-- Run this to add customer data fields

ALTER TABLE reward_logs 
ADD COLUMN IF NOT EXISTS customer_name VARCHAR(150) NULL AFTER qr_code_id,
ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(20) NULL AFTER customer_name,
ADD COLUMN IF NOT EXISTS customer_address TEXT NULL AFTER customer_phone;

-- Add index for better queries
CREATE INDEX IF NOT EXISTS idx_customer_phone ON reward_logs(customer_phone);
CREATE INDEX IF NOT EXISTS idx_customer_name ON reward_logs(customer_name);

