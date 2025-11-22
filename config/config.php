<?php
// ✅ Manual Firebase JWT include (correct order!)
require_once __DIR__ . '/../admin/includes/php-jwt/JWTExceptionWithPayloadInterface.php';
require_once __DIR__ . '/../admin/includes/php-jwt/BeforeValidException.php';
require_once __DIR__ . '/../admin/includes/php-jwt/ExpiredException.php';
require_once __DIR__ . '/../admin/includes/php-jwt/SignatureInvalidException.php';
require_once __DIR__ . '/../admin/includes/php-jwt/Key.php';
require_once __DIR__ . '/../admin/includes/php-jwt/JWT.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Database Configuration - Load from db_config.php
require_once __DIR__ . '/db_config.php';
// $pdo is now available from db_config.php

$JWT_SECRET = "super_secure_secret_987654321";
$JWT_EXPIRE = 3600;

// Root path detection (project root)
$ROOT_PATH = dirname(__DIR__);  // e.g. C:\xampp\htdocs\liyas-mineral-water

define('UPLOAD_DIR', '/uploads/');
define('UPLOAD_DIR_SERVER', $ROOT_PATH . '/uploads/');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
