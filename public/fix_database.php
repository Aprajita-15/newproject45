<?php
// Basic security check - only allow running from localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('This script can only be run from localhost');
}

require_once '../includes/db.php';

function fixDatabase() {
    $conn = getDbConnection();
    
    // First, let's update any existing NULL or empty emails with temporary unique values
    $sql = "SELECT id FROM users WHERE email IS NULL OR email = ''";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tempEmail = "temp_" . $row['id'] . "_" . time() . "@temporary.com";
            $updateSql = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("si", $tempEmail, $row['id']);
            $stmt->execute();
            $stmt->close();
        }
        echo "Updated " . $result->num_rows . " users with temporary email addresses.<br>";
    }

    // Now modify the table structure
    $alterQueries = [
        "ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NOT NULL",
        "ALTER TABLE users ADD UNIQUE INDEX email_unique (email)"
    ];

    foreach ($alterQueries as $query) {
        try {
            $conn->query($query);
            echo "Successfully executed: " . htmlspecialchars($query) . "<br>";
        } catch (Exception $e) {
            echo "Error executing query: " . htmlspecialchars($query) . "<br>";
            echo "Error message: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    }

    $conn->close();
    echo "Database structure update completed.<br>";
}

// Run the fix
fixDatabase();

// Delete this file after execution for security
@unlink(__FILE__);
echo "This script has self-deleted for security purposes.<br>";
?> 