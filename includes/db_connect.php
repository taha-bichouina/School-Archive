<?php
// Database connection settings for InfinityFree
$host = "localhost"; // SQL Host provided by InfinityFree
$username = "root";       // Database username
$password = ""; // Database password
$database = "school_archive";    // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?>