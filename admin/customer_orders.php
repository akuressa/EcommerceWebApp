<?php session_start(); ?>
<?php include_once("./templates/top.php"); ?>
<?php include_once("./templates/navbar.php"); ?>
<div class="container-fluid">
  <div class="row">
    
    <?php include "./templates/sidebar.php"; ?>

      <div class="row">
      	<div class="col-8">
      		<h2> Orders</h2>
      	</div>
      	<div class="col-4 text-right">
      		<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_order_modal">
      			<i class="fa fa-plus"></i> Add Order
      		</button>
      	</div>
      </div>
      
      <!-- Message Container -->
      <div id="message-container" style="margin-top: 15px;"></div>
      
      <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
        <table class="table table-striped table-sm" style="min-width: 800px; width: 100%;">
          <thead>
            <tr>
              <th style="width: 5%;">#</th>
              <th style="width: 8%;">Order #</th>
              <th style="width: 15%;">Customer</th>
              <th style="width: 20%;">Product Name</th>
              <th style="width: 8%;">Quantity</th>
              <!-- <th style="width: 20%;">Trx Id</th> -->
              <th style="width: 14%;">Payment Method</th>
              <th style="width: 15%;">Status</th>
              <th style="width: 15%;">Actions</th>
            </tr>
          </thead>
          <tbody id="customer_order_list">
           
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>



<!-- Modal -->
<div class="modal fade" id="add_product_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Product</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="add-product-form" enctype="multipart/form-data">
        	<div class="row">
        		<div class="col-12">
        			<div class="form-group">
		        		<label>Product Name</label>
		        		<input type="text" name="product_name" class="form-control" placeholder="Enter Product Name">
		        	</div>
        		</div>
        		<div class="col-12">
        			<div class="form-group">
		        		<label>Brand Name</label>
		        		<select class="form-control brand_list" name="brand_id">
		        			<option value="">Select Brand</option>
		        		</select>
		        	</div>
        		</div>
        		<div class="col-12">
        			<div class="form-group">
		        		<label>Category Name</label>
		        		<select class="form-control category_list" name="category_id">
		        			<option value="">Select Category</option>
		        		</select>
		        	</div>
        		</div>
        		<div class="col-12">
        			<div class="form-group">
		        		<label>Product Description</label>
		        		<textarea class="form-control" name="product_desc" placeholder="Enter product desc"></textarea>
		        	</div>
        		</div>
        		<div class="col-12">
        			<div class="form-group">
		        		<label>Product Price</label>
		        		<input type="number" name="product_price" class="form-control" placeholder="Enter Product Price">
		        	</div>
        		</div>
        		<div class="col-12">
        			<div class="form-group">
		        		<label>Product Keywords <small>(eg: apple, iphone, mobile)</small></label>
		        		<input type="text" name="product_keywords" class="form-control" placeholder="Enter Product Keywords">
		        	</div>
        		</div>
        		<div class="col-12">
        			<div class="form-group">
		        		<label>Product Image <small>(format: jpg, jpeg, png)</small></label>
		        		<input type="file" name="product_image" class="form-control">
		        	</div>
        		</div>
        		<input type="hidden" name="add_product" value="1">
        		<div class="col-12">
        			<button type="button" class="btn btn-primary add-product">Add Product</button>
        		</div>
        	</div>
        	
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Receipt Viewer Modal -->
<div class="modal fade" id="receipt_modal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="receiptModalLabel">Payment Receipt</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <div id="receipt_content">
          <!-- Receipt content will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="status_modal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="statusModalLabel">Update Order Status</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="status-update-form">
          <div class="form-group">
            <label for="status_select"></label>
            <select class="form-control" id="status_select" name="new_status" required>
              <option value="Pending">Pending</option>
              <option value="Payment Completed">Payment Completed</option>
              <option value="Delivered">Delivered</option>
              <option value="Rejected">Rejected</option>
              <option value="Cancelled">Delete</option>
            </select>
          </div>
          <input type="hidden" id="order_id_input" name="order_id">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="update_status_btn">Update Status</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="add_order_modal" tabindex="-1" role="dialog" aria-labelledby="addOrderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addOrderModalLabel">Add New Order</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="add-order-form" enctype="multipart/form-data">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="customer_select">Customer *</label>
                <select class="form-control" id="customer_select" name="customer_id" required>
                  <option value="">Select Customer</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="product_select">Product *</label>
                <select class="form-control" id="product_select" name="product_id" required>
                  <option value="">Select Product</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="payment_method">Payment Method *</label>
                <select class="form-control" id="payment_method" name="payment_method" required>
                  <option value="">Select Payment Method</option>
                  <option value="cod">Cash on Delivery</option>
                  <option value="receipt_upload">Receipt Upload</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="order_status">Order Status *</label>
                <select class="form-control" id="order_status" name="order_status" required>
                  <option value="Pending">Pending</option>
                  <option value="Payment Completed">Payment Completed</option>
                  <option value="Delivered">Delivered</option>
                  <option value="Rejected">Rejected</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="transaction_id">Transaction ID</label>
                <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="Auto-generated if empty">
              </div>
            </div>
          </div>
          <div class="row" id="receipt_upload_section" style="display: none;">
            <div class="col-12">
              <div class="form-group">
                <label for="receipt_file">Receipt File</label>
                <input type="file" class="form-control" id="receipt_file" name="receipt_file" accept="image/*,.pdf">
                <small class="form-text text-muted">Upload receipt image or PDF file</small>
              </div>
            </div>
          </div>
          <input type="hidden" name="add_order" value="1">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary add-order">Add Order</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->

