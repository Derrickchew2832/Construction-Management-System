@extends('layouts.supplierapp')

@section('title', 'Supplier Supply Orders Dashboard')

@section('content')
    <div class="container mt-4">
        <h3 class="text-primary mb-4" style="font-weight: bold;">Supply Orders</h3>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Error Message -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- No Orders -->
        @if ($supplyOrders->isEmpty())
            <div class="alert alert-info">No supply orders yet.</div>
        @else
            <!-- Display Supply Orders Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover mt-4">
                    <thead class="table-primary">
                        <tr>
                            <th>Item Name</th>
                            <th>Project Name</th>
                            <th>Contractor Name</th>
                            <th>Quantity</th>
                            <th>Stock Available</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supplyOrders as $order)
                            <tr>
                                <td>{{ $order->item_name }}</td>
                                <td>{{ $order->project_name }}</td>
                                <td>{{ $order->contractor_name }}</td>
                                <td>{{ $order->quantity }}</td>
                                <td>{{ $order->stock_quantity }}</td>
                                <td>RM {{ number_format($order->quantity * $order->price, 2) }}</td>
                                <td><span class="badge bg-{{ $order->status == 'pending' ? 'warning' : ($order->status == 'accepted' ? 'success' : ($order->status == 'received' ? 'info' : 'danger')) }}">{{ ucfirst($order->status) }}</span></td>
                                <td>
                                    <!-- Accept/Reject Buttons with Confirmation -->
                                    @if ($order->status == 'pending')
                                        <form action="{{ route('supplier.quote.update', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" name="status" value="accepted" class="btn btn-success btn-sm"
                                                onclick="return confirm('Are you sure you want to accept this order?')">Accept</button>
                                            <button type="submit" name="status" value="rejected" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to reject this order?')">Reject</button>
                                        </form>
                                    @endif

                                    <!-- Delivery Form and Image Upload if Accepted -->
                                    @if ($order->status == 'accepted')
                                        <form action="{{ route('supplier.order.delivery.submit', $order->id) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                            @csrf
                                            <div class="form-group">
                                                <label>Delivery Form (PDF)</label>
                                                <input type="file" name="delivery_form" class="form-control @error('delivery_form') is-invalid @enderror" accept="application/pdf" required>
                                                @error('delivery_form')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group mt-2">
                                                <label>Delivery Image (Photo)</label>
                                                <input type="file" name="delivery_image" class="form-control @error('delivery_image') is-invalid @enderror" accept="image/*" required>
                                                @error('delivery_image')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm mt-2"
                                                onclick="return confirm('Are you sure you want to submit this delivery?')">Submit Delivery</button>
                                        </form>
                                    @endif

                                    <!-- View Received Image if Status is Received -->
                                    @if ($order->status == 'received' && $order->received_image)
                                        <div class="mt-3">
                                            <label>Proof of Receipt:</label>
                                            <a href="{{ asset('storage/' . $order->received_image) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $order->received_image) }}" alt="Proof of Receipt" class="img-thumbnail mt-2" style="width: 100px; height: auto;">
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // JavaScript to handle confirmation messages and validation
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    if (form.querySelector('input[name="delivery_form"]') && form.querySelector('input[name="delivery_image"]')) {
                        const deliveryForm = form.querySelector('input[name="delivery_form"]');
                        const deliveryImage = form.querySelector('input[name="delivery_image"]');

                        if (!deliveryForm.files[0] || deliveryForm.files[0].type !== 'application/pdf') {
                            alert('Please upload a valid PDF document for the delivery form.');
                            event.preventDefault();
                        }
                        if (!deliveryImage.files[0] || !deliveryImage.files[0].type.startsWith('image/')) {
                            alert('Please upload a valid image for the delivery photo.');
                            event.preventDefault();
                        }
                    }
                });
            });
        });
    </script>
@endpush
