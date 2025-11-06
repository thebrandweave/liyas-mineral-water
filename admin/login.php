<?php
/**
 * Global configuration file for Liyas Mineral Water admin portal.
 * ---------------------------------------------------------------
 * - Initializes DB connection
 * - Loads Firebase JWT manually (no Composer)
 * - Starts session
 * - Defines constants
 */

// ✅ Root path detection (project root)
$ROOT_PATH = dirname(__DIR__);  // e.g. C:\xampp\htdocs\liyas-mineral-water

// ✅ --- Load Firebase PHP-JWT manually ---
$JWT_LIB_PATH = $ROOT_PATH . '/admin/includes/php-jwt/';

$jwtFiles = [
    'JWTExceptionWithPayloadInterface.php',
    'BeforeValidException.php',
    'ExpiredException.php',
    'SignatureInvalidException.php',
    'Key.php',
    'JWT.php'
];

foreach ($jwtFiles as $file) {
    $filePath = $JWT_LIB_PATH . $file;
    if (file_exists($filePath)) {
        require_once $filePath;
    } else {
        die("❌ Missing JWT library file: {$filePath}");
    }
}

// ✅ Import namespaces globally for all includes
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ✅ --- Database Configuration ---
$host = "localhost";
$dbname = "beverage_website";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// ✅ --- JWT Configuration ---
$JWT_SECRET = "super_secure_secret_987654321"; // change this to a long random string
$JWT_EXPIRE = 3600; // 1 hour

// ✅ --- File Upload Configuration ---
define('UPLOAD_DIR', '/uploads/');
define('UPLOAD_DIR_SERVER', $ROOT_PATH . '/uploads/');

// ✅ --- Session Start (safe) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ --- Helper function: JWT Encode / Decode shortcuts (optional) ---
if (!function_exists('jwt_encode')) {
    function jwt_encode($payload) {
        global $JWT_SECRET;
        return \Firebase\JWT\JWT::encode($payload, $JWT_SECRET, 'HS256');
    }
}

if (!function_exists('jwt_decode')) {
    function jwt_decode($token) {
        global $JWT_SECRET;
        return \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($JWT_SECRET, 'HS256'));
    }
}
?>
