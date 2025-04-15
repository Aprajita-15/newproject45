<?php
// Basic security check - only allow running from localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('This script can only be run from localhost');
}

require_once '../includes/db.php';

function resetUsersTable() {
    $conn = getDbConnection();
    
    // Disable foreign key checks temporarily
    $conn->query('SET FOREIGN_KEY_CHECKS = 0');
    
    // Drop the users table if it exists
    $conn->query('DROP TABLE IF EXISTS users');
    
    // Create the users table with proper structure
    $createTableSQL = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY username_unique (username),
        UNIQUE KEY email_unique (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createTableSQL)) {
        echo "Users table has been successfully reset.<br>";
        echo "You can now <a href='register.php'>register a new user</a>.";
    } else {
        echo "Error creating users table: " . $conn->error;
    }
    
    // Re-enable foreign key checks
    $conn->query('SET FOREIGN_KEY_CHECKS = 1');
    
    $conn->close();
}

// Execute the reset
resetUsersTable();

// Delete this file after execution for security
@unlink(__FILE__);
?> 