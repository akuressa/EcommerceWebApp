<?php
/**
 * Azure Database Configuration
 * Update these values for your Azure deployment
 */

// Azure MySQL Database Configuration
// Replace these with your actual Azure database details
$azure_db_config = [
    'host' => 'dbyeshicraft.mysql.database.azure.com',        // Change to your Azure MySQL server
    'username' => 'yeshicraft',         // Change to your Azure MySQL username  
    'password' => 'Yeshicraft@123',             // Change to your Azure MySQL password
    'database' => 'ecommerceapp', // Your database name
    'port' => 3306                // MySQL port (usually 3306)
];

// Function to get Azure database connection
function getAzureConnection() {
    global $azure_db_config;
    
    // Check if config array exists and has required keys
    if (!isset($azure_db_config) || !is_array($azure_db_config)) {
        error_log("Azure database configuration not found or invalid");
        return false;
    }
    
    // Check if all required keys exist
    $required_keys = ['host', 'username', 'password', 'database', 'port'];
    foreach ($required_keys as $key) {
        if (!isset($azure_db_config[$key])) {
            error_log("Missing required database configuration key: " . $key);
            return false;
        }
    }
    
    $con = new mysqli(
        $azure_db_config['host'],
        $azure_db_config['username'],
        $azure_db_config['password'],
        $azure_db_config['database'],
        $azure_db_config['port']
    );
    
    if ($con->connect_error) {
        error_log("Azure database connection failed: " . $con->connect_error);
        return false;
    }
    
    return $con;
}
?>
