<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to login page if user is not logged in
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: ../index.php");
        exit;
    }
}

/**
 * Check if user has required role
 * @param string $required_role Required role (admin, teacher, staff)
 */
function require_role($required_role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
        header("Location: ../dashboard.php?error=unauthorized");
        exit;
    }
}

/**
 * Check if user has at least one of the required roles
 * @param array $allowed_roles Array of allowed roles
 */
function require_any_role($allowed_roles) {
    if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
        header("Location: ../dashboard.php?error=unauthorized");
        exit;
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }
}
?>