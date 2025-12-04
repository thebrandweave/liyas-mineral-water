<?php
/**
 * Activity Logger Helper
 * 
 * This file provides functions to log admin activities throughout the system.
 * Include this file in any admin page where you want to track activities.
 */

/**
 * Log an admin activity
 * 
 * @param PDO $pdo Database connection
 * @param int $admin_id Admin ID who performed the action
 * @param string $admin_name Admin username/name
 * @param string $action_type Type of action (create, update, delete, login, logout, view, export, etc.)
 * @param string|null $entity_type Type of entity (product, category, user, order, qr_reward, etc.)
 * @param int|null $entity_id ID of the affected entity
 * @param string $description Human-readable description
 * @return bool True on success, false on failure
 */
function logActivity($pdo, $admin_id, $admin_name, $action_type, $entity_type = null, $entity_id = null, $description = '') {
    try {
        // Get IP address
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        // Get user agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        if ($user_agent && strlen($user_agent) > 500) {
            $user_agent = substr($user_agent, 0, 500);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs 
            (admin_id, admin_name, action_type, entity_type, entity_id, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $admin_id,
            $admin_name,
            $action_type,
            $entity_type,
            $entity_id,
            $description,
            $ip_address,
            $user_agent
        ]);
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
        return false;
    }
}

/**
 * Quick log function using session data
 * This is a convenience function that automatically gets admin info from session
 * 
 * @param PDO $pdo Database connection
 * @param string $action_type Type of action
 * @param string|null $entity_type Type of entity
 * @param int|null $entity_id ID of the affected entity
 * @param string $description Human-readable description
 * @return bool True on success, false on failure
 */
function quickLog($pdo, $action_type, $entity_type = null, $entity_id = null, $description = '') {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
        return false;
    }
    
    return logActivity(
        $pdo,
        $_SESSION['admin_id'],
        $_SESSION['admin_name'],
        $action_type,
        $entity_type,
        $entity_id,
        $description
    );
}

