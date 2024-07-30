@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container mt-4">
    <h1>Edit Profile</h1>
    <form action="{{ route('projectmanager.profile.update') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
@endsection
