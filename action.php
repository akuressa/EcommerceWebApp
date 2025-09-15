<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$session_id = session_id();
$ip_add = $session_id;

// Debug: Log the request
error_log("Action.php called with POST data: " . print_r($_POST, true));

include "db.php";

// Debug: Check database connection
if (!$con) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed");
}
if(isset($_POST["category"])){
	$category_query = "SELECT * FROM categories";
	$run_query = mysqli_query($con,$category_query) or die(mysqli_error($con));
	echo "
		<div class='nav nav-pills nav-stacked'>
			<li class='active'><a href='#'><h4>Product Categories</h4></a></li>
	";
	if(mysqli_num_rows($run_query) > 0){
		while($row = mysqli_fetch_array($run_query)){
			$cid = $row["cat_id"];
			$cat_name = $row["cat_title"];
			echo "
					<li><a href='#' class='category' cid='$cid'>$cat_name</a></li>
			";
		}
		echo "</div>";
	}
}
if(isset($_POST["brand"])){
	$brand_query = "SELECT * FROM brands";
	$run_query = mysqli_query($con,$brand_query);
	echo "
		<div class='nav nav-pills nav-stacked'>
			<li class='active'><a href='#'><h4>Brands</h4></a></li>
	";
	if(mysqli_num_rows($run_query) > 0){
		while($row = mysqli_fetch_array($run_query)){
			$bid = $row["brand_id"];
			$brand_name = $row["brand_title"];
			echo "
					<li><a href='#' class='selectBrand' bid='$bid'>$brand_name</a></li>
			";
		}
		echo "</div>";
	}
}
if(isset($_POST["page"])){
	$sql = "SELECT * FROM products";
	$run_query = mysqli_query($con,$sql);
	$count = mysqli_num_rows($run_query);
	$pageno = ceil($count/9);
	for($i=1;$i<=$pageno;$i++){
		echo "
			<li><a href='#' page='$i' id='page'>$i</a></li>
		";
	}
}
if(isset($_POST["getProduct"])){
	$limit = 9;
	if(isset($_POST["setPage"])){
		$pageno = $_POST["pageNumber"];
		$start = ($pageno * $limit) - $limit;
	}else{
		$start = 0;
	}
	$product_query = "SELECT * FROM products LIMIT $start,$limit";
	$run_query = mysqli_query($con,$product_query);
	if(mysqli_num_rows($run_query) > 0){
		while($row = mysqli_fetch_array($run_query)){
			$pro_id    = $row['product_id'];
			$pro_cat   = $row['product_cat'];
			$pro_brand = $row['product_brand'];
			$pro_title = $row['product_title'];
			$pro_price = $row['product_price'];
			$pro_image = $row['product_image'];
			echo "
				<div class='col-md-4'>
							<div class='panel panel-info'>
								<div class='panel-heading'>$pro_title</div>
								<div class='panel-body'>
									<img src='product_images/$pro_image' style='width:220px; height:250px;'/>
								</div>
								<div class='panel-heading'>".CURRENCY.". $pro_price.00/-
									<button pid='$pro_id' style='float:right;' id='product' class='btn btn-danger btn-xs'>Add To Cart</button>
								</div>
							</div>
						</div>	
			";
		}
	}
}
if(isset($_POST["get_seleted_Category"]) || isset($_POST["selectBrand"]) || isset($_POST["search"])){
	if(isset($_POST["get_seleted_Category"])){
		$id = $_POST["cat_id"];
		$sql = "SELECT * FROM products WHERE product_cat = '$id'";
	}else if(isset($_POST["selectBrand"])){
		$id = $_POST["brand_id"];
		$sql = "SELECT * FROM products WHERE product_brand = '$id'";
	}else {
		$keyword = $_POST["keyword"];
		$sql = "SELECT * FROM products WHERE product_keywords LIKE '%$keyword%'";
	}
	
	$run_query = mysqli_query($con,$sql);
	while($row=mysqli_fetch_array($run_query)){
			$pro_id    = $row['product_id'];
			$pro_cat   = $row['product_cat'];
			$pro_brand = $row['product_brand'];
			$pro_title = $row['product_title'];
			$pro_price = $row['product_price'];
			$pro_image = $row['product_image'];
			echo "
				<div class='col-md-4'>
							<div class='panel panel-info'>
								<div class='panel-heading'>$pro_title</div>
								<div class='panel-body'>
									<img src='product_images/$pro_image' style='width:220px; height:250px;'/>
								</div>
								<div class='panel-heading'>Rs.$pro_price.00/-
									<button pid='$pro_id' style='float:right;' id='product' class='btn btn-danger btn-xs'>Add To Cart</button>
								</div>
							</div>
						</div>	
			";
		}
	}
	


	if(isset($_POST["addToCart"])){
		

		$p_id = $_POST["proId"];
		

		if(isset($_SESSION["uid"])){

		$user_id = $_SESSION["uid"];

		$sql = "SELECT * FROM cart WHERE p_id = '$p_id' AND user_id = '$user_id'";
		$run_query = mysqli_query($con,$sql);
		$count = mysqli_num_rows($run_query);
		if($count > 0){
			// Update quantity instead of showing warning
			$sql = "UPDATE cart SET qty = qty + 1 WHERE p_id = '$p_id' AND user_id = '$user_id'";
			if(mysqli_query($con,$sql)){
				echo "
					<div class='alert alert-info'>
						<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
						<b>Product quantity increased in cart!</b>
					</div>
				";
			}
		} else {
			$sql = "INSERT INTO `cart`
			(`p_id`, `ip_add`, `user_id`, `qty`) 
			VALUES ('$p_id','$ip_add','$user_id','1')";
			if(mysqli_query($con,$sql)){
				echo "
					<div class='alert alert-success'>
						<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
						<b>Product is Added..!</b>
					</div>
				";
			}
		}
		}else{
			$sql = "SELECT id FROM cart WHERE ip_add = '$ip_add' AND p_id = '$p_id' AND user_id = -1";
			$query = mysqli_query($con,$sql);
			if (mysqli_num_rows($query) > 0) {
				echo "
					<div class='alert alert-warning'>
							<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
							<b>Product is already added into the cart Continue Shopping..!</b>
					</div>";
					exit();
			}
			$sql = "INSERT INTO `cart`
			(`p_id`, `ip_add`, `user_id`, `qty`) 
			VALUES ('$p_id','$ip_add','-1','1')";
			if (mysqli_query($con,$sql)) {
				echo "
					<div class='alert alert-success'>
						<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
						<b>Your product has been added to cart!</b>
					</div>
				";
				exit();
			}
			
		}
		
		
		
		
	}

