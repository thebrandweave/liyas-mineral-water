<?php
/**
 * Live Server Setup Script
 * Run this ONCE on your live server to create db_config.php
 * 
 * Usage: 
 * 1. Upload this file to your server
 * 2. Visit: https://liyasinternational.com/setup_live_config.php
 * 3. Delete this file after setup is complete
 */

// Security: Only allow if db_config.php doesn't exist
if (file_exists(__DIR__ . '/config/db_config.php')) {
    die('db_config.php already exists. Setup not needed.');
}

// Create db_config.php with live settings
$config_content = <<<'PHP'
<?php
/**
 * Database Configuration
 * Switch between LOCAL and LIVE by commenting/uncommenting the appropriate section
 */

// ============================================
// ENVIRONMENT SELECTION
// ============================================
// Uncomment the environment you want to use:

// LOCAL DEVELOPMENT (XAMPP)
// $ENVIRONMENT = 'local';

// LIVE PRODUCTION
$ENVIRONMENT = 'live';

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
    $password = "Brandweave@24"; // Live database password
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
PHP;

// Write the file
$config_file = __DIR__ . '/config/db_config.php';
if (file_put_contents($config_file, $config_content)) {
    // Test connection
    require_once $config_file;
    
    echo "<!DOCTYPE html><html><head><title>Setup Complete</title></head><body>";
    echo "<h1>✅ Setup Complete!</h1>";
    echo "<p><strong>db_config.php</strong> has been created successfully.</p>";
    echo "<p>Environment: <strong>" . getEnvironment() . "</strong></p>";
    echo "<p>Database: <strong>u232955123_liyas_inter</strong></p>";
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    echo "<hr>";
    echo "<p style='color: red;'><strong>⚠️ IMPORTANT:</strong> Delete this file (setup_live_config.php) now for security!</p>";
    echo "<p><a href='admin/login.php'>Go to Admin Panel</a></p>";
    echo "</body></html>";
} else {
    die("❌ Failed to create db_config.php. Please check file permissions on the config/ directory.");
}

