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
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tableau de Bord - Système de Gestion des Archives Scolaires</title>
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
            <!-- Google Fonts (Poppins) -->
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
            <!-- Custom CSS -->
            <style>
                body {
                    font-family: 'Poppins', sans-serif;
                    background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
                    min-height: 100vh;
                    overflow-x: hidden;
                }
                .card {
                    border: none;
                    border-radius: 1.5rem;
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
                    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
                }
                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
                }
                .card-header {
                    background: linear-gradient(135deg, #6a11cb, #2575fc);
                    border-radius: 1.5rem 1.5rem 0 0;
                    color: #fff;
                }
                .btn-primary {
                    background: linear-gradient(135deg, #6a11cb, #2575fc);
                    border: none;
                    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
                }
                .btn-primary:hover {
                    transform: scale(1.05);
                    box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
                }
                .input-group-text {
                    background: #f8f9fa;
                    border: none;
                    color: #6a11cb;
                }
                .table thead {
                    background: linear-gradient(135deg, #6a11cb, #2575fc); /* Fond dégradé pour <thead> */
                    color: #fff; /* Texte en blanc */
                }
                .table tbody {
                    background: #ffffff; /* Fond blanc pour le corps du tableau */
                    color: #333; /* Texte en gris foncé */
                }
                .table-hover tbody tr:hover {
                    background-color: rgba(106, 17, 203, 0.1); /* Effet de survol */
                }
                .small a {
                    color: #6a11cb;
                    text-decoration: none;
                    transition: color 0.3s ease-in-out;
                }
                .small a:hover {
                    color: #2575fc;
                }
                .floating-btn {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    width: 60px;
                    height: 60px;
                    background: linear-gradient(135deg, #6a11cb, #2575fc);
                    border: none;
                    border-radius: 50%;
                    color: #fff;
                    font-size: 24px;
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
                    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
                }
                .floating-btn:hover {
                    transform: scale(1.1);
                    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
                }
                .search-suggestions {
                    position: absolute;
                    z-index: 1000;
                    background: #fff;
                    border-radius: 0.5rem;
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
                    max-height: 200px;
                    overflow-y: auto;
                }
                .search-suggestions a {
                    display: block;
                    padding: 0.75rem 1rem;
                    color: #333;
                    text-decoration: none;
                    transition: background 0.3s ease-in-out;
                }
                .search-suggestions a:hover {
                    background: rgba(106, 17, 203, 0.1);
                }
            </style>
        </head>
        <body>
            <div class="container-fluid">
                <!-- En-tête Moderne -->
                <header class="d-flex justify-content-between align-items-center py-4 px-3 bg-white shadow-sm">
                    <h1 class="h3 text-primary fw-bold mb-0">Tableau de Bord des Archives Scolaires</h1>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                            <i class="fas fa-download me-2"></i> Exporter
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                            <i class="fas fa-print me-2"></i> Imprimer
                        </button>
                        <a href="logout.php" class="btn btn-danger btn-sm d-flex align-items-center">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </a>
                    </div>
                </header>
                <!-- Barre de Recherche Moderne -->
                <div class="row mt-4 px-3">
                    <div class="col-12">
                        <form id="searchForm" action="search_results.php" method="get" class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Rechercher des étudiants par nom, ID ou CIN" required>
                                <button type="submit" class="btn btn-primary">Rechercher</button>
                            </div>
                            <div id="searchSuggestions" class="search-suggestions mt-2" style="display: none;"></div>
                        </form>
                    </div>
                </div>
                <!-- Cartes Statistiques Modernes -->
                <div class="row g-4 mt-4 px-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title text-primary fw-bold">Étudiants Totals</h5>
                                    <h2 class="display-5 fw-bold"><?php echo $student_count; ?></h2>
                                </div>
                                <div class="mt-3">
                                    <a href="students.php" class="btn btn-primary w-100">Voir les Détails</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title text-info fw-bold">Étudiants en TC</h5>
                                    <h2 class="display-5 fw-bold"><?php echo $tc_count; ?></h2>
                                </div>
                                <div class="mt-3">
                                    <a href="students.php?class=TC" class="btn btn-info w-100">Voir les Étudiants en TC</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title text-success fw-bold">Étudiants en 1BAC</h5>
                                    <h2 class="display-5 fw-bold"><?php echo $bac1_count; ?></h2>
                                </div>
                                <div class="mt-3">
                                    <a href="students.php?class=1BAC" class="btn btn-success w-100">Voir les Étudiants en 1BAC</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title text-warning fw-bold">Étudiants en 2BAC</h5>
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
                <div class="row mt-4 px-3">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                                <h5 class="mb-0 fw-bold">Étudiants Ajoutés Récemment</h5>
                                <a href="students.php" class="btn btn-primary btn-sm">Voir Tous les Étudiants</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead>
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
            <!-- Bouton Flottant Moderne -->
            <a href="add_student.php" class="floating-btn">
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
        </body>
        </html>