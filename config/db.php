<?php
/**
 * Database Configuration for QR Reward System
 * Uses the main database configuration from config.php
 */

// Use the main database configuration (constants from config.php)
require_once __DIR__ . '/config.php';

// PDO connection is already available as $pdo from config.php
// MySQLi connection is available via getMysqliConnection() function

// Helper function to get database connection (backward compatibility)
function getDB() {
    global $pdo;
    return $pdo;
}
