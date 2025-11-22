<?php
/**
 * Database Configuration for QR Reward System
 * Uses the existing database connection from the main project
 */

// Use existing database configuration (matches config/config.php)
$host = "localhost";
$dbname = "liyas_international"; // Using existing database
$username = "root";
$password = "";

// PDO Options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

// Create PDO connection
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Log error securely (don't expose credentials)
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Helper function to get database connection
function getDB() {
    global $pdo;
    return $pdo;
}
