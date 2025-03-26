<?php
/**
 * Page d'ajout d'étudiant - Version Modernisée
 */
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $first_name = $last_name = $user_id_or_cin = $class_id = $season = $date_of_birth = $date_of_start = $date_of_end = $place_of_birth = "";
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
    $place_of_birth = trim($_POST['place_of_birth']);

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
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Étudiant - Archives Scolaires</title>
    
    <!-- Bootstrap CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #60a5fa;
            --secondary: #64748b;
            --success: #10b981;
            --info: #0ea5e9;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f1f5f9;
            --dark: #0f172a;
            --bg-dark: #111827;
            --card-dark: #1f2937;
            --border-dark: #374151;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --hover-dark: #2d3748;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .main-header {
            background: linear-gradient(to right, var(--card-dark), var(--dark));
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: visible;
        }

        .main-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(59, 130, 246, 0.1), transparent);
            pointer-events: none;
        }

        .form-container {
            background: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 2rem;
        }

        .form-header {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            border-radius: 1rem 1rem 0 0;
            padding: 1.5rem;
            margin: -2rem -2rem 2rem -2rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline-light {
            border-color: var(--border-dark);
            color: var(--text-primary);
        }

        .btn-outline-light:hover {
            background: var(--hover-dark);
            border-color: var(--border-dark);
            color: var(--text-primary);
            transform: translateY(-1px);
        }

        .form-control, .form-select {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            color: var(--text-primary);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--card-dark);
            border-color: var(--primary);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }

        .form-control::placeholder {
            color: var(--text-primary) !important;
            opacity: 0.7;
        }

        /* For better browser compatibility */
        .form-control::-webkit-input-placeholder {
            color: var(--text-primary);
            opacity: 0.7;
        }

        .form-control::-moz-placeholder {
            color: var(--text-primary);
            opacity: 0.7;
        }

        .form-control:-ms-input-placeholder {
            color: var(--text-primary);
            opacity: 0.7;
        }

        .form-control:-moz-placeholder {
            color: var(--text-primary);
            opacity: 0.7;
        }

        .alert {
            border-radius: 0.75rem;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            position: relative;
        }

        .stat-icon.total {
            background: rgba(59, 130, 246, 0.2);
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <header class="main-header mb-4 p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="d-flex align-items-center mb-3 mb-md-0">
                    <div class="stat-icon total me-3">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h1 class="h4 mb-0 fw-bold">Archives Scolaires</h1>
                        <p class="text-secondary mb-0">Ajouter un Étudiant</p>
                    </div>
                </div>
                
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <div class="form-header text-center">
                        <h3 class="font-weight-light my-2">Ajouter un nouvel étudiant</h3>
                    </div>
                    
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
                            <label for="place_of_birth" class="form-label">Lieu de naissance (optionnel)</label>
                            <input type="text" class="form-control" id="place_of_birth" name="place_of_birth" placeholder="Entrez le lieu de naissance" value="<?php echo htmlspecialchars($place_of_birth); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="date_of_start" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_of_start" name="date_of_start" value="<?php echo htmlspecialchars($date_of_start); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="date_of_end" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_of_end" name="date_of_end" value="<?php echo htmlspecialchars($date_of_end); ?>">
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Ajouter l'étudiant</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="text-decoration-none text-primary">
                            <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.getElementById("place_of_birth").addEventListener("input", function () {
    const place = this.value.trim();
    const dobField = document.getElementById("date_of_birth");
    if (place !== "") {
        dobField.setAttribute("required", "required");
    } else {
        dobField.removeAttribute("required");
    }
});
</script>

</body>
</html>