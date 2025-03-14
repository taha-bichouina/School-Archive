<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Inclure la connexion à la base de données
require_once 'includes/db_connect.php';

// Définir les en-têtes HTTP pour un fichier CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students.csv');

// Ouvrir la sortie en mode écriture
$output = fopen('php://output', 'w');

// Écrire les en-têtes des colonnes
fputcsv($output, ['Student ID', 'First Name', 'Last Name', 'User ID/CIN', 'Class', 'Date of Birth', 'Date of Start', 'Date of End', 'Season']);

// Récupérer les données des étudiants
$query = "SELECT student_id, first_name, last_name, user_id_or_cin, class_id, date_of_birth, date_of_start, date_of_end, season FROM students";
$result = $conn->query($query);

// Écrire les lignes des étudiants dans le fichier CSV
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

// Fermer la connexion
fclose($output);
exit;
?>
