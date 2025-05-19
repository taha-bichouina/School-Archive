<?php
// Database connection settings for InfinityFree
$host = "sql110.infinityfree.com"; // SQL Host provided by InfinityFree
$username = "if0_38512020";       // Database username
$password = "owTQ6KFONPrDd"; // Database password
$database = "if0_38512020_school_archive";    // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?>