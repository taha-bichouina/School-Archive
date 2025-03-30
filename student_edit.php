<?php
/**
 * Student Edit Page - Modern Design
 */
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'includes/db_connect.php';

$student_id = $error = '';
$student = [];

// Get student ID from URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $student_id = intval($_GET['id']);
    
    // Fetch student data
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
    
    if (!$student) {
        $error = "Étudiant non trouvé";
    }
} else {
    $error = "ID étudiant invalide";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($student)) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_id_or_cin = trim($_POST['user_id_or_cin']);
    $class_id = trim($_POST['class_id']);
    $season = trim($_POST['season']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $date_of_start = trim($_POST['date_of_start']);
    $date_of_end = trim($_POST['date_of_end']);
    $place_of_birth = trim($_POST['place_of_birth']);

    // Validation
    if (empty($first_name) || empty($last_name) || empty($user_id_or_cin) || empty($class_id) || 
        empty($season) || empty($date_of_birth) || empty($date_of_start)) {
        $error = "Tous les champs obligatoires doivent être remplis";
    } elseif (!in_array($class_id, ['TC', '1BAC', '2BAC'])) {
        $error = "Classe sélectionnée invalide";
    } else {
        // Update student record
        $stmt = $conn->prepare("UPDATE students SET 
            first_name = ?, 
            last_name = ?, 
            user_id_or_cin = ?, 
            class_id = ?, 
            season = ?, 
            date_of_birth = ?, 
            date_of_start = ?, 
            date_of_end = ?,
            place_of_birth = ?
            WHERE id = ?");
        
        $stmt->bind_param("sssssssssi", 
        $last_name, 
        $first_name, 
        $user_id_or_cin, 
        $class_id, 
        $season, 
        $date_of_birth, 
        $date_of_start, 
        $date_of_end, 
        $place_of_birth,
        $student_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Étudiant mis à jour avec succès";
            header("Location: student_view.php?id=" . $student_id);
            exit;
        } else {
            $error = "Erreur lors de la mise à jour de l'étudiant: " . $conn->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Étudiant - Archives Scolaires</title>
    
    <!-- Bootstrap CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css">
    
    <style>
        /* Reuse your existing styles from students.php */
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
            color: white;
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

        .avatar-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: 600;
            margin: 0 auto 1rem;
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
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 fw-bold">
                    <a href="students.php" class="text-decoration-none text-white">
                        <i class="fas fa-arrow-left me-2"></i>
                        Modifier Étudiant
                    </a>
                </h1>
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
        </header>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($student)): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <div class="form-header text-center">
                        <div class="avatar-circle">
                            <?php 
                                $initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
                                echo htmlspecialchars($initials);
                            ?>
                        </div>
                        <h3 class="font-weight-light my-2">Modifier <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>
                        <p class="mb-0">ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
                    </div>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $student_id); ?>" method="post">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Nom de famille</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="user_id_or_cin" class="form-label">Identifiant utilisateur, User R ou CIN</label>
                                <input type="text" class="form-control" id="user_id_or_cin" name="user_id_or_cin" 
                                       value="<?php echo htmlspecialchars($student['user_id_or_cin']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="class_id" class="form-label">Classe</label>
                                <select class="form-select" id="class_id" name="class_id" required>
                                    <option value="TC" <?php echo ($student['class_id'] === 'TC') ? 'selected' : ''; ?>>TC</option>
                                    <option value="1BAC" <?php echo ($student['class_id'] === '1BAC') ? 'selected' : ''; ?>>1BAC</option>
                                    <option value="2BAC" <?php echo ($student['class_id'] === '2BAC') ? 'selected' : ''; ?>>2BAC</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="season" class="form-label">Année scolaire</label>
                                <select class="form-select" id="season" name="season" required>
                                    <?php
                                    $current_year = date("Y");
                                    for ($year = 2008; $year <= $current_year; $year++) {
                                        $season_option = $year . "-" . ($year + 1);
                                        $selected = ($student['season'] === $season_option) ? 'selected' : '';
                                        echo '<option value="' . $season_option . '" ' . $selected . '>' . $season_option . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                       value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="place_of_birth" class="form-label">Lieu De Naissance</label>
                                <input type="text" class="form-control" id="place_of_birth" name="place_of_birth" value="<?php echo htmlspecialchars($student['place_of_birth']);?>">
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_start" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_of_start" name="date_of_start" 
                                       value="<?php echo htmlspecialchars($student['date_of_start']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_end" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_of_end" name="date_of_end" 
                                       value="<?php echo htmlspecialchars($student['date_of_end']); ?>">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="student_view.php?id=<?php echo $student_id; ?>" class="btn btn-outline-light">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add any necessary JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // You can add form validation or other interactive features here
        });
    </script>
    <footer class="text-center py-3 mt-5 text-secondary" style="font-size: 0.9rem;">
    &copy; <?php echo date("Y"); ?> Taha Bichouina & Nacer Eddine Bouras & Lycée Chahid Hrizi
</footer>
</body>
</html>