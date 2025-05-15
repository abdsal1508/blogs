<?php
// Include database configuration
require_once 'config/database.php';

// Function to get all tables
function getTables()
{
    $query = "SHOW TABLES";
    return db_fetch_all($query);
}

// Function to get create table statement
function getCreateTableStatement($table)
{
    $query = "SHOW CREATE TABLE `$table`";
    $result = db_fetch_row($query);
    return $result['Create Table'];
}

// Function to get table data as INSERT statements
function getTableData($table)
{
    $query = "SELECT * FROM `$table`";
    $rows = db_fetch_all($query);

    $inserts = [];
    foreach ($rows as $row) {
        $columns = array_keys($row);
        $values = array_map(function ($value) {
            if ($value === null) {
                return "NULL";
            }
            return "'" . addslashes($value) . "'";
        }, array_values($row));

        $inserts[] = "INSERT INTO `$table` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $values) . ");";
    }

    return $inserts;
}

// Start building the SQL file
$sql = "-- Blog Management System Database Schema\n";
$sql .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";

// Add database creation
$sql .= "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`;\n";
$sql .= "USE `" . DB_NAME . "`;\n\n";

// Get all tables
$tables = getTables();
$tableNames = [];
foreach ($tables as $table) {
    $tableName = array_values($table)[0];
    $tableNames[] = $tableName;
}

// Add DROP TABLE statements
$sql .= "-- Drop tables if they exist\n";
foreach (array_reverse($tableNames) as $tableName) {
    $sql .= "DROP TABLE IF EXISTS `$tableName`;\n";
}
$sql .= "\n";

// Add CREATE TABLE statements
$sql .= "-- Create tables\n";
foreach ($tableNames as $tableName) {
    $createStatement = getCreateTableStatement($tableName);
    $sql .= $createStatement . ";\n\n";
}

// Add INSERT statements for essential data
$sql .= "-- Insert essential data\n";
$essentialTables = ['categories', 'roles', 'users']; // Add your essential tables here
foreach ($essentialTables as $tableName) {
    if (in_array($tableName, $tableNames)) {
        $inserts = getTableData($tableName);
        if (!empty($inserts)) {
            $sql .= "-- Data for table `$tableName`\n";
            $sql .= implode("\n", $inserts) . "\n\n";
        }
    }
}

// Write to file
file_put_contents('database_schema.sql', $sql);
echo "Schema exported successfully to database_schema.sql\n";
?>