@extends('layouts.clientapp')

@section('title', 'Invitations')

@section('content')
<div class="container mt-4">
    <h3 class="text-primary font-weight-bold mb-3">Invitations</h3> <!-- Enhanced title styling -->

    @if($invitations->isEmpty())
        <div class="alert alert-info text-center">
            <p>No invitations available.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-borderless">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Project Name</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $invitation)
                        <tr>
                            @php
                                $project = DB::table('projects')->where('id', $invitation->project_id)->first();
                            @endphp
                            <td class="align-middle">{{ $project->name ?? 'Project not found' }}</td>
                            <td class="align-middle">
                                <span class="badge 
                                    {{ $invitation->status == 'pending' ? 'bg-warning text-dark' : ($invitation->status == 'accepted' ? 'bg-success' : 'bg-danger') }}">
                                    {{ ucfirst($invitation->status) }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                @if($invitation->status == 'pending')
                                    <form action="{{ route('client.invitation.update', $invitation->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" name="status" value="accepted" class="btn btn-sm btn-outline-success mx-1" onclick="return confirm('Are you sure you want to accept this invitation?')">
                                            <i class="fas fa-check"></i> Accept
                                        </button>
                                        <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger mx-1" onclick="return confirm('Are you sure you want to reject this invitation?')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                @else
                                    <span>{{ ucfirst($invitation->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- FontAwesome for icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<style>
    /* Table Styling */
    .table-hover tbody tr:hover {
        background-color: #f9f9f9;
    }

    /* Adjust badge colors */
    .badge.bg-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge.bg-success {
        background-color: #28a745;
    }

    .badge.bg-danger {
        background-color: #dc3545;
    }

    /* Button Styling */
    .btn-sm {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }

    /* Center-align actions */
    .text-center {
        text-align: center;
    }
</style>
@endsection
