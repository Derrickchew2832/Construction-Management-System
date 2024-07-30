@extends('layouts.app')

@section('title', 'Project Manager Dashboard')

@section('content')
<div class="container mt-4">
    <h1>Project Manager Dashboard</h1>
    <div class="card">
        <div class="card-header">Manage Projects</div>
        <div class="card-body">
            <a href="{{ route('projectmanager.projects.index') }}" class="btn btn-primary">View Projects</a>
            <a href="{{ route('projectmanager.projects.create') }}" class="btn btn-primary">Create New Project</a>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header">Profile</div>
        <div class="card-body">
            <a href="{{ route('projectmanager.profile.edit') }}" class="btn btn-primary">Edit Profile</a>
            <a href="{{ route('projectmanager.password.change') }}" class="btn btn-primary">Change Password</a>
        </div>
    </div>
</div>
@endsection
