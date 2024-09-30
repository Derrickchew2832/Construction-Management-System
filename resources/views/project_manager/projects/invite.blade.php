@extends('layouts.projectmanagerapp')

@section('title', 'Invite Contractor')

@section('content')
<div class="container mt-4">
    <h1>Invite Contractor for Project: {{ $project->name }}</h1>
    
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
                
                @php
                    // Determine the status badge and message
                    $status = $contractor->quote_status ?? $contractor->invitation_status;
                    $badgeClass = 'secondary'; // Default

                    if ($status === 'submitted') {
                        $badgeClass = 'success';
                    } elseif ($status === 'rejected') {
                        $badgeClass = 'danger';
                    } elseif ($status === 'pending') {
                        $badgeClass = 'warning';
                    }
                @endphp

                <!-- Display status with appropriate color and message -->
                <span class="badge badge-{{ $badgeClass }}">
                    {{ ucfirst($status) }}
                </span>
            </li>
        @endforeach
    </ul>
</div>
@endsection
