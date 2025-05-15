<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Start session
start_session();

// Get user ID before clearing session
$user_id = $_SESSION['user_id'] ?? null;

// Clear session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_user'])) {
    $token = $_COOKIE['remember_user'];

    // Delete token from database if user_id is available
    if ($user_id) {
        db_query("DELETE FROM user_tokens WHERE user_id = ? AND token = ?", [$user_id, $token]);
    }

    // Clear the cookie
    setcookie('remember_user', '', time() - 3600, '/');
    unset($_COOKIE['remember_user']);
}

// Redirect to login page
redirect('login.php');
?>