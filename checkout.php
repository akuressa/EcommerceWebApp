<?php
session_start();
$ip_add = getenv("REMOTE_ADDR");
require "config/constants.php";
include "db.php";

// Check if user is logged in
// Debug: Check session status
if(!isset($_SESSION["uid"])){
	// Display simple message and redirect
	echo '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Login Required - Ecommerce</title>
	<link rel="stylesheet" href="css/bootstrap.min.css"/>
	<script src="js/jquery2.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<link rel="stylesheet" type="text/css" href="style.css"/>
	<style>
		body {
			background: #f8f9fa;
			font-family: Arial, sans-serif;
		}
		.message-container {
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background: white;
			padding: 40px;
			border-radius: 10px;
			box-shadow: 0 4px 20px rgba(0,0,0,0.1);
			text-align: center;
			max-width: 400px;
			width: 90%;
		}
		.message-icon {
			font-size: 48px;
			color: #dc3545;
			margin-bottom: 20px;
		}
		.message-text {
			font-size: 18px;
			color: #333;
			margin-bottom: 20px;
			font-weight: 500;
		}
		.redirect-text {
			color: #666;
			font-size: 14px;
		}
		.countdown {
			color: #007bff;
			font-weight: bold;
		}
	</style>
</head>
<body>
	<div class="message-container">
		<div class="message-icon">
			<span class="glyphicon glyphicon-exclamation-sign"></span>
		</div>
		<div class="message-text">
			Please login before making an order.
		</div>
		<div class="redirect-text">
			Redirecting to home page in <span class="countdown" id="countdown">3</span> seconds...
		</div>
	</div>
	
	<script>
		// Countdown timer
		var countdown = 3;
		var timer = setInterval(function() {
			countdown--;
			document.getElementById("countdown").textContent = countdown;
			if (countdown <= 0) {
				clearInterval(timer);
				window.location.href = "index.php";
			}
		}, 1000);
	</script>
</body>
</html>';
	exit();
}

