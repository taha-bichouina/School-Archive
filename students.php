<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'includes/db_connect.php';

$page = 1;
$limit = 999999;
$offset = 0;

$sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'created_at';
$sort_order = isset($_GET['sort_order']) ? trim($_GET['sort_order']) : 'DESC';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$class_filter = isset($_GET['class']) ? trim($_GET['class']) : '';

$query = "SELECT * FROM students WHERE (student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR user_id_or_cin LIKE ?)";
$params = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];

if (!empty($class_filter)) {
    $query .= " AND class_id = ?";
    $params[] = $class_filter;
}

$allowed_sort_columns = ['student_id', 'first_name', 'last_name', 'class_id', 'date_of_birth', 'date_of_start', 'date_of_end', 'season'];
if (in_array($sort_by, $allowed_sort_columns)) {
    $query .= " ORDER BY $sort_by $sort_order";
} else {
    $query .= " ORDER BY created_at DESC";
}

$query .= " LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étudiants - Archives Scolaires</title>
    
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

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -10px rgba(0, 0, 0, 0.2);
        }

        .stat-card:hover::before {
            opacity: 1;
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

        .stat-icon::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: inherit;
            box-shadow: 0 0 0 8px currentColor;
            opacity: 0.1;
        }

        .stat-icon.total {
            background: rgba(59, 130, 246, 0.2);
            color: var(--primary);
        }

        .stat-icon.tc {
            background: rgba(14, 165, 233, 0.2);
            color: var(--info);
        }

        .stat-icon.bac1 {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .stat-icon.bac2 {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            line-height: 1;
            letter-spacing: -0.025em;
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

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
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

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: var(--hover-dark);
        }

        .table tbody td {
            border-bottom: 1px solid var(--border-dark);
            vertical-align: middle;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            text-transform: uppercase;
        }

        .badge-tc {
            background: rgba(14, 165, 233, 0.2);
            color: #38bdf8;
        }

        .badge-1bac {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .badge-2bac {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        .dropdown-menu {
            background: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 0.75rem;
            padding: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            min-width: 200px;
            z-index: 1050;
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

        .action-buttons {
            display: flex;
            gap: 0.75rem;
        }

        .empty-state {
            padding: 3rem 2rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            font-size: 1rem;
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

        .input-group-text {
            background-color: var(--dark);
            border: 1px solid var(--border-dark);
            color: var(--text-primary);
        }

        /* Search suggestions styling */
.search-container {
    position: relative;
}

#suggestions-container {
    position: absolute;
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
    background: var(--card-dark);
    border: 1px solid var(--border-dark);
    border-radius: 0 0 1rem 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    margin-top: -0.5rem;
    border-top: none;
}

.suggestion-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--border-dark);
}

.suggestion-item:last-child {
    border-bottom: none;
    border-radius: 0 0 1rem 1rem;
}

.suggestion-item:hover {
    background: var(--hover-dark);
}

.suggestion-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 500;
    margin-right: 1rem;
    flex-shrink: 0;
    background: var(--primary);
}

.suggestion-text {
    flex-grow: 1;
}

.suggestion-title {
    font-weight: 500;
    color: var(--text-primary);
}

.suggestion-subtext {
    font-size: 0.875rem;
    margin-top: 0.25rem;
    color: var(--text-secondary);
}

.suggestion-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    margin-left: 1rem;
    text-transform: uppercase;
    font-weight: 500;
}

.suggestion-item.empty {
    justify-content: center;
    color: var(--text-secondary);
    cursor: default;
}

