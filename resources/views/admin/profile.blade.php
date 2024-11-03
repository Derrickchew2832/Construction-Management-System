@extends('layouts.adminapp')

@section('title', 'Edit Profile')

@section('content')
<div class="container mt-4">
    <h1>Edit Profile</h1>
    
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

    <div class="card mb-4">
        <div class="card-header">Profile Information</div>
        <div class="card-body">
            <form action="{{ route('admin.updateProfile') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @if ($errors->has('name'))
                        <small class="text-danger">{{ $errors->first('name') }}</small>
                    @endif
                </div>
                
                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @if ($errors->has('email'))
                        <small class="text-danger">{{ $errors->first('email') }}</small>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Change Password</div>
        <div class="card-body">
            <form action="{{ route('admin.updatePassword') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="password">New Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <small class="form-text text-muted">
                        Password must be at least 8 characters long and include letters, numbers, and symbols.
                    </small>
                    @if ($errors->has('password'))
                        <small class="text-danger">{{ $errors->first('password') }}</small>
                    @endif
                </div>
                
                <div class="form-group mb-3">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                    @if ($errors->has('password_confirmation'))
                        <small class="text-danger">{{ $errors->first('password_confirmation') }}</small>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
            
            @if ($errors->updatePassword->any())
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->updatePassword->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