// Process Payment
if (isset($_POST["processPayment"])) {
	// Debug: Log every time this is called
	$debug_file = 'debug_orders.txt';
	$debug_time = date('Y-m-d H:i:s');
	$debug_user = isset($_SESSION["uid"]) ? $_SESSION["uid"] : 'not_set';
	file_put_contents($debug_file, "[$debug_time] processPayment called for user: $debug_user\n", FILE_APPEND);
	
	if (isset($_SESSION['processing_order']) && $_SESSION['processing_order'] === true) {
		file_put_contents($debug_file, "[$debug_time] BLOCKED: Order already being processed\n", FILE_APPEND);
		header("Location: index.php?order_success=1&duplicate=1");
		exit();
	}
	
	// Set processing flag
	$_SESSION['processing_order'] = true;
	file_put_contents($debug_file, "[$debug_time] Set processing flag for user: $debug_user\n", FILE_APPEND);
	
	if (isset($_SESSION["uid"])) {
		$user_id = $_SESSION["uid"];
		$payment_method = $_POST["payment_method"];
		$receipt_file = null;
		$bank_name = null;
		$transaction_id = null;
		$card_details = null;
		
		// Handle different payment methods
		if ($payment_method == "receipt_upload") {
			// Handle file upload for receipt payments
			if (isset($_FILES["receipt_file"]) && !empty($_FILES["receipt_file"]["name"])) {
				$upload_dir = "uploads/";
				$file_extension = pathinfo($_FILES["receipt_file"]["name"], PATHINFO_EXTENSION);
				$receipt_file = "receipt_" . time() . "_" . $user_id . "." . $file_extension;
				$upload_path = $upload_dir . $receipt_file;
				
				if (!file_exists($upload_dir)) {
					mkdir($upload_dir, 0777, true);
				}
				
				if (move_uploaded_file($_FILES["receipt_file"]["tmp_name"], $upload_path)) {
					$receipt_file = $upload_path;
				}
			}
		} elseif ($payment_method == "card_payment") {
			// Handle card payment details
			$card_number = isset($_POST["card_number"]) ? $_POST["card_number"] : "";
			$card_holder = isset($_POST["card_holder"]) ? $_POST["card_holder"] : "";
			$expiry_month = isset($_POST["expiry_month"]) ? $_POST["expiry_month"] : "";
			$expiry_year = isset($_POST["expiry_year"]) ? $_POST["expiry_year"] : "";
			$cvv = isset($_POST["cvv"]) ? $_POST["cvv"] : "";
			
			// Store card details (in real implementation, this should be encrypted)
			$card_details = json_encode(array(
				'card_number' => substr($card_number, -4), // Store only last 4 digits
				'card_holder' => $card_holder,
				'expiry' => $expiry_month . '/' . $expiry_year,
				'cvv' => '***' // Never store CVV
			));
		}
		
		// Generate unique transaction ID
		$trx_id = "TXN_" . time() . "_" . $user_id;
		
		// Get cart items
		$sql = "SELECT p_id,qty FROM cart WHERE user_id = '$user_id'";
		error_log("DEBUG: Cart query: " . $sql);
		$query = mysqli_query($con,$sql);
		
		// Debug: Check how many cart items we have
		$cart_count = mysqli_num_rows($query);
		error_log("DEBUG: Found $cart_count items in cart for user $user_id");
		
		// Reset the query result pointer
		mysqli_data_seek($query, 0);
		
		if (mysqli_num_rows($query) > 0) {
			$product_ids = array();
			$qtys = array();
			
			while ($row=mysqli_fetch_array($query)) {
				$product_ids[] = $row["p_id"];
				$qtys[] = $row["qty"];
			}
			
			error_log("DEBUG: Product IDs array: " . print_r($product_ids, true));
			error_log("DEBUG: Qtys array: " . print_r($qtys, true));
			
			// Insert orders with additional payment details
			file_put_contents($debug_file, "[$debug_time] Starting insertion of " . count($product_ids) . " products for trx_id: $trx_id\n", FILE_APPEND);
			file_put_contents($debug_file, "[$debug_time] Product IDs: " . implode(',', $product_ids) . "\n", FILE_APPEND);
			file_put_contents($debug_file, "[$debug_time] Quantities: " . implode(',', $qtys) . "\n", FILE_APPEND);
			
			for ($i=0; $i < count($product_ids); $i++) { 
				$sql = "INSERT INTO orders (user_id,product_id,qty,trx_id,p_status,payment_method,receipt_file) VALUES ('$user_id','".$product_ids[$i]."','".$qtys[$i]."','$trx_id','Pending','$payment_method','$receipt_file')";
				$result = mysqli_query($con,$sql);
				file_put_contents($debug_file, "[$debug_time] Inserted product {$product_ids[$i]} (qty: {$qtys[$i]}) - Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n", FILE_APPEND);
			}
			
			// Store additional payment details in a separate table or as JSON
			if ($payment_method != "cod") {
				$payment_details = json_encode(array(
					'card_details' => $card_details
				));

				// Update the first order with payment details
				$update_sql = "UPDATE orders SET payment_details = '$payment_details' WHERE trx_id = '$trx_id' LIMIT 1";
				mysqli_query($con, $update_sql);
			}
			
			// Clear cart
			$sql = "DELETE FROM cart WHERE user_id = '$user_id'";
			
			if (mysqli_query($con,$sql)) {
				// Clear processing flag
				unset($_SESSION['processing_order']);
				file_put_contents($debug_file, "[$debug_time] Order completed successfully, clearing processing flag\n", FILE_APPEND);
				
				// Redirect based on payment method
				if ($payment_method == "cod") {
					header("Location: index.php?order_success=1&trx_id=" . $trx_id . "&payment_method=cod");
				} elseif ($payment_method == "receipt_upload") {
					header("Location: index.php?order_success=1&trx_id=" . $trx_id . "&payment_method=receipt_upload");
				} elseif ($payment_method == "card_payment") {
					header("Location: index.php?order_success=1&trx_id=" . $trx_id . "&payment_method=card_payment");
				} else {
					// Default redirect to success page
					header("Location: payment_success.php?trx_id=" . $trx_id . "&payment_method=" . $payment_method);
				}
				exit();
			} else {
				// Clear processing flag on error
				unset($_SESSION['processing_order']);
				file_put_contents($debug_file, "[$debug_time] Order failed, clearing processing flag\n", FILE_APPEND);
			}
		} else {
			// Clear processing flag
			unset($_SESSION['processing_order']);
			file_put_contents($debug_file, "[$debug_time] Cart empty, clearing processing flag\n", FILE_APPEND);
			echo "<div class='alert alert-warning'>
					<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
					<b>Your cart is empty. Please add items to cart first.</b>
				</div>";
		}
	} else {
		// Clear processing flag
		unset($_SESSION['processing_order']);
		file_put_contents($debug_file, "[$debug_time] User not logged in, clearing processing flag\n", FILE_APPEND);
		echo "<div class='alert alert-danger'>
				<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
				<b>Please login to complete your order.</b>
			</div>";
	}
}

