<?php
/**
 * Page d'ajout d'étudiant - Version Modernisée
 */
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
include 'includes/header.php';

$student_id = $first_name = $last_name = $user_id_or_cin = $class_id = $season = $date_of_birth = $date_of_start = $date_of_end = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'includes/db_connect.php';

    $student_id = trim($_POST['student_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_id_or_cin = trim($_POST['user_id_or_cin']);
    $class_id = trim($_POST['class_id']);
    $season = trim($_POST['season']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $date_of_start = trim($_POST['date_of_start']);
    $date_of_end = trim($_POST['date_of_end']);

    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($user_id_or_cin) || empty($class_id) || empty($season) || empty($date_of_birth) || empty($date_of_start)) {
        $error = "Tous les champs sont obligatoires";
    } elseif (!in_array($class_id, ['TC', '1BAC', '2BAC'])) {
        $error = "Classe sélectionnée invalide";
    } elseif (!validateDate($date_of_birth) || !validateDate($date_of_start) || (!empty($date_of_end) && !validateDate($date_of_end))) {
        $error = "Format de date invalide. Utilisez AAAA-MM-JJ.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "L'ID étudiant existe déjà";
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, user_id_or_cin, class_id, season, date_of_birth, date_of_start, date_of_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("sssssssss", $student_id, $first_name, $last_name, $user_id_or_cin, $class_id, $season, $date_of_birth, $date_of_start, $date_of_end);
                    if ($insert_stmt->execute()) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Étudiant ajouté avec succès !<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        $student_id = $first_name = $last_name = $user_id_or_cin = $class_id = $season = $date_of_birth = $date_of_start = $date_of_end = "";
                    } else {
                        $error = "Une erreur de base de données s'est produite lors de l'insertion";
                    }
                    $insert_stmt->close();
                } else {
                    $error = "Une erreur de base de données s'est produite";
                }
            }
            $stmt->close();
        } else {
            $error = "Une erreur de base de données s'est produite";
        }
    }
    $conn->close();
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Étudiant</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: #6a11cb;
            background: linear-gradient(to right, #2575fc, #6a11cb);
            border-radius: 15px 15px 0 0;
        }
        .btn-primary {
            background: #6a11cb;
            background: linear-gradient(to right, #2575fc, #6a11cb);
            border: none;
        }
        .btn-primary:hover {
            background: #2575fc;
        }
        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 5px rgba(106, 17, 203, 0.5);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center rounded-top">
                        <h3 class="font-weight-light my-2">Ajouter un nouvel étudiant</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">ID Étudiant</label>
                                <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Entrez l'ID de l'étudiant" value="<?php echo htmlspecialchars($student_id); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Entrez le prénom" value="<?php echo htmlspecialchars($first_name); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Nom de famille</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Entrez le nom de famille" value="<?php echo htmlspecialchars($last_name); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="user_id_or_cin" class="form-label">Identifiant utilisateur, User R ou CIN</label>
                                <input type="text" class="form-control" id="user_id_or_cin" name="user_id_or_cin" placeholder="Entrez l'identifiant utilisateur, User R ou CIN" value="<?php echo htmlspecialchars($user_id_or_cin); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="class_id" class="form-label">Classe</label>
                                <select class="form-select" id="class_id" name="class_id" required>
                                    <option value="" disabled selected>Sélectionnez une classe</option>
                                    <option value="TC" <?php echo ($class_id === 'TC') ? 'selected' : ''; ?>>TC</option>
                                    <option value="1BAC" <?php echo ($class_id === '1BAC') ? 'selected' : ''; ?>>1BAC</option>
                                    <option value="2BAC" <?php echo ($class_id === '2BAC') ? 'selected' : ''; ?>>2BAC</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="season" class="form-label">Année scolaire</label>
                                <select class="form-select" id="season" name="season" required>
                                    <option value="" disabled selected>Sélectionnez une année scolaire</option>
                                    <?php
                                    $current_year = date("Y");
                                    for ($year = 2008; $year <= $current_year; $year++) {
                                        $season_option = $year . "-" . ($year + 1);
                                        $selected = ($season === $season_option) ? 'selected' : '';
                                        echo '<option value="' . $season_option . '" ' . $selected . '>' . $season_option . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="date_of_start" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_of_start" name="date_of_start" value="<?php echo htmlspecialchars($date_of_start); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="date_of_end" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_of_end" name="date_of_end" value="<?php echo htmlspecialchars($date_of_end); ?>" required>
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Ajouter l'étudiant</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <a href="dashboard.php" class="small text-decoration-none">Retour au tableau de bord</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>