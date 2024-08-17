@extends('layouts.projectmanagerapp')

@section('title', 'Invite Contractor')

@section('content')
<div class="container mt-4">
    <h1>Invite Contractor for Project: {{ $project->name }}</h1>
    
    <!-- Display Success Message -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Display Error Message -->
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <form action="{{ route('project_manager.projects.storeInvite', $project->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="contractor_email">Contractor Email:</label>
            <input type="email" name="contractor_email" id="contractor_email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Send Invitation</button>
    </form>

    <h3 class="mt-5">Invited Contractors</h3>
    <ul class="list-group">
        @foreach($invitedContractors as $contractor)
            <li class="list-group-item">
                {{ $contractor->name }} ({{ $contractor->email }}) - 
                <span class="badge badge-{{ $contractor->status == 'submitted' ? 'success' : 'secondary' }}">
                    {{ ucfirst($contractor->status) }}
                </span>
            </li>
        @endforeach
    </ul>
</div>
@endsection
