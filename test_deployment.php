<?php
// Test file to verify deployment and database connection
echo "<h1>Deployment Test</h1>";

// Test 1: Check if action.php exists
if (file_exists('action.php')) {
    echo "<p style='color: green;'>✓ action.php file exists</p>";
} else {
    echo "<p style='color: red;'>✗ action.php file NOT found</p>";
}

// Test 2: Check database connection
require "config/constants.php";

$servername = HOST;
$username = USER;
$password = PASSWORD;
$db = DATABASE_NAME;

$con = mysqli_connect($servername, $username, $password, $db);

if (!$con) {
    echo "<p style='color: red;'>✗ Database connection failed: " . mysqli_connect_error() . "</p>";
    echo "<p>Connection details: Host=$servername, User=$username, DB=$db</p>";
} else {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test 3: Check if tables exist
    $tables = ['products', 'cart', 'categories', 'brands'];
    foreach ($tables as $table) {
        $result = mysqli_query($con, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($result) > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' NOT found</p>";
        }
    }
}

// Test 4: Check file permissions
$files_to_check = ['action.php', 'db.php', 'config/constants.php'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "<p style='color: green;'>✓ $file is readable</p>";
        } else {
            echo "<p style='color: red;'>✗ $file is NOT readable</p>";
        }
    }
}

echo "<hr>";
echo "<p><strong>Environment Info:</strong></p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current Directory: " . getcwd() . "</p>";

// List files in current directory
echo "<p><strong>Files in current directory:</strong></p>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "<p>- $file</p>";
    }
}
?>
