<?php
// General utility functions

// Start or resume a session with consistent settings
function start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        // Set consistent session name
        session_name("blog_session");
        session_start();
    }
}

// Redirect to a specified URL
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Check if user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function is_admin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Check if user is author
function is_author()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'author';
}

// Check if user is guest
function is_guest()
{
    return isset($_SESSION['guest']) && $_SESSION['guest'] === true;
}

// Require user to be logged in, redirect to login if not
function require_login()
{
    if (!is_logged_in()) {
        set_flash_message('error', 'Please log in to access this page');
        redirect('login.php');
    }
}

// Require user to be admin, redirect if not
function require_admin()
{
    require_login();
    if (!is_admin()) {
        set_flash_message('error', 'You do not have permission to access this page');
        redirect('end_user.php');
    }
}

// Require user to be author, redirect if not
function require_author()
{
    require_login();

    // Debug information
    error_log("require_author check - User ID: " . ($_SESSION['user_id'] ?? 'Not set'));
    error_log("require_author check - User Role: " . ($_SESSION['user_role'] ?? 'Not set'));

    if (!is_author() && !is_admin()) {
        set_flash_message('error', 'You do not have permission to access this page');
        redirect('end_user.php');
    }
}

// Set a flash message to be displayed on the next page
function set_flash_message($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get and clear the flash message
function get_flash_message()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Escape HTML to prevent XSS
function html_escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Format a date with a specified format
function format_date($date, $format = 'F j, Y')
{
    return date($format, strtotime($date));
}

// Upload an image file
function upload_image($file)
{
    // Check if upload directory exists, create if not
    $upload_dir = 'assets/uploads/posts/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $filename = time() . '_' . basename($file['name']);
    $target_path = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $target_path;
    }

    return null;
}

// Check if user can edit a post
function can_edit_post($post_id, $user_id)
{
    // Admin can edit any post
    if (is_admin()) {
        return true;
    }

    // Author can only edit their own posts
    $post = get_post($post_id);
    return $post && $post['r_author_id'] == $user_id;
}

// Check if current page matches given page
function is_current_page($page)
{
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page === $page;
}

// Get account age in days
function get_account_age($create_time)
{
    $create_date = new DateTime($create_time);
    $current_date = new DateTime();
    $interval = $create_date->diff($current_date);
    return $interval->days;
}

// Count posts with filtering options
function count_posts($options = [])
{
    $where_clauses = ["p.status_del = 1"];
    $params = [];

    // Apply filters
    if (isset($options['author_id'])) {
        $where_clauses[] = "p.r_author_id = ?";
        $params[] = $options['author_id'];
    }

    if (isset($options['category_id'])) {
        $where_clauses[] = "p.r_category_id = ?";
        $params[] = $options['category_id'];
    }

    if (isset($options['status'])) {
        $where_clauses[] = "p.post_status = ?";
        $params[] = $options['status'];
    }

    // Build the WHERE clause
    $where_clause = implode(' AND ', $where_clauses);

    // Get count
    $result = db_fetch_row(
        "SELECT COUNT(*) as count FROM posts_details p WHERE $where_clause",
        $params
    );

    return $result ? $result['count'] : 0;
}

// Count categories
function count_categories()
{
    $result = db_fetch_row("SELECT COUNT(*) as count FROM categories WHERE status_del = 1");
    return $result ? $result['count'] : 0;
}

// Count users
function count_users()
{
    $result = db_fetch_row("SELECT COUNT(*) as count FROM user_details WHERE access = 1");
    return $result ? $result['count'] : 0;
}
?>