<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/user_functions.php';
require_once 'includes/post_functions.php';

// Start session and require admin
start_session();
require_admin();

// Handle user creation
if (isset($_POST['create_user'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'author';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        set_flash_message('error', 'All fields are required');
    } elseif ($password !== $confirm_password) {
        set_flash_message('error', 'Passwords do not match');
    } elseif (strlen($password) < 6) {
        set_flash_message('error', 'Password must be at least 6 characters long');
    } else {
        // Create user
        $result = db_query(
            "INSERT INTO user_details (user_name, user_email, user_password, user_role, access) 
             VALUES (?, ?, ?, ?, 1)",
            [$username, $email, $password, $role]
        );
        
        if ($result) {
            set_flash_message('success', 'User created successfully');
        } else {
            set_flash_message('error', 'Failed to create user');
        }
    }
    
    redirect($_SERVER['PHP_SELF']);
}

// Handle user update
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'author';
    
    if (empty($username) || empty($email)) {
        set_flash_message('error', 'Username and email are required');
    } else {
        // Update user
        $result = db_query(
            "UPDATE user_details 
             SET user_name = ?, 
                 user_email = ?, 
                 user_role = ?
             WHERE user_id = ?",
            [$username, $email, $role, $user_id]
        );
        
        if ($result) {
            set_flash_message('success', 'User updated successfully');
        } else {
            set_flash_message('error', 'Failed to update user');
        }
    }
    
    redirect($_SERVER['PHP_SELF']);
}

// Handle user deletion
if (isset($_POST['delete_item']) && $_POST['delete_type'] === 'users') {
    $user_id = $_POST['delete_id'];
    
    // Check if user has posts
    $posts_result = get_posts(['author_id' => $user_id]);
    $post_count = count($posts_result['posts']);
    
    if ($post_count > 0) {
        set_flash_message('error', "Cannot delete user with $post_count posts. Please reassign or delete these posts first.");
    } else {
        // Soft delete user - set access to 0
        $result = db_query(
            "UPDATE user_details SET access = 0 WHERE user_id = ?",
            [$user_id]
        );
        
        if ($result) {
            set_flash_message('success', 'User deleted successfully');
        } else {
            set_flash_message('error', 'Failed to delete user');
        }
    }
    
    redirect($_SERVER['PHP_SELF']);
}

// Get user for editing
$edit_user = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_user = get_user($_GET['edit']);
}

// Get all users
$users = get_users();

// Set page variables for header
$page_title = "Manage Users";
$current_page = "users";

// Include header
include('components/header.php');
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-users me-2"></i> Manage Users</h2>
        <p class="text-muted">Create and manage users for your blog.</p>
    </div>
</div>

<div class="row">
    <!-- User Form -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0">
                    <i class="fas fa-<?php echo $edit_user ? 'edit' : 'user-plus'; ?> me-2"></i> 
                    <?php echo $edit_user ? 'Edit User' : 'Add New User'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <?php if ($edit_user): ?>
                        <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required
                            value="<?php echo $edit_user ? html_escape($edit_user['user_name']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required
                            value="<?php echo $edit_user ? html_escape($edit_user['user_email']) : ''; ?>">
                    </div>
                    
                    <?php if (!$edit_user): ?>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Password must be at least 6 characters long</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="author" <?php echo ($edit_user && $edit_user['user_role'] == 'author') ? 'selected' : ''; ?>>Author</option>
                            <option value="admin" <?php echo ($edit_user && $edit_user['user_role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="end_user" <?php echo ($edit_user && $edit_user['user_role'] == 'end_user') ? 'selected' : ''; ?>>End User</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="<?php echo $edit_user ? 'update_user' : 'create_user'; ?>" class="btn manual-button">
                            <i class="fas fa-<?php echo $edit_user ? 'save' : 'user-plus'; ?> me-1"></i> 
                            <?php echo $edit_user ? 'Update User' : 'Add User'; ?>
                        </button>
                        
                        <?php if ($edit_user): ?>
                            <a href="users.php" class="btn manual-button cancel">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Username</th>
                                <th width="30%">Email</th>
                                <th width="15%">Role</th>
                                <th width="15%">Joined</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $index => $user): 
                                    // Get post count for this user
                                    $user_posts = get_posts(['author_id' => $user['user_id']]);
                                    $post_count = count($user_posts['posts']);
                                ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo html_escape($user['user_name']); ?></td>
                                        <td><?php echo html_escape($user['user_email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['user_role'] == 'admin' ? 'danger' : ($user['user_role'] == 'author' ? 'primary' : 'secondary'); ?>">
                                                <?php echo ucfirst($user['user_role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($user['create_time'], 'M j, Y'); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="users.php?edit=<?php echo $user['user_id']; ?>" class="btn btn-sm manual-button">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <?php
                                                // Don't allow deleting yourself
                                                if ($user['user_id'] != $_SESSION['user_id']):
                                                    // Set up delete modal variables
                                                    $item = [
                                                        'id' => $user['user_id'], 
                                                        'name' => $user['user_name']
                                                    ];
                                                    
                                                    $table = 'users';
                                                    $item_type = 'User';
                                                    
                                                    include('components/delete_modal.php');
                                                ?>
                                                
                                                <button type="button" class="btn btn-sm manual-button" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#<?php echo $modal_id; ?>"
                                                    <?php echo $post_count > 0 ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include('components/footer.php');
?>
