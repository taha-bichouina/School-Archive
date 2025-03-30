<?php
/**
 * Résultats de recherche - Design Moderne
 * 
 * Affiche les étudiants correspondant à la recherche avec pagination, tri et filtres
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'includes/db_connect.php';

// Variables de pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Variables de tri
$sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'created_at';
$sort_order = isset($_GET['sort_order']) ? trim($_GET['sort_order']) : 'DESC';

// Récupérer la recherche et les filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$class_filter = isset($_GET['class']) ? trim($_GET['class']) : '';
$season_filter = isset($_GET['season']) ? trim($_GET['season']) : '';

// Construire la requête
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM students WHERE student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR user_id_or_cin LIKE ?";
$params = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];

if (!empty($class_filter)) {
    $query .= " AND class_id = ?";
    $params[] = $class_filter;
}
if (!empty($season_filter)) {
    $query .= " AND season = ?";
    $params[] = $season_filter;
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

$total_rows_query = "SELECT FOUND_ROWS() as total";
$total_rows_result = $conn->query($total_rows_query);
$total_rows = $total_rows_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de Recherche - Archives Scolaires</title>
    
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

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 0;
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
                        <h1 class="h4 mb-0 fw-bold">Archives Scolaires</h1>
                        <p class="text-secondary mb-0">Résultats de Recherche</p>
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

        <div class="table-container">
            <div class="p-4">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-search"></i>
                        Résultats pour "<?php echo htmlspecialchars($search); ?>"
                    </h2>
                    <div class="action-buttons">
                        <a href="dashboard.php" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left"></i>
                            Retour
                        </a>
                    </div>
                </div>

                <!-- Search Bar -->
                <form action="search_results.php" method="get" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Rechercher des étudiants par nom, ID ou CIN">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </div>
                </form>

                <!-- Results Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th id="first-table-col">ID</th>
                                <th>Nom Complet</th>
                                <th>CIN/ID</th>
                                <th>Classe</th>
                                <th>Date Naiss.</th>
                                <th>Date Début</th>
                                <th>Date Fin</th>
                                <th>Année</th>
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
                                                <a href="student_view.php?id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-outline-light" 
                                                   title="Voir détails">
                                                    <i class="fas fa-eye"></i>
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
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <footer class="text-center py-3 mt-5 text-secondary" style="font-size: 0.9rem;">
    &copy; <?php echo date("Y"); ?> Taha Bichouina & Nacer Eddine Bouras & Lycée Chahid Hrizi
</footer>
</body>
</html>