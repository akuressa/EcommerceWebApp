<?php
require "config/constants.php";
session_start();
if(isset($_SESSION["uid"])){
	// Check if this is a redirect from order success
	if(isset($_GET['order_success']) && $_GET['order_success'] == '1'){
		// Redirect to profile.php with the same parameters
		$params = http_build_query($_GET);
		header("location:profile.php?" . $params);
		exit();
	} else {
		header("location:profile.php");
		exit();
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
		<link rel="stylesheet" type="text/css" href="style.css">
		<link rel="stylesheet" type="text/css" href="css/responsive.css">
		<script src="js/jquery2.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="main.js"></script>
	</head>
<body>
<div class="wait overlay">
	<div class="loader"></div>
</div>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container-fluid">	
		<div class="navbar-header">
			<a href="index.php" class="navbar-brand">Ecommerce Site</a>
			<ul class="nav navbar-nav mobile-cart-nav">
				<li class="mobile-cart-dropdown">
					<a href="#" class="mobile-cart dropdown-toggle" data-toggle="dropdown">
						<span class="glyphicon glyphicon-shopping-cart"></span> Cart <span class="badge">0</span>
					</a>
					<div class="dropdown-menu mobile-cart-menu" style="width:100%; max-width: 100%;">
						<button class="close-btn" onclick="$('.mobile-cart-menu').removeClass('show')">&times;</button>
						<!-- <div class="cart-header">Cart Checkout</div> -->
						<div class="panel panel-success">
							<div class="panel-heading" style="display: none;">
							</div>
							<div class="panel-body">
								<div id="mobile_cart_product">
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
				<li><a href="index.php"><span class="glyphicon glyphicon-modal-window"></span> Products</a></li>
			</ul>
			<form class="navbar-form navbar-left">
		        <div class="form-group">
		          <input type="text" class="form-control" placeholder="Search" id="search">
		        </div>
		        <button type="submit" class="btn btn-primary" id="search_btn"><span class="glyphicon glyphicon-search"></span></button>
		     </form>
			<ul class="nav navbar-nav navbar-right">
				<li><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-shopping-cart"></span> Cart <span class="badge" >0</span></a>
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
				<li><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> Login/Register</a>
					<ul class="dropdown-menu">
						<div style="width:300px;">
							<div class="panel panel-primary">
								<div class="panel-heading">Login</div>
								<div class="panel-heading">
									<form onsubmit="return false" id="login">
										<label for="email">Email</label>
										<input type="email" class="form-control" name="email" id="email" required/>
										<label for="email">Password</label>
										<input type="password" class="form-control" name="password" id="password" required/>
										<p><br/></p>
										<input type="submit" class="btn btn-warning" value="Login">
										<a href="customer_registration.php?register=1" style="color:white; text-decoration:none;">Create Account Now</a>
									</form>
								</div>
								<div class="panel-footer" id="e_msg"></div>
							</div>
						</div>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</div>	
	<p><br/></p>
	<p><br/></p>
	<p><br/></p>
	
	
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-1"></div>
			<div class="col-md-2 col-xs-12">
				<div id="get_category">
				</div>
				<!--<div class="nav nav-pills nav-stacked">
					<li class="active"><a href="#"><h4>Categories</h4></a></li>
					<li><a href="#">Categories</a></li>
					<li><a href="#">Categories</a></li>
					<li><a href="#">Categories</a></li>
					<li><a href="#">Categories</a></li>
				</div> -->
				<div id="get_brand">
				</div>
				<!--<div class="nav nav-pills nav-stacked">
					<li class="active"><a href="#"><h4>Brand</h4></a></li>
					<li><a href="#">Categories</a></li>
					<li><a href="#">Categories</a></li>
					<li><a href="#">Categories</a></li>
					<li><a href="#">Categories</a></li>
				</div> -->
			</div>
			<div class="col-md-8 col-xs-12">
				<div class="row">
					<div class="col-md-12 col-xs-12" id="product_msg">
					</div>
				</div>
				<div class="panel panel-info">
					<div class="panel-heading">Products</div>
					<div class="panel-body">
						<div id="get_product">
							<!--Here we get product jquery Ajax Request-->
						</div>
						<!--<div class="col-md-4">
							<div class="panel panel-info">
								<div class="panel-heading">Samsung Galaxy</div>
								<div class="panel-body">
									<img src="product_images/images.JPG"/>
								</div>
								<div class="panel-heading">Rs.500.00
									<button style="float:right;" class="btn btn-danger btn-xs">AddToCart</button>
								</div>
							</div>
						</div> -->
					</div>
					<div class="panel-footer">&copy; <?php echo date("Y"); ?></div>
				</div>
			</div>
			<div class="col-md-1"></div>
		</div>
	</div>
	
	<script>
		// Simple mobile cart display - just show the cart content directly
		function loadMobileCart() {
			console.log("Loading mobile cart...");
			
			$.ajax({
				url: "action.php",
				method: "POST",
				data: {Common: 1, getCartItem: 1},
				success: function(data) {
					console.log("Cart data received:", data);
					
					// Just display the raw cart content for now
					if (data && data.trim() !== '') {
						$("#mobile_cart_product").html(data);
					} else {
						$("#mobile_cart_product").html('<div class="text-center" style="padding: 40px; color: #666;">Your cart is empty</div>');
					}
				},
				error: function(xhr, status, error) {
					console.log("Cart load error:", error);
					$("#mobile_cart_product").html('<div class="text-center" style="padding: 40px; color: #666;">Error loading cart: ' + error + '</div>');
				}
			});
		}
		
		// Handle make payment button click
		function handleMakePayment() {
			// Check if user is logged in by making a simple AJAX call
			$.ajax({
				url: "action.php",
				method: "POST",
				data: {checkLogin: 1},
				success: function(response) {
					if (response === "logged_in") {
						// User is logged in, redirect to checkout
						window.location.href = "checkout.php";
					} else {
						// User is not logged in, show login modal
						$('#loginModal').modal('show');
					}
				},
				error: function() {
					// On error, show login modal
					$('#loginModal').modal('show');
				}
			});
		}
		
		// Load mobile cart when dropdown is opened
		$(document).ready(function() {
			console.log("Document ready - setting up mobile cart");
			
			// Load cart when mobile cart dropdown is clicked
			$('.mobile-cart').on('click', function(e) {
				e.preventDefault();
				console.log("Mobile cart clicked");
				loadMobileCart();
			});
			
			// Handle make payment button clicks in mobile cart
			$(document).on('click', '.make-payment-btn', function(e) {
				e.preventDefault();
				handleMakePayment();
			});
			
			// Also load on page load
			loadMobileCart();
		});
		
		// Update mobile cart when main cart is updated
		$(document).on('cartUpdated', function() {
			console.log("Cart updated event received");
			loadMobileCart();
		});
	</script>
</body>
</html>
















































