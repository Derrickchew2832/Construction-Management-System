@extends('layouts.management')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Supply Orders</h4> <!-- Adjusted the heading size -->
        </div>
        
        <!-- Order Supply Button (moved to below Supply Orders heading) -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#orderSupplyModal">Order Supply</button>

        <!-- Success and Error Messages -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- No Supply Orders Yet -->
        @if ($orders->isEmpty())
            <div class="alert alert-info">No supply orders yet.</div>
        @else
            <!-- Display Supply Orders Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Price</th>
                        <th>Supplier Name</th> <!-- New column for supplier name -->
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>{{ $order->item_name }}</td>
                            <td>{{ $order->description }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>${{ number_format($order->quoted_price / $order->quantity, 2) }}</td>
                            <td>${{ number_format($order->quoted_price, 2) }}</td>
                            <td>{{ $order->supplier_name }}</td> <!-- Display supplier's name here -->
                            <td>{{ ucfirst($order->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Modal for Ordering Supply -->
    <div class="modal fade" id="orderSupplyModal" tabindex="-1" aria-labelledby="orderSupplyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderSupplyModalLabel">Select Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Supplier Selection -->
                    <div id="supplier-selection">
                        <h5>Select a Supplier</h5>
                        <ul class="list-group">
                            @foreach ($suppliers as $supplier)
                                <li class="list-group-item supplier-option btn btn-outline-primary"
                                    data-supplier-id="{{ $supplier->id }}" style="cursor: pointer;">
                                    {{ $supplier->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Supply Items Selection (Initially hidden) -->
                    <div id="supply-items-section" class="d-none">
                        <h5>Available Items</h5>
                        <form id="order-form">
                            @csrf
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Available Stock</th>
                                        <th>Order Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="supply-items-list"></tbody>
                            </table>
                            <button type="button" class="btn btn-primary" id="confirm-order">Proceed</button>
                            <button type="button" class="btn btn-secondary" id="back-to-selection">Back</button>
                        </form>
                    </div>

                    <!-- Order Summary and Details (Initially hidden) -->
                    <div id="order-summary-section" class="d-none">
                        <h5>Order Summary</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody id="order-summary-list"></tbody>
                        </table>
                        <div class="form-group">
                            <label for="delivery_address">Delivery Address</label>
                            <textarea id="delivery_address" name="delivery_address" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="description">Description (optional)</label>
                            <textarea id="description" name="description" class="form-control"></textarea>
                        </div>
                        <button type="button" class="btn btn-success" id="place-order">Place Order</button>
                        <button type="button" class="btn btn-secondary" id="back-to-items">Back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            console.log('Page and jQuery are ready.');

            // Attach click event to supplier selection dynamically using event delegation
            $(document).on('click', '.supplier-option', function() {
                var supplierId = $(this).data('supplier-id');
                var projectId = '{{ $projectId }}'; // Ensuring projectId is available in JS

                console.log('Supplier clicked, Supplier ID:', supplierId);
                console.log('AJAX URL:', '/projects/' + projectId + '/supplieritems/' +
                    supplierId); // Debug URL

                fetchSupplyItems(supplierId, projectId);
            });

            function fetchSupplyItems(supplierId, projectId) {
                console.log('Fetching supply items for supplier:', supplierId);

                // Using jQuery AJAX to get the supply items
                $.ajax({
                    url: '/projects/' + projectId + '/supplieritems/' + supplierId,
                    method: 'GET',
                    success: function(data) {
                        console.log('Received supply items:', data);

                        // Make sure only table body is updated, not the whole table
                        if (data.html) {
                            // Avoid re-adding the headers by targeting the tbody
                            $('#supply-items-list').html(data.html);
                            $('#supplier-selection').hide();
                            $('#supply-items-section').removeClass('d-none');
                        } else {
                            alert('No supply items found for this supplier.');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching supply items:', xhr.responseText);
                        alert('Error fetching supply items: ' + xhr.responseText); // Alert failure
                    }
                });
            }

            // Handle proceeding to order confirmation
            $('#confirm-order').on('click', function(e) {
                e.preventDefault(); // Prevent form submission until validation passes
                var orderSummary = '';
                var hasValidationError = false;

                // Loop through each item in the supply list
                $('#supply-items-list tr').each(function() {
                    var itemName = $(this).find('.item-name').text();
                    var description = $(this).find('.item-description').text();
                    var quantity = parseInt($(this).find('.order-quantity').val(), 10);
                    var stockQuantity = parseInt($(this).find('.stock-quantity').text(), 10); // Ensure class is 'stock-quantity'
                    var price = parseFloat($(this).find('.item-price').text());
                    var totalPrice = quantity * price;

                    // Debugging: Output the values to the console to ensure correct values
                    console.log(
                        `Item: ${itemName}, Quantity Entered: ${quantity}, Stock Quantity: ${stockQuantity}`
                    );

                    // Check if quantity is a valid number
                    if (isNaN(quantity) || quantity <= 0) {
                        alert(`Please enter a valid quantity for ${itemName}.`);
                        hasValidationError = true;
                        return false; // Stop the loop
                    }

                    // Validate if the quantity exceeds the stock quantity
                    if (quantity > stockQuantity) {
                        alert(
                            `The quantity ordered for ${itemName} exceeds the stock quantity (${stockQuantity}).`
                        );
                        hasValidationError = true;
                        return false; // Stop the loop
                    }

                    if (quantity > 0) {
                        // Add the item to the order summary
                        orderSummary += `<tr>
                            <td>${itemName}</td>
                            <td>${description}</td>
                            <td>${quantity}</td>
                            <td>${price.toFixed(2)}</td>
                            <td>${totalPrice.toFixed(2)}</td>
                        </tr>`;
                    }
                });

                // If validation passes, proceed to display the order summary
                if (!hasValidationError) {
                    $('#order-summary-list').html(orderSummary);
                    $('#supply-items-section').addClass('d-none');
                    $('#order-summary-section').removeClass('d-none');
                } else {
                    console.log('Validation failed, order not submitted.');
                }
            });

            // Handle placing the order
            $('#place-order').on('click', function() {
                var formData = {
                    _token: $('input[name="_token"]').val(),
                    items: [],
                    delivery_address: $('#delivery_address').val(),
                    description: $('#description').val(),
                };

                // Loop through each item in the table
                $('#supply-items-list tr').each(function() {
                    var itemId = $(this).data('item-id'); // Get the item_id from the data attribute
                    var quantity = $(this).find('.order-quantity').val(); // Get the quantity entered

                    // Ensure the quantity is greater than 0 before adding to formData
                    if (quantity > 0) {
                        formData.items.push({
                            item_id: itemId, // Push the item_id to the items array
                            quantity: quantity
                        });
                    }
                });

                var projectId = '{{ $projectId }}'; // Ensure projectId is available in JS

                // Using jQuery AJAX to submit the order
                $.ajax({
                    url: '/projects/' + projectId + '/place-order',
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), // Adding CSRF token here
                    },
                    data: JSON.stringify(formData),
                    success: function(response) {
                        console.log('Order placed successfully:', response);
                        $('#orderSupplyModal').modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        console.error('Error placing order:', xhr.responseText);
                        alert('Error placing order: ' + xhr.responseText); // Show error to user
                    }
                });
            });

            // Back to supplier selection
            $('#back-to-selection').on('click', function() {
                $('#supply-items-section').addClass('d-none');
                $('#supplier-selection').removeClass('d-none');
            });

            // Back to item selection
            $('#back-to-items').on('click', function() {
                $('#order-summary-section').addClass('d-none');
                $('#supply-items-section').removeClass('d-none');
            });
        });
    </script>
@endpush
