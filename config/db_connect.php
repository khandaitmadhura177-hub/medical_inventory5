<?php
// Database configuration
$host   = "127.0.0.1";
$user   = "root";
$pass   = "";
$dbname = "medicaldata";
$port   = 3306; 

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// CHECK CONNECTION: This is vital for debugging
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 (Recommended for special characters)
$conn->set_charset("utf8mb4");
?>