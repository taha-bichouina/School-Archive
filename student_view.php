<?php
/**
 * Student View Page - Modern Design
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

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Étudiant - Archives Scolaires</title>
    <!-- Bootstrap CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reuse styles from dashboard.php */
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
            overflow: visible; /* Ensure overflow is visible */
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

        .stat-card {
            background: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            padding: 1.75rem;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
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
        .table-container {
            background: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table > :not(caption) > * > * {
            padding: 1rem 1.25rem;
            background: transparent;
            border-bottom-width: 1px;
            box-shadow: none;
            color: var(--text-primary);
        }

        .table thead th {
            background: var(--dark);
            color: var(--text-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--border-dark);
            white-space: nowrap;
            padding-top: 1.25rem;
            padding-bottom: 1.25rem;
        }

        #first-table-col {
            border-top-left-radius: 1rem;
        }

        #last-table-col {
            border-top-right-radius: 1rem;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: var(--hover-dark);
        }

        .table tbody td {
            border-bottom: 1px solid var(--border-dark);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .dropdown-menu {
            background: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 0.75rem;
            padding: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            min-width: 200px;
            z-index: 1050; /* Ensure it appears above other elements */
        }

        .dropdown-item {
            color: var(--text-primary);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: var(--hover-dark);
            color: var(--text-primary);
            transform: translateX(4px);
        }

        .dropdown-item i {
            font-size: 1rem;
            width: 1.25rem;
            text-align: center;
        }

        .dropdown-divider {
            border-top: 1px solid var(--border-dark);
            margin: 0.5rem 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0 0.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
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
                        <h1 class="h4 mb-0 fw-bold">
                            <a href="dashboard.php" class="text-decoration-none text-white">
                                <i class="fas fa-graduation-cap me-2"></i>Archives Scolaires
                            </a>
                        </h1>
                        <p class="text-secondary mb-0">Tableau de Bord Administratif</p>
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

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (!empty($student)): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="stat-card">
                    <div class="text-center mb-4">
                        <div class="avatar-circle mx-auto" style="background: var(--primary); width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                            <?php 
                                $initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
                                echo htmlspecialchars($initials);
                            ?>
                        </div>
                        <h3 class="font-weight-light my-2"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>
                        <p class="mb-0 text-secondary">ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
                    </div>
                    <div class="table-container">
                        <div class="p-4">
                            <div class="section-header">
                                <h2 class="section-title">
                                    <i class="fas fa-info-circle"></i>
                                    Informations détaillées
                                </h2>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th id="first-table-col">Attribut</th>
                                            <th id="last-table-col">Valeur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>ID Utilisateur/CIN</td>
                                            <td>
                                                <span class="text-secondary"><?php echo htmlspecialchars($student['user_id_or_cin']); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Classe</td>
                                            <td>
                                                <?php 
                                                    $class = htmlspecialchars($student['class_id']);
                                                    $badgeClass = match($class) {
                                                        'TC' => 'badge-tc',
                                                        '1BAC' => 'badge-1bac',
                                                        '2BAC' => 'badge-2bac',
                                                        default => 'badge-secondary'
                                                    };
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <?php echo $class; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Année Scolaire</td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    <?php echo htmlspecialchars($student['season']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Date de Naissance</td>
                                            <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                                        </tr>     
                                        <tr>
                                            <td>Lieu De Naissance</td>
                                            <td><?php echo htmlspecialchars($student['place_of_birth']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Date de Début</td>
                                            <td><?php echo htmlspecialchars($student['date_of_start']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Date de Fin</td>
                                            <td><?php echo htmlspecialchars($student['date_of_end']); ?></td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="students.php" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                        <div>
                            <a href="student_edit.php?id=<?php echo $student_id; ?>" class="btn btn-primary me-2">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <button class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete() {
            if (confirm("Êtes-vous sûr de vouloir supprimer cet étudiant ? Cette action est irréversible.")) {
                window.location.href = "delete_student.php?id=<?php echo $student_id; ?>";
            }
        }
    </script>
    <footer class="text-center py-3 mt-5 text-secondary" style="font-size: 0.9rem;">
    &copy; <?php echo date("Y"); ?> Taha Bichouina & Nacer Eddine Bouras & Lycée Chahid Hrizi
</footer>
</body>
</html>