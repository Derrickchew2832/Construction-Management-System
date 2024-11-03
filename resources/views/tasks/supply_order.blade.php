@extends('layouts.management')

@section('content')
    <div class="container">
        <!-- Card for Project Financial Overview -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Project Financial Overview</h5>
                <p class="card-text"><strong>Quoted Price:</strong> ${{ number_format($quotedPrice, 2) }}</p>
                <p class="card-text"><strong>Total Supply Orders:</strong> ${{ number_format($totalSupplyOrderPrice, 2) }}
                </p>
                <div class="d-flex justify-content-center">
                    <div style="width: 400px; height: 400px;">
                        <canvas id="financePieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divider between financial overview and supply orders -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Supply Orders</h4>
        </div>

        <!-- Order Supply Button -->
        <!-- Order Supply Button - Disabled if project is completed -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#orderSupplyModal"
            @if ($project->status === 'completed') disabled @endif>
            Order Supply
        </button>


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
                        <th>Supplier Name</th>
                        <th>Status</th>
                        <th>Actions</th>
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
                            <td>{{ $order->supplier_name }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>
                                @if ($order->status == 'shipped')
                                    <button type="button" class="btn btn-primary" data-toggle="modal"
                                        data-target="#orderReceivedModal-{{ $order->id }}">
                                        Confirm Received
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal for Order Received -->
                        <div class="modal fade" id="orderReceivedModal-{{ $order->id }}" tabindex="-1"
                            aria-labelledby="orderReceivedModalLabel-{{ $order->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="orderReceivedModalLabel-{{ $order->id }}">Confirm
                                            Order Received</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form
                                        action="{{ route('tasks.order.received', ['projectId' => $projectId, 'orderId' => $order->id]) }}"
                                        method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="received_image_{{ $order->id }}">Upload Received
                                                    Image</label>
                                                <input type="file" name="received_image"
                                                    id="received_image_{{ $order->id }}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Order Received</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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

                    <!-- Order Summary (Initially hidden) -->
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
    <!-- Add Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            console.log('Page and jQuery are ready.');

            // Variables for the pie chart (these should come from the server)
            var quotedPrice = {{ $quotedPrice ?? 0 }};
            var totalSupplyOrderPrice = {{ $totalSupplyOrderPrice ?? 0 }};

            // Draw the pie chart
            var ctx = document.getElementById('financePieChart').getContext('2d');
            var budgetPieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Quoted Price', 'Total Supply Orders'],
                    datasets: [{
                        data: [quotedPrice, totalSupplyOrderPrice],
                        backgroundColor: ['#FF6384', '#36A2EB'],
                        hoverBackgroundColor: ['#FF6384', '#36A2EB']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.raw.toLocaleString('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    });
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Attach click event to supplier selection dynamically using event delegation
            $(document).on('click', '.supplier-option', function() {
                var supplierId = $(this).data('supplier-id');
                var projectId = '{{ $projectId }}';

                console.log('Supplier clicked, Supplier ID:', supplierId);
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

                        if (data.html) {
                            $('#supply-items-list').html(data.html);
                            $('#supplier-selection').hide();
                            $('#supply-items-section').removeClass('d-none');
                        } else {
                            alert('No supply items found for this supplier.');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching supply items:', xhr.responseText);
                        alert('Error fetching supply items: ' + xhr.responseText);
                    }
                });
            }

            // Handle proceeding to order confirmation
            $('#confirm-order').on('click', function(e) {
                e.preventDefault();
                var orderSummary = '';
                var hasValidationError = false;

                $('#supply-items-list tr').each(function() {
                    var itemName = $(this).find('.item-name').text();
                    var description = $(this).find('.item-description').text();
                    var quantity = parseInt($(this).find('.order-quantity').val(), 10);
                    var stockQuantity = parseInt($(this).find('.stock-quantity').text(), 10);
                    var price = parseFloat($(this).find('.item-price').text());
                    var totalPrice = quantity * price;

                    if (isNaN(quantity) || quantity <= 0) {
                        alert(`Please enter a valid quantity for ${itemName}.`);
                        hasValidationError = true;
                        return false;
                    }

                    if (quantity > stockQuantity) {
                        alert(
                            `The quantity ordered for ${itemName} exceeds the stock quantity (${stockQuantity}).`
                            );
                        hasValidationError = true;
                        return false;
                    }

                    if (quantity > 0) {
                        orderSummary += `<tr>
                            <td>${itemName}</td>
                            <td>${description}</td>
                            <td>${quantity}</td>
                            <td>${price.toFixed(2)}</td>
                            <td>${totalPrice.toFixed(2)}</td>
                        </tr>`;
                    }
                });

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

                $('#supply-items-list tr').each(function() {
                    var itemId = $(this).data('item-id');
                    var quantity = $(this).find('.order-quantity').val();

                    if (quantity > 0) {
                        formData.items.push({
                            item_id: itemId,
                            quantity: quantity
                        });
                    }
                });

                var projectId = '{{ $projectId }}';

                $.ajax({
                    url: '/projects/' + projectId + '/place-order',
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    data: JSON.stringify(formData),
                    success: function(response) {
                        console.log('Order placed successfully:', response);
                        $('#orderSupplyModal').modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        console.error('Error placing order:', xhr.responseText);
                        alert('Error placing order: ' + xhr.responseText);
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
