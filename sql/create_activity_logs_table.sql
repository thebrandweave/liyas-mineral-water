-- Activity Logs Table
-- This table tracks all admin activities in the system

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `action_type` varchar(50) NOT NULL COMMENT 'create, update, delete, login, logout, view, export, etc.',
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'product, category, user, order, qr_reward, etc.',
  `entity_id` int(11) DEFAULT NULL COMMENT 'ID of the affected entity',
  `description` text NOT NULL COMMENT 'Human-readable description of the activity',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IPv4 or IPv6 address',
  `user_agent` varchar(500) DEFAULT NULL COMMENT 'Browser user agent',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`activity_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_entity_type` (`entity_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_admin_created` (`admin_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraint (optional, if admins table exists)
-- ALTER TABLE `activity_logs` 
-- ADD CONSTRAINT `fk_activity_logs_admin` 
-- FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) 
-- ON DELETE CASCADE ON UPDATE CASCADE;

