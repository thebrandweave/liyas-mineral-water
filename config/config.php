<?php
// ✅ Manual Firebase JWT include (correct order!)

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ============================================
// DATABASE CONFIGURATION
// ============================================
// Switch between LOCAL and LIVE by commenting/uncommenting

// LIVE PRODUCTION
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'u232955123_liyas_inter');
define('DB_USER', 'u232955123_liyas');
define('DB_PASS', 'Brandweave@24');

// LOCAL DEVELOPMENT (XAMPP)    
// define('DB_HOST', 'localhost');
// define('DB_PORT', 3306);
// define('DB_NAME', 'liyas_international');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// ============================================
// PDO CONNECTION (for existing codebase)
// ============================================
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Set timezone to Indian Standard Time (IST) for PHP
    date_default_timezone_set('Asia/Kolkata');
    
    // Set MySQL timezone to IST as well
    $pdo->exec("SET time_zone = '+05:30'");
    
    // Verify connection by running a simple query
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    error_log("PDO Database connection failed: " . $e->getMessage());
    
    // More detailed error message for debugging
    $error_msg = "Database connection failed. ";
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        $error_msg .= "Database '" . DB_NAME . "' does not exist. Please create it first.";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        $error_msg .= "Access denied. Please check your database username and password.";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        $error_msg .= "Cannot connect to database server. Please make sure MySQL is running.";
    } else {
        $error_msg .= "Error: " . $e->getMessage();
    }
    
    die($error_msg);
}

// ============================================
// MYSQLI CONNECTION (alternative)
// ============================================
function getMysqliConnection() {
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }
        
        // Set charset
        $mysqli->set_charset("utf8mb4");
        
        return $mysqli;
    } catch (Exception $e) {
        error_log("MySQLi Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// ============================================
// TEST CONNECTION FUNCTION
// ============================================
function testDatabaseConnection() {
    try {
        $mysqli = getMysqliConnection();
        echo "Database connection successful!";
        $mysqli->close();
        return true;
    } catch (Exception $e) {
        echo "Database connection failed: " . $e->getMessage();
        return false;
    }
}

// ============================================
// JWT CONFIGURATION
// ============================================
$JWT_SECRET = "super_secure_secret_987654321";
$JWT_EXPIRE = 3600;

// ============================================
// PATH CONFIGURATION
// ============================================
// Root path detection (project root)
$ROOT_PATH = dirname(__DIR__);  // e.g. C:\xampp\htdocs\liyas-mineral-water

define('UPLOAD_DIR', '/uploads/');
define('UPLOAD_DIR_SERVER', $ROOT_PATH . '/uploads/');

// ============================================
// SESSION START
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// HELPER FUNCTIONS
// ============================================
// Get PDO connection (for backward compatibility)
function getDB() {
    global $pdo;
    return $pdo;
}

// Get current environment
function getEnvironment() {
    // Determine environment based on database name
    if (defined('DB_NAME')) {
        if (DB_NAME === 'u232955123_liyas_inter') {
            return 'live';
        } elseif (DB_NAME === 'liyas_international') {
            return 'local';
        }
    }
    return 'unknown';
}

// ============================================
// VERIFY DATABASE CONNECTION
// ============================================
function verifyDatabaseConnection() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Uncomment to test database connection
// testDatabaseConnection();
