<?php
// Category related functions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get all categories
function get_categories($with_post_count = false)
{
    if ($with_post_count) {
        return db_fetch_all(
            "SELECT 
                c.category_id, 
                c.category_name, 
                c.category_description,
                COUNT(p.post_id) as post_count
            FROM categories c
            LEFT JOIN posts_details p ON c.category_id = p.r_category_id AND p.post_status = 'published' AND p.status_del = 1
            WHERE c.status_del = 1
            GROUP BY c.category_id
            ORDER BY c.category_name"
        );
    } else {
        return db_fetch_all(
            "SELECT 
                category_id, 
                category_name, 
                category_description
            FROM categories
            WHERE status_del = 1
            ORDER BY category_name"
        );
    }
}

// Get a single category
function get_category($category_id)
{
    return db_fetch_row(
        "SELECT * FROM categories WHERE category_id = ? AND status_del = 1",
        [$category_id]
    );
}

// Create a new category
function create_category($name, $description)
{
    // Check if category already exists
    if (db_count("SELECT * FROM categories WHERE category_name = ?", [$name]) > 0) {
        return [
            'success' => false,
            'message' => 'Category name already exists'
        ];
    }

    // Insert category
    db_query(
        "INSERT INTO categories (category_name, category_description, status_del) 
         VALUES (?, ?, 1)",
        [$name, $description]
    );

    $category_id = db_last_insert_id();

    return [
        'success' => true,
        'category_id' => $category_id,
        'message' => 'Category created successfully'
    ];
}

// Update a category
function update_category($category_id, $name, $description)
{
    // Check if category name already exists (excluding current category)
    if (
        db_count(
            "SELECT * FROM categories WHERE category_name = ? AND category_id != ?",
            [$name, $category_id]
        ) > 0
    ) {
        return [
            'success' => false,
            'message' => 'Category name already exists'
        ];
    }

    // Update category
    db_query(
        "UPDATE categories 
         SET category_name = ?, category_description = ?
         WHERE category_id = ?",
        [$name, $description, $category_id]
    );

    return [
        'success' => true,
        'message' => 'Category updated successfully'
    ];
}

// Delete a category (soft delete)
function delete_category($category_id)
{
    // Check if category is used in any posts
    $post_count = db_fetch_row(
        "SELECT COUNT(*) as post_count FROM posts_details WHERE r_category_id = ?",
        [$category_id]
    )['post_count'];

    if ($post_count > 0) {
        return [
            'success' => false,
            'message' => "Cannot delete: This category is used in $post_count post(s)"
        ];
    }

    // Soft delete
    db_query(
        "UPDATE categories SET status_del = 0 WHERE category_id = ?",
        [$category_id]
    );

    return [
        'success' => true,
        'message' => 'Category deleted successfully'
    ];
}

// Get popular categories (top 5 by post count)
function get_popular_categories($limit = 5)
{
    $categories = get_categories(true);

    // Sort by post count
    usort($categories, function ($a, $b) {
        return $b['post_count'] - $a['post_count'];
    });

    // Return top categories
    return array_slice($categories, 0, $limit);
}
?>