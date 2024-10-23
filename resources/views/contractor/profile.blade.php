@extends('layouts.contractorapp')

@section('title', 'Edit Profile')

@section('content')
<div class="container mt-4">
    <!-- Profile Editing Section -->
    <h3 class="mb-4">Edit Profile</h3>

    <!-- Display success or error messages -->
    @if (session('status') == 'password-updated')
        <div class="alert alert-success">
            Password updated successfully.
        </div>
    @endif
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

    <!-- Profile Information Form -->
    <form action="{{ route('contractor.profile.update') }}" method="POST" class="mb-4">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            @if ($errors->has('name'))
                <small class="text-danger">{{ $errors->first('name') }}</small>
            @endif
        </div>

        <div class="form-group mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            @if ($errors->has('email'))
                <small class="text-danger">{{ $errors->first('email') }}</small>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>

    <!-- Change Password Section -->
    <h3 class="mb-4">Change Password</h3>
    <form action="{{ route('contractor.update_password') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" required>
            @if ($errors->has('password'))
                <small class="text-danger">{{ $errors->first('password') }}</small>
            @endif
        </div>

        <div class="form-group mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
            @if ($errors->has('password_confirmation'))
                <small class="text-danger">{{ $errors->first('password_confirmation') }}</small>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>

<!-- Simple and clean styles -->
<style>
    .form-group {
        margin-bottom: 1rem;
    }
    .form-control {
        border-radius: 4px;
        padding: 10px;
    }
    .btn {
        font-size: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 4px;
    }
    .alert {
        margin-top: 1rem;
        border-radius: 4px;
    }
</style>
@endsection
