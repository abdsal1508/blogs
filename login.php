<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Start session
start_session();

// Clear any existing session data to prevent login issues
session_unset();
session_destroy();
session_start();

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $result = authenticate_user($email, $password);

        if ($result['success']) {
            // Debug information
            error_log("Login successful: User={$_SESSION['username']}, Role={$_SESSION['user_role']}");

            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); // 30 days

                // Store token in database
                db_query(
                    "INSERT INTO user_tokens (user_id, token, expires) VALUES (?, ?, ?)",
                    [$_SESSION['user_id'], $token, $expires]
                );

                // Set cookie
                setcookie('remember_user', $token, time() + 60 * 60 * 24 * 30, '/');
            }

            // Redirect based on user role
            if ($_SESSION['user_role'] === 'admin') {
                redirect('admin.php');
            } elseif ($_SESSION['user_role'] === 'author') {
                redirect('user_dashboard.php');
            } else {
                redirect('end_user.php');
            }
        } else {
            $error = $result['message'];
        }
    }
}

// Set page variables for header
$page_title = "Login";
$current_page = "login";
$hide_footer = true;
$hide_header_nav = true;

// Include header
include('components/header.php');
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i> Login</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email or Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="text" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn manual-button">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                    </div>
                </form>

                <hr>

                <div class="text-center">
                    <p>Don't have an account? <a href="signup.php">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include('components/footer.php');
?>