<?php
require "config/constants.php";
include "db.php";

session_start();
if(!isset($_SESSION["uid"])){
	header("location:index.php");
}

// Handle order rejection
if (isset($_POST["reject_order"])) {
	$order_id = $_POST["order_id"];
	$user_id = $_SESSION["uid"];
	
	// Check if order belongs to user and is not delivered
	$check_sql = "SELECT p_status FROM orders WHERE order_id = '$order_id' AND user_id = '$user_id'";
	$check_query = mysqli_query($con, $check_sql);
	
	if (mysqli_num_rows($check_query) > 0) {
		$order = mysqli_fetch_array($check_query);
		if ($order["p_status"] != "Delivered") {
			// Update order status to rejected
			$update_sql = "UPDATE orders SET p_status = 'Rejected' WHERE order_id = '$order_id'";
			if (mysqli_query($con, $update_sql)) {
				$success_message = "Order has been rejected successfully.";
			} else {
				$error_message = "Error rejecting order. Please try again.";
			}
		} else {
			$error_message = "Cannot reject delivered orders.";
		}
	} else {
		$error_message = "Order not found or you don't have permission to reject this order.";
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Ecommerce</title>
		<link rel="stylesheet" href="css/bootstrap.min.css"/>
		<script src="js/jquery2.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="main.js"></script>
		<link rel="stylesheet" type="text/css" href="style.css"/>
		<style>
			.order-card {
				background: white;
				border-radius: 12px;
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
				margin-bottom: 20px;
				overflow: hidden;
				transition: all 0.3s ease;
			}
			.order-card:hover {
				box-shadow: 0 6px 20px rgba(0,0,0,0.15);
			}
			.order-header {
				background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
				color: white;
				padding: 15px 20px;
				font-weight: 600;
			}
			.order-body {
				padding: 20px;
			}
			.product-image {
				width: 120px;
				height: 120px;
				object-fit: cover;
				border-radius: 8px;
				border: 2px solid #e9ecef;
			}
			.product-details {
				padding-left: 20px;
			}
			.status-badge {
				padding: 6px 12px;
				border-radius: 20px;
				font-size: 12px;
				font-weight: 600;
				text-transform: uppercase;
			}
			.status-pending { background: #fff3cd; color: #856404; }
			.status-processing { background: #d1ecf1; color: #0c5460; }
			.status-shipped { background: #d4edda; color: #155724; }
			.status-delivered { background: #d1ecf1; color: #0c5460; }
			.status-rejected { background: #f8d7da; color: #721c24; }
			.status-cancelled { background: #e2e3e5; color: #383d41; }
			.reject-btn {
				background: #dc3545;
				border: none;
				color: white;
				padding: 8px 16px;
				border-radius: 6px;
				font-size: 12px;
				cursor: pointer;
				transition: all 0.3s ease;
			}
			.reject-btn:hover {
				background: #c82333;
				transform: translateY(-1px);
			}
			.reject-btn:disabled {
				background: #6c757d;
				cursor: not-allowed;
				transform: none;
			}
			.order-info {
				background: #f8f9fa;
				padding: 15px;
				border-radius: 8px;
				margin-top: 15px;
			}
			.info-row {
				display: flex;
				justify-content: space-between;
				margin-bottom: 8px;
				padding: 5px 0;
				border-bottom: 1px solid #e9ecef;
			}
			.info-row:last-child {
				border-bottom: none;
				margin-bottom: 0;
			}
			.info-label {
				font-weight: 600;
				color: #495057;
			}
			.info-value {
				color: #6c757d;
			}
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
				<li><a href="index.php"><span class="glyphicon glyphicon-modal-window"></span>Product</a></li>
			</ul>
		</div>
	</div>
	<p><br/></p>
	<p><br/></p>
	<p><br/></p>
	<div class="container-fluid" style="background: #f8f9fa; min-height: 100vh; padding: 20px 0;">
		<div class="row">
			<div class="col-md-1"></div>
			<div class="col-md-10">
				<div class="panel panel-default">
					<div class="panel-heading" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white;">
						<h2 style="margin: 0; font-weight: 600;">
							<span class="glyphicon glyphicon-list-alt"></span> My Orders
						</h2>
					</div>
					<div class="panel-body" style="padding: 30px;">
						
						<?php if (isset($success_message)): ?>
							<div class="alert alert-success alert-dismissible">
								<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
								<strong>Success!</strong> <?php echo $success_message; ?>
							</div>
						<?php endif; ?>
						
						<?php if (isset($error_message)): ?>
							<div class="alert alert-danger alert-dismissible">
								<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
								<strong>Error!</strong> <?php echo $error_message; ?>
							</div>
						<?php endif; ?>
						
						<?php
							$user_id = $_SESSION["uid"];
							
							// Get unique transaction IDs for this user
							$trx_sql = "SELECT DISTINCT trx_id FROM orders WHERE user_id='$user_id' ORDER BY trx_id DESC";
							$trx_query = mysqli_query($con, $trx_sql);
							
							if (mysqli_num_rows($trx_query) > 0) {
								while ($trx_row = mysqli_fetch_array($trx_query)) {
									$current_trx = $trx_row["trx_id"];
									
									// Get all items for this transaction
									$orders_sql = "SELECT o.order_id,o.product_id,o.qty,o.p_status,o.payment_method,o.receipt_file,p.product_title,p.product_price,p.product_image FROM orders o,products p WHERE o.trx_id='$current_trx' AND o.product_id=p.product_id";
									$orders_query = mysqli_query($con, $orders_sql);
									
									$total_amount = 0;
									$item_count = 0;
									$first_order = null;
									
									// Calculate totals and get first order details
									while ($order_row = mysqli_fetch_array($orders_query)) {
										if ($first_order === null) {
											$first_order = $order_row;
										}
										$total_amount += $order_row["product_price"] * $order_row["qty"];
										$item_count += $order_row["qty"];
									}
									
									$status = $first_order["p_status"];
									$payment_method = $first_order["payment_method"];
									$receipt_file = $first_order["receipt_file"];
									
									// Status badge class
									$status_class = 'status-' . strtolower($status);
									
									echo '<div class="order-card">';
									echo '<div class="order-header">';
									echo '<div class="row">';
									echo '<div class="col-md-8">';
									echo '<h4 style="margin: 0;">Order #' . $first_order["order_id"] . ' - ' . $current_trx . '</h4>';
									// echo '<small>Transaction ID: ' . $current_trx . '</small>';
									echo '</div>';
									echo '<div class="col-md-4 text-right">';
									echo '<span class="status-badge ' . $status_class . '">' . $status . '</span>';
									echo '</div>';
									echo '</div>';
									echo '</div>';
									
									echo '<div class="order-body">';
									
									// Display all items in this transaction
									$orders_query = mysqli_query($con, $orders_sql);
									while ($order_row = mysqli_fetch_array($orders_query)) {
										echo '<div class="row" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e9ecef;">';
										echo '<div class="col-md-3">';
										echo '<img src="product_images/' . $order_row['product_image'] . '" class="product-image" />';
										echo '</div>';
										echo '<div class="col-md-6 product-details">';
										echo '<h5 style="margin: 0 0 10px 0; font-weight: 600;">' . $order_row["product_title"] . '</h5>';
										echo '<p style="margin: 5px 0; color: #6c757d;">Price: ' . CURRENCY . ' ' . $order_row["product_price"] . '</p>';
										echo '<p style="margin: 5px 0; color: #6c757d;">Quantity: ' . $order_row["qty"] . '</p>';
										echo '<p style="margin: 5px 0; font-weight: 600; color: #28a745;">Subtotal: ' . CURRENCY . ' ' . ($order_row["product_price"] * $order_row["qty"]) . '</p>';
										echo '</div>';
										echo '<div class="col-md-3 text-right">';
										echo '<span class="status-badge ' . $status_class . '">' . $order_row["p_status"] . '</span>';
										echo '</div>';
										echo '</div>';
									}
									
									// Order summary
									echo '<div class="order-info">';
									echo '<div class="info-row">';
									echo '<span class="info-label">Total Items:</span>';
									echo '<span class="info-value">' . $item_count . '</span>';
									echo '</div>';
									echo '<div class="info-row">';
									echo '<span class="info-label">Total Amount:</span>';
									echo '<span class="info-value" style="font-weight: 600; color: #28a745; font-size: 16px;">' . CURRENCY . ' ' . $total_amount . '</span>';
									echo '</div>';
									echo '<div class="info-row">';
									echo '<span class="info-label">Payment Method:</span>';
									echo '<span class="info-value">' . ucfirst(str_replace('_', ' ', $payment_method)) . '</span>';
									echo '</div>';
									if ($receipt_file && $payment_method == 'receipt_upload') {
										echo '<div class="info-row">';
										echo '<span class="info-label">Receipt:</span>';
										echo '<span class="info-value"><a href="' . $receipt_file . '" target="_blank" class="btn btn-sm btn-info">View Receipt</a></span>';
										echo '</div>';
									}
									echo '</div>';
									
									// Action buttons
									echo '<div class="text-right" style="margin-top: 20px;">';
									if ($status != "Delivered" && $status != "Rejected" && $status != "Cancelled") {
										echo '<form method="post" style="display: inline-block;" onsubmit="return confirm(\'Are you sure you want to reject this order?\')">';
										echo '<input type="hidden" name="order_id" value="' . $first_order["order_id"] . '">';
										echo '<button type="submit" name="reject_order" class="reject-btn">';
										echo '<span class="glyphicon glyphicon-remove"></span> Reject Order';
										echo '</button>';
										echo '</form>';
									} else {
										echo '<button class="reject-btn" disabled>';
										echo '<span class="glyphicon glyphicon-ban-circle"></span> Cannot Reject';
										echo '</button>';
									}
									echo '</div>';
									
									echo '</div>'; // order-body
									echo '</div>'; // order-card
								}
							} else {
								echo '<div class="text-center" style="padding: 60px 20px;">';
								echo '<span class="glyphicon glyphicon-shopping-cart" style="font-size: 64px; color: #ccc;"></span>';
								echo '<h3 style="color: #6c757d; margin: 20px 0;">No orders found</h3>';
								echo '<p style="color: #6c757d;">You haven\'t placed any orders yet.</p>';
								echo '<a href="index.php" class="btn btn-primary btn-lg">Start Shopping</a>';
								echo '</div>';
							}
						?>
						
					</div>
				</div>
			</div>
			<div class="col-md-1"></div>
		</div>
	</div>
</body>
</html>
















































