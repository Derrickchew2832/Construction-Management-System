@extends('layouts.projectmanagerapp')

@section('content')
<div class="container mt-4">
    <!-- Smaller and Left-Aligned Heading -->
    <h3 class="text-left mb-4">Edit Profile</h3>

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

    <!-- Profile Information Section -->
    <div class="card mb-4">
        <div class="card-header">Profile Information</div>
        <div class="card-body">
            <form action="{{ route('project_manager.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @if ($errors->has('name'))
                        <small class="text-danger">{{ $errors->first('name') }}</small>
                    @endif
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @if ($errors->has('email'))
                        <small class="text-danger">{{ $errors->first('email') }}</small>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

    <!-- Change Password Section -->
    <div class="card">
        <div class="card-header">Change Password</div>
        <div class="card-body">
            <form action="{{ route('project_manager.profile.updatePassword') }}" method="POST">
                @csrf

                <!-- New Password -->
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" class="form-control" required>
                
                    @if ($errors->has('password'))
                        <small class="text-danger">{{ $errors->first('password') }}</small>
                    @endif
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                    @if ($errors->has('password_confirmation'))
                        <small class="text-danger">{{ $errors->first('password_confirmation') }}</small>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
