<?php
echo "<h2>XAMPP Setup Verification</h2>";

// Check if we're in the correct directory
echo "<h3>Current Directory Structure:</h3>";
echo "<pre>";
echo "Current script location: " . __FILE__ . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "</pre>";

// Check if Apache is running on the correct port
echo "<h3>Server Information:</h3>";
echo "<pre>";
echo "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Server port: " . $_SERVER['SERVER_PORT'] . "\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "</pre>";

// Check MySQL connection
require_once 'includes/db.php';
echo "<h3>Database Connection:</h3>";
try {
    $conn = getDbConnection();
    echo "<pre>Successfully connected to MySQL on {$db_host}</pre>";
    $conn->close();
} catch (Exception $e) {
    echo "<pre>Error connecting to MySQL: " . $e->getMessage() . "</pre>";
}

// Display directory structure
echo "<h3>Project Files:</h3>";
echo "<pre>";
function listFiles($dir, $indent = '') {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            echo $indent . $file . "\n";
            if (is_dir($dir . '/' . $file)) {
                listFiles($dir . '/' . $file, $indent . '  ');
            }
        }
    }
}
listFiles('.');
echo "</pre>";

// Check if important files exist
echo "<h3>Required Files Check:</h3>";
echo "<pre>";
$required_files = [
    'includes/db.php',
    'public/register.php',
    'public/reset_users_table.php'
];

foreach ($required_files as $file) {
    echo "Checking $file: " . (file_exists($file) ? "EXISTS" : "MISSING") . "\n";
}
echo "</pre>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Make sure all the required files exist in the correct locations</li>";
echo "<li>Verify that Apache and MySQL are running in XAMPP</li>";
echo "<li>Try accessing the reset script at: <a href='/Tani/verify_setup.php'>http://localhost/Tani/verify_setup.php</a></li>";
echo "</ol>";
?> 