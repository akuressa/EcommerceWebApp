

$(document).ready(function(){
	try {
		getCustomers();
		getCustomerOrders();
	} catch (error) {
		// Error handling
	}

	function getCustomers(){
		try {
			$.ajax({
				url : '../admin/classes/Customers.php',
				method : 'POST',
				data : {GET_CUSTOMERS:1},
				success : function(response){
					var resp = $.parseJSON(response);
					if (resp.status == 202) {

						var customersHTML = "";

						$.each(resp.message, function(index, value){

							customersHTML += '<tr>'+
											          '<td>#</td>'+
											          '<td>'+value.first_name+' '+value.last_name+'</td>'+
											          '<td>'+value.email+'</td>'+
											          '<td>'+value.mobile+'</td>'+
											          '<td>'+value.address1+'<br>'+value.address2+'</td>'+
											       '</tr>'

						});

						$("#customer_list").html(customersHTML);

					}else if(resp.status == 303){

					}

				},
			error: function(xhr, status, error) {
				// Error handling
			}
			});
		} catch (error) {
			// Error handling
		}
	}

	function getCustomerOrders(){
		try {
			$.ajax({
				url : '../admin/classes/Customers.php',
				method : 'POST',
				data : {GET_CUSTOMER_ORDERS:1},
				success : function(response){
				
				var resp = $.parseJSON(response);
				if (resp.status == 202) {

					var customerOrderHTML = "";

					try {
						$.each(resp.message, function(index, value){
						
						// Ensure we have all required data
						var firstName = value.first_name || 'Unknown';
						var lastName = value.last_name || 'Customer';
						var email = value.email || 'No email';
						var paymentMethod = value.payment_method || 'Unknown';
						var receiptFile = value.receipt_file || '';
						var status = value.p_status || 'Pending';
						
						var statusBadge = getStatusBadge(status);
						
						var actionButtons = getActionButtons(value.order_id, paymentMethod, receiptFile, status);

						customerOrderHTML +='<tr>'+
								              '<td>'+(index+1)+'</td>'+
								              '<td>'+ value.order_id +'</td>'+
								              '<td>'+ firstName +' '+ lastName +'<br><small class="text-muted">'+ email +'</small></td>'+
								              '<td>'+ value.product_title +'</td>'+
								              '<td>'+ value.qty +'</td>'+
								              '<td>'+ paymentMethod +'</td>'+
								              '<td>'+ statusBadge +'</td>'+
								              '<td>'+ actionButtons +'</td>'+
								            '</tr>';

						});
						
					} catch (error) {
						// Error handling
					}

					
					// Add a small delay to ensure DOM is ready
					setTimeout(function() {	
						$("#customer_order_list").html(customerOrderHTML);
						
						// Verify the update worked
						var rowCount = $("#customer_order_list tr").length;
						
						// Log the actual table content
					}, 100);

				}else if(resp.status == 303){
					$("#customer_order_list").html(resp.message);
				}

				},
			error: function(xhr, status, error) {
				// Error handling
			}
			});
		} catch (error) {
			// Error handling
		}
	}

	// Helper function to get status badge
	function getStatusBadge(status) {
		try {
			var badgeClass = 'badge-secondary';
			switch(status) {
				case 'Pending':
					badgeClass = 'badge-warning';
					break;
				case 'Payment Completed':
					badgeClass = 'badge-info';
					break;
				case 'Delivered':
					badgeClass = 'badge-success';
					break;
				case 'Rejected':
				case 'Delete':
					badgeClass = 'badge-danger';
					break;
			}
			var result = '<span class="badge ' + badgeClass + '">' + status + '</span>';
			return result;
		} catch (error) {
			return '<span class="badge badge-secondary">Error</span>';
		}
	}

	// Helper function to get action buttons
	function getActionButtons(orderId, paymentMethod, receiptFile, currentStatus) {
		try {
			var buttons = '<div class="btn-group" role="group">';
			
			// Status update button
			buttons += '<button type="button" class="btn btn-sm btn-outline-primary" onclick="openStatusModal(' + orderId + ', \'' + currentStatus + '\')" title="Update Status">';
			buttons += 'Edit';
			buttons += '</button>';
			
			// Receipt viewer button (only for receipt_upload payment method)
			if (paymentMethod === 'receipt_upload' && receiptFile) {
				buttons += '<button type="button" class="btn btn-sm btn-outline-info" onclick="viewReceipt(\'' + receiptFile + '\')" title="View Receipt">';
				buttons += 'Receipt';
				buttons += '</button>';
			}
			
			buttons += '</div>';
			return buttons;
		} catch (error) {
			return '<div class="btn-group"><button class="btn btn-sm btn-danger">Error</button></div>';
		}
	}

	// Open status update modal
	window.openStatusModal = function(orderId, currentStatus) {
		$('#order_id_input').val(orderId);
		$('#status_select').val(currentStatus);
		$('#status_modal').modal('show');
	};

	// View receipt modal
	window.viewReceipt = function(receiptFile) {
		var receiptPath = '../' + receiptFile;
		var fileExtension = receiptFile.split('.').pop().toLowerCase();
		
		var content = '';
		if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
			content = '<img src="' + receiptPath + '" class="img-fluid" alt="Payment Receipt" style="max-height: 500px;">';
		} else if (fileExtension === 'pdf') {
			content = '<iframe src="' + receiptPath + '" width="100%" height="500px" style="border: none;"></iframe>';
		} else {
			content = '<p>Unsupported file format. <a href="' + receiptPath + '" target="_blank">Download file</a></p>';
		}
		
		$('#receipt_content').html(content);
		$('#receipt_modal').modal('show');
	};

	// Update order status
	$('#update_status_btn').on('click', function() {
		var orderId = $('#order_id_input').val();
		var newStatus = $('#status_select').val();
		
		$.ajax({
			url: '../admin/classes/Customers.php',
			method: 'POST',
			data: {
				UPDATE_ORDER_STATUS: 1,
				order_id: orderId,
				new_status: newStatus
			},
			success: function(response) {
				var resp = $.parseJSON(response);
				if (resp.status == 202) {
					$('#status_modal').modal('hide');
					// Refresh the order list
					getCustomerOrders();
					// Show success message					
				} else {
					alert('Error: ' + resp.message);
				}
			},
			error: function() {
				alert('Error updating order status. Please try again.');
			}
		});
	});

	// Test function to verify JavaScript is working
	window.testOrderManagement = function() {
		alert('Order management functions are loaded!');
		
		// Test table update
		var testHTML = '<tr><td>1</td><td>TEST</td><td>Test Customer</td><td>Test Product</td><td>1</td><td>TEST123</td><td>cod</td><td><span class="badge badge-warning">Pending</span></td><td><button class="btn btn-sm btn-primary">Edit</button></td></tr>';
		$("#customer_order_list").html(testHTML);
	};

});