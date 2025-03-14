<?php
/**
 * Page de connexion
 * 
 * Gère l'authentification et la connexion des utilisateurs au Système de Gestion des Archives Scolaires
 */

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Initialiser les variables
$username = $password = "";
$error = "";

// Traiter la soumission du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inclure la connexion à la base de données
    require_once 'includes/db_connect.php';
    
    // Récupérer les données du formulaire
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Ne pas nettoyer le mot de passe avant vérification
    
    // Valider les données du formulaire
    if (empty($username) || empty($password)) {
        $error = "Le nom d'utilisateur et le mot de passe sont requis";
    } else {
        // Préparer et exécuter la requête
        $stmt = $conn->prepare("SELECT id, username, password, first_name, last_name, role FROM users WHERE username = ? AND status = 'Active'");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Vérifier le mot de passe
                if (password_verify($password, $user['password'])) {
                    // Le mot de passe est correct, démarrer une nouvelle session
                    session_regenerate_id(); // Empêcher les attaques de fixation de session
                    
                    // Stocker les données utilisateur dans la session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time();
                    
                    // Enregistrer l'activité de connexion
                    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity, ip_address) VALUES (?, 'Login', ?)");
                    if ($log_stmt) {
                        $ip = $_SERVER['REMOTE_ADDR'];
                        $log_stmt->bind_param("is", $user['id'], $ip);
                        $log_stmt->execute();
                        $log_stmt->close();
                    }
                    
                    // Rediriger vers le tableau de bord
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Nom d'utilisateur ou mot de passe incorrect";
                }
            } else {
                $error = "Nom d'utilisateur ou mot de passe incorrect";
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
    <title>Connexion - Système de Gestion des Archives Scolaires</title>
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
        }
        .card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border-radius: 1.5rem 1.5rem 0 0;
        }
        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border: none;
            transition: transform 0.3s ease-in-out;
        }
        .btn-primary:hover {
            transform: scale(1.05);
        }
        .input-group-text {
            background: #f8f9fa;
            border: none;
            color: #6a11cb;
        }
        .alert {
            border-radius: 1rem;
        }
        .small a {
            color: #6a11cb;
            text-decoration: none;
        }
        .small a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-5 col-md-7">
                <div class="card shadow-lg">
                    <div class="card-header bg-transparent text-center">
                        <h3 class="text-primary fw-bold mb-0">Système de Gestion des Archives Scolaires</h3>
                    </div>
                    <div class="card-body p-4">
                        <!-- Message d'erreur -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger rounded-pill"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <!-- Formulaire de connexion -->
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Entrez votre nom d'utilisateur" value="<?php echo htmlspecialchars($username); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                                </div>
                                <a class="small text-decoration-none" href="#">Mot de passe oublié ?</a>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Se connecter</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3 bg-transparent">
                        <div class="small text-muted">
                            Vous n'avez pas de compte ? 
                            <a href="register.php" class="text-decoration-none text-primary fw-bold">Créer un compte</a>
                        </div>
                        <div class="small text-muted">&copy; <?php echo date("Y"); ?> Système de Gestion des Archives Scolaires</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Basculer la visibilité du mot de passe
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>