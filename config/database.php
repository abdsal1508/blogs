<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '0000');
define('DB_NAME', 'blog_management_system'); // Changed back to 'blog_management_system'

// Site configuration
define('SITE_NAME', 'Simply Blogs');
define('SITE_URL', 'http://localhost/blog');
define('UPLOAD_DIR', 'assets/uploads/posts/');
define('ITEMS_PER_PAGE', 10);

// Database connection handler
function db_connect()
{
    static $conn = null;

    if ($conn === null) {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $conn;
}

// Helper function to execute a query and return results
function db_query($query, $params = [])
{
    $conn = db_connect();
    $stmt = $conn->prepare($query);

    foreach ($params as $key => $value) {
        if (is_int($key)) {
            $paramIndex = $key + 1;
        } else {
            $paramIndex = $key;
        }

        // Bind integer values properly for LIMIT and OFFSET
        if (is_int($value)) {
            $stmt->bindValue($paramIndex, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($paramIndex, $value);
        }
    }

    $stmt->execute();
    return $stmt;
}

// Helper function to fetch a single row
function db_fetch_row($query, $params = [])
{
    $stmt = db_query($query, $params);
    return $stmt->fetch();
}

// Helper function to fetch all rows
function db_fetch_all($query, $params = [])
{
    $stmt = db_query($query, $params);
    return $stmt->fetchAll();
}

// Helper function to get last insert ID
function db_last_insert_id()
{
    return db_connect()->lastInsertId();
}

// Helper function to count rows
function db_count($query, $params = [])
{
    $stmt = db_query($query, $params);
    return $stmt->rowCount();
}
?>