<?php 
session_start();
/**
 * 
 */
class Credentials
{
	
	private $con;

	function __construct()
	{
		// Azure-compatible path resolution
		$db_path = __DIR__ . "/Database.php";
		if (file_exists($db_path)) {
			include_once($db_path);
		} else {
			// Fallback for Azure
			include_once("Database.php");
		}
		
		$db = new Database();
		$this->con = $db->connect();
		
		// Check connection for Azure
		if ($this->con->connect_error) {
			// Log error for Azure debugging
			error_log("Database connection failed: " . $this->con->connect_error);
		}
	}


	public function createAdminAccount($name, $email, $password){
		// Check if connection is valid
		if (!$this->con || $this->con->connect_error) {
			return ['status'=> 500, 'message'=> 'Database connection failed'];
		}
		
		$q = $this->con->query("SELECT email FROM admin WHERE email = '$email'");
		if (!$q) {
			return ['status'=> 500, 'message'=> 'Query failed: ' . $this->con->error];
		}
		
		if ($q->num_rows > 0) {
			return ['status'=> 303, 'message'=> 'Email already exists'];
		}else{
			$password = password_hash($password, PASSWORD_BCRYPT, ["COST"=> 8]);
			$q = $this->con->query("INSERT INTO `admin`(`name`, `email`, `password`, `is_active`) VALUES ('$name','$email','$password','0')");
			if (!$q) {
				return ['status'=> 500, 'message'=> 'Insert failed: ' . $this->con->error];
			}
			if ($q) {
				return ['status'=> 202, 'message'=> 'Admin Created Successfully'];
			}
		}
	}

	public function loginAdmin($email, $password){
		// Check if connection is valid
		if (!$this->con || $this->con->connect_error) {
			return ['status'=> 500, 'message'=> 'Database connection failed'];
		}
		
		$q = $this->con->query("SELECT * FROM admin WHERE email = '$email' LIMIT 1");
		if (!$q) {
			return ['status'=> 500, 'message'=> 'Query failed: ' . $this->con->error];
		}
		
		if ($q->num_rows > 0) {
			$row = $q->fetch_assoc();
			if (password_verify($password, $row['password'])) {
				$_SESSION['admin_name'] = $row['name'];
				$_SESSION['admin_id'] = $row['id'];
				return ['status'=> 202, 'message'=> 'Login Successful'];
			}else{
				return ['status'=> 303, 'message'=> 'Login Fail'];
			}
		}else{
			return ['status'=> 303, 'message'=> 'Account not created yet with this email'];
		}
	}

}

//$c = new Credentials();
//$c->createAdminAccount("Rizwan", "rizwan@gmail.com", "12345");

//PRINT_R($c->loginAdmin("rizwan@gmail.com", "12345"));

if (isset($_POST['admin_register'])) {
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	extract($_POST);
	if (!empty($name) && !empty($email) && !empty($password) && !empty($cpassword)) {
		if ($password == $cpassword) {
			$c = new Credentials();
			$result = $c->createAdminAccount($name, $email, $password);
			echo json_encode($result);
			exit();
		}else{
			echo json_encode(['status'=> 303, 'message'=> 'Password mismatch']);
			exit();
		}
	}else{
		echo json_encode(['status'=> 303, 'message'=> 'Empty fields']);
		exit();
	}
}

if (isset($_POST['admin_login'])) {
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	extract($_POST);
	if (!empty($email) && !empty($password)) {
		$c = new Credentials();
		$result = $c->loginAdmin($email, $password);
		echo json_encode($result);
		exit();
	}else{
		echo json_encode(['status'=> 303, 'message'=> 'Empty fields']);
		exit();
	}
}


?>