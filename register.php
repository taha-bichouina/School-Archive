<?php
/**
 * Page d'inscription
 * 
 * Gère l'inscription des utilisateurs pour le Système de Gestion des Archives Scolaires
 */

// Démarrer la session
session_start();

// Rediriger les utilisateurs connectés vers le tableau de bord
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Initialiser les variables
$username = $password = $first_name = $last_name = $role = "";
$error = "";

// Traiter la soumission du formulaire d'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inclure la connexion à la base de données
    require_once 'includes/db_connect.php';
    
    // Récupérer les données du formulaire
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $role = trim($_POST['role']);
    
    // Valider les données du formulaire
    if (empty($username) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name) || empty($role)) {
        $error = "Tous les champs sont obligatoires";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        // Vérifier si le nom d'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "Le nom d'utilisateur existe déjà";
            } else {
                // Hacher le mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer un nouvel utilisateur dans la base de données
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, 'Actif')");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("sssss", $username, $hashed_password, $first_name, $last_name, $role);
                    
                    if ($insert_stmt->execute()) {
                        // Inscription réussie, rediriger vers la page de connexion
                        header("Location: index.php?registration=success");
                        exit;
                    } else {
                        $error = "Une erreur de base de données s'est produite lors de l'inscription";
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
    
    // Fermer la connexion
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Système de Gestion des Archives Scolaires</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-lg-5 col-md-7">
                <div class="card shadow-lg border-0 rounded-lg mt-5">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="font-weight-light my-2">Système de Gestion des Archives Scolaires</h3>
                    </div>
                    <div class="card-body">
                        <!-- Logo de l'école -->
                        <div class="text-center mb-4">
                            <img src="assets/img/logo.png" alt="Logo de l'école" class="img-fluid" style="max-height: 100px;">
                        </div>
                        
                        <!-- Message de succès -->
                        <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
                            <div class="alert alert-success">Inscription réussie ! Veuillez vous connecter.</div>
                        <?php endif; ?>
                        
                        <!-- Message d'erreur -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <!-- Formulaire d'inscription -->
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Entrez votre nom d'utilisateur" value="<?php echo htmlspecialchars($username); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Prénom</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Entrez votre prénom" value="<?php echo htmlspecialchars($first_name); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Nom de famille</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Entrez votre nom de famille" value="<?php echo htmlspecialchars($last_name); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="role" class="form-label">Rôle</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="" disabled selected>Sélectionnez votre rôle</option>
                                    <option value="admin" <?php echo ($role === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                                    <option value="teacher" <?php echo ($role === 'teacher') ? 'selected' : ''; ?>>Enseignant</option>
                                    <option value="student" <?php echo ($role === 'student') ? 'selected' : ''; ?>>Étudiant</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">S'inscrire</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <div class="small">
                            Vous avez déjà un compte ? <a href="index.php" class="text-decoration-none">Connectez-vous ici</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>