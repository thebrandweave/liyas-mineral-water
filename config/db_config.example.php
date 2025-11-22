<?php
/**
 * Database Configuration Example
 * Copy this file to db_config.php and configure your settings
 * 
 * IMPORTANT: Add db_config.php to .gitignore to keep passwords secure!
 */

// ============================================
// ENVIRONMENT SELECTION
// ============================================
// Uncomment the environment you want to use:

// LOCAL DEVELOPMENT (XAMPP)
$ENVIRONMENT = 'local';

// LIVE PRODUCTION
// $ENVIRONMENT = 'live';

// ============================================
// LOCAL DATABASE CONFIGURATION
// ============================================
if ($ENVIRONMENT === 'local') {
    $host = "localhost";
    $dbname = "liyas_international";
    $username = "root";
    $password = "";
}

// ============================================
// LIVE DATABASE CONFIGURATION
// ============================================
if ($ENVIRONMENT === 'live') {
    $host = "localhost"; // Usually localhost on shared hosting
    $dbname = "u232955123_liyas_inter";
    $username = "u232955123_liyas";
    $password = "Brandweave@24"; // ⚠️ ADD YOUR LIVE DATABASE PASSWORD HERE
}

// ============================================
// PDO CONNECTION OPTIONS
// ============================================
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

// ============================================
// CREATE DATABASE CONNECTION
// ============================================
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Log error securely (don't expose credentials)
    error_log("Database connection failed [{$ENVIRONMENT}]: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Helper function to get database connection
function getDB() {
    global $pdo;
    return $pdo;
}

// Helper function to get current environment
function getEnvironment() {
    global $ENVIRONMENT;
    return $ENVIRONMENT;
}

