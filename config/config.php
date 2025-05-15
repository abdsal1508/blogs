<?php
// Global configuration file
// Database settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '0000');
define('DB_NAME', 'blog_management_system');

// Session settings
define('SESSION_NAME', 'blog_session');
define('COOKIE_LIFETIME', 60 * 60 * 24 * 30); // 30 days

// File upload settings
define('UPLOAD_DIR', 'assets/uploads/posts/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Site settings
define('SITE_NAME', 'Simply Blogs');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('ITEMS_PER_PAGE', 10);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
