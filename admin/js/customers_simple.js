
$(document).ready(function(){
    
    // Call the function
    getCustomerOrders();
});

// Make getCustomerOrders globally accessible
function getCustomerOrders(){
        
        $.ajax({
            url : '../admin/classes/Customers.php',
            method : 'POST',
            data : {GET_CUSTOMER_ORDERS:1},
            success : function(response){
                
                var resp = $.parseJSON(response);
                
                if (resp.status == 202) {
                    
                    var customerOrderHTML = "";
                    
                    $.each(resp.message, function(index, value){
                        
                        // Create status badge
                        var statusBadge = getStatusBadge(value.p_status);
                        
                        // Create action dropdown
                        var actionDropdown = createActionDropdown(value.order_id, value.p_status, value.payment_method, value.receipt_file);
                        
                        customerOrderHTML += '<tr data-receipt-file="'+ (value.receipt_file || '') +'">'+
                            '<td>'+(index+1)+'</td>'+
                            '<td>'+ value.order_id +'</td>'+
                            '<td>'+ value.first_name +' '+ value.last_name +'</td>'+
                            '<td>'+ value.product_title +'</td>'+
                            '<td>'+ value.qty +'</td>'+
                            '<td>'+ value.payment_method +'</td>'+
                            '<td id="status_'+value.order_id+'">'+ statusBadge +'</td>'+
                            '<td>'+ actionDropdown +'</td>'+
                        '</tr>';
                    });
                    
                    $("#customer_order_list").html(customerOrderHTML);
                }
            },
            error: function(xhr, status, error) {
            }
        });
    }
    
    // Helper function to create status badge
    function getStatusBadge(status) {
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
            case 'Deleted':
                badgeClass = 'badge-danger';
                break;
        }
        return '<span class="badge ' + badgeClass + '" style="padding: 8px 12px; font-size: 12px; border-radius: 4px;">' + status + '</span>';
    }
    
    // Helper function to create action dropdown
    function createActionDropdown(orderId, currentStatus, paymentMethod, receiptFile) {
        var dropdown = '<div class="btn-group" id="dropdown_' + orderId + '" style="position: relative;">';
        dropdown += '<button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" onclick="toggleDropdown(' + orderId + ')" aria-haspopup="true" aria-expanded="false">';
        dropdown += 'Actions <span class="caret"></span>';
        dropdown += '</button>';
        dropdown += '<ul class="dropdown-menu" id="menu_' + orderId + '" style="display: none; position: absolute; z-index: 99999; background: white; border: 1px solid #ccc; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); min-width: 150px; top: 100%; left: 0; margin-top: 2px; width: 150px;">';
        
        // Receipt viewer option (only for receipt_upload payment method) - FIRST ITEM
        if (paymentMethod === 'receipt_upload' && receiptFile) {
            dropdown += '<li style="display: block; width: 100%;"><a href="#" onclick="viewReceipt(\'' + receiptFile + '\'); hideDropdown(' + orderId + '); return false;" style="display: block; padding: 8px 15px; color: #333; text-decoration: none; width: 100%;">View Receipt</a></li>';
            dropdown += '<li class="divider" style="height: 1px; margin: 5px 0; background: #e5e5e5; display: block; width: 100%; border-top: 1px solid #e5e5e5;"></li>';
        }
        
        // Status change options (excluding current status)
        var statuses = ['Payment Completed', 'Delivered', 'Rejected', 'Deleted'];
        if (currentStatus === 'Pending') {
            statuses = ['Payment Completed','Delivered', 'Rejected', 'Deleted'];
        }
        if (currentStatus === 'Payment Completed') {
            statuses = ['Delivered', 'Rejected', 'Deleted'];
        }
        if (currentStatus === 'Delivered') {
            statuses = [ 'Deleted'];
        }
        if (currentStatus === 'Rejected') {
            statuses = ['Deleted'];
        }
        if (currentStatus === 'Deleted') {
            statuses = [];
        }
        statuses.forEach(function(status) {
            if (status !== currentStatus) {
                dropdown += '<li style="display: block; width: 100%;"><a href="#" onclick="changeOrderStatus(' + orderId + ', \'' + status + '\'); hideDropdown(' + orderId + '); return false;" style="display: block; padding: 8px 15px; color: #333; text-decoration: none; width: 100%;">' + status + '</a></li>';
                dropdown += '<li class="divider" style="height: 1px; margin: 5px 0; background: #e5e5e5; display: block; width: 100%; border-top: 1px solid #e5e5e5;"></li>';
            }
        });
        
        dropdown += '</ul>';
        dropdown += '</div>';
        
        return dropdown;
    }
    
