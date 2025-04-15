<?php

$db_host = 'localhost:3307';
$db_user = 'root';
$db_pass = '';
$db_name = 'fitness_tracker';

function getDbConnection() {
    global $db_host, $db_user, $db_pass, $db_name;
    
    if (!extension_loaded('mysqli')) {
        die("MySQLi extension is not loaded. Please check your PHP configuration.");
    }
    
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . 
            "\nPlease check:\n" .
            "1. Is MySQL running in XAMPP?\n" .
            "2. Are the credentials correct?");
    }

    // Create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
    $conn->select_db($db_name);

    // Drop existing users table if it has wrong structure
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        $columnResult = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
        if ($columnResult->num_rows === 0) {
            // If email column doesn't exist, drop and recreate the table
            $conn->query("DROP TABLE users");
        }
    }

    // Create users table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTableSQL)) {
        die("Error creating users table: " . $conn->error);
    }
    
    return $conn;
}

function addWorkout($username, $workout) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("INSERT INTO workouts (username, exercise_name, sets, reps, weight, date) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssiii", $username, $workout['exerciseName'], $workout['sets'], $workout['reps'], $workout['weight']);
    
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function getUserWorkouts($username) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT * FROM workouts WHERE username = ? ORDER BY date DESC");
    $stmt->bind_param("s", $username);
    
    $stmt->execute();
    $result = $stmt->get_result();
    $workouts = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $conn->close();
    
    return $workouts;
}

function getRecentWorkouts($username, $limit = 3) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT * FROM workouts WHERE username = ? ORDER BY date DESC LIMIT ?");
    $stmt->bind_param("si", $username, $limit);
    
    $stmt->execute();
    $result = $stmt->get_result();
    $workouts = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $conn->close();
    
    return $workouts;
}

function addProgress($username, $progress) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("INSERT INTO progress (username, weight, body_fat, date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sdd", $username, $progress['weight'], $progress['body_fat']);
    
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function getUserProgress($username) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT * FROM progress WHERE username = ? ORDER BY date DESC");
    $stmt->bind_param("s", $username);
    
    $stmt->execute();
    $result = $stmt->get_result();
    $progress = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $conn->close();
    
    return $progress;
}

function getRecentProgress($username, $limit = 3) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT * FROM progress WHERE username = ? ORDER BY date DESC LIMIT ?");
    $stmt->bind_param("si", $username, $limit);
    
    $stmt->execute();
    $result = $stmt->get_result();
    $progress = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $conn->close();
    
    return $progress;
}
?>