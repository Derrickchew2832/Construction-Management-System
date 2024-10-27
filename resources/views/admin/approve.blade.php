@extends('layouts.adminapp')

@section('title', 'Admin Approve Users')

@section('content')
<div class="container mt-4">
    <h3 class="text-start text-primary mb-3">Pending User Approvals</h3>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($users->isEmpty())
        <p class="text-center">No users pending approval.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Document</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td>{{ $user->phone }}</td>
                            <td><a href="{{ Storage::url($user->document_path) }}" target="_blank" class="btn btn-link">View Document</a></td>
                            <td class="text-center">
                                <form action="{{ route('admin.approve', $user->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm me-2">Approve</button>
                                </form>
                                <form action="{{ route('admin.reject', $user->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Styles for better layout and design -->
<style>
    .container h3 {
        color: #007bff;
    }
    .table {
        border-radius: 8px;
        overflow: hidden;
        background-color: #ffffff;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #dee2e6;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .btn-link {
        color: #007bff;
        text-decoration: none;
    }
    .btn-link:hover {
        text-decoration: underline;
    }
</style>
@endsection