.suggestion-item.empty:hover {
    background: transparent;
}

        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .stat-value {
                font-size: 2rem;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 0;
            }

            .action-buttons {
                width: 100%;
                justify-content: flex-start;
            }

            .table-container {
                border-radius: 0.75rem;
            }

            .table thead th {
                padding: 1rem;
            }

            .table tbody td {
                padding: 0.75rem 1rem;
            }

            .badge {
                padding: 0.35rem 0.5rem;
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
                        <p class="text-secondary mb-0">Gestion des Étudiants</p>
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

        <div class="table-container mb-4">
            <div class="p-4">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-users"></i>
                        Tous les Étudiants
                    </h2>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-outline-light" onclick="window.print();">
                            <i class="fas fa-print"></i>
                            Imprimer
                        </button>
                        <a href="export_results.php?search=<?php echo urlencode($search); ?>&class=<?php echo urlencode($class_filter); ?>" class="btn btn-outline-light">
                            <i class="fas fa-file-csv"></i>
                            Exporter CSV
                        </a>
                        <a href="#" class="btn btn-outline-light">
                            <i class="fas fa-file-pdf"></i>
                            Exporter PDF
                        </a>
                        <a href="add_student.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Ajouter
                        </a>
                    </div>
                </div>

                <!-- Search Bar with Suggestions -->
                <div class="search-container position-relative mb-4">
                    <form action="students.php" method="get" class="mb-0">
                    <div class="input-group">
                    <input type="text" id="search-input" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Rechercher des étudiants par nom, ID ou CIN" autocomplete="off">
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
                    </form>
                <div id="suggestions-container" class="d-none"></div>
            </div>


                <!-- Filters -->
                <form action="students.php" method="get" class="mb-4">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>">
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="classFilter" class="form-label">Filtrer par Classe</label>
                            <select class="form-select" id="classFilter" name="class">
                                <option value="" <?php echo empty($class_filter) ? 'selected' : ''; ?>>Toutes les Classes</option>
                                <option value="TC" <?php echo ($class_filter === 'TC') ? 'selected' : ''; ?>>TC</option>
                                <option value="1BAC" <?php echo ($class_filter === '1BAC') ? 'selected' : ''; ?>>1BAC</option>
                                <option value="2BAC" <?php echo ($class_filter === '2BAC') ? 'selected' : ''; ?>>2BAC</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Appliquer les Filtres</button>
                        </div>
                    </div>
                </form>

                <!-- Students Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th id="first-table-col"><a href="?search=<?php echo urlencode($search); ?>&class=<?php echo urlencode($class_filter); ?>&sort_by=student_id&sort_order=<?php echo ($sort_by === 'student_id' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>" class="text-decoration-none text-white">ID <i class="fas fa-sort"></i></a></th>
                                <th><a href="?search=<?php echo urlencode($search); ?>&class=<?php echo urlencode($class_filter); ?>&sort_by=first_name&sort_order=<?php echo ($sort_by === 'first_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>" class="text-decoration-none text-white">Nom <i class="fas fa-sort"></i></a></th>
                                <th>User ID/CIN</th>
                                <th><a href="?search=<?php echo urlencode($search); ?>&class=<?php echo urlencode($class_filter); ?>&sort_by=class_id&sort_order=<?php echo ($sort_by === 'class_id' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?>" class="text-decoration-none text-white">Classe <i class="fas fa-sort"></i></a></th>
                                <th>Date de Naissance</th>
                                <th>Date de Début</th>
                                <th>Date de Fin</th>
                                <th>Année Scolaire</th>
                                <th id="last-table-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($student = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="text-secondary">#</span><?php echo htmlspecialchars($student['student_id']); ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="avatar-circle" style="background: var(--primary); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 500;">
                                                        <?php 
                                                            $initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
                                                            echo htmlspecialchars($initials);
                                                        ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-secondary"><?php echo htmlspecialchars($student['user_id_or_cin']); ?></span>
                                        </td>
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
                                        <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                                        <td><?php echo htmlspecialchars($student['date_of_start']); ?></td>
                                        <td><?php echo htmlspecialchars($student['date_of_end'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                <?php echo htmlspecialchars($student['season']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <!-- Voir détails -->
                                                <a href="student_view.php?id=<?php echo $student['id']; ?>"                                            
           class="btn btn-sm btn-outline-light" 
           title="Voir détails">
            <i class="fas fa-eye"></i>
                                               </a>
                                                                                
                                                <!-- Modifier l'étudiant -->
                                                <a href="student_edit.php?id=<?php echo $student['id']; ?>"                                            
                                                   class="btn btn-sm btn-outline-light" 
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                                                
                                                <!-- Télécharger l'attestation scolaire -->
                                                <a href="generate_attestation.php?id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-outline-light" 
                                                   title="Télécharger l'attestation scolaire" 
                                                   target="_blank">
                                                    <i class="fas fa-file-download"></i>
                                                </a>
                                            </div>
                                        </td>

                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <i class="fas fa-user-slash"></i>
                                            <p>Aucun étudiant trouvé</p>
                                            <a href="add_student.php" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>
                                                Ajouter un étudiant
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/search-suggestions.js"></script>
    <footer class="text-center py-3 mt-5 text-secondary" style="font-size: 0.9rem;">
    &copy; <?php echo date("Y"); ?> Taha Bichouina & Nacer Eddine Bouras & Lycée Chahid Hrizi
</footer>
</body>
</html>