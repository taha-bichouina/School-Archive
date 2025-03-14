<?php
/**
 * Page du Tableau de Bord
 * 
 * Affiche un aperçu du Système de Gestion des Archives Scolaires
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
// Obtenir le nombre total d'étudiants
$student_query = "SELECT COUNT(*) as total FROM students";
$student_result = $conn->query($student_query);
$student_count = $student_result ? $student_result->fetch_assoc()['total'] : 0;
// Obtenir les comptes pour TC, 1BAC et BAC
$tc_query = "SELECT COUNT(*) as total FROM students WHERE class_id = 'TC'";
$tc_result = $conn->query($tc_query);
$tc_count = $tc_result ? $tc_result->fetch_assoc()['total'] : 0;
$bac1_query = "SELECT COUNT(*) as total FROM students WHERE class_id = '1BAC'";
$bac1_result = $conn->query($bac1_query);
$bac1_count = $bac1_result ? $bac1_result->fetch_assoc()['total'] : 0;
$bac2_query = "SELECT COUNT(*) as total FROM students WHERE class_id = 'BAC'";
$bac2_result = $conn->query($bac2_query);
$bac2_count = $bac2_result ? $bac2_result->fetch_assoc()['total'] : 0;
// Obtenir les étudiants récents
$recent_students_query = "SELECT * FROM students ORDER BY created_at DESC LIMIT 5";
$recent_students_result = $conn->query($recent_students_query);
// Fermer la connexion
$conn->close();
?>
<div class="container-fluid">
    <!-- Section de l'En-tête -->
    <div class="d-flex justify-content-between align-items-center pt-4 pb-3 border-bottom">
        <h1 class="h2 text-primary fw-bold">Tableau de Bord des Archives Scolaires</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary me-2">
                <i class="fas fa-download me-1"></i> Exporter
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-print me-1"></i> Imprimer
            </button>
            <a href="logout.php" class="btn btn-sm btn-danger ms-2">
                <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
            </a>
        </div>
    </div>
    <!-- Barre de Recherche avec Suggestions en Direct -->
    <div class="row mt-4">
        <div class="col-12">
            <form id="searchForm" action="search_results.php" method="get" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" id="searchInput" class="form-control" placeholder="Rechercher des étudiants par nom, ID ou CIN" required>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
                <div id="searchSuggestions" class="list-group mt-2" style="display: none;"></div>
            </form>
        </div>
    </div>
    <!-- Cartes Statistiques -->
    <div class="row mt-4">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title text-primary">Nombre Total d'Étudiants</h5>
                        <h2 class="display-5 fw-bold"><?php echo $student_count; ?></h2>
                    </div>
                    <div class="mt-3">
                        <a href="students.php" class="btn btn-primary w-100">Voir les Détails</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title text-info">Nombre Total d'Étudiants en TC</h5>
                        <h2 class="display-5 fw-bold"><?php echo $tc_count; ?></h2>
                    </div>
                    <div class="mt-3">
                        <a href="students.php?class=TC" class="btn btn-info w-100">Voir les Étudiants en TC</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title text-success">Nombre Total d'Étudiants en 1BAC</h5>
                        <h2 class="display-5 fw-bold"><?php echo $bac1_count; ?></h2>
                    </div>
                    <div class="mt-3">
                        <a href="students.php?class=1BAC" class="btn btn-success w-100">Voir les Étudiants en 1BAC</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title text-warning">Nombre Total d'Étudiants en 2BAC</h5>
                        <h2 class="display-5 fw-bold"><?php echo $bac2_count; ?></h2>
                    </div>
                    <div class="mt-3">
                        <a href="students.php?class=2BAC" class="btn btn-warning w-100">Voir les Étudiants en BAC</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tableau des Étudiants Récents -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Étudiants Ajoutés Récemment</h5>
                    <a href="students.php" class="btn btn-primary btn-sm">Voir Tous les Étudiants</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>ID Utilisateur/CIN</th>
                                    <th>Classe</th>
                                    <th>Date de Naissance</th>
                                    <th>Date de Début</th>
                                    <th>Date de Fin</th>
                                    <th>Année Scolaire</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_students_result && $recent_students_result->num_rows > 0): ?>
                                    <?php while ($student = $recent_students_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['user_id_or_cin']); ?></td>
                                            <td><?php echo htmlspecialchars($student['class_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                                            <td><?php echo htmlspecialchars($student['date_of_start']); ?></td>
                                            <td><?php echo htmlspecialchars($student['date_of_end'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($student['season']); ?></td>
                                            <td>
                                                <a href="student_view.php?id=<?php echo $student['id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Aucun étudiant trouvé</td>
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
<!-- Bouton Flottant pour Ajouter un Étudiant -->
<a href="student_add.php" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4" style="width: 60px; height: 60px; font-size: 24px;">
    <i class="fas fa-plus"></i>
</a>
<!-- JavaScript pour les Suggestions de Recherche en Direct -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#searchInput').on('input', function() {
        const query = $(this).val().trim();
        if (query.length >= 2) {
            $.ajax({
                url: 'search_suggestions.php',
                method: 'GET',
                data: { search: query },
                success: function(response) {
                    const suggestions = JSON.parse(response);
                    const suggestionsDiv = $('#searchSuggestions');
                    suggestionsDiv.empty();
                    if (suggestions.length > 0) {
                        suggestionsDiv.css('display', 'block');
                        suggestions.forEach(function(suggestion) {
                            suggestionsDiv.append(
                                `<a href="search_results.php?search=${encodeURIComponent(suggestion)}" class="list-group-item list-group-item-action">${suggestion}</a>`
                            );
                        });
                    } else {
                        suggestionsDiv.css('display', 'none');
                    }
                }
            });
        } else {
            $('#searchSuggestions').css('display', 'none');
        }
    });
    // Masquer les suggestions lors d'un clic à l'extérieur
    $(document).on('click', function(event) {
        if (!$(event.target).closest('#searchInput, #searchSuggestions').length) {
            $('#searchSuggestions').css('display', 'none');
        }
    });
});
</script>
<?php
// Inclure le pied de page
include 'includes/footer.php';
?>