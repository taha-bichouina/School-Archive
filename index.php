<?php
session_start();

$username = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'includes/db_connect.php';

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Le nom d'utilisateur et le mot de passe sont requis";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? AND status = 'Active'");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
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
            $error = "Erreur de connexion à la base de données";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Archives Scolaires</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --bg-dark: #111827;
            --card-dark: #1f2937;
            --border-dark: #374151;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --hover-dark: #2d3748;
            --danger: #ef4444;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .card {
            background: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }
        .card-header {
            background: linear-gradient(to right, var(--card-dark), var(--bg-dark));
            border-bottom: 1px solid var(--border-dark);
            padding: 2rem;
            text-align: center;
        }
        .form-control, .form-control:focus {
            background: var(--bg-dark);
            border: 1px solid var(--border-dark);
            color: var(--text-primary);
        }
        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 1;
        }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .input-group-text {
            background-color: var(--hover-dark);
            border: 1px solid var(--border-dark);
            color: var(--primary);
        }
        .alert {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        .small a {
            color: var(--primary);
        }
        .small a:hover {
            text-decoration: underline;
        }
        .card-footer .small {
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
        <h3 class="fw-bold" style="color: var(--text-primary);">Système de Gestion des Archives Scolaires</h3>
        <p class="text-secondary" style="color: var(--text-secondary);">Connexion</p>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error)): ?>
                <div class="alert rounded-pill text-center"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Nom d'utilisateur" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="small">Mot de passe oublié ?</a>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center p-3 border-top border-dark">
            <div class="small">
                Pas de compte ? <a href="register.php">Créer un compte</a>
            </div>
            <div class="small text-secondary mt-2">&copy; <?php echo date("Y"); ?> Taha Bichouina & Nacer Eddine Bouras</div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>