// Function to toggle dropdown
window.toggleDropdown = function(orderId) {
        // Hide all other dropdowns first
        $('.dropdown-menu').hide();
        
        // Toggle current dropdown
        var menu = $('#menu_' + orderId);
        
        if (menu.is(':visible')) {
            menu.hide();
        } else {
            // Ensure proper positioning before showing
            menu.css({
                'position': 'absolute',
                'z-index': '99999',
                'top': '100%',
                'left': '0',
                'margin-top': '2px',
                'width': '150px',
                'background': 'white',
                'border': '1px solid #ccc',
                'border-radius': '4px',
                'box-shadow': '0 4px 15px rgba(0,0,0,0.2)'
            });
            menu.show();
        }
    };
    
// Function to hide dropdown
window.hideDropdown = function(orderId) {
        $('#menu_' + orderId).hide();
    };
    
    // Hide dropdowns when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.btn-group').length) {
            $('.dropdown-menu').hide();
        }
    });
    
// Function to change order status
window.changeOrderStatus = function(orderId, newStatus) {
        
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
                    // Update the status badge in the table
                    var statusElement = $('#status_' + orderId);
                    var newBadge = getStatusBadge(newStatus);
                    statusElement.html(newBadge);
                    
                    // Update the dropdown to reflect the new status
                    updateDropdownForNewStatus(orderId, newStatus);
                    
                    // Show success message
                    if (typeof showSuccessMessage === 'function') {
                        showSuccessMessage('Order status updated to ' + newStatus + '!');
                    } else {
                        alert('Order status updated to ' + newStatus + '!');
                    }
                } else {
                    if (typeof showErrorMessage === 'function') {
                        showErrorMessage('Error: ' + resp.message);
                    } else {
                        alert('Error: ' + resp.message);
                    }
                }
            },
            error: function() {
                if (typeof showErrorMessage === 'function') {
                    showErrorMessage('Error updating order status. Please try again.');
                } else {
                    alert('Error updating order status. Please try again.');
                }
            }
        });
    };
    
    // Function to update dropdown after status change
    function updateDropdownForNewStatus(orderId, newStatus) {
        // Get the current dropdown container
        var dropdownContainer = $('#dropdown_' + orderId);
        
        // Get the payment method and receipt file from the current row
        var row = dropdownContainer.closest('tr');
        var paymentMethod = row.find('td').eq(5).text().trim(); // Payment Method column
        var receiptFile = row.data('receipt-file') || ''; // Get receipt file from data attribute
        
        // Recreate the dropdown with the new status
        var newDropdown = createActionDropdown(orderId, newStatus, paymentMethod, receiptFile);
        
        // Replace the dropdown container
        dropdownContainer.replaceWith(newDropdown);
    }
    
// Function to view receipt
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
        
        // Create and show modal
        var modal = '<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog modal-lg" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title">Payment Receipt</h5>' +
            '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
            '</div>' +
            '<div class="modal-body text-center">' + content + '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        // Remove existing modal if any
        $('#receiptModal').remove();
        
        // Add modal to body and show
        $('body').append(modal);
        $('#receiptModal').modal('show');
    };
