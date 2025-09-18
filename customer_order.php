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
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Ecommerce</title>
		<link rel="stylesheet" href="css/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="css/responsive.css"/>
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
			
			/* Mobile responsive styles for customer order page */
			@media (max-width: 767.98px) {
				body {
					padding-top: 60px !important;
				}
				
				.container-fluid {
					padding-left: 10px;
					padding-right: 10px;
				}
				
				/* Mobile navbar header styling */
				.navbar-header {
					display: flex;
					justify-content: space-between;
					align-items: center;
					width: 100%;
				}
				
				.navbar-header .navbar-brand {
					flex: 1;
					margin-left: 15px;
				}
				
				.navbar-header .mobile-cart {
					display: block !important;
					color: white;
					text-decoration: none;
					padding: 8px 12px;
					margin-right: 15px;
					border-radius: 4px;
					background: rgba(255,255,255,0.1);
					transition: background 0.2s ease;
				}
				
				.navbar-header .mobile-cart:hover {
					background: rgba(255,255,255,0.2);
					color: white;
					text-decoration: none;
				}
				
				.navbar-header .mobile-cart .badge {
					background: #dc3545;
					color: white;
					border-radius: 50%;
					padding: 2px 6px;
					font-size: 12px;
					margin-left: 5px;
				}
				
				.navbar-toggle {
					display: block;
					margin-right: -20px;
					float: right;
				}
				
				/* Hide desktop cart on mobile */
				#cart_container {
					display: none !important;
				}
				
				/* Style search bar for mobile - override external CSS */
				.navbar-form {
					margin: 10px 0 !important;
					padding: 0 15px !important;
					width: 100% !important;
					display: flex !important;
					align-items: stretch !important;
					gap: 0 !important;
					background: transparent !important;
					border: none !important;
					border-radius: 0 !important;
				}
				
				.navbar-form .form-group {
					margin-bottom: 0 !important;
					flex: 1 !important;
					display: flex !important;
					position: relative !important;
				}
				
				.navbar-form .form-control {
					font-size: 16px !important;
					height: 40px !important;
					width: 100% !important;
					margin-bottom: 0 !important;
					border: 2px solid #ddd !important;
					border-radius: 4px 0 0 4px !important;
					border-right: none !important;
					padding-left: 40px !important;
					background: white !important;
					background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='%23999' d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3e%3c/svg%3e") !important;
					background-repeat: no-repeat !important;
					background-position: 12px center !important;
					background-size: 16px !important;
				}
				
				.navbar-form .form-control:focus {
					border-color: #007bff !important;
					box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
					border-right: none !important;
					outline: none !important;
				}
				
				.navbar-form .form-control::placeholder {
					color: #999 !important;
					font-style: italic !important;
				}
				
				.navbar-form .btn {
					height: 40px !important;
					padding: 8px 12px !important;
					width: auto !important;
					min-width: 60px !important;
					margin-top: 0 !important;
					font-weight: 600 !important;
					flex-shrink: 0 !important;
					background: #007bff !important;
					border: 2px solid #007bff !important;
					border-radius: 0 4px 4px 0 !important;
					color: white !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					position: relative !important;
				}
				
				
				.navbar-form .btn:hover {
					background: #0056b3 !important;
					border-color: #0056b3 !important;
				}
				
				/* Hide sidebar on mobile */
				.col-md-1 {
					display: none;
				}
				
				.col-md-10 {
					width: 100%;
					float: none;
				}
				
				/* Mobile order card styling */
				.order-card {
					margin-bottom: 15px;
					border-radius: 8px;
				}
				
				.order-header {
					padding: 12px 15px;
					font-size: 14px;
				}
				
				.order-header h4 {
					font-size: 16px;
					margin: 0;
				}
				
				.order-body {
					padding: 15px;
				}
				
				.product-image {
					width: 80px;
					height: 80px;
					margin-bottom: 10px;
				}
				
				.product-details {
					padding-left: 0;
					margin-bottom: 15px;
				}
				
				.product-details h5 {
					font-size: 16px;
					margin-bottom: 8px;
				}
				
				.product-details p {
					font-size: 14px;
					margin: 3px 0;
				}
				
				.status-badge {
					font-size: 10px;
					padding: 4px 8px;
				}
				
				.order-info {
					padding: 12px;
					margin-top: 12px;
				}
				
				.info-row {
					font-size: 14px;
					margin-bottom: 6px;
					padding: 3px 0;
				}
				
				.reject-btn {
					padding: 10px 16px;
					font-size: 14px;
					width: 100%;
					margin-top: 10px;
				}
				
				.navbar-nav .open .dropdown-menu {
					background-color: white;
				}
				
				/* Mobile cart dropdown styling */
				.mobile-cart-menu {
					background: #dcd8d8 !important;
					border: 1px solid #ccc !important;
					border-radius: 8px !important;
					box-shadow: 0 4px 20px rgba(0,0,0,0.3) !important;
				}
				
				/* Mobile scrollable order list */
				.panel-body {
					max-height: 60vh !important;
					overflow-y: auto;
					padding: 15px !important;
				}
				
				/* Custom scrollbar for mobile */
				.panel-body::-webkit-scrollbar {
					width: 6px;
				}
				
				.panel-body::-webkit-scrollbar-track {
					background: #f1f1f1;
					border-radius: 3px;
				}
				
				.panel-body::-webkit-scrollbar-thumb {
					background: #888;
					border-radius: 3px;
				}
				
				.panel-body::-webkit-scrollbar-thumb:hover {
					background: #555;
				}
			}
		</style>
	</head>
