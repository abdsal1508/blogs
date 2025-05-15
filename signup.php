<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/user_functions.php';

// Start session
start_session();

// Clear any existing session data to ensure clean signup
session_unset();
session_destroy();
session_start();

$error = '';
$success = '';

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if username exists
        if (db_count("SELECT * FROM user_details WHERE user_name = ?", [$username]) > 0) {
            $error = 'Username already exists';
        }
        // Check if email exists
        elseif (db_count("SELECT * FROM user_details WHERE user_email = ?", [$email]) > 0) {
            $error = 'Email already exists';
        } else {
            // Insert new user
            db_query(
                "INSERT INTO user_details (user_name, user_email, user_password, user_role, access) 
                VALUES (?, ?, ?, 'end_user', 1)",
                [$username, $email, $password]
            );

            $user_id = db_last_insert_id();

            if ($user_id) {
                $success = 'Registration successful! Please log in with your new account.';

                // Redirect to login page instead of auto-login
                set_flash_message('success', 'Account created successfully! Please log in with your new credentials.');
                redirect('login.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

// Set page variables for header
$page_title = "Sign Up";
$current_page = "signup";
$hide_footer = true;
$hide_header_nav = true;

// Include header
include('components/header.php');
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i> Sign Up</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-text">Password must be at least 6 characters long</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn manual-button">
                            <i class="fas fa-user-plus me-2"></i> Sign Up
                        </button>
                    </div>
                </form>

                <hr>

                <div class="text-center">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// Include footer
include('components/footer.php');
?>