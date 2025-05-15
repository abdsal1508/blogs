<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/post_functions.php';
require_once 'includes/category_functions.php';

// Start session
start_session();

// Initialize variables
$search_query = '';
$category_id = '';
$options = [
    'status' => 'published'
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

// Get posts
$posts_result = get_posts($options);
$posts = $posts_result['posts'];

// Get categories for sidebar
$categories = get_categories();

// Get featured post (most recent)
$featured_post = get_featured_post();

// Get popular categories (top 5 by post count)
$popular_categories = [];
foreach ($categories as $category) {
    $cat_posts = get_posts(['category_id' => $category['category_id'], 'status' => 'published']);
    $popular_categories[] = [
        'category_id' => $category['category_id'],
        'category_name' => $category['category_name'],
        'post_count' => count($cat_posts['posts'])
    ];
}

// Sort by post count
usort($popular_categories, function ($a, $b) {
    return $b['post_count'] - $a['post_count'];
});

// Get top 5
$popular_categories = array_slice($popular_categories, 0, 5);

// Set page variables for header
$page_title = "Blog";
$current_page = "blog";
$container_class = "article-container";

// Include header
include('components/header.php');
?>

<!-- Featured Post -->
<?php if ($featured_post && empty($search_query) && empty($category_id)): ?>
    <div class="card mb-4 shadow-lg featured-post">
        <div class="row g-0">
            <div class="col-md-6">
                <img src="<?php echo !empty($featured_post['image_link']) ? $featured_post['image_link'] : 'assets/images/placeholder.jpg'; ?>"
                    class="img-fluid rounded-start" alt="<?php echo html_escape($featured_post['title']); ?>"
                    style="height: 100%; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <div class="card-body d-flex flex-column h-100">
                    <div class="mb-2">
                        <span class="badge heading-for"><?php echo html_escape($featured_post['category_name']); ?></span>
                        <span class="text-muted ms-2"><i class="fas fa-calendar-alt me-1"></i>
                            <?php echo format_date($featured_post['create_time']); ?></span>
                    </div>
                    <h2 class="card-title"><?php echo html_escape($featured_post['title']); ?></h2>
                    <p class="card-text flex-grow-1">
                        <?php echo substr(html_escape($featured_post['content']), 0, 200) . '...'; ?>
                    </p>
                    <div class="mt-auto">
                        <div class="d-flex align-items-center mb-3">
                            <div class="author-avatar me-2">
                                <?php echo substr($featured_post['author_name'], 0, 1); ?>
                            </div>
                            <span><?php echo html_escape($featured_post['author_name']); ?></span>
                        </div>
                        <a href="view_post.php?id=<?php echo $featured_post['post_id']; ?>" class="btn manual-button">
                            <i class="fas fa-book-reader me-1"></i> Read Full Article
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Search and Filter Section -->
        <div class="card mb-4 shadow-lg">
            <div class="card-body">
                <form action="end_user.php" method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control search-input" placeholder="Search articles..."
                            name="search" value="<?php echo html_escape($search_query); ?>">
                        <button class="btn search-button" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <div class="d-flex flex-wrap gap-2 category-filter">
                    <a href="end_user.php" class="filter-btn <?php echo empty($category_id) ? 'active' : ''; ?>">
                        All Posts
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="end_user.php?category=<?php echo $category['category_id']; ?>"
                            class="filter-btn <?php echo $category_id == $category['category_id'] ? 'active' : ''; ?>">
                            <?php echo html_escape($category['category_name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Results Header -->
        <div class="mb-4">
            <h2>
                <?php if (!empty($search_query)): ?>
                    <i class="fas fa-search me-2"></i> Search Results for "<?php echo html_escape($search_query); ?>"
                <?php elseif (!empty($category_id)):
                    $category_name = "Category";
                    foreach ($categories as $category) {
                        if ($category['category_id'] == $category_id) {
                            $category_name = $category['category_name'];
                            break;
                        }
                    }
                    ?>
                    <i class="fas fa-tag me-2"></i> <?php echo html_escape($category_name); ?> Articles
                <?php else: ?>
                    <i class="fas fa-newspaper me-2"></i> Latest Articles
                <?php endif; ?>
            </h2>
        </div>

        <!-- Blog Posts -->
        <div class="row">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-lg blog-post">
                            <img src="<?php echo !empty($post['image_link']) ? $post['image_link'] : 'assets/images/placeholder.jpg'; ?>"
                                class="card-img-top" alt="<?php echo html_escape($post['title']); ?>"
                                style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column post-content-wrapper">
                                <div class="mb-2">
                                    <span
                                        class="badge heading-for category-badge"><?php echo html_escape($post['category_name']); ?></span>
                                    <span class="text-muted ms-2"><i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo format_date($post['create_time'], 'M j, Y'); ?></span>
                                </div>
                                <h5 class="card-title post-title"><?php echo html_escape($post['title']); ?></h5>
                                <p class="card-text flex-grow-1 post-excerpt">
                                    <?php echo substr(html_escape($post['content']), 0, 100) . '...'; ?>
                                </p>
                                <div class="mt-auto">
                                    <div class="d-flex align-items-center mb-3 post-meta">
                                        <div class="author-avatar me-2">
                                            <?php echo substr($post['author_name'], 0, 1); ?>
                                        </div>
                                        <span><?php echo html_escape($post['author_name']); ?></span>
                                    </div>
                                    <a href="view_post.php?id=<?php echo $post['post_id']; ?>"
                                        class="btn manual-button btn-sm read-more">
                                        Read More
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info no-posts">
                        <i class="fas fa-info-circle me-2"></i> No posts found. Please try a different search or category.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- About -->
        <div class="card mb-4 shadow-lg sidebar-card">
            <div class="card-header heading-for text-white sidebar-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> About</h5>
            </div>
            <div class="card-body sidebar-body">
                <p>Welcome to our Blog Management System. Here you can find the latest articles from our authors on
                    various topics.</p>
                <?php if (!is_logged_in()): ?>
                    <p>Want to contribute? <a href="login.php">Login</a> or <a href="signup.php">Sign up</a> to get started!
                    </p>
                <?php elseif (is_author() || is_admin()): ?>
                    <p>You're logged in as an author. <a href="user_dashboard.php">Write a post</a> in your dashboard!</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Categories -->
        <div class="card mb-4 shadow-lg sidebar-card">
            <div class="card-header heading-for text-white sidebar-header">
                <h5 class="mb-0"><i class="fas fa-tags me-2"></i> Categories</h5>
            </div>
            <div class="card-body sidebar-body">
                <ul class="list-group list-group-flush category-list">
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="end_user.php?category=<?php echo $category['category_id']; ?>"
                                class="text-decoration-none">
                                <i class="fas fa-folder category-icon"></i>
                                <?php echo html_escape($category['category_name']); ?>
                            </a>
                            <span class="badge heading-for rounded-pill post-count">
                                <?php
                                $cat_posts = get_posts(['category_id' => $category['category_id'], 'status' => 'published']);
                                echo count($cat_posts['posts']);
                                ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Popular Categories -->
        <div class="card mb-4 shadow-sm sidebar-card">
            <div class="card-header heading-for text-white sidebar-header">
                <h5 class="mb-0"><i class="fas fa-hashtag me-2"></i> Popular Categories</h5>
            </div>
            <div class="card-body sidebar-body">
                <div class="popular-tags">
                    <?php foreach ($popular_categories as $category): ?>
                        <a href="end_user.php?category=<?php echo $category['category_id']; ?>" class="tag">
                            <?php echo $category['category_name']; ?> (<?php echo $category['post_count']; ?>)
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Newsletter -->
        <div class="card mb-4 shadow-sm sidebar-card">
            <div class="card-header heading-for text-white sidebar-header">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i> Newsletter</h5>
            </div>
            <div class="card-body sidebar-body">
                <p>Subscribe to our newsletter to get the latest updates directly to your inbox.</p>
                <form>
                    <div class="mb-3">
                        <input type="email" class="form-control newsletter-input" placeholder="Your email address">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn newsletter-button">
                            <i class="fas fa-paper-plane me-1"></i> Subscribe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include('components/footer.php');
?>