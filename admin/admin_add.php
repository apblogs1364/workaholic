<?php
include 'config.php';

// Admin details
$admin_name = "Drashti";
$admin_email = "db@gmail.com";
$admin_password_plain = "Drashti12@"; // the password you want

// Hash the password
$admin_password_hashed = password_hash($admin_password_plain, PASSWORD_DEFAULT);

// Insert into database
$stmt = $conn->prepare("INSERT INTO admins (admin_name, admin_email, admin_password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $admin_name, $admin_email, $admin_password_hashed);

if ($stmt->execute()) {
    echo "Admin added successfully.";
} else {
    echo "Error: " . $stmt->error;
}