// Get cart items for logged in user
$user_id = $_SESSION["uid"];
$sql = "SELECT a.product_id,a.product_title,a.product_price,a.product_image,b.id,b.qty FROM products a,cart b WHERE a.product_id=b.p_id AND b.user_id='$user_id'";
$query = mysqli_query($con,$sql);

?>
<!DOCTYPE html>
<html>
<head>
		<meta charset="UTF-8">
		<title>Ecommerce</title>
		<link rel="stylesheet" href="css/bootstrap.min.css"/>
		<script src="js/jquery2.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<!-- <script src="main.js"></script> -->
		<link rel="stylesheet" type="text/css" href="style.css"/>
		<link rel="stylesheet" type="text/css" href="css/cart.css"/>
		<style>
			@media screen and (max-width:480px){
				#search{width:80%;}
				#search_btn{width:30%;float:right;margin-top:-32px;margin-right:10px;}
			}
			.checkout-container {
				background: #f8f9fa;
				min-height: 100vh;
				padding: 20px 0;
			}
			.order-summary {
				background: white;
				border-radius: 12px;
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
				padding: 25px;
				margin-bottom: 20px;
			}
			.payment-section {
				background: white;
				border-radius: 12px;
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
				padding: 25px;
				max-height: 80vh;
				overflow-y: auto;
			}
			.payment-section::-webkit-scrollbar {
				width: 8px;
			}
			.payment-section::-webkit-scrollbar-track {
				background: #f1f1f1;
				border-radius: 4px;
			}
			.payment-section::-webkit-scrollbar-thumb {
				background: #c1c1c1;
				border-radius: 4px;
			}
			.payment-section::-webkit-scrollbar-thumb:hover {
				background: #a8a8a8;
			}
			.cart-item-summary {
				display: flex;
				align-items: center;
				padding: 15px 0;
				border-bottom: 1px solid #eee;
			}
			.cart-item-summary:last-child {
				border-bottom: none;
			}
			.item-image {
				width: 60px;
				height: 60px;
				object-fit: cover;
				border-radius: 8px;
				margin-right: 15px;
			}
			.item-details {
				flex: 1;
			}
			.item-name {
				font-weight: 600;
				color: #333;
				margin-bottom: 5px;
			}
			.item-qty {
				color: #666;
				font-size: 14px;
			}
			.item-price {
				font-weight: 700;
				color: #28a745;
				font-size: 16px;
			}
			.total-section {
				background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
				color: white;
				padding: 20px;
				border-radius: 8px;
				text-align: center;
				margin-top: 20px;
			}
			.payment-method-card {
				border: 2px solid #e9ecef;
				border-radius: 8px;
				padding: 20px;
				margin-bottom: 15px;
				cursor: pointer;
				transition: all 0.3s ease;
			}
			.payment-method-card:hover {
				border-color: #007bff;
				box-shadow: 0 4px 12px rgba(0,123,255,0.15);
			}
			.payment-method-card.active {
				border-color: #007bff;
				background: #e3f2fd;
				box-shadow: 0 4px 12px rgba(0,123,255,0.3);
			}
			.btn-complete-order {
				background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
				border: none;
				padding: 15px 40px;
				font-size: 18px;
				font-weight: 600;
				border-radius: 8px;
				box-shadow: 0 4px 8px rgba(0,0,0,0.1);
				transition: all 0.3s ease;
				width: 100%;
			}
			.btn-complete-order:hover {
				transform: translateY(-2px);
				box-shadow: 0 6px 12px rgba(0,0,0,0.2);
			}
		</style>
	</head>
