<?php
/**
 * Résultats de recherche - Design Moderne
 * 
 * Affiche les étudiants correspondant à la recherche avec pagination, tri et filtres
 */

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Inclure l'en-tête
include 'includes/header.php';

// Inclure la connexion à la base de données
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

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">🔍 Résultats de recherche pour "<?php echo htmlspecialchars($search); ?>"</h4>
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm">🏠 Retour</a>
                </div>
                <div class="card-body">
                    <!-- Barre de recherche -->
                    <form action="search_results.php" method="get" class="mb-4">
                        <div class="input-group shadow-sm">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control form-control-lg" placeholder="Rechercher un étudiant...">
                            <button type="submit" class="btn btn-primary btn-lg">🔎 Rechercher</button>
                        </div>
                    </form>

                    <!-- Tableau des résultats -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered text-center rounded">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>ID Utilisateur/CIN</th>
                                    <th>Classe</th>
                                    <th>Date de naissance</th>
                                    <th>Date d'inscription</th>
                                    <th>Date de fin</th>
                                    <th>Année scolaire</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($student = $result->fetch_assoc()): ?>
                                        <tr class="align-middle">
                                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['user_id_or_cin']); ?></td>
                                            <td><?php echo htmlspecialchars($student['class_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                                            <td><?php echo htmlspecialchars($student['date_of_start']); ?></td>
                                            <td><?php echo htmlspecialchars($student['date_of_end'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($student['season']); ?></td>
                                            <td>
                                                <a href="student_view.php?id=<?php echo $student['id']; ?>" class="btn btn-success btn-sm shadow-sm">
                                                    👁 Voir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-danger fw-bold">🚫 Aucun étudiant trouvé</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>
