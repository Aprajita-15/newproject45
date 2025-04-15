<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

require_once '../includes/db.php';

// Check and fix database structure if needed
function fixDatabaseStructure($conn) {
    // First, remove existing constraints on email if they exist
    try {
        $conn->query("ALTER TABLE users DROP INDEX email_unique");
    } catch (Exception $e) {
        // Ignore if index doesn't exist
    }
    
    try {
        $conn->query("ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NULL");
    } catch (Exception $e) {
        // Ignore if modification fails
    }
    
    // Update any NULL or empty emails with unique temporary values
    $result = $conn->query("SELECT id FROM users WHERE email IS NULL OR email = ''");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tempEmail = "temp_" . $row['id'] . "_" . time() . "@temporary.com";
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $tempEmail, $row['id']);
            $stmt->execute();
        }
    }
    
    // Now add the constraints back
    $conn->query("ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NOT NULL");
    $conn->query("ALTER TABLE users ADD UNIQUE INDEX email_unique (email)");
}

// Initialize database connection and fix structure
$conn = getDbConnection();
fixDatabaseStructure($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $error = '';

    // Validate all fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $conn = getDbConnection();
        
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            if ($existing['username'] === $username) {
                $error = 'Username already exists';
            } else {
                $error = 'Email already exists';
            }
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $conn->insert_id;
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $insert_stmt->close();
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Virtual Personal Trainer</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php if (isset($_SESSION['username'])): ?>
    <?php include 'includes/navigation.php'; ?>
  <?php endif; ?>
  <main>
    <div class="container <?php echo isset($_SESSION['username']) ? '' : 'login-container'; ?>">
      <?php if (!isset($_SESSION['username'])): ?>
        <h1>Create an Account</h1>
        <p>Join Virtual Personal Trainer and start your fitness journey today.</p>
        
        <?php if (isset($error)): ?>
          <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'not_registered'): ?>
          <div class="error-message">
            You are not registered. Please create an account to continue.
          </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="login-form">
          <div class="form-group">
            <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                   placeholder="Username" required minlength="3" maxlength="50">
          </div>
          <div class="form-group">
            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                   placeholder="Email" required>
          </div>
          <div class="form-group">
            <input type="password" name="password" placeholder="Password" required minlength="6">
          </div>
          <div class="form-group">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="6">
          </div>
          <button type="submit">Register</button>
          <p class="register-link">Already have an account? <a href="home.php">Login here</a></p>
        </form>
      <?php else: ?>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>You are already logged in. <a href="dashboard.php">Go to Dashboard</a></p>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>