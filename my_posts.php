<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/post_functions.php';
require_once 'includes/category_functions.php';

// Start session and require author
start_session();
require_author();

// Handle post submission
if (isset($_POST['submit_post'])) {
    // Debug information
    error_log("Post form submitted in my_posts.php: " . print_r($_POST, true));
    
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

// Initialize variables
$search_query = '';
$category_id = '';
$status_filter = '';
$options = [
    'author_id' => $_SESSION['user_id']
];

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $options['search'] = $search_query;
}

// Handle category filter
if (isset($_GET['category']) && !empty($_GET['category']) && $_GET['category'] != 'all') {
    $category_id = $_GET['category'];
    $options['category_id'] = $category_id;
}

// Handle status filter
if (isset($_GET['status']) && !empty($_GET['status']) && $_GET['status'] != 'all') {
    $status_filter = $_GET['status'];
    $options['status'] = $status_filter;
}

// Get posts with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$options['limit'] = $per_page;
$options['offset'] = ($page - 1) * $per_page;

$posts_result = get_posts($options);
$posts = $posts_result['posts'];
$total_posts = $posts_result['total'];
$total_pages = ceil($total_posts / $per_page);

// Get categories for filter
$categories = get_categories();

// Get post for editing if requested
$edit_post = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_post = get_post($_GET['edit']);
    
    // Verify ownership
    if (!$edit_post || $edit_post['r_author_id'] != $_SESSION['user_id']) {
        set_flash_message('error', 'You do not have permission to edit this post');
        redirect('my_posts.php');
    }
}

// Handle post deletion
if (isset($_POST['delete_item']) && $_POST['delete_type'] === 'posts') {
    $post_id = $_POST['delete_id'];
    
    // Verify ownership
    $post = get_post($post_id);
    if (!$post || $post['r_author_id'] != $_SESSION['user_id']) {
        set_flash_message('error', 'You do not have permission to delete this post');
        redirect('my_posts.php');
    }
    
    $result = delete_post($post_id);
    
    if ($result['success']) {
        set_flash_message('success', $result['message']);
    } else {
        set_flash_message('error', $result['message']);
    }
    
    redirect('my_posts.php');
}

// Set page variables for header
$page_title = "My Posts";
$current_page = "my_posts";

// Include header
include('components/header.php');
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-file-alt me-2"></i> My Posts</h2>
        <p class="text-muted">Manage your blog posts.</p>
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
    $total_published = count_posts(['status' => 'published', 'author_id' => $_SESSION['user_id']]);
    $total_drafts = count_posts(['status' => 'draft', 'author_id' => $_SESSION['user_id']]);
    
    // Define stats cards
    $stats = [
        [
            'title' => 'Published Posts',
            'count' => $total_published,
            'icon' => 'file-alt',
            'color' => 'primary',
            'link' => '?status=published'
        ],
        [
            'title' => 'Draft Posts',
            'count' => $total_drafts,
            'icon' => 'file',
            'color' => 'warning',
            'link' => '?status=draft'
        ]
    ];
    
    // Output stats cards
    foreach ($stats as $stat):
    ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2"><?php echo $stat['title']; ?></h6>
                            <h3 class="mb-0"><?php echo $stat['count']; ?></h3>
                        </div>
                        <div class="icon-circle bg-<?php echo $stat['color']; ?>">
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

<!-- Filter and Search -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="row g-3">
            <!-- Search -->
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search posts..." name="search" value="<?php echo html_escape($search_query); ?>">
                    <button class="btn manual-button" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <!-- Category Filter -->
            <div class="col-md-3">
                <select class="form-select" name="category" onchange="this.form.submit()">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo html_escape($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Status Filter -->
            <div class="col-md-3">
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="all">All Statuses</option>
                    <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
            
            <!-- Reset Filters -->
            <div class="col-md-2">
                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn manual-button w-100">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Posts Table -->
<div class="card shadow-sm mb-4">
    <div class="card-header heading-for text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i> My Posts</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">#</th>
                        <th width="40%">Title</th>
                        <th width="15%">Category</th>
                        <th width="15%">Status</th>
                        <th width="15%">Date</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $index => $post): ?>
                            <tr>
                                <td><?php echo ($page - 1) * $per_page + $index + 1; ?></td>
                                <td>
                                    <a href="view_post.php?id=<?php echo $post['post_id']; ?>" class="text-decoration-none fw-bold">
                                        <?php echo html_escape($post['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo html_escape($post['category_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $post['post_status'] == 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($post['post_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo format_date($post['create_time']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $post['post_id']; ?>" class="btn btn-sm manual-button">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php
                                        // Set up delete modal variables
                                        $item = [
                                            'id' => $post['post_id'], 
                                            'name' => $post['title']
                                        ];
                                        
                                        $table = 'posts';
                                        $item_type = 'Post';
                                        
                                        include('components/delete_modal.php');
                                        ?>
                                        
                                        <button type="button" class="btn btn-sm manual-button" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#<?php echo $modal_id; ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3">No posts found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-white">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo $category_id; ?>&status=<?php echo $status_filter; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo $category_id; ?>&status=<?php echo $status_filter; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo $category_id; ?>&status=<?php echo $status_filter; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php
// Check if the modal should be displayed
$show_post_modal = isset($_GET['create_post']) || isset($edit_post);

// Include Post Modal
include('components/post_modal.php');

// Include footer
include('components/footer.php');
?>
