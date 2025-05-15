<?php
// Authentication related functions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Authenticate user
function authenticate_user($username_or_email, $password)
{
    // Get user from database
    $user = db_fetch_row(
        "SELECT * FROM user_details WHERE (user_name = ? OR user_email = ?) AND access = 1",
        [$username_or_email, $username_or_email]
    );

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid username or email'];
    }

    // Use direct comparison for now since we're not using password_hash
    if ($password !== $user['user_password']) {
        return ['success' => false, 'message' => 'Invalid password'];
    }

    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_role'] = $user['user_role'];
    $_SESSION['username'] = $user['user_name'];

    // Debug information
    error_log("User authenticated: ID={$user['user_id']}, Name={$user['user_name']}, Role={$user['user_role']}");

    return ['success' => true, 'user' => $user];
}

// Register a new user
function register_user($username, $email, $password)
{
    // Check if username exists
    if (db_count("SELECT * FROM user_details WHERE user_name = ?", [$username]) > 0) {
        return ['success' => false, 'message' => 'Username already exists'];
    }

    // Check if email exists
    if (db_count("SELECT * FROM user_details WHERE user_email = ?", [$email]) > 0) {
        return ['success' => false, 'message' => 'Email already exists'];
    }

    // Insert new user
    db_query(
        "INSERT INTO user_details (user_name, user_email, user_password, user_role, access) 
        VALUES (?, ?, ?, 'author', 1)",
        [$username, $email, $password]
    );

    $user_id = db_last_insert_id();

    if (!$user_id) {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }

    return ['success' => true, 'user_id' => $user_id];
}

// Check remember me cookie
function check_remember_me()
{
    if (!isset($_COOKIE['remember_user'])) {
        return false;
    }

    $token = $_COOKIE['remember_user'];
    $user = db_fetch_row(
        "SELECT u.* FROM user_details u 
         JOIN user_tokens t ON u.user_id = t.user_id 
         WHERE t.token = ? AND t.expires > NOW() AND u.access = 1",
        [$token]
    );

    if (!$user) {
        setcookie('remember_user', '', time() - 3600, '/');
        unset($_COOKIE['remember_user']);
        return false;
    }

    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_role'] = $user['user_role'];
    $_SESSION['username'] = $user['user_name'];

    return true;
}

// Get user details
function get_user_details($user_id)
{
    return db_fetch_row(
        "SELECT * FROM user_details WHERE user_id = ? AND access = 1",
        [$user_id]
    );
}
?>