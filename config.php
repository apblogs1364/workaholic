<?php
// config.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "workaholic";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Optional: Set UTF-8 encoding
$conn->set_charset("utf8");

// echo "Database connected successfully!";
