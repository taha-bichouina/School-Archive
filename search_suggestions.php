<?php
/**
 * Search Suggestions Script
 * 
 * Provides live search suggestions for the dashboard
 */

// Include database connection
require_once 'includes/db_connect.php';

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$suggestions = [];

if (!empty($search)) {
    // Query to find matching students
    $query = "SELECT CONCAT(first_name, ' ', last_name) AS full_name, student_id, user_id_or_cin 
              FROM students 
              WHERE student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR user_id_or_cin LIKE ?
              LIMIT 5"; // Limit to 5 suggestions for performance

    $search_param = '%' . $search . '%';

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    // Collect suggestions
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['full_name']; // Suggest full name
        $suggestions[] = $row['user_id_or_cin']; // Suggest user ID/R/CIN
    }

    // Remove duplicates and return unique suggestions
    $suggestions = array_unique($suggestions);
}

// Return JSON response
echo json_encode($suggestions);

// Close connection
$conn->close();
?>