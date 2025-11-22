-- Update codes table to add customer information fields
-- Run this if you already have the codes table without customer fields

ALTER TABLE codes 
ADD COLUMN IF NOT EXISTS customer_name VARCHAR(150) NULL AFTER used_at,
ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(20) NULL AFTER customer_name,
ADD COLUMN IF NOT EXISTS customer_email VARCHAR(150) NULL AFTER customer_phone,
ADD COLUMN IF NOT EXISTS customer_address TEXT NULL AFTER customer_email;

-- Add indexes for better queries
CREATE INDEX IF NOT EXISTS idx_customer_phone ON codes(customer_phone);
CREATE INDEX IF NOT EXISTS idx_customer_email ON codes(customer_email);