<body>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container-fluid">	
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#collapse" aria-expanded="false">
					<span class="sr-only"> navigation toggle</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a href="#" class="navbar-brand">Ecommerce Site</a>
			</div>
		<div class="collapse navbar-collapse" id="collapse">
			<ul class="nav navbar-nav">
				<li><a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
				<li><a href="index.php"><span class="glyphicon glyphicon-modal-window"></span> Product</a></li>
				<li style="width:300px;left:10px;top:10px;"><input type="text" class="form-control" id="search"></li>
				<li style="top:10px;left:20px;"><button class="btn btn-primary" id="search_btn">Search</button></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li><a href="#" id="cart_container" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-shopping-cart"></span> Cart <span class="badge">0</span></a>
					<div class="dropdown-menu" style="width:1000px; max-width: 1000px;">
						<div class="panel panel-success">
							<div class="panel-heading" style="display: none;">
							</div>
							<div class="panel-body">
								<div id="cart_product">
								<!--<div class="row">
									<div class="col-md-3">Sl.No</div>
									<div class="col-md-3">Product Image</div>
									<div class="col-md-3">Product Name</div>
									<div class="col-md-3">Price in $.</div>
								</div>-->
								</div>
							</div>
							<div class="panel-footer"></div>
						</div>
					</div>
				</li>
				<li><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <?php echo "Hi, ".$_SESSION["name"]; ?></a>
					<ul class="dropdown-menu">
						<li><a href="cart.php" style="text-decoration:none; color:black;"><span class="glyphicon glyphicon-shopping-cart"> Cart</a></li>
						<li class="divider"></li>
						<li><a href="customer_order.php" style="text-decoration:none; color:black;">Orders</a></li>
						<li class="divider"></li>
						
						<li><a href="logout.php" style="text-decoration:none; color:black;">Logout</a></li>
					</ul>
				</li>
				
			</ul>
		</div>
	</div>
	
	<div class="checkout-container">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-2"></div>
				<div class="col-md-8">
					<div class="row">
						<!-- Order Summary -->
						<div class="col-md-5">
							<div class="order-summary">
								<h3 style="margin: 0 0 20px 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
									<span class="glyphicon glyphicon-list-alt"></span> Order Summary
								</h3>
								
								<?php if (mysqli_num_rows($query) > 0): ?>
									<?php 
									$total_amount = 0;
									$item_count = 0;
									while ($row=mysqli_fetch_array($query)): 
										$item_count++;
										$product_id = $row["product_id"];
										$product_title = $row["product_title"];
										$product_price = $row["product_price"];
										$product_image = $row["product_image"];
										$qty = $row["qty"];
										$item_total = $product_price * $qty;
										$total_amount += $item_total;
									?>
									<div class="cart-item-summary">
										<img src="product_images/<?php echo $product_image; ?>" class="item-image" />
										<div class="item-details">
											<div class="item-name"><?php echo $product_title; ?></div>
											<div class="item-qty">Qty: <?php echo $qty; ?> × <?php echo CURRENCY; ?> <?php echo $product_price; ?></div>
										</div>
										<div class="item-price"><?php echo CURRENCY; ?> <?php echo $item_total; ?></div>
									</div>
									<?php endwhile; ?>
									
									<div class="total-section">
										<div style="font-size: 24px; font-weight: 700; margin: 0;">
											Total (<?php echo $item_count; ?> items): <?php echo CURRENCY; ?> <?php echo $total_amount; ?>
										</div>
										<small style="opacity: 0.9; font-size: 14px;">Including all taxes</small>
									</div>
								<?php else: ?>
									<div style="text-align: center; padding: 40px 20px;">
										<span class="glyphicon glyphicon-shopping-cart" style="font-size: 48px; color: #ccc;"></span>
										<p style="color: #666; margin: 15px 0;">Your cart is empty</p>
										<a href="index.php" class="btn btn-primary">Start Shopping</a>
									</div>
								<?php endif; ?>
							</div>
						</div>
						
						<!-- Payment Options -->
						<div class="col-md-7">
							<div class="payment-section">
								<h3 style="margin: 0 0 20px 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
									<span class="glyphicon glyphicon-credit-card"></span> Select Payment Method
								</h3>
								
								<form id="paymentForm" method="post" enctype="multipart/form-data">
									<div class="form-group">
										<div class="payment-method-card active" onclick="selectPaymentMethod('cod')">
											<label style="cursor: pointer; margin: 0;">
												<input type="radio" name="payment_method" value="cod" checked style="margin-right: 10px;">
												<strong style="color: black; font-size: 18px;">Cash on Delivery (COD)</strong>
												<br><small class="text-muted">Pay when your order is delivered</small>
											</label>
										</div>
										
										<div class="payment-method-card" onclick="selectPaymentMethod('receipt_upload')">
											<label style="cursor: pointer; margin: 0;">
												<input type="radio" name="payment_method" value="receipt_upload" style="margin-right: 10px;">
												<strong style="color: black; font-size: 18px;">Bank Transfer / Online Banking</strong>
												<br><small class="text-muted">Pay through any bank and upload receipt</small>
											</label>
											
											<!-- Receipt Upload Details - appears directly under this option -->
											<div id="receiptDetails" style="display:none; margin-top: 15px; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
												<h5 style="margin: 0 0 15px 0; color: #495057; font-weight: 600;">
													<span class="glyphicon glyphicon-upload"></span> Payment Receipt Upload
												</h5>
												<div class="form-group">
													<div class="input-group">
														<span class="input-group-addon" style="background: #e9ecef; border: 1px solid #ced4da;">
															<span class="glyphicon glyphicon-paperclip"></span>
														</span>
														<input type="file" class="form-control" name="receipt_file" id="receipt_file" accept="image/*,.pdf" maxlength="5242880" style="border-radius: 0 6px 6px 0; border: 1px solid #ced4da;">
													</div>
													<small class="text-muted" style="margin-top: 5px; display: block;">
														<i class="glyphicon glyphicon-info-sign"></i> Upload screenshot or PDF of your payment confirmation (Max 5MB)
													</small>
												</div>
											</div>
										</div>
										
										<!-- <div class="payment-method-card" onclick="selectPaymentMethod('card_payment')">
											<label style="cursor: pointer; margin: 0;">
												<input type="radio" name="payment_method" value="card_payment" style="margin-right: 10px;">
												<strong style="color: black; font-size: 18px;">Card Payment</strong>
												<br><small class="text-muted">Enter card details for direct payment</small>
											</label>
											
										
											<div id="cardDetails" style="display:none; margin-top: 15px; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
												<h5 style="margin: 0 0 15px 0; color: #495057; font-weight: 600;">
													<span class="glyphicon glyphicon-credit-card"></span> Card Payment Details
												</h5>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label for="card_number" style="font-weight: 600; color: #495057;">Card Number:</label>
															<input type="text" class="form-control" name="card_number" id="card_number" placeholder="" maxlength="19" style="border-radius: 6px; border: 1px solid #ced4da;">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label for="card_holder" style="font-weight: 600; color: #495057;">Card Holder Name:</label>
															<input type="text" class="form-control" name="card_holder" id="card_holder" placeholder="" style="border-radius: 6px; border: 1px solid #ced4da;">
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-4">
														<div class="form-group">
															<label for="expiry_month" style="font-weight: 600; color: #495057;">Expiry Month:</label>
															<select class="form-control" name="expiry_month" id="expiry_month" style="border-radius: 6px; border: 1px solid #ced4da;">
																<option value="">Month</option>
																<?php for($i=1; $i<=12; $i++): ?>
																	<option value="<?php echo sprintf("%02d", $i); ?>"><?php echo sprintf("%02d", $i); ?></option>
																<?php endfor; ?>
															</select>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="expiry_year" style="font-weight: 600; color: #495057;">Expiry Year:</label>
															<select class="form-control" name="expiry_year" id="expiry_year" style="border-radius: 6px; border: 1px solid #ced4da;">
																<option value="">Year</option>
																<?php for($i=date('Y'); $i<=date('Y')+10; $i++): ?>
																	<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
																<?php endfor; ?>
															</select>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="cvv" style="font-weight: 600; color: #495057;">CVV:</label>
															<input type="text" class="form-control" name="cvv" id="cvv" placeholder="123" maxlength="4" style="border-radius: 6px; border: 1px solid #ced4da;">
														</div>
													</div>
												</div>
												<div class="alert alert-info" style="margin-top: 15px; border-radius: 6px;">
													<small>
														<i class="glyphicon glyphicon-lock"></i> Your payment information is secure and encrypted. We do not store your card details.
													</small>
												</div>
											</div>
										</div> -->
									</div>
									
									<div class="form-group" style="text-align: center; margin-top: 30px;">
										<button type="submit" name="processPayment" class="btn btn-success btn-lg btn-complete-order" onclick="return validateForm(event)">
											<span class="glyphicon glyphicon-credit-card"></span> Complete Order
										</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-2"></div>
			</div>
		</div>
	</div>

