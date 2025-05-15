<?php
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Start session if not already started
start_session();

// Check for remember me cookie
if (!is_logged_in() && isset($_COOKIE['remember_user'])) {
    check_remember_me();
}

// Get user details if logged in
$logged_in = is_logged_in();
$is_guest = is_guest();
$user_details = [];

if ($logged_in) {
    $user_details = get_user_details($_SESSION['user_id']);
    $user_role = $_SESSION['user_role']; // Use session role to ensure consistency
}

// Get current page for active nav link
$current_page = basename($_SERVER['PHP_SELF']);

// Default page title
$page_title = $page_title ?? SITE_NAME;
$header_title = $header_title ?? SITE_NAME;
$container_class = $container_class ?? '';
$hide_header_nav = $hide_header_nav ?? false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/main.css">
    <?php if (isset($extra_css)):
        echo $extra_css;
    endif; ?>
</head>

<body>
    <?php if (!$hide_header_nav): ?>
        <div class="header">
            <nav class="navbar navbar-expand-lg manual-navbar fixed-top">
                <div class="container-fluid px-3">
                    <a class="navbar-brand"
                        href="<?php echo $logged_in ? ($user_role == 'admin' ? 'admin.php' : ($user_role == 'author' ? 'user_dashboard.php' : 'end_user.php')) : 'end_user.php'; ?>">
                        <img src="assets/images/imga.svg" alt="Logo" width=150" height="50">
                    </a>
                    <h1 class="navbar-title "><?php echo html_escape($header_title); ?></h1>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText"
                        aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarText">
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <!-- Common link for all users -->
                            <li class="nav-item">
                                <a class="nav-link <?php echo is_current_page('end_user.php') ? 'active' : ''; ?>"
                                    href="end_user.php">Home</a>
                            </li>

                            <?php if ($logged_in): ?>
                                <!-- Profile link for all logged-in users -->
                                <li class="nav-item">
                                    <a class="nav-link <?php echo is_current_page('profile.php') ? 'active' : ''; ?>"
                                        href="profile.php">Profile</a>
                                </li>

                                <?php if ($user_role == 'admin'): ?>
                                    <!-- Admin links -->
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo is_current_page('admin.php') ? 'active' : ''; ?>"
                                            href="admin.php">Dashboard</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo is_current_page('categories.php') ? 'active' : ''; ?>"
                                            href="categories.php">Categories</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo is_current_page('users.php') ? 'active' : ''; ?>"
                                            href="users.php">Users</a>
                                    </li>
                                <?php elseif ($user_role == 'author'): ?>
                                    <!-- Author links -->
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo is_current_page('user_dashboard.php') ? 'active' : ''; ?>"
                                            href="user_dashboard.php">Author Dashboard</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo is_current_page('my_posts.php') ? 'active' : ''; ?>"
                                            href="my_posts.php">My Posts</a>
                                    </li>
                                <?php endif; ?>

                                <!-- Logout button for all logged-in users -->
                                <a href="logout.php" class="btn btn-danger ms-2"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            <?php elseif ($is_guest): ?>
                                <!-- Guest user indicator -->
                                <li class="nav-item">
                                    <span class="nav-link">
                                        <i class="fas fa-user-secret me-1"></i>Guest
                                    </span>
                                </li>
                                <a href="login.php" class="btn manual-button ms-2"><i class="fas fa-sign-in-alt"></i> Login</a>
                            <?php else: ?>
                                <!-- Login/Signup for non-logged in users -->
                                <a href="login.php" class="btn manual-button ms-2"><i class="fas fa-sign-in-alt"></i> Login</a>
                                <a href="signup.php" class="btn manual-button ms-2"><i class="fas fa-user-plus"></i> Sign Up</a>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    <?php endif; ?>

    <!-- Main content container -->
    <div class="container my-4 <?php echo $container_class; ?>">
        <?php
        // Display flash messages
        $flash_message = get_flash_message();
        if ($flash_message): ?>
            <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                <?php if ($flash_message['type'] === 'success'): ?>
                    <i class="fas fa-check-circle me-2"></i>
                <?php elseif ($flash_message['type'] === 'error' || $flash_message['type'] === 'danger'): ?>
                    <i class="fas fa-exclamation-circle me-2"></i>
                <?php elseif ($flash_message['type'] === 'warning'): ?>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                <?php elseif ($flash_message['type'] === 'info'): ?>
                    <i class="fas fa-info-circle me-2"></i>
                <?php endif; ?>
                <?php echo $flash_message['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>