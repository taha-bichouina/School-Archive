<?php
session_start();

$username = $password = $confirm_password = $first_name = $last_name = "";
$error = "";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'includes/db_connect.php';

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    if (empty($username) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name)) {
        $error = "Tous les champs sont obligatoires";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Le nom d'utilisateur existe déjà";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = "student";
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, 'Actif')");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("sssss", $username, $hashed_password, $first_name, $last_name, $role);
                    if ($insert_stmt->execute()) {
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
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Archives Scolaires</title>
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
            --success: #10b981;
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
            max-width: 500px;
        }
        .card-header {
            background: linear-gradient(to right, var(--card-dark), var(--bg-dark));
            border-bottom: 1px solid var(--border-dark);
            padding: 2rem;
            text-align: center;
        }
        .card-header h3 {
            color: var(--text-primary);
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
            border: 1px solid;
            border-radius: 0.75rem;
            padding: 0.75rem 1.25rem;
        }
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border-color: var(--danger);
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-color: var(--success);
        }
        .card-footer .small {
            color: var(--text-secondary);
        }
        .card-footer .small a {
            color: var(--primary);
        }
        .card-footer .small a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<!-- le reste du HTML est inchangé -->
<body>
    <div class="card">
        <div class="card-header">
            <h3 class="fw-bold">Système de Gestion des Archives Scolaires</h3>
            <p class="text-secondary">Inscription</p>
        </div>
        <div class="card-body p-4">
            <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
                <div class="alert alert-success">Inscription réussie ! Veuillez vous connecter.</div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
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
                    </div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmez le mot de passe" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="first_name" class="form-label">Prénom</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Votre prénom" value="<?php echo htmlspecialchars($first_name); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Nom de famille</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Votre nom de famille" value="<?php echo htmlspecialchars($last_name); ?>" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">S'inscrire</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center p-3 border-top border-dark">
            <div class="small">
                Vous avez déjà un compte ? <a href="index.php">Connectez-vous ici</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <footer class="text-center py-3 mt-5 text-secondary" style="font-size: 0.9rem;">
    &copy; <?php echo date("Y"); ?> Taha Bichouina & Nacer Eddine Bouras & Lycée Chahid Hrizi
</body>

</html>