<body>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container-fluid">	
			<div class="navbar-header">
				<a href="#" class="navbar-brand">Ecommerce Site</a>
				<ul class="nav navbar-nav mobile-cart-nav">
					<li class="mobile-cart-dropdown">
						<a href="#" class="mobile-cart dropdown-toggle" data-toggle="dropdown">
							<span class="glyphicon glyphicon-shopping-cart"></span> Cart <span class="badge">0</span>
						</a>
						<div class="dropdown-menu mobile-cart-menu" style="width:100%; max-width: 100%;">
							<button class="close-btn" onclick="$('.mobile-cart-menu').removeClass('show')">&times;</button>
							<div class="panel panel-success">
								<div class="panel-heading" style="display: none;">
								</div>
								<div class="panel-body">
									<div id="mobile_cart_product">
									
									</div>
								</div>
								<div class="panel-footer"></div>
							</div>
						</div>
					</li>
				</ul>
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#collapse" aria-expanded="false">
					<span class="sr-only">navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
		<div class="collapse navbar-collapse" id="collapse">
			<ul class="nav navbar-nav">
				<li><a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
				<li><a href="index.php"><span class="glyphicon glyphicon-modal-window"></span> Product</a></li>
			</ul>
			<form class="navbar-form navbar-left">
		        <div class="form-group">
		          <input type="text" class="form-control" placeholder="Search" id="search">
		        </div>
		        <button type="submit" class="btn btn-primary" id="search_btn">Search</button>
		     </form>
			<ul class="nav navbar-nav navbar-right">
				<!-- Desktop Cart -->
				<li><a href="#" id="cart_container" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-shopping-cart"></span> Cart <span class="badge">0</span></a>
					<div class="dropdown-menu" style="width:1000px; max-width: 1000px;">
						<div class="panel panel-success">
							<div class="panel-heading" style="display: none;">
							</div>
							<div class="panel-body">
								<div id="cart_product">
								
								</div>
							</div>
							<div class="panel-footer"></div>
						</div>
					</div>
				</li>
				<li><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <?php echo "Hi, ".$_SESSION["name"]; ?></a>
					<ul class="dropdown-menu">
						<li><a href="customer_order.php" style="text-decoration:none; color:black;">Orders</a></li>
						<li class="divider"></li>
						<li><a href="logout.php" style="text-decoration:none; color:black;">Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
	</div>
	
	<!-- Mobile Cart Dropdown - Outside navbar to avoid Bootstrap collapse issues -->
	
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
					<div class="panel-body" style="padding: 30px; max-height: 70vh; overflow-y: auto;">
						
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
									echo '<h4 style="margin: 0;">Order #' . $first_order["order_id"] . '</h4>';
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
										echo '<div class="col-md-9 product-details">';
										echo '<h5 style="margin: 0 0 10px 0; font-weight: 600;">' . $order_row["product_title"] . '</h5>';
										echo '<p style="margin: 5px 0; color: #6c757d;">Price: ' . CURRENCY . ' ' . $order_row["product_price"] . '</p>';
										echo '<p style="margin: 5px 0; color: #6c757d;">Quantity: ' . $order_row["qty"] . '</p>';
										echo '<p style="margin: 5px 0; font-weight: 600; color: #28a745;">Subtotal: ' . CURRENCY . ' ' . ($order_row["product_price"] * $order_row["qty"]) . '</p>';
										echo '</div>';
										echo '</div>';
									}
									
									// Order summary
									echo '<div class="order-info">';
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
	
	<script>
		// Mobile cart functionality - matching index.php structure
		$(document).ready(function() {
			console.log("Customer Order page: Document ready - setting up mobile cart");
			
			// Load mobile cart when dropdown is opened
			$('.mobile-cart').on('click', function(e) {
				e.preventDefault();
				console.log("Mobile cart clicked in customer order");
				loadMobileCart();
			});
			
			// Load cart content function
			function loadMobileCart() {
				console.log("Loading mobile cart in customer order...");
				
				$.ajax({
					url: "action.php",
					method: "POST",
					data: {Common: 1, getCartItem: 1},
					success: function(data) {
						console.log("Cart data received in customer order:", data);
						
						if (data && data.trim() !== '') {
							$("#mobile_cart_product").html(data);
						} else {
							$("#mobile_cart_product").html('<div class="text-center" style="padding: 40px; color: #666;">Your cart is empty</div>');
						}
					},
					error: function(xhr, status, error) {
						console.log("Cart load error in customer order:", error);
						$("#mobile_cart_product").html('<div class="text-center" style="padding: 40px; color: #666;">Error loading cart: ' + error + '</div>');
					}
				});
			}
			
			// Also load on page load
			loadMobileCart();
			
			// Update mobile cart when main cart is updated
			$(document).on('cartUpdated', function() {
				console.log("Cart updated event received in customer order");
				loadMobileCart();
			});
		});
	</script>
</body>
</html>
















































