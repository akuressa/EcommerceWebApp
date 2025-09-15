<?php

/**
 * 
 */
class Database
{
	
	private $con;
	public function connect(){
		// Check if we're on Azure
		$is_azure = true;
		
		if ($is_azure) {
			// Try Azure configuration first
			$azure_config_path = __DIR__ . "/../../azure_db_config.php";
			if (file_exists($azure_config_path)) {
				include_once($azure_config_path);
				if (function_exists('getAzureConnection')) {
					$con = getAzureConnection();
					if ($con) {
						$this->con = $con;
						return $this->con;
					}
				}
			}
			
			// Fallback Azure settings (use your actual Azure database details)
			$host = "localhost"; // Change this to your Azure MySQL server
			$username = "root";  // Change this to your Azure MySQL username
			$password = "";      // Change this to your Azure MySQL password
			$database = "ecommerceapp";
		} else {
			// Local settings
			$host = "localhost";
			$username = "root";
			$password = "";
			$database = "ecommerceapp";
		}
		
		$this->con = new Mysqli($host, $username, $password, $database);
		return $this->con;
	}
}

?>