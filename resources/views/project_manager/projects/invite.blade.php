@extends('layouts.projectmanagerapp')

@section('title', 'Invite Contractor')

@section('content')
<div class="container mt-4">
    <!-- Smaller Project Title -->
    <h3 class="text-primary font-weight-bold">Invite Contractor for Project: {{ $project->name }}</h3> 
    
    <!-- Invitation Form -->
    <form action="{{ route('project_manager.projects.storeInvite', $project->id) }}" method="POST" class="mb-5">
        @csrf
        <div class="form-group">
            <label for="contractor_email" class="form-label">Contractor Email:</label>
            <input type="email" name="contractor_email" id="contractor_email" class="form-control" required placeholder="Enter contractor's email">
        </div>
        <button type="submit" class="btn btn-primary mt-3">Send Invitation</button>
    </form>

    <!-- Invited Contractors Section -->
    <h4 class="mb-4">Invited Contractors</h4>
    
    @if($invitedContractors->isEmpty())
        <p class="text-warning">No contractors have been invited yet.</p>
    @else
        <ul class="list-group">
            @foreach($invitedContractors as $contractor)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $contractor->name }}</strong> ({{ $contractor->email }})
                    </div>
                    
                    @php
                        // Determine the status badge and message
                        $status = $contractor->quote_status ?? $contractor->invitation_status;
                        $badgeClass = 'secondary'; // Default status color

                        if ($status === 'submitted') {
                            $badgeClass = 'success';
                        } elseif ($status === 'rejected') {
                            $badgeClass = 'danger';
                        } elseif ($status === 'pending') {
                            $badgeClass = 'warning';
                        }
                    @endphp

                    <!-- Status Badge with Correct Class -->
                    <span class="badge badge-{{ $badgeClass }}">
                        {{ ucfirst($status) }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<!-- Additional Styling for Cleaner Design -->
<style>
    h3 {
        font-size: 1.75rem; /* Smaller title */
    }
    h4 {
        font-size: 1.25rem;
        font-weight: bold;
    }
    .form-control {
        padding: 10px;
        font-size: 1rem;
    }
    .btn-primary {
        font-size: 1rem;
        padding: 10px 20px;
    }
    .list-group-item {
        background-color: #f8f9fa;
        border: none;
        margin-bottom: 0.5rem;
        padding: 1rem 1.5rem;
        border-radius: 0.25rem;
    }
    .list-group-item:hover {
        background-color: #e9ecef;
    }
    .badge {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
</style>
@endsection
