@extends('layouts.clientapp')

@section('title', 'Invitations')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">Invitation</h3>

    @if($invitations->isEmpty())
        <div class="alert alert-info">
            <p>No invitations available.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Project Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $invitation)
                        <tr>
                            @php
                                $project = DB::table('projects')->where('id', $invitation->project_id)->first();
                            @endphp
                            <td>{{ $project->name ?? 'Project not found' }}</td>
                            <td>{{ ucfirst($invitation->status) }}</td>
                            <td>
                                @if($invitation->status == 'pending')
                                    <form action="{{ route('client.invitation.update', $invitation->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" name="status" value="accepted" class="btn btn-sm btn-outline-success">Accept</button>
                                        <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button>
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
@endsection
