@extends('layouts.management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Supply Orders</h2>
        <button class="btn btn-primary" data-toggle="modal" data-target="#orderSupplyModal">Order Supply</button>
    </div>

    <!-- Success and Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- No Supply Orders Yet -->
    @if($orders->isEmpty())
        <div class="alert alert-info">No supply orders yet.</div>
    @else
        <!-- Display Supply Orders Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->item_name }}</td>
                        <td>{{ $order->quantity }}</td>
                        <td>${{ number_format($order->quoted_price / $order->quantity, 2) }}</td>
                        <td>${{ number_format($order->quoted_price, 2) }}</td>
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
                        @foreach($suppliers as $supplier)
                            <li class="list-group-item supplier-option" data-supplier-id="{{ $supplier->id }}">
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
                    </form>
                </div>

                <!-- Order Summary and Details (Initially hidden) -->
                <div id="order-summary-section" class="d-none">
                    <h5>Order Summary</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item Name</th>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // When a supplier is clicked, fetch the supply items
        $('.supplier-option').on('click', function() {
            var supplierId = $(this).data('supplier-id');
            fetchSupplyItems(supplierId);
        });

        // Fetch supply items based on supplier
        function fetchSupplyItems(supplierId) {
            $.ajax({
                url: '/tasks/supplier-items/' + supplierId,
                method: 'GET',
                success: function(data) {
                    $('#supply-items-list').html(data.html);
                    $('#supplier-selection').hide();
                    $('#supply-items-section').removeClass('d-none');
                }
            });
        }

        // Handle proceeding to order confirmation
        $('#confirm-order').on('click', function() {
            var orderSummary = '';

            // Iterate over each item and generate the summary
            $('#supply-items-list tr').each(function() {
                var itemName = $(this).find('.item-name').text();
                var quantity = $(this).find('.order-quantity').val();
                var price = $(this).find('.item-price').text();
                var totalPrice = quantity * parseFloat(price);

                if (quantity > 0) {
                    orderSummary += `<tr>
                        <td>${itemName}</td>
                        <td>${quantity}</td>
                        <td>${price}</td>
                        <td>${totalPrice.toFixed(2)}</td>
                    </tr>`;
                }
            });

            // Show order summary
            $('#order-summary-list').html(orderSummary);
            $('#supply-items-section').hide();
            $('#order-summary-section').removeClass('d-none');
        });

        // Handle placing the order
        $('#place-order').on('click', function() {
            var formData = {
                _token: $('input[name="_token"]').val(),
                items: [],
                delivery_address: $('#delivery_address').val(),
                description: $('#description').val(),
            };

            // Gather the item quantities
            $('#supply-items-list tr').each(function() {
                var itemId = $(this).data('item-id');
                var quantity = $(this).find('.order-quantity').val();
                if (quantity > 0) {
                    formData.items.push({ item_id: itemId, quantity: quantity });
                }
            });

            $.ajax({
                url: '/tasks/place-order',
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#orderSupplyModal').modal('hide');
                    location.reload();  // Reload the page to show the new order
                }
            });
        });
    });
</script>
@endpush
