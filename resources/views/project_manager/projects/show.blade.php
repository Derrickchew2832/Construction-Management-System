@extends('layouts.projectmanagerapp')

@section('title', 'Project Details')

@section('content')
<div class="container mt-4">
    <h1>{{ $project->name }}</h1>
    <p>{{ $project->description }}</p>
    <p><strong>Location:</strong> {{ $project->location }}</p>
    <p><strong>Start Date:</strong> {{ $project->start_date }}</p>
    <p><strong>End Date:</strong> {{ $project->end_date }}</p>
    <p><strong>Total Budget:</strong> ${{ number_format($project->total_budget, 2) }}</p>
    <hr>

    <h2>Contractors</h2>
    @if ($project->contractors->isEmpty())
        <p>No contractors have been invited yet.</p>
    @else
        <ul class="list-group">
            @foreach ($project->contractors as $contractor)
                <li class="list-group-item">
                    <strong>{{ $contractor->email }}</strong>
                    @if ($contractor->status === 'approved')
                        - <span class="badge badge-success">Approved</span>
                    @elseif ($contractor->status === 'submitted')
                        - <span class="badge badge-info">Quote Submitted</span>
                    @elseif ($contractor->status === 'rejected')
                        - <span class="badge badge-danger">Rejected</span>
                    @elseif ($contractor->status === 'suggested')
                        - <span class="badge badge-warning">Suggestion Made</span>
                    @else
                        - <span class="badge badge-secondary">Pending</span>
                    @endif

                    @if ($contractor->main_contractor)
                        - <span class="badge badge-primary">Main Contractor</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <hr>

    <h2>Project Status</h2>
    @if ($project->contractors->contains('main_contractor', true))
        <p><strong>Status:</strong> Project Started - Main Contractor Assigned</p>
    @else
        <p><strong>Status:</strong> Awaiting Main Contractor</p>
    @endif
</div>
@endsection
