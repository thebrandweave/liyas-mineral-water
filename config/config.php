<?php
// Database configuration
$host = "localhost";         // Database host
$username = "root";          // MySQL username
$password = "";              // MySQL password
$database = "liyas_international";  // Database name

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: set charset
$conn->set_charset("utf8mb4");

// Uncomment this line for debugging only
// echo "Database connected successfully!";
?>
