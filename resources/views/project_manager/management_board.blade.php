@extends('layouts.projectmanagerapp')

@section('title', $project->name)

@section('content')
<div class="container mt-4">
    <!-- Project Name as the title -->
    <h3 class="mb-4">{{ $project->name }}</h3>

    <div class="row">
        <!-- Project Details Card -->
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Project Details</h5>
                    <p><strong>Description:</strong> {{ $project->description }}</p>
                    <p><strong>Budget:</strong> ${{ number_format($project->total_budget, 2) }}</p>
                    <p><strong>Remaining Balance:</strong> ${{ number_format($project->budget_remaining, 2) }}</p> <!-- Updated Remaining Budget Display -->
                    <p><strong>Location:</strong> {{ $project->location }}</p>
                    <p><strong>Start Date:</strong> {{ $project->start_date }}</p>
                    <p><strong>End Date:</strong> {{ $project->end_date }}</p>

                    <!-- Moved Task Management Button below the details -->
                    <a href="{{ route('tasks.index', $project->id) }}" class="btn btn-success mb-3">Enter Task Management Page</a>
                    
                    @if ($project->status !== 'started')
                        <a href="{{ route('project_manager.projects.edit', $project->id) }}" class="btn btn-primary mb-3">Edit Project</a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Contractor Card -->
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Contractor Management</h5>
                    @if ($main_contractor)
                        <p><strong>Main Contractor:</strong> {{ $main_contractor->name }} ({{ $main_contractor->email }})</p>
                    @else
                        <p>No main contractor assigned yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistical Analysis Section in a Card View -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Statistical Analysis</h5>
                    <p>Graphical analysis related to the project will be added here later.</p>
                    <!-- Placeholder for future graphs -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    h3 {
        font-size: 1.75rem;
    }
    h5 {
        font-size: 1.25rem;
    }
    .card-title {
        font-weight: bold;
    }
</style>
@endsection
