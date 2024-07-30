@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="container mt-4">
    <h1>Change Password</h1>
    <form action="{{ route('projectmanager.password.update') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>
@endsection
