@extends('layouts.management')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Invitation Form Modal Trigger -->
            <div class="d-flex justify-content-start">
                <button type="button" class="btn btn-primary btn-sm mb-3" data-toggle="modal" data-target="#inviteModal">
                    Invite
                </button>
            </div>

            <!-- Modal for inviting client -->
            <div class="modal fade" id="inviteModal" tabindex="-1" role="dialog" aria-labelledby="inviteModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="inviteModalLabel">Invite Client</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('tasks.inviteClient', ['projectId' => $projectId]) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="email">Client Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter client's email" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary btn-sm">Send Invitation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List of Invitations -->
            <div class="card mt-3">
                <div class="card-header">
                    Client Invitation Status
                </div>
                <div class="card-body">
                    @if($invitations->isEmpty())
                        <p>No invitations have been sent yet.</p>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invitations as $invitation)
                                    <tr>
                                        <td>{{ $invitation->email }}</td>
                                        <td>
                                            <span class="badge badge-{{ $invitation->status == 'pending' ? 'warning' : ($invitation->status == 'accepted' ? 'success' : 'danger') }}">
                                                {{ ucfirst($invitation->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for simpler styling -->
<style>
    body {
        background-color: #f8f9fa;
    }
    .btn-primary {
        font-size: 0.9rem;
        padding: 6px 12px;
    }
    .card {
        border-radius: 5px;
    }
    .card-header {
        background-color: #007bff;
        color: white;
    }
    .badge-warning {
        background-color: #ffc107;
    }
    .badge-success {
        background-color: #28a745;
    }
    .badge-danger {
        background-color: #dc3545;
    }
</style>
@endsection