<script>
var CURRENCY = '<?php echo CURRENCY; ?>';
// Payment method selection
function selectPaymentMethod(method) {
	
	// Remove active class from all cards
	document.querySelectorAll('.payment-method-card').forEach(card => {
		card.classList.remove('active');
	});
	
	// Add active class to selected card
	event.currentTarget.classList.add('active');
	
	// Set radio button
	document.querySelector(`input[value="${method}"]`).checked = true;
	
	// Hide all payment detail sections
	var receiptDetails = document.getElementById('receiptDetails');
	var cardDetails = document.getElementById('cardDetails');
	
	if (receiptDetails) receiptDetails.style.display = 'none';
	if (cardDetails) cardDetails.style.display = 'none';
	
	// Show relevant section
	if (method === 'receipt_upload') {
		if (receiptDetails) {
			receiptDetails.style.display = 'block';
		}
	} else if (method === 'card_payment') {
		if (cardDetails) {
			cardDetails.style.display = 'block';
		}
	}
}

// Alternative event listeners for payment method selection
document.addEventListener('DOMContentLoaded', function() {
	// Add click listeners to payment method cards
	document.querySelectorAll('.payment-method-card').forEach(card => {
		card.addEventListener('click', function() {
			var radio = this.querySelector('input[type="radio"]');
			if (radio) {
				selectPaymentMethod(radio.value);
			}
		});
	});
	
	// Add change listeners to radio buttons
	document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
		radio.addEventListener('change', function() {
			selectPaymentMethod(this.value);
		});
	});
});

// Card number formatting
document.getElementById('card_number').addEventListener('input', function() {
	var value = this.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
	var formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
	this.value = formattedValue;
});

// CVV formatting (numbers only)
document.getElementById('cvv').addEventListener('input', function() {
	this.value = this.value.replace(/[^0-9]/g, '');
});


// Simple validation function
function validateForm(event) {
	
	var paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
	
	if (paymentMethod === 'receipt_upload') {
		var receiptFile = document.getElementById('receipt_file');
		var file = receiptFile.files[0];
		
		if (!file) {
			alert('Please upload a payment receipt.');
			return false;
		}
		
		// Check file size (5MB max)
		var maxSize = 5 * 1024 * 1024; // 5MB in bytes
		var fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
		
		if (file.size > maxSize) {
			alert('File size (' + fileSizeMB + ' MB) must be less than 5MB. Please choose a smaller file.');
			return false;
		}
		
		// Check file type
		var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
		
		if (!allowedTypes.includes(file.type)) {
			alert('Invalid file type. Please upload only JPG, PNG, GIF, or PDF files.');
			return false;
		}
	}
	
	return true;
}
</script>
</body>	
</html>
