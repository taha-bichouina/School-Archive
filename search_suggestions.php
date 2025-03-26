<?php
/**
 * Search Suggestions Script
 * 
 * Provides live search suggestions for the dashboard
 */

// Start session and check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['error' => 'Unauthorized access']));
}

// Include database connection
require_once 'includes/db_connect.php';

// Set JSON content type
header('Content-Type: application/json');

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$suggestions = [];

if (!empty($search)) {
    // Query to find matching students with more details
    $query = "SELECT id, student_id, first_name, last_name, user_id_or_cin, class_id 
              FROM students 
              WHERE student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR user_id_or_cin LIKE ?
              LIMIT 5"; // Limit to 5 suggestions for performance

    $search_param = '%' . $search . '%';

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    // Collect suggestions with more details
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'id' => $row['id'],
            'text' => $row['first_name'] . ' ' . $row['last_name'],
            'subtext' => 'ID: ' . $row['student_id'] . ' | CIN: ' . $row['user_id_or_cin'],
            'class' => $row['class_id'],
            'initials' => strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1))
        ];
    }
}

// Return JSON response with success status
echo json_encode([
    'success' => true,
    'results' => $suggestions
]);

// Close connection
$conn->close();
?>