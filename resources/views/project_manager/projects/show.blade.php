@extends('layouts.projectmanagerapp')

@section('title', 'Project Details')

@section('content')
<div class="container mt-4">
    <!-- Project Name as a Title -->
    <h3 class="text-primary font-weight-bold">{{ $project->name }}</h3> <!-- Smaller title -->
    <p class="lead text-muted">{{ $project->description }}</p>
    <div class="mb-4">
        <p><strong>Location:</strong> {{ $project->location }}</p>
        <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}</p> <!-- Formatted Date -->
        <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($project->end_date)->format('M d, Y') }}</p>
        <p><strong>Total Budget:</strong> ${{ number_format($project->total_budget, 2) }}</p>
    </div>
    <hr class="mb-4">

    <!-- Documents Section -->
    <h4 class="mb-4">Project Documents</h4> <!-- No color or large heading -->
    @if ($documents->isEmpty())
        <p>No documents have been uploaded yet.</p>
    @else
        <ul class="list-group">
            @foreach ($documents as $document)
                <li class="list-group-item">
                    <a href="{{ asset('storage/' . $document->document_path) }}" target="_blank">
                        {{ $document->original_name }} <!-- Display the original file name -->
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <hr class="my-4">

    <!-- Contractors Section -->
    <h4 class="mb-4">Contractors</h4> <!-- No color or large heading -->
    @if ($project->contractors->isEmpty())
        <p>No contractors have been invited yet.</p>
    @else
        <ul class="list-group">
            @foreach ($project->contractors as $contractor)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $contractor->email }}</strong>
                        @if ($contractor->status === 'approved')
                            - <span>Approved</span>
                        @elseif ($contractor->status === 'submitted')
                            - <span>Quote Submitted</span>
                        @elseif ($contractor->status === 'rejected')
                            - <span>Rejected</span>
                        @elseif ($contractor->status === 'suggested')
                            - <span>Suggestion Made</span>
                        @else
                            - <span>Pending</span>
                        @endif

                        @if ($contractor->main_contractor)
                            - <span>Main Contractor</span>
                        @endif
                    </div>
                    <span>{{ \Carbon\Carbon::parse($contractor->updated_at)->format('M d, Y') }}</span> <!-- Date of last update -->
                </li>
            @endforeach
        </ul>
    @endif

    <hr class="my-4">

    <!-- Project Status Section -->
    <h4 class="mb-4">Project Status</h4> <!-- No color or large heading -->
    @if ($project->contractors->contains('main_contractor', true))
        <p><strong>Status:</strong> Project Started - Main Contractor Assigned</p>
    @else
        <p><strong>Status:</strong> Awaiting Main Contractor</p>
    @endif
</div>

<!-- Additional Styling for a Clean Design -->
<style>
    h3 {
        font-size: 1.5rem; /* Smaller project title */
    }
    h4 {
        font-size: 1.2rem;
        font-weight: bold;
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
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
    .hr {
        margin: 2rem 0;
    }
</style>
@endsection
