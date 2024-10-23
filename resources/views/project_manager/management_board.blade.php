@extends('layouts.projectmanagerapp')

@section('title', $project->name)

@section('content')
<div class="container mt-5">
    <!-- Project Name as the title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary font-weight-bold">{{ $project->name }}</h2>
        <a href="{{ route('tasks.index', $project->id) }}" class="btn btn-success btn-lg">Task Management</a> <!-- Elevated Task Management Button -->
    </div>

    <div class="row">
        <!-- Project Details Card -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body">
                    <h5 class="text-secondary font-weight-bold">Project Details</h5>
                    <hr>
                    <p><strong>Description:</strong> {{ $project->description }}</p>
                    <p><strong>Total Budget:</strong> ${{ number_format($project->total_budget, 2) }}</p>
                    <p><strong>Remaining Balance:</strong> <span class="{{ $project->budget_remaining < 0 ? 'text-danger' : 'text-success' }}">$ {{ number_format($project->budget_remaining, 2) }}</span></p> <!-- Highlighted remaining balance -->
                    <p><strong>Location:</strong> {{ $project->location }}</p>
                    <p><strong>Start Date:</strong> {{ $project->start_date->format('M d, Y') }}</p> <!-- Improved date format -->
                    <p><strong>End Date:</strong> {{ $project->end_date->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Contractor Management Card -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body">
                    <h5 class="text-secondary font-weight-bold">Contractor Management</h5>
                    <hr>
                    @if ($main_contractor)
                        <p><strong>Main Contractor:</strong> <span class="text-primary">{{ $main_contractor->name }}</span> <br> ({{ $main_contractor->email }})</p>
                    @else
                        <p class="text-muted">No main contractor assigned yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    h2 {
        font-size: 2rem;
        font-weight: bold;
    }
    h5 {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .card {
        border-radius: 12px;
        padding: 20px;
    }
    .btn-success {
        padding: 10px 20px;
        font-size: 1.1rem;
    }
    .card-body {
        font-size: 1.1rem;
    }
    hr {
        border: 0;
        height: 1px;
        background: #ddd;
        margin-bottom: 1.5rem;
    }
</style>
@endsection
