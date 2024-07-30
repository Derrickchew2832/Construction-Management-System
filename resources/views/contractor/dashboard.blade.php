@extends('layouts.app')

@section('title', 'Contractor Dashboard')

@section('content')
<div class="container mt-4">
    <h1>Contractor Dashboard</h1>
    <div class="card">
        <div class="card-header">Manage Quotes</div>
        <div class="card-body">
            <a href="{{ route('contractor.quotes.index') }}" class="btn btn-primary">View Quotes</a>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header">Profile</div>
        <div class="card-body">
            <a href="{{ route('contractor.profile.edit') }}" class="btn btn-primary">Edit Profile</a>
            <a href="{{ route('contractor.password.change') }}" class="btn btn-primary">Change Password</a>
        </div>
    </div>
</div>
@endsection
