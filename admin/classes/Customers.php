<?php 
session_start();
/**
 * 
 */
class Customers
{
	
	private $con;

	function __construct()
	{
		include_once("Database.php");
		$db = new Database();
		$this->con = $db->connect();
	}

	public function getCustomers(){
		$query = $this->con->query("SELECT `user_id`, `first_name`, `last_name`, `email`, `mobile`, `address1`, `address2` FROM `user_info`");
		$ar = [];
		if (@$query->num_rows > 0) {
			while ($row = $query->fetch_assoc()) {
				$ar[] = $row;
			}
			return ['status'=> 202, 'message'=> $ar];
		}
		return ['status'=> 303, 'message'=> 'no customer data'];
	}


	public function getCustomersOrder(){
		$query = $this->con->query("SELECT o.order_id, o.product_id, o.qty, o.trx_id, o.p_status, o.payment_method, o.receipt_file, o.user_id, p.product_title, p.product_image, u.first_name, u.last_name, u.email FROM orders o JOIN products p ON o.product_id = p.product_id JOIN user_info u ON o.user_id = u.user_id ORDER BY o.order_id DESC");
		$ar = [];
		if (@$query->num_rows > 0) {
			while ($row = $query->fetch_assoc()) {
				$ar[] = $row;
			}
			// Debug: Log the data being returned
			error_log("Orders data: " . print_r($ar, true));
			return ['status'=> 202, 'message'=> $ar];
		}
		return ['status'=> 303, 'message'=> 'no orders yet'];
	}

	public function updateOrderStatus($order_id, $new_status){
		$stmt = $this->con->prepare("UPDATE orders SET p_status = ? WHERE order_id = ?");
		$stmt->bind_param("si", $new_status, $order_id);
		if ($stmt->execute()) {
			return ['status'=> 202, 'message'=> 'Order status updated successfully'];
		}
		return ['status'=> 303, 'message'=> 'Failed to update order status'];
	}
	
	public function addOrder($post_data, $files) {
		// Validate required fields
		if (empty($post_data['customer_id']) || empty($post_data['product_id']) || 
			empty($post_data['quantity']) || empty($post_data['payment_method']) || 
			empty($post_data['order_status'])) {
			return ['status' => 303, 'message' => 'All required fields must be filled'];
		}
		
		$customer_id = $post_data['customer_id'];
		$product_id = $post_data['product_id'];
		$quantity = $post_data['quantity'];
		$payment_method = $post_data['payment_method'];
		$order_status = $post_data['order_status'];
		$transaction_id = !empty($post_data['transaction_id']) ? $post_data['transaction_id'] : 'TXN_' . time() . '_' . rand(1000, 9999);
		$receipt_file = '';
		
		// Handle receipt file upload if payment method is receipt_upload
		if ($payment_method === 'receipt_upload' && isset($files['receipt_file']) && $files['receipt_file']['error'] == 0) {
			$upload_dir = '../uploads/';
			if (!file_exists($upload_dir)) {
				mkdir($upload_dir, 0777, true);
			}
			
			$file_extension = pathinfo($files['receipt_file']['name'], PATHINFO_EXTENSION);
			$receipt_file = 'receipt_' . time() . '_' . $customer_id . '.' . $file_extension;
			$upload_path = $upload_dir . $receipt_file;
			

			if (!move_uploaded_file($files['receipt_file']['tmp_name'], $upload_path)) {
				return ['status' => 303, 'message' => 'Failed to upload receipt file'];
			}
		}
		
		// Insert order into database
		$stmt = $this->con->prepare("INSERT INTO orders (user_id, product_id, qty, trx_id, p_status, payment_method, receipt_file) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("iiissss", $customer_id, $product_id, $quantity, $transaction_id, $order_status, $payment_method, 'uploads/' . $receipt_file);
		
		if ($stmt->execute()) {
			return ['status' => 202, 'message' => 'Order added successfully'];
		} else {
			return ['status' => 303, 'message' => 'Failed to add order: ' . $stmt->error];
		}
	}
	

}


/*$c = new Customers();
echo "<pre>";
print_r($c->getCustomers());
exit();*/

if (isset($_POST["GET_CUSTOMERS"])) {
	if (isset($_SESSION['admin_id'])) {
		$c = new Customers();
		echo json_encode($c->getCustomers());
		exit();
	}
}

if (isset($_POST["GET_CUSTOMER_ORDERS"])) {
	if (isset($_SESSION['admin_id'])) {
		$c = new Customers();
		echo json_encode($c->getCustomersOrder());
		exit();
	}
}

if (isset($_POST["UPDATE_ORDER_STATUS"])) {
	if (isset($_SESSION['admin_id'])) {
		$c = new Customers();
		$order_id = $_POST['order_id'];
		$new_status = $_POST['new_status'];
		echo json_encode($c->updateOrderStatus($order_id, $new_status));
		exit();
	}
}

// Handle Add Order request
if (isset($_POST["add_order"])) {
	if (isset($_SESSION['admin_id'])) {
		$c = new Customers();
		echo json_encode($c->addOrder($_POST, $_FILES));
		exit();
	}
}


?>