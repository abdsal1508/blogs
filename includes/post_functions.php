<?php
// Post related functions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get a single post by ID
function get_post($post_id)
{
    $post = db_fetch_row(
        "SELECT 
            p.post_id, 
            p.title, 
            p.content,
            p.image_link,
            p.r_author_id,
            p.r_category_id,
            p.post_status,
            IFNULL(u.user_name, 'Unknown') AS author_name,
            IFNULL(c.category_name, 'Uncategorized') AS category_name,
            p.create_time,
            p.update_time
        FROM posts_details p 
        LEFT JOIN user_details u ON p.r_author_id = u.user_id 
        LEFT JOIN categories c ON p.r_category_id = c.category_id
        WHERE p.post_id = ? AND p.status_del = 1",
        [$post_id]
    );

    return $post;
}

// Get posts with filtering options
function get_posts($options = [])
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

    if (isset($options['search'])) {
        $where_clauses[] = "(p.title LIKE ? OR p.content LIKE ?)";
        $search_term = '%' . $options['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
    }

    if (isset($options['exclude'])) {
        $where_clauses[] = "p.post_id != ?";
        $params[] = $options['exclude'];
    }

    // Build the WHERE clause
    $where_clause = implode(' AND ', $where_clauses);

    // Pagination
    $limit = isset($options['limit']) ? (int) $options['limit'] : ITEMS_PER_PAGE;
    $offset = isset($options['offset']) ? (int) $options['offset'] : 0;

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM posts_details p WHERE $where_clause";
    $total = db_fetch_row($count_query, $params)['total'];

    // Get posts
    $query = "SELECT 
                p.post_id, 
                p.title, 
                p.content,
                p.image_link,
                p.r_author_id,
                p.r_category_id,
                p.post_status,
                IFNULL(u.user_name, 'Unknown') AS author_name,
                IFNULL(c.category_name, 'Uncategorized') AS category_name,
                p.create_time,
                p.update_time
            FROM posts_details p 
            LEFT JOIN user_details u ON p.r_author_id = u.user_id 
            LEFT JOIN categories c ON p.r_category_id = c.category_id
            WHERE $where_clause
            ORDER BY p.create_time DESC
            LIMIT ? OFFSET ?";

    $params[] = (int) $limit;
    $params[] = (int) $offset;

    $posts = db_fetch_all($query, $params);

    return [
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
        'posts' => $posts
    ];
}

// Create a new post
function create_post($data, $files = [])
{
    $title = $data['title'] ?? '';
    $content = $data['content'] ?? '';
    $category_id = $data['category'] ?? null;
    $post_status = $data['post_status'] ?? 'draft';
    $author_id = $_SESSION['user_id'];

    // Handle image upload
    $image_link = null;
    if (isset($files['image_upload']) && $files['image_upload']['error'] == 0) {
        $image_link = upload_image($files['image_upload']);
    }

    // Debug information
    error_log("Creating post: Title=$title, Category=$category_id, Status=$post_status, Author=$author_id");

    try {
        // Insert post
        db_query(
            "INSERT INTO posts_details (title, content, r_author_id, r_category_id, image_link, post_status, status_del) 
             VALUES (?, ?, ?, ?, ?, ?, 1)",
            [$title, $content, $author_id, $category_id, $image_link, $post_status]
        );

        $post_id = db_last_insert_id();

        error_log("Post created successfully with ID: $post_id");

        return [
            'success' => true,
            'post_id' => $post_id,
            'message' => 'Post created successfully'
        ];
    } catch (Exception $e) {
        error_log("Error creating post: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error creating post: ' . $e->getMessage()
        ];
    }
}

// Update an existing post
function update_post($data, $files = [])
{
    $post_id = $data['post_id'] ?? 0;
    $title = $data['title'] ?? '';
    $content = $data['content'] ?? '';
    $category_id = $data['category'] ?? null;
    $post_status = $data['post_status'] ?? 'draft';
    $existing_image = $data['existing_image'] ?? null;
    $user_id = $_SESSION['user_id'];

    // Debug information
    error_log("Updating post: ID=$post_id, Title=$title, Category=$category_id, Status=$post_status, User=$user_id");

    // Check if user can edit this post
    if (!can_edit_post($post_id, $user_id)) {
        return [
            'success' => false,
            'message' => 'You do not have permission to edit this post'
        ];
    }

    // Handle image upload
    $image_link = $existing_image;
    if (isset($files['image_upload']) && $files['image_upload']['error'] == 0) {
        $image_link = upload_image($files['image_upload']);
    }

    try {
        // Update post
        db_query(
            "UPDATE posts_details 
             SET title = ?, 
                 content = ?, 
                 r_category_id = ?, 
                 image_link = ?, 
                 post_status = ?
             WHERE post_id = ?",
            [$title, $content, $category_id, $image_link, $post_status, $post_id]
        );

        error_log("Post updated successfully: ID=$post_id");

        return [
            'success' => true,
            'message' => 'Post updated successfully'
        ];
    } catch (Exception $e) {
        error_log("Error updating post: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error updating post: ' . $e->getMessage()
        ];
    }
}

// Delete a post (soft delete)
function delete_post($post_id)
{
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];

    // Check if user can delete this post
    if (!can_edit_post($post_id, $user_id)) {
        return [
            'success' => false,
            'message' => 'You do not have permission to delete this post'
        ];
    }

    // Soft delete
    db_query(
        "UPDATE posts_details SET status_del = 0 WHERE post_id = ?",
        [$post_id]
    );

    return [
        'success' => true,
        'message' => 'Post deleted successfully'
    ];
}

// Get featured post (most recent published post)
function get_featured_post()
{
    return db_fetch_row(
        "SELECT 
            p.post_id, 
            p.title, 
            p.content,
            p.image_link,
            p.r_author_id,
            p.r_category_id,
            p.post_status,
            IFNULL(u.user_name, 'Unknown') AS author_name,
            IFNULL(c.category_name, 'Uncategorized') AS category_name,
            p.create_time,
            p.update_time
        FROM posts_details p 
        LEFT JOIN user_details u ON p.r_author_id = u.user_id 
        LEFT JOIN categories c ON p.r_category_id = c.category_id
        WHERE p.status_del = 1 
          AND p.post_status = 'published'
        ORDER BY p.create_time DESC
        LIMIT 1"
    );
}

?>