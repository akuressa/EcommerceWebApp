<?php
require "config/constants.php";
session_start();
if(!isset($_SESSION["uid"])){
	header("location:index.php");
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
		<style>
			@media screen and (max-width:480px){
				#search{width:80%;}
				#search_btn{width:30%;float:right;margin-top:-48px;margin-right:10px;}
			}
			
			/* Mobile responsive styles for profile page */
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
				.col-md-1,
				.col-md-2 {
					display: none;
				}
				
				.col-md-8 {
					width: 100%;
					float: none;
				}
				
				/* Make panels mobile-friendly */
				.panel {
					margin-bottom: 20px;
					border-radius: 8px;
					box-shadow: 0 2px 4px rgba(0,0,0,0.1);
				}
				
				.panel-heading {
					font-size: 18px;
					padding: 12px 15px;
					font-weight: 600;
				}
				
				.panel-body {
					padding: 15px;
				}
				
				/* Make product cards responsive */
				.panel-info .panel-body {
					padding: 10px;
				}
				
				/* Fix product grid for mobile */
				.col-md-4,
				.col-sm-6,
				.col-xs-12 {
					width: 100%;
					float: none;
					margin-bottom: 20px;
				}
				
				/* Product card improvements */
				.panel-info {
					border: 1px solid #ddd;
					border-radius: 8px;
					box-shadow: 0 2px 4px rgba(0,0,0,0.1);
					margin-bottom: 20px;
				}
				
				.panel-info .panel-heading {
					background-color: #17a2b8;
					color: white;
					font-size: 16px;
					font-weight: 600;
					padding: 12px 15px;
					border-radius: 8px 8px 0 0;
				}
				
				.panel-info .panel-body {
					padding: 15px;
				}
				
				.panel-info .panel-body img {
					width: 100%;
					height: auto;
					max-height: 250px;
					object-fit: cover;
					border-radius: 4px;
					margin-bottom: 15px;
				}
				
				/* Price and button row styling */
				.panel-heading:last-child {
					background-color: #17a2b8;
					color: white;
					font-size: 16px;
					font-weight: bold;
					padding: 12px 15px;
					border-radius: 0 0 8px 8px;
					display: flex;
					justify-content: space-between;
					align-items: center;
					flex-wrap: wrap;
					gap: 10px;
				}
				
				.panel-heading:last-child .btn {
					margin: 0;
					flex-shrink: 0;
				}
				
				/* Make buttons mobile-friendly */
				.btn {
					font-size: 16px;
					padding: 12px 16px;
					border-radius: 6px;
					min-height: 44px;
					min-width: 44px;
				}
				
				/* Specific styling for Add to Cart button */
				.btn-danger {
					background-color: #dc3545;
					border-color: #dc3545;
					color: white;
					font-weight: 600;
					font-size: 16px;
					padding: 12px 20px;
					border-radius: 6px;
					min-height: 44px;
					transition: all 0.2s ease;
				}
				
				.btn-danger:hover {
					background-color: #c82333;
					border-color: #bd2130;
					transform: translateY(-1px);
					box-shadow: 0 2px 4px rgba(0,0,0,0.2);
				}
				
				/* Fix pagination for mobile */
				.pagination {
					margin: 20px 0;
				}
				
				.pagination > li > a {
					padding: 8px 12px;
					font-size: 14px;
				}

				.navbar-nav .open .dropdown-menu {
					background-color: white;
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
	
	<?php if (isset($_GET['order_success']) && $_GET['order_success'] == '1'): ?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4"></div>
				<div class="col-md-4">
					<div class="alert alert-success alert-dismissible" id="orderSuccessAlert" style="margin: 0 0 20px 0; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
						<div style="text-align: center;">
							<h3 style="margin: 0; color: #155724; display: flex; align-items: center; justify-content: center;">
								<span class="glyphicon glyphicon-ok-circle" style="font-size: 36px; color: #28a745; margin-right: 15px;"></span>
								<span style="font-size: 20px;">Order Placed Successfully!</span>
							</h3>
							
						</div>
					</div>
				</div>
				<div class="col-md-2"></div>
			</div>
		</div>
		
		<script>
		// Clear URL parameters immediately and auto-close success message after 5 seconds
		document.addEventListener('DOMContentLoaded', function() {
			var alertElement = document.getElementById('orderSuccessAlert');
			
			// Clear URL parameters immediately to prevent showing on refresh
			if (window.history && window.history.replaceState) {
				window.history.replaceState({}, document.title, window.location.pathname);
			}
			
			if (alertElement) {
				// Auto-close after 5 seconds
				setTimeout(function() {
					alertElement.style.transition = 'opacity 0.5s ease-out';
					alertElement.style.opacity = '0';
					
					setTimeout(function() {
						alertElement.remove();
					}, 300);
				}, 1000);
			}
		});
		</script>
	<?php endif; ?>
	
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-1"></div>
			<div class="col-md-2">
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
			<div class="col-md-8">	
				<div class="row">
					<div class="col-md-12 col-xs-12" id="product_msg">
					</div>
				</div>
				<div class="panel panel-info" id="scroll">
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
								<div class="panel-heading">$.500.00
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
		<div class="row">
			<div class="col-md-12">
				<center>
					<ul class="pagination" id="pageno">
						<li><a href="#">1</a></li>
					</ul>
				</center>
			</div>
		</div>
	</div>
	
	<script>
		// Mobile cart functionality - matching index.php structure
		$(document).ready(function() {
			console.log("Profile page: Document ready - setting up mobile cart");
			
			// Load mobile cart when dropdown is opened
			$('.mobile-cart').on('click', function(e) {
				e.preventDefault();
				console.log("Mobile cart clicked in profile");
				loadMobileCart();
			});
			
			// Load cart content function
			function loadMobileCart() {
				console.log("Loading mobile cart in profile...");
				
				$.ajax({
					url: "action.php",
					method: "POST",
					data: {Common: 1, getCartItem: 1},
					success: function(data) {
						console.log("Cart data received in profile:", data);
						
						if (data && data.trim() !== '') {
							$("#mobile_cart_product").html(data);
						} else {
							$("#mobile_cart_product").html('<div class="text-center" style="padding: 40px; color: #666;">Your cart is empty</div>');
						}
					},
					error: function(xhr, status, error) {
						console.log("Cart load error in profile:", error);
						$("#mobile_cart_product").html('<div class="text-center" style="padding: 40px; color: #666;">Error loading cart: ' + error + '</div>');
					}
				});
			}
			
			// Also load on page load
			loadMobileCart();
			
			// Update mobile cart when main cart is updated
			$(document).on('cartUpdated', function() {
				console.log("Cart updated event received in profile");
				loadMobileCart();
			});
		});
	</script>
</body>
</html>
















































