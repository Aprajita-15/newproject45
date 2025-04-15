<?php
require_once 'includes/db.php';

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
    }

    // Now modify the table structure
    $alterQueries = [
        "ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NOT NULL",
        "ALTER TABLE users ADD UNIQUE INDEX email_unique (email)"
    ];

    foreach ($alterQueries as $query) {
        try {
            $conn->query($query);
            echo "Successfully executed: " . $query . "\n";
        } catch (Exception $e) {
            echo "Error executing query: " . $query . "\n";
            echo "Error message: " . $e->getMessage() . "\n";
        }
    }

    $conn->close();
    echo "Database structure update completed.\n";
}

// Run the fix
fixDatabase();
?> 