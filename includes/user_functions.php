<?php
// User related functions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get all users
function get_users($options = [])
{
    $where_clauses = ["access = 1"]; // Only active users
    $params = [];

    // Apply filters
    if (isset($options['role'])) {
        $where_clauses[] = "user_role = ?";
        $params[] = $options['role'];
    }

    if (isset($options['search'])) {
        $where_clauses[] = "(user_name LIKE ? OR user_email LIKE ?)";
        $search_term = '%' . $options['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
    }

    // Build the WHERE clause
    $where_clause = implode(' AND ', $where_clauses);

    // Pagination
    $limit = isset($options['limit']) ? (int) $options['limit'] : 100;
    $offset = isset($options['offset']) ? (int) $options['offset'] : 0;

    // Get users
    $query = "SELECT 
                user_id, 
                user_name, 
                user_email,
                user_role,
                create_time,
                access
            FROM user_details 
            WHERE $where_clause
            ORDER BY user_name
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    return db_fetch_all($query, $params);
}

// Get a single user
function get_user($user_id)
{
    return db_fetch_row(
        "SELECT 
            user_id, 
            user_name, 
            user_email,
            user_role,
            create_time,
            access,
            user_password
        FROM user_details 
        WHERE user_id = ? AND access = 1",
        [$user_id]
    );
}

// Create a new user (admin function)
function create_user($username, $email, $password, $role = 'end_user')
{
    // Check if username exists
    if (db_count("SELECT * FROM user_details WHERE user_name = ?", [$username]) > 0) {
        return [
            'success' => false,
            'message' => 'Username already exists'
        ];
    }

    // Check if email exists
    if (db_count("SELECT * FROM user_details WHERE user_email = ?", [$email]) > 0) {
        return [
            'success' => false,
            'message' => 'Email already exists'
        ];
    }

    // Insert user
    db_query(
        "INSERT INTO user_details (user_name, user_email, user_password, user_role, access) 
         VALUES (?, ?, ?, ?, 1)",
        [$username, $email, $password, $role]
    );

    $user_id = db_last_insert_id();

    return [
        'success' => true,
        'user_id' => $user_id,
        'message' => 'User created successfully'
    ];
}

// Update a user (admin function)
function update_user($user_id, $username, $email, $role)
{
    // Check if username already exists (excluding current user)
    if (
        db_count(
            "SELECT * FROM user_details WHERE user_name = ? AND user_id != ?",
            [$username, $user_id]
        ) > 0
    ) {
        return [
            'success' => false,
            'message' => 'Username already exists'
        ];
    }

    // Check if email already exists (excluding current user)
    if (
        db_count(
            "SELECT * FROM user_details WHERE user_email = ? AND user_id != ?",
            [$email, $user_id]
        ) > 0
    ) {
        return [
            'success' => false,
            'message' => 'Email already exists'
        ];
    }

    // Update user
    db_query(
        "UPDATE user_details 
         SET user_name = ?, user_email = ?, user_role = ? 
         WHERE user_id = ?",
        [$username, $email, $role, $user_id]
    );

    return [
        'success' => true,
        'message' => 'User updated successfully'
    ];
}

// Delete a user (soft delete)
function delete_user($user_id)
{
    // Check if user has posts
    $post_count = db_fetch_row(
        "SELECT COUNT(*) as post_count FROM posts_details WHERE r_author_id = ? AND status_del = 1",
        [$user_id]
    )['post_count'];

    if ($post_count > 0) {
        return [
            'success' => false,
            'message' => "Cannot delete: This user has $post_count post(s). Please reassign or delete these posts first."
        ];
    }

    // Soft delete - set access to 0
    db_query(
        "UPDATE user_details SET access = 0 WHERE user_id = ?",
        [$user_id]
    );

    return [
        'success' => true,
        'message' => 'User deleted successfully'
    ];
}

// Get user statistics
function get_user_stats($user_id)
{
    // Total posts
    $total_posts = db_fetch_row(
        "SELECT COUNT(*) as total FROM posts_details WHERE r_author_id = ? AND status_del = 1",
        [$user_id]
    )['total'];

    // Published posts
    $published_posts = db_fetch_row(
        "SELECT COUNT(*) as published FROM posts_details 
         WHERE r_author_id = ? AND post_status = 'published' AND status_del = 1",
        [$user_id]
    )['published'];

    // Draft posts
    $draft_posts = db_fetch_row(
        "SELECT COUNT(*) as drafts FROM posts_details 
         WHERE r_author_id = ? AND post_status = 'draft' AND status_del = 1",
        [$user_id]
    )['drafts'];

    return [
        'total_posts' => $total_posts,
        'published_posts' => $published_posts,
        'draft_posts' => $draft_posts
    ];
}
?>