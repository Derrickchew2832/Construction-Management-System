@extends('layouts.supplierapp')

@section('title', 'Supplier Supply Orders Dashboard')

@section('content')
    <div class="container">
        <h3>Supply Orders</h3>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- No Orders -->
        @if ($supplyOrders->isEmpty())
            <div class="alert alert-info">No supply orders yet.</div>
        @else
            <!-- Display Supply Orders Table -->
            <table class="table table-bordered mt-4">
                <thead>
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
                            <td>{{ $order->stock_quantity }}</td> <!-- Use stock_quantity here -->
                            <td>RM {{ number_format($order->quantity * $order->price, 2) }}</td> <!-- Use price instead of price_per_unit -->
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>
                                <!-- Only show Accept/Reject buttons if the order is pending -->
                                @if ($order->status == 'Pending')
                                    <form action="{{ route('supplier.quote.update', $order->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="status" value="Accepted" class="btn btn-success btn-sm">Accept</button>
                                        <button type="submit" name="status" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                @endif

                                <!-- Show delivery form and image upload if accepted -->
                                @if ($order->status == 'Accepted')
                                    <form action="{{ route('supplier.order.delivery.submit', $order->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group mt-2">
                                            <label>Delivery Form (PDF)</label>
                                            <input type="file" name="delivery_form" class="form-control" required>
                                        </div>
                                        <div class="form-group mt-2">
                                            <label>Delivery Image (Photo)</label>
                                            <input type="file" name="delivery_image" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm mt-2">Submit Delivery</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
