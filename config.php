<?php
// Start a session so the website remembers who is logged in across pages
session_start();

// Database connection settings (must match your MySQL Workbench connection)
$host   = "localhost";
$user   = "root";
$pass   = "933693"; // Leave blank if using default XAMPP MySQL, or put your MySQL password here
$dbname = "code_review_db";

// Connect PHP to MySQL
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Stop the page and show an error if the connection fails
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Registered Administrator Credentials (for Figure 5: Admin Module)
$admin_username = "admin";
$admin_password = "admin123";
?>