//Count User cart item
if (isset($_POST["count_item"])) {
	//When user is logged in then we will count number of item in cart by using user session id
	if (isset($_SESSION["uid"])) {
		$user_id = $_SESSION["uid"];
		$sql = "SELECT COUNT(*) AS count_item FROM cart WHERE user_id = '$user_id'";
		echo "<!-- DEBUG: count_item - User ID: $user_id -->";
	}else{
		//When user is not logged in then we will count number of item in cart by using users unique ip address
		$sql = "SELECT COUNT(*) AS count_item FROM cart WHERE ip_add = '$ip_add' AND user_id < 0";
		echo "<!-- DEBUG: count_item - No user session, using IP: $ip_add -->";
	}
	
	$query = mysqli_query($con,$sql);
	$row = mysqli_fetch_array($query);
	$count = $row["count_item"];
	echo "<!-- DEBUG: count_item result: $count -->";
	echo $count;
	exit();
}
//Count User cart item

//Get Cart Item From Database to Dropdown menu
if (isset($_POST["Common"])) {

	if (isset($_SESSION["uid"])) {
		//When user is logged in this query will execute
		$sql = "SELECT a.product_id,a.product_title,a.product_price,a.product_image,b.id,b.qty FROM products a,cart b WHERE a.product_id=b.p_id AND b.user_id='$_SESSION[uid]'";
	}else{
		//When user is not logged in this query will execute
		$sql = "SELECT a.product_id,a.product_title,a.product_price,a.product_image,b.id,b.qty FROM products a,cart b WHERE a.product_id=b.p_id AND b.ip_add='$ip_add' AND b.user_id < 0";
	}
	$query = mysqli_query($con,$sql);
	if (isset($_POST["getCartItem"])) {
		//display cart item in dropdown menu with edit/delete functionality
		if (mysqli_num_rows($query) > 0) {
			echo '<style>
				.cart-modal-content::-webkit-scrollbar {
					width: 12px;
				}
				.cart-modal-content::-webkit-scrollbar-track {
					background: #f1f1f1;
					border-radius: 6px;
				}
				.cart-modal-content::-webkit-scrollbar-thumb {
					background: #007bff;
					border-radius: 6px;
					border: 2px solid #f1f1f1;
				}
				.cart-modal-content::-webkit-scrollbar-thumb:hover {
					background: #0056b3;
				}
			</style>';
			echo '<div class="cart-modal-content" style="padding: 0; max-width: 1000px; max-height: 80vh; overflow-y: auto; min-width: 950px;">';
			
			// Calculate total first
			$total_amount = 0;
			$temp_query = mysqli_query($con,$sql);
			while ($temp_row=mysqli_fetch_array($temp_query)) {
				$temp_price = $temp_row["product_price"];
				$temp_qty = $temp_row["qty"];
				$total_amount += ($temp_price * $temp_qty);
			}
			
			// Display header with total inline
			echo '<div style="display: flex; justify-content: space-between; align-items: center; margin: 20px; margin-bottom: 30px; border-bottom: 3px solid #007bff; padding-bottom: 15px;">
					<h4 style="margin: 0; color: #333; font-size: 24px;">
						<span class="glyphicon glyphicon-shopping-cart"></span> Cart Checkout
					</h4>
					<b class="net_total" style="font-size: 20px; color: #28a745; background: #f8f9fa; padding: 8px 15px; border-radius: 5px; border: 2px solid #28a745;">
						Total: '.CURRENCY.' '.$total_amount.'
					</b>
				</div>';
			
			echo '<div class="row" style="background: #f8f9fa; padding: 15px; margin: 0 20px 20px 20px; border-radius: 4px; font-weight: 600; color: #495057;">
							<div class="col-md-2 col-xs-2"><b>Product Image</b></div>
							<div class="col-md-2 col-xs-2"><b>Product Name</b></div>
							<div class="col-md-2 col-xs-2"><b>Quantity</b></div>
							<div class="col-md-2 col-xs-2"><b>Unit Price</b></div>
							<div class="col-md-2 col-xs-2"><b>Total Price</b></div>
							<div class="col-md-2 col-xs-2"><b>Actions</b></div>
						</div>';
			
			$total_amount = 0;
			$item_count = 0;
			while ($row=mysqli_fetch_array($query)) {
				$item_count++;
				$product_id = $row["product_id"];
				$product_title = $row["product_title"];
				$product_price = $row["product_price"];
				$product_image = $row["product_image"];
				$cart_item_id = $row["id"];
				$qty = $row["qty"];
				$item_total = $product_price * $qty;
				$total_amount += $item_total;
				
				echo '
				
					<div class="row cart-item-row" style="margin: 0 20px 25px 20px; padding-top: 25px; padding-bottom: 25px; border: 1px solid #e0e0e0; border-radius: 15px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s ease;">
						<div class="col-md-2" style="width: 15%;">
							<img class="img-responsive" src="product_images/'.$product_image.'" style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px; box-shadow: 0 3px 6px rgba(0,0,0,0.15);">
						</div>
						<div class="col-md-2" style="width: 20%;">
							<h5 style="margin: 0 0 5px 0; font-weight: 700; color: #333; font-size: 18px; line-height: 1.3;">'.$product_title.'</h5>
							<small class="text-muted" style="color: #6c757d; font-size: 14px;">Product ID: '.$product_id.'</small>
						</div>
						<div class="col-md-2" style="width: 10%;">
							<div class="text-center" style="padding: 6px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
								<input type="number" class="form-control qty text-center" value="'.$qty.'" min="1" style="width: 100%; font-weight: 700; font-size: 16px; padding: 8px; border-radius: 6px; border: none; background: transparent; text-align: center; box-shadow: none;">
							</div>
						</div>
						<div class="col-md-2" style="width: 15%;">
							<div class="text-center" style="padding: 12px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
								<span class="price-display" style="font-weight: 700; color: #495057; font-size: 16px;">'.CURRENCY.' '.$product_price.'</span>
								<input type="hidden" class="price" value="'.$product_price.'">
							</div>
						</div>
						<div class="col-md-2" style="width: 15%;">
							<div class="text-center" style="padding: 12px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 8px; color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
								<span class="total-display" style="font-weight: 700; font-size: 16px;">'.CURRENCY.' '.$item_total.'</span>
								<input type="hidden" class="total" value="'.$item_total.'">
							</div>
						</div>
						<div class="col-md-2" style="text-align: center; width: 25%;">
							<div style="display: flex; flex-direction: row; gap: 8px; align-items: center; justify-content: center;">
								<button type="button" remove_id="'.$product_id.'" class="btn btn-danger btn-sm remove" style="border-radius: 6px; font-weight: 600; transition: all 0.3s ease; padding: 8px 12px; font-size: 12px; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
									<span class="glyphicon glyphicon-trash"></span> Delete
								</button>
								<button type="button" update_id="'.$product_id.'" class="btn btn-primary btn-sm update" style="border-radius: 6px; font-weight: 600; transition: all 0.3s ease; padding: 8px 12px; font-size: 12px; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
									<span class="glyphicon glyphicon-ok-sign"></span> Update
								</button>
							</div>
						</div>
					</div>';
			}
			
			echo '<div style="margin: 40px 20px 20px 20px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 2px solid #e9ecef;">
					<a href="checkout.php" class="btn btn-success btn-lg" style="border-radius: 12px; font-weight: 700; padding: 18px 50px; box-shadow: 0 6px 12px rgba(0,0,0,0.15); transition: all 0.3s ease; font-size: 18px; text-decoration: none; display: inline-block;">
						<span class="glyphicon glyphicon-credit-card"></span> Make Payment
					</a>
				  </div>';
			echo '</div>';
			exit();
		} else {
			echo '<div class="cart-modal-content" style="padding: 30px; text-align: center; max-width: 400px;">
					<span class="glyphicon glyphicon-shopping-cart" style="font-size: 64px; color: #ccc; display: block; margin-bottom: 20px;"></span>
					<h4 style="color: #666; margin: 0 0 20px 0;">Your cart is empty</h4>
					<a href="index.php" class="btn btn-primary btn-lg" style="border-radius: 8px; padding: 12px 25px;">Start Shopping</a>
				  </div>';
			exit();
		}
	}
	// if (isset($_POST["checkOutDetails"])) {
	// 	if (mysqli_num_rows($query) > 0) {
	// 		//display user cart item with checkout details only (no edit/delete)
	// 		echo "<form method='post' action='login_form.php'>";
	// 			$n=0;
	// 			while ($row=mysqli_fetch_array($query)) {
	// 				$n++;
	// 				$product_id = $row["product_id"];
	// 				$product_title = $row["product_title"];
	// 				$product_price = $row["product_price"];
	// 				$product_image = $row["product_image"];
	// 				$cart_item_id = $row["id"];
	// 				$qty = $row["qty"];

	// 				echo 
	// 					'<div class="row cart-item-row" style="margin-bottom: 15px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fafafa;">
	// 							<input type="hidden" name="product_id[]" value="'.$product_id.'"/>
	// 							<input type="hidden" name="" value="'.$cart_item_id.'"/>
	// 							<div class="col-md-2">
	// 								<img class="img-responsive" src="product_images/'.$product_image.'" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
	// 							</div>
	// 							<div class="col-md-3">
	// 								<h5 style="margin: 0; font-weight: 600; color: #333;">'.$product_title.'</h5>
	// 							</div>
	// 							<div class="col-md-2">
	// 								<span style="font-weight: 600; color: #666; font-size: 16px;">Qty: '.$qty.'</span>
	// 							</div>
	// 							<div class="col-md-2">
	// 								<span style="font-weight: 600; color: #666; font-size: 16px;">'.CURRENCY.' '.$product_price.'</span>
	// 							</div>
	// 							<div class="col-md-3">
	// 								<span style="font-weight: 700; color: #2c3e50; font-size: 18px;">'.CURRENCY.' '.($product_price * $qty).'</span>
	// 							</div>
	// 						</div>';
	// 			}
				
	// 			echo '<div class="row">
	// 						<div class="col-md-8"></div>
	// 						<div class="col-md-4">
	// 							<div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
	// 								<div class="net_total" style="font-size: 24px; font-weight: 700; margin: 0;"></div>
	// 								<small style="opacity: 0.9; font-size: 14px;">Including all taxes</small>
	// 							</div>
	// 				</div>';
	// 			if (!isset($_SESSION["uid"])) {
	// 				echo '<input type="submit" style="float:right;" name="login_user_with_product" class="btn btn-info btn-lg" value="Ready to Checkout" >
	// 						</form>';
					
	// 			}
	// 		}
	// }
	
	
}

