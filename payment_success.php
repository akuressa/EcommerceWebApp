<?php

session_start();
if(!isset($_SESSION["uid"])){
	header("location:index.php");
}

// Handle new payment methods
if (isset($_GET["trx_id"]) && isset($_GET["payment_method"])) {
	$trx_id = $_GET["trx_id"];
	$payment_method = $_GET["payment_method"];
	
	include_once("db.php");
	
	// Get order details
	$sql = "SELECT * FROM orders WHERE trx_id = '$trx_id' AND user_id = '$_SESSION[uid]' LIMIT 1";
	$query = mysqli_query($con,$sql);
	
	if (mysqli_num_rows($query) > 0) {
		$order = mysqli_fetch_array($query);
		$p_status = $order["p_status"];
		$receipt_file = $order["receipt_file"];
		
		// Get payment details
		$payment_details = json_decode($order["payment_details"], true);
		
		// Display success message based on payment method
		if ($payment_method == "cod") {
			$payment_message = "Your order has been placed successfully! You will pay <strong>Cash on Delivery</strong> when your order arrives.";
			$payment_icon = "glyphicon-cash";
		} else if ($payment_method == "receipt_upload") {
			$bank_name = isset($payment_details['bank_name']) ? $payment_details['bank_name'] : 'Unknown Bank';
			$transaction_id = isset($payment_details['transaction_id']) ? $payment_details['transaction_id'] : 'N/A';
			$payment_message = "Your order has been placed successfully! We have received your <strong>Bank Transfer payment receipt</strong> from <strong>$bank_name</strong> and will process your order shortly.";
			$payment_icon = "glyphicon-upload";
		} else if ($payment_method == "card_payment") {
			$card_details = isset($payment_details['card_details']) ? json_decode($payment_details['card_details'], true) : null;
			$card_holder = isset($card_details['card_holder']) ? $card_details['card_holder'] : 'Unknown';
			$card_number = isset($card_details['card_number']) ? $card_details['card_number'] : '****';
			$payment_message = "Your order has been placed successfully! Your <strong>Card Payment</strong> has been processed for card ending in <strong>$card_number</strong> under <strong>$card_holder</strong>.";
			$payment_icon = "glyphicon-credit-card";
		}
		
		// Show success page
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="UTF-8">
				<title>Ecommerce - Order Confirmation</title>
				<link rel="stylesheet" href="css/bootstrap.min.css"/>
				<script src="js/jquery2.js"></script>
				<script src="js/bootstrap.min.js"></script>
				<script src="main.js"></script>
				<style>
					table tr td {padding:10px;}
					.payment-info {background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;}
				</style>
			</head>
		<body>
			<div class="navbar navbar-inverse navbar-fixed-top">
				<div class="container-fluid">	
					<div class="navbar-header">
						<a href="#" class="navbar-brand">Ecommerce</a>
					</div>
					<ul class="nav navbar-nav">
						<li><a href="index.php"><span class="glyphicon glyphicon-home"></span>Home</a></li>
						<li><a href="profile.php"><span class="glyphicon glyphicon-modal-window"></span>Product</a></li>
					</ul>
				</div>
			</div>
			<p><br/></p>
			<p><br/></p>
			<p><br/></p>
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-2"></div>
					<div class="col-md-8">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3><span class="glyphicon <?php echo $payment_icon; ?> text-success"></span> Order Confirmation</h3>
							</div>
							<div class="panel-body">
								<h1>Thank You!</h1>
								<hr/>
								<p>Hello <strong><?php echo $_SESSION["name"]; ?></strong>,</p>
								<div class="payment-info">
									<?php echo $payment_message; ?>
								</div>
								
								<div class="row">
									<div class="col-md-6">
										<p><strong>Transaction ID:</strong> <?php echo $trx_id; ?></p>
										<p><strong>Payment Method:</strong> 
											<?php 
											$method_names = array(
												'cod' => 'Cash on Delivery',
												'receipt_upload' => 'Bank Transfer',
												'card_payment' => 'Card Payment'
											);
											echo $method_names[$payment_method] ?? strtoupper($payment_method); 
											?>
										</p>
										<p><strong>Order Status:</strong> <span class="label label-warning"><?php echo $p_status; ?></span></p>
									</div>
									<div class="col-md-6">
										<?php if ($payment_method == "receipt_upload"): ?>
											<p><strong>Bank:</strong> <?php echo $bank_name; ?></p>
											<p><strong>Transaction ID:</strong> <?php echo $transaction_id; ?></p>
											<?php if ($receipt_file): ?>
												<p><strong>Receipt:</strong> <a href="<?php echo $receipt_file; ?>" target="_blank" class="btn btn-sm btn-info">View Receipt</a></p>
											<?php endif; ?>
										<?php elseif ($payment_method == "card_payment"): ?>
											<p><strong>Card Holder:</strong> <?php echo $card_holder; ?></p>
											<p><strong>Card Number:</strong> **** **** **** <?php echo $card_number; ?></p>
										<?php endif; ?>
									</div>
								</div>
								
								<hr/>
								<p>You can continue shopping or check your order status in your profile.</p>
								<a href="index.php" class="btn btn-success btn-lg">Continue Shopping</a>
								<a href="profile.php" class="btn btn-info btn-lg">View Profile</a>
							</div>
							<div class="panel-footer"></div>
						</div>
					</div>
					<div class="col-md-2"></div>
				</div>
			</div>
		</body>
		</html>
		<?php
		exit();
	} else {
		header("location:index.php");
	}
}




?>

















































