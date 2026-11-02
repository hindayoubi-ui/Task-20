
<?php

// Database connection variables
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "task 20";

// Create database connection
$conn = new mysqli(
    $servername,
    $username,
    $password,
    $dbname
);

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character encoding
$conn->set_charset("utf8mb4");

?>