//Remove Item From cart
if (isset($_POST["removeItemFromCart"])) {
	$remove_id = $_POST["rid"];
	if (isset($_SESSION["uid"])) {
		$sql = "DELETE FROM cart WHERE p_id = '$remove_id' AND user_id = '$_SESSION[uid]'";
	}else{
		$sql = "DELETE FROM cart WHERE p_id = '$remove_id' AND ip_add = '$ip_add'";
	}
	if(mysqli_query($con,$sql)){
		echo "success";
		exit();
	}
}


//Update Item From cart
if (isset($_POST["updateCartItem"])) {
	$update_id = $_POST["update_id"];
	$qty = $_POST["qty"];
	if (isset($_SESSION["uid"])) {
		$sql = "UPDATE cart SET qty='$qty' WHERE p_id = '$update_id' AND user_id = '$_SESSION[uid]'";
	}else{
		$sql = "UPDATE cart SET qty='$qty' WHERE p_id = '$update_id' AND ip_add = '$ip_add'";
	}
	if(mysqli_query($con,$sql)){
		echo "success";
		exit();
	}
}


//Get Cart Total for Header
if (isset($_POST["getCartTotal"])) {
	if (isset($_SESSION["uid"])) {
		$sql = "SELECT a.product_price, b.qty FROM products a, cart b WHERE a.product_id=b.p_id AND b.user_id='$_SESSION[uid]'";
	}else{
		$ip_add = $session_id;
		$sql = "SELECT a.product_price, b.qty FROM products a, cart b WHERE a.product_id=b.p_id AND b.ip_add='$ip_add' AND b.user_id < 0";
	}
	$query = mysqli_query($con,$sql);
	$total_amount = 0;
	while($row=mysqli_fetch_array($query)){
		$total_amount += ($row["product_price"] * $row["qty"]);
	}
	echo CURRENCY . " " . $total_amount;
	exit();
}

?>






