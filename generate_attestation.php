<?php
require_once 'includes/db_connect.php';
require('lib/fpdf.php');

// Vérifier si un ID est passé
if (!isset($_GET['id'])) {
    die("Aucun étudiant sélectionné.");
}

// Récupérer les infos de l'étudiant
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Étudiant non trouvé.");
}

$student = $result->fetch_assoc();

// Données pour l'attestation
$full_name = strtoupper($student['first_name'] . ' ' . $student['last_name']);
$date_naissance = $student['date_of_birth'];
$lieu_naissance = $student['place_of_birth'] ?: '---'; // Valeur par défaut si vide
$num_inscription = str_pad($student['student_id'], 3, "0", STR_PAD_LEFT) . "/073";
$classe = $student['class_id'];
$annee_scolaire = $student['season'];
$date_discontinuation = $student['date_of_end'] ?? date('Y-m-d');
$ville = "casa";
$date_attestation = date('Y-m-d');

// Création du PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('assets/images/logo_ministere.png', 75, 12, 60); // x=75, y=10, width=60mm
$pdf->Image('assets/images/logo_bernoussi.jpg', 0, 2, 50); // x=75, y=10, width=60mm
$pdf->Image('assets/images/logo_casa.jpg', 160, 0, 50); // x=75, y=10, width=60mm
$pdf->Ln(35); // Espace sous le logo
$pdf->SetFont('Arial', '', 12);

// Titre
$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(0, 75, utf8_decode("Attestation de scolarité :"), 0, 1, 'C');
$pdf->Ln(-30);

// Contenu
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, utf8_decode("Soussigné, Mr belaid boudounit directeur de l'établissement atteste que :"));

$pdf->Ln(5);
$pdf->Cell(60, 8, utf8_decode("Nom et Prénom :"), 0, 0);
$pdf->Cell(0, 8, utf8_decode($full_name), 0, 1);

$pdf->Cell(60, 8, utf8_decode("Né(e) le :"), 0, 0);
$pdf->Cell(60, 8, $date_naissance, 0, 0);
$pdf->Cell(20, 8, utf8_decode("A : "), 0, 0);
$pdf->Cell(0, 8, utf8_decode($lieu_naissance), 0, 1);

$pdf->Cell(60, 8, utf8_decode("Inscrit(e) sous le numéro :"), 0, 0);
$pdf->Cell(0, 8, $num_inscription, 0, 1);

$pdf->Cell(60, 8, utf8_decode("Études en :"), 0, 0);
$pdf->Cell(0, 8, utf8_decode($classe), 0, 1);

$pdf->Cell(60, 8, utf8_decode("Année scolaire :"), 0, 0);
$pdf->Cell(0, 8, $annee_scolaire, 0, 1);

$pdf->Cell(60, 8, utf8_decode("Date de discontinuation :"), 0, 0);
$pdf->Cell(0, 8, $date_discontinuation, 0, 1);

$pdf->Ln(10);
$pdf->MultiCell(0, 8, utf8_decode("Nous lui délivrons la présente attestation pour servir et valoir ce que de droit."));

$pdf->Ln(5);
$pdf->MultiCell(0, 8, utf8_decode("NB : Ce certificat ne permet pas de s'inscrire dans un autre établissement."));

$pdf->Ln(10);
$pdf->Cell(30, 8, utf8_decode("Fait à"), 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, utf8_decode($ville), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(30, 8, utf8_decode("Le :"), 0, 0);
$pdf->Cell(0, 8, $date_attestation, 0, 1);

$pdf->Ln(15);
$pdf->Cell(0, 8, utf8_decode("Le directeur de l'établissement"), 0, 1, 'R');

// Générer le PDF
$pdf->Output('I', 'attestation_' . $student['last_name'] . '.pdf');
?>
