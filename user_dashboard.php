<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/post_functions.php';
require_once 'includes/category_functions.php';
require_once 'includes/user_functions.php';

// Start session and require author
start_session();
require_author();

// Debug session information
error_log("User Dashboard - User ID: " . ($_SESSION['user_id'] ?? 'Not set'));
error_log("User Dashboard - Username: " . ($_SESSION['username'] ?? 'Not set'));
error_log("User Dashboard - User Role: " . ($_SESSION['user_role'] ?? 'Not set'));

// Handle post submission
if (isset($_POST['submit_post'])) {
    // Debug information
    error_log("Post form submitted in user_dashboard.php: " . print_r($_POST, true));

    if (isset($_POST['post_id'])) {
        // Update existing post
        $result = update_post($_POST, $_FILES);
    } else {
        // Create new post
        $result = create_post($_POST, $_FILES);
    }

    if ($result['success']) {
        set_flash_message('success', $result['message']);
    } else {
        set_flash_message('error', $result['message']);
    }

    redirect($_SERVER['PHP_SELF']);
}

// Get user details
$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Get recent posts
$recent_posts_options = [
    'author_id' => $user_id,
    'limit' => 5,
    'offset' => 0
];
$recent_posts = get_posts($recent_posts_options);

// Get post for editing if requested
$edit_post = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_post = get_post($_GET['edit']);

    // Verify ownership
    if (!$edit_post || $edit_post['r_author_id'] != $user_id) {
        set_flash_message('error', 'You do not have permission to edit this post');
        redirect('user_dashboard.php');
    }
}

// Set page variables for header
$page_title = "Author Dashboard";
$current_page = "dashboard";

// Include header
include('components/header.php');
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-tachometer-alt me-2"></i> Author Dashboard</h2>
        <p class="text-muted">Welcome back, <?php echo html_escape($_SESSION['username']); ?>! Manage your blog posts
            and view your statistics.</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="?create_post=1" class="btn manual-button">
            <i class="fas fa-plus-circle me-1"></i> Create New Post
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <?php
    // Get stats
    $total_posts = count_posts(['author_id' => $user_id]);
    $published_posts = count_posts(['author_id' => $user_id, 'status' => 'published']);
    $draft_posts = count_posts(['author_id' => $user_id, 'status' => 'draft']);

    // Define stats cards
    $stats = [
        [
            'title' => 'Total Posts',
            'count' => $total_posts,
            'icon' => 'file-alt',
            'color' => 'primary',
            'link' => 'my_posts.php'
        ],
        [
            'title' => 'Published Posts',
            'count' => $published_posts,
            'icon' => 'check-circle',
            'color' => 'success',
            'link' => 'my_posts.php?status=published'
        ],
        [
            'title' => 'Draft Posts',
            'count' => $draft_posts,
            'icon' => 'edit',
            'color' => 'warning',
            'link' => 'my_posts.php?status=draft'
        ]
    ];

    // Output stats cards
    foreach ($stats as $stat):
        ?>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2"><?php echo $stat['title']; ?></h6>
                            <h3 class="mb-0 stats-number"><?php echo $stat['count']; ?></h3>
                        </div>
                        <div class="icon-circle bg-<?php echo $stat['color']; ?> stats-icon">
                            <i class="fas fa-<?php echo $stat['icon']; ?>"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="<?php echo $stat['link']; ?>" class="text-<?php echo $stat['color']; ?> text-decoration-none">
                        View Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <!-- Recent Posts -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Posts</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark sticky-thead">
                            <tr>
                                <th width="40%">Title</th>
                                <th width="20%">Category</th>
                                <th width="15%">Status</th>
                                <th width="15%">Date</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_posts['posts'])): ?>
                                <?php foreach ($recent_posts['posts'] as $post): ?>
                                    <tr>
                                        <td>
                                            <a href="view_post.php?id=<?php echo $post['post_id']; ?>"
                                                class="text-decoration-none fw-bold">
                                                <?php echo html_escape($post['title']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo html_escape($post['category_name']); ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo $post['post_status'] == 'published' ? 'success' : 'warning'; ?> post-status status-<?php echo $post['post_status']; ?>">
                                                <?php echo ucfirst($post['post_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($post['create_time']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?edit=<?php echo $post['post_id']; ?>"
                                                    class="btn btn-sm manual-button action-btn edit-btn">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-3">No posts found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <a href="my_posts.php" class="btn manual-button">
                    <i class="fas fa-list me-1"></i> View All Posts
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-link me-2"></i> Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="?create_post=1"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-plus-circle me-2"></i> Create New Post
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="my_posts.php"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-file-alt me-2"></i> Manage My Posts
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="profile.php"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-user-circle me-2"></i> Edit Profile
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="end_user.php"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-eye me-2"></i> View Blog
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recently Updated Posts -->
        <div class="card shadow-sm mt-4">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Recently Updated</h5>
            </div>
            <div class="card-body px-0 py-2">
                <?php
                // Get recently updated posts (by update_time)
                $updated_posts_options = [
                    'author_id' => $user_id,
                    'limit' => 5,
                    'offset' => 0
                ];
                $updated_posts = get_posts($updated_posts_options);

                if (!empty($updated_posts['posts'])):
                    // Sort by update_time
                    usort($updated_posts['posts'], function ($a, $b) {
                        return strtotime($b['update_time']) - strtotime($a['update_time']);
                    });

                    foreach (array_slice($updated_posts['posts'], 0, 5) as $post):
                        ?>
                        <div class="recent-post-item px-3">
                            <a href="view_post.php?id=<?php echo $post['post_id']; ?>" class="recent-post-link">
                                <?php echo html_escape($post['title']); ?>
                            </a>
                            <div class="recent-post-date">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo format_date($post['update_time'], 'M j, Y g:i A'); ?>
                            </div>
                        </div>
                        <?php
                    endforeach;
                else:
                    ?>
                    <div class="px-3 py-3 text-center">
                        <p class="mb-0">No recently updated posts.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tips -->
        <div class="card shadow-sm mt-4">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Writing Tips</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Use clear, concise titles
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Break content into paragraphs
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Include relevant images
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Choose the right category
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Proofread before publishing
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// Check if the modal should be displayed
$show_post_modal = isset($_GET['create_post']) || isset($edit_post);

// Include Post Modal
include('components/post_modal.php');

// Include footer
include('components/footer.php');
?>