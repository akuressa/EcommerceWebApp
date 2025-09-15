<?php
/**
 * Simple test to check Azure database connection
 */

echo "<h1>Azure Database Connection Test</h1>";

// Test 1: Check if azure_db_config.php exists
echo "<h2>1. Configuration File Test</h2>";
$config_path = __DIR__ . "/azure_db_config.php";
if (file_exists($config_path)) {
    echo "✓ azure_db_config.php found<br>";
    include_once($config_path);
    
    if (isset($azure_db_config)) {
        echo "✓ Configuration array loaded<br>";
        echo "Host: " . $azure_db_config['host'] . "<br>";
        echo "Username: " . $azure_db_config['username'] . "<br>";
        echo "Database: " . $azure_db_config['database'] . "<br>";
        echo "Port: " . $azure_db_config['port'] . "<br>";
    } else {
        echo "✗ Configuration array not found<br>";
    }
} else {
    echo "✗ azure_db_config.php not found<br>";
}

// Test 2: Test database connection
echo "<h2>2. Database Connection Test</h2>";
if (function_exists('getAzureConnection')) {
    $con = getAzureConnection();
    if ($con) {
        echo "✓ Database connection successful<br>";
        
        // Test a simple query
        $result = $con->query("SELECT 1 as test");
        if ($result) {
            echo "✓ Database query test successful<br>";
        } else {
            echo "✗ Database query test failed: " . $con->error . "<br>";
        }
    } else {
        echo "✗ Database connection failed<br>";
    }
} else {
    echo "✗ getAzureConnection function not available<br>";
}

// Test 3: Test Credentials class
echo "<h2>3. Credentials Class Test</h2>";
try {
    include_once(__DIR__ . '/admin/classes/Credentials.php');
    $credentials = new Credentials();
    echo "✓ Credentials class instantiated successfully<br>";
    
    // Test login method
    $result = $credentials->loginAdmin("test@test.com", "test");
    echo "✓ loginAdmin method executed (result: " . json_encode($result) . ")<br>";
} catch (Exception $e) {
    echo "✗ Credentials class error: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If you see any ✗ marks, those need to be fixed before deploying to Azure.</p>";
?>