<?php include_once("./templates/footer.php"); ?>



<style>
.dropdown-menu {
    position: absolute !important;
    z-index: 99999 !important;
    background: white !important;
    border: 1px solid #ccc !important;
    border-radius: 4px !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
    min-width: 180px !important;
    width: 180px !important;
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
    top: 100% !important;
    left: 0 !important;
    margin-top: 2px !important;
}

.dropdown-menu li {
    display: block !important;
    width: 100% !important;
}

.dropdown-menu li a {
    display: block !important;
    width: 100% !important;
    clear: both !important;
    white-space: nowrap !important;
}

.dropdown-menu li a:hover {
    background-color: #f5f5f5 !important;
    color: #333 !important;
}

.dropdown-menu .divider {
    height: 1px !important;
    margin: 5px 0 !important;
    background: #e5e5e5 !important;
    border-top: 1px solid #e5e5e5 !important;
    display: block !important;
    width: 100% !important;
}

.badge {
    padding: 8px 12px !important;
    font-size: 12px !important;
    border-radius: 4px !important;
    font-weight: 500 !important;
}

.btn-group {
    position: relative !important;
    display: inline-block !important;
}

.table-responsive {
    overflow-x: auto !important;
    max-width: 100% !important;
}

.table {
    table-layout: fixed !important;
    word-wrap: break-word !important;
}

.table td, .table th {
    word-wrap: break-word !important;
    overflow: visible !important;
    text-overflow: ellipsis !important;
}

.table-responsive {
    overflow: visible !important;
}

.table {
    overflow: visible !important;
}

</style>

<script type="text/javascript" src="./js/customers_simple.js?v=<?php echo time(); ?>"></script>

<script type="text/javascript">
$(document).ready(function(){
    // Load customers and products for Add Order modal
    loadCustomersForOrder();
    loadProductsForOrder();
    
    // Show/hide receipt upload section based on payment method
    $('#payment_method').change(function() {
        if ($(this).val() === 'receipt_upload') {
            $('#receipt_upload_section').show();
        } else {
            $('#receipt_upload_section').hide();
        }
    });
    
    // Add Order button click handler
    $('.add-order').click(function() {
        addNewOrder();
    });
    
    // Function to show success message
    function showSuccessMessage(message) {
        var messageHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
            '<i class="fa fa-check-circle"></i> ' + message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>';
        
        $('#message-container').html(messageHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    // Function to show error message
    function showErrorMessage(message) {
        var messageHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
            '<i class="fa fa-exclamation-circle"></i> ' + message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>';
        
        $('#message-container').html(messageHtml);
        
        // Auto-hide after 7 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 7000);
    }
    
    // Function to load customers for Add Order modal
    function loadCustomersForOrder() {
        $.ajax({
            url: '../admin/classes/Customers.php',
            method: 'POST',
            data: {GET_CUSTOMERS: 1},
            success: function(response) {
     
                var resp = $.parseJSON(response);
           
                if (resp.status == 202) {
                    var customerSelect = $('#customer_select');
                    customerSelect.empty().append('<option value="">Select Customer</option>');
                    
                    $.each(resp.message, function(index, customer) {
           
                        customerSelect.append('<option value="' + customer.user_id + '">' + 
                            customer.first_name + ' ' + customer.last_name + ' (' + customer.email + ')</option>');
                    });
                }
            },
            error: function(xhr, status, error) {
            
            }
        });
    }
    
    // Function to load products for Add Order modal
    function loadProductsForOrder() {
        $.ajax({
            url: '../admin/classes/Products.php',
            method: 'POST',
            data: {GET_PRODUCTS: 1},
            success: function(response) {
        
                var resp = $.parseJSON(response);
           
                if (resp.status == 202) {
                    var productSelect = $('#product_select');
                    productSelect.empty().append('<option value="">Select Product</option>');
                    
                    // Access products from the correct data structure
                    var products = resp.message.products || [];
          
                    $.each(products, function(index, product) {
              
                        productSelect.append('<option value="' + product.product_id + '">' + 
                            product.product_title + ' - $' + product.product_price + '</option>');
                    });
                }
            },
            error: function(xhr, status, error) {
               
            }
        });
    }
    
    // Function to add new order
    function addNewOrder() {
        var form = $('#add-order-form');
        var formData = new FormData(form[0]);
        
        // Validate required fields
        if (!$('#customer_select').val() || !$('#product_select').val() || !$('#quantity').val() || !$('#payment_method').val()) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Generate transaction ID if not provided
        if (!$('#transaction_id').val()) {
            var timestamp = Date.now();
            var randomNum = Math.floor(Math.random() * 10000);
            formData.set('transaction_id', 'TXN_' + timestamp + '_' + randomNum);
        }
        
        $.ajax({
            url: '../admin/classes/Customers.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var resp = $.parseJSON(response);
                if (resp.status == 202) {
                    // Show success message
                    showSuccessMessage('Order placed successfully!');
                    
                    // Close modal and reset form
                    $('#add_order_modal').modal('hide');
                    form[0].reset();
                    $('#receipt_upload_section').hide();
                    
                    // Auto-refresh the orders list
                    setTimeout(function() {
             
                        if (typeof getCustomerOrders === 'function') {
             
                            getCustomerOrders();
                        } else {
              
                            location.reload();
                        }
                    }, 500);
                } else {
                    showErrorMessage('Error: ' + resp.message);
                }
            },
            error: function() {
                showErrorMessage('Error adding order. Please try again.');
            }
        });
    }
});
</script>