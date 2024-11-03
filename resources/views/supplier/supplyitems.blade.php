@extends('layouts.supplierapp')

@section('title', 'Supply Items')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary font-weight-bold">Supply Items</h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addSupplyModal">+ Add Item</button>
        </div>

        <!-- Success and Error Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Show table or message if supply items are empty -->
        @if ($supplyItems->isEmpty())
            <div class="alert alert-info">No supply items available. Click the "Add Supply Item" button to add new supplies.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover mt-4">
                    <thead class="thead-light">
                        <tr>
                            <th>Supply Number</th>
                            <th>Item Name</th>
                            <th>Price (per unit)</th>
                            <th>Stock Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supplyItems as $item)
                            <tr>
                                <td>{{ $item->supplier_item_number }}</td>
                                <td>{{ $item->name }}</td>
                                <td>RM {{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->stock_quantity }}</td>
                                <td>
                                    @if ($item->stock_quantity == 0)
                                        <span class="badge badge-danger">Out of Stock</span>
                                    @elseif($item->stock_quantity <= 10)
                                        <span class="badge badge-warning">Low Stock</span>
                                    @else
                                        <span class="badge badge-success">In Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- Edit Button with unique modal trigger -->
                                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                        data-target="#editSupplyModal-{{ $item->id }}">
                                        Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('supplier.supplyitems.delete', $item->id) }}" method="POST"
                                        style="display:inline-block;" onsubmit="return confirmDelete()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Supply Item Modal for each item -->
                            <div class="modal fade" id="editSupplyModal-{{ $item->id }}" tabindex="-1"
                                aria-labelledby="editSupplyModalLabel-{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editSupplyModalLabel-{{ $item->id }}">Edit Supply Item</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('supplier.supplyitems.update', $item->id) }}" method="POST"
                                            onsubmit="return confirmUpdate()">
                                            @csrf
                                            @method('PUT') <!-- Hidden field to override the method to PUT -->

                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="edit_name_{{ $item->id }}">Item Name</label>
                                                    <input type="text" id="edit_name_{{ $item->id }}" name="name"
                                                        class="form-control" value="{{ $item->name }}" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="edit_description_{{ $item->id }}">Description</label>
                                                    <textarea id="edit_description_{{ $item->id }}" name="description"
                                                        class="form-control">{{ $item->description }}</textarea>
                                                </div>

                                                <div class="form-group">
                                                    <label for="edit_price_{{ $item->id }}">Price (per unit)</label>
                                                    <input type="number" id="edit_price_{{ $item->id }}" step="0.01"
                                                        name="price" class="form-control" value="{{ $item->price }}" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="edit_stock_quantity_{{ $item->id }}">Stock Quantity</label>
                                                    <input type="number" id="edit_stock_quantity_{{ $item->id }}"
                                                        name="stock_quantity" class="form-control"
                                                        value="{{ $item->stock_quantity }}" required min="1">
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Update Supply Item</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Add Supply Item Modal -->
    <div class="modal fade" id="addSupplyModal" tabindex="-1" aria-labelledby="addSupplyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplyModalLabel">Add New Supply Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('supplier.supplyitems.store') }}" method="POST" onsubmit="return validatePrice()">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Item Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="price">Price (per unit)</label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" required min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Supply Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<!-- JavaScript to handle confirmation for delete, edit update, and price validation -->
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Confirm before deleting an item
            window.confirmDelete = function() {
                return confirm("Are you sure you want to delete this item?");
            };

            // Confirm before updating an item
            window.confirmUpdate = function() {
                return confirm("Are you sure you want to update this item?");
            };

            // Validate price before form submission
            window.validatePrice = function() {
                var priceInput = document.querySelector('input[name="price"]');
                if (parseFloat(priceInput.value) <= 1) {
                    alert('The price must be greater than 1.');
                    return false;
                }
                return true;
            };
        });
    </script>
@endsection
