$(document).ready(function(){
	var productList;

	function getProducts(){
		$.ajax({
			url : '../admin/classes/Products.php',
			method : 'POST',
			data : {GET_PRODUCT:1},
			dataType : 'json',
			success : function(response){
				var resp = response; // Response is already parsed due to dataType: 'json'
				if (resp.status == 202) {

					var productHTML = '';

					productList = resp.message.products;

					if (productList) {
						$.each(resp.message.products, function(index, value){

							productHTML += '<tr>'+
								              '<td>'+''+'</td>'+
								              '<td>'+ value.product_title +'</td>'+
								              '<td><img width="60" height="60" src="../product_images/'+value.product_image+'"></td>'+
								              '<td>'+ value.product_price +'</td>'+
								              '<td>'+ value.product_qty +'</td>'+
								              '<td>'+ value.cat_title +'</td>'+
								              '<td>'+ value.brand_title +'</td>'+
								              '<td><a class="btn btn-sm btn-info edit-product" style="color:#fff;"><span style="display:none;">'+JSON.stringify(value)+'</span><i class="fas fa-pencil-alt"></i></a>&nbsp;<a pid="'+value.product_id+'" class="btn btn-sm btn-danger delete-product" style="color:#fff;"><i class="fas fa-trash-alt"></i></a></td>'+
								            '</tr>';

						});

						$("#product_list").html(productHTML);
					}

					


					var catSelectHTML = '<option value="">Select Category</option>';
					$.each(resp.message.categories, function(index, value){

						catSelectHTML += '<option value="'+ value.cat_id +'">'+ value.cat_title +'</option>';

					});

					$(".category_list").html(catSelectHTML);

					var brandSelectHTML = '<option value="">Select Brand</option>';
					$.each(resp.message.brands, function(index, value){

						brandSelectHTML += '<option value="'+ value.brand_id +'">'+ value.brand_title +'</option>';

					});

					$(".brand_list").html(brandSelectHTML);

				}
			}

		});
	}

	getProducts();

	// Test function to close modal (can be called from console)
	window.testModalClose = function() {
		$("#add_product_modal").modal('hide');
	};

	// Test function to manually close modal
	window.forceCloseModal = function() {
		alert('Force closing modal...');
		$("#add_product_modal").modal('hide');
		alert('Modal should be closed now');
	};

	// Test function to manually trigger add product
	window.testAddProduct = function() {
		alert('Manually triggering add product...');
		handleAddProduct();
	};

	// Test function to check if everything is working
	window.testEverything = function() {
		alert('Check console for test results');
	};

	// Test function to check if button exists
	window.testButtonExists = function() {
		var button = $('.add-product');
		return button.length > 0;
	};

	// Function to handle add product
	function handleAddProduct() {
		
		$.ajax({
			url : '../admin/classes/Products.php',
			method : 'POST',
			data : new FormData($("#add-product-form")[0]),
			contentType : false,
			cache : false,
			processData : false,
			dataType : 'json', // Specify that we expect JSON response
			success : function(response){
				
				if (response.status == 202) {
					// Close modal and refresh product list
					$("#add-product-form").trigger("reset");
					$("#add_product_modal").modal('hide');
					getProducts();
					alert('Product added successfully!');
				} else if (response.status == 303) {
					alert('Error: ' + response.message);
				} else {
					alert('Unexpected response status: ' + response.status);
				}
			},
			error: function(xhr, status, error) {
				alert('Error adding product. Please try again.');
			}

		});
	}

	// Event handler for add product button
	$(document).on("click", ".add-product", function(e){
		e.preventDefault();
		handleAddProduct();
	});

	$(document.body).on('click', '.edit-product', function(){

		var product = JSON.parse($.trim($(this).find('span').text()));

		$("input[name='e_product_name']").val(product.product_title);
		$("select[name='e_brand_id']").val(product.brand_id);
		$("select[name='e_category_id']").val(product.cat_id);
		$("textarea[name='e_product_desc']").val(product.product_desc);
		$("input[name='e_product_qty']").val(product.product_qty);
		$("input[name='e_product_price']").val(product.product_price);
		$("input[name='e_product_keywords']").val(product.product_keywords);
		$("input[name='e_product_image']").siblings("img").attr("src", "../product_images/"+product.product_image);
		$("input[name='pid']").val(product.product_id);
		$("#edit_product_modal").modal('show');

	});

	$(".submit-edit-product").on('click', function(){

		$.ajax({

			url : '../admin/classes/Products.php',
			method : 'POST',
			data : new FormData($("#edit-product-form")[0]),
			contentType : false,
			cache : false,
			processData : false,
			dataType : 'json',
			success : function(response){
				var resp = response; // Response is already parsed due to dataType: 'json'
				if (resp.status == 202) {
					$("#edit-product-form").trigger("reset");
					$("#edit_product_modal").modal('hide');
					getProducts();
					alert(resp.message);
					window.location.href = "products.php";
				}else if(resp.status == 303){
					alert(resp.message);
				}
			}

		});


	});

	$(document.body).on('click', '.delete-product', function(){

		var pid = $(this).attr('pid');
		if (confirm("Are you sure to delete this item ?")) {
			$.ajax({

				url : '../admin/classes/Products.php',
				method : 'POST',
				data : {DELETE_PRODUCT: 1, pid:pid},
				dataType : 'json',
				success : function(response){
					var resp = response; // Response is already parsed due to dataType: 'json'
					if (resp.status == 202) {
						getProducts();
					}else if (resp.status == 303) {
						alert(resp.message);
					}
				}

			});
		}else{
			alert('Cancelled');
		}
		

	});

});