@extends('layouts.projectmanagerapp')

@section('title', 'Management Board')

@section('content')
<div class="container mt-4">
    <h1>{{ $project->name }} - Management Board</h1>

    <div class="row">
        <div class="col-md-3">
            <h4>Project Details</h4>
            <p><strong>Description:</strong> {{ $project->description }}</p>
            <p><strong>Budget:</strong> ${{ number_format($project->total_budget, 2) }}</p>
            <p><strong>Remaining Budget:</strong> ${{ number_format($project->budget_remaining, 2) }}</p>
            <p><strong>Location:</strong> {{ $project->location }}</p>
            <p><strong>Start Date:</strong> {{ $project->start_date }}</p>
            <p><strong>End Date:</strong> {{ $project->end_date }}</p>

            <a href="{{ route('project_manager.projects.edit', $project->id) }}" class="btn btn-primary mb-3">Edit Project</a>
        </div>

        <div class="col-md-9">
            <div class="mb-4">
                <h4>Task Management</h4>
                <!-- Updated Button to point to Task Management Page -->
                <a href="{{ route('tasks.index', $project->id) }}" class="btn btn-success mb-3">Enter Task Management Page</a>
                <div class="card-deck">
                    @foreach ($tasks as $task)
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">{{ $task->title }}</h5>
                                <p class="card-text">{{ $task->description }}</p>
                                <p><strong>Status:</strong> {{ ucfirst($task->status) }}</p>
                                <a href="#" class="btn btn-warning">Edit Task</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <h4>Contractor Management</h4>
                <a href="{{ route('project_manager.projects.invite', $project->id) }}" class="btn btn-info mb-3">Invite Contractor</a>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contractors as $contractor)
                            <tr>
                                <td>{{ $contractor->name }}</td>
                                <td>{{ $contractor->email }}</td>
                                <td>{{ ucfirst($contractor->pivot->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <h4>Budget Management</h4>
                <p><strong>Total Budget:</strong> ${{ number_format($project->total_budget, 2) }}</p>
                <p><strong>Budget Remaining:</strong> ${{ number_format($project->budget_remaining, 2) }}</p>
                <a href="#" class="btn btn-secondary">Manage Budget</a>
            </div>

            <div class="mb-4">
                <h4>Project Files</h4>
                <a href="#" class="btn btn-dark mb-3">Upload New File</a>
                <ul class="list-group">
                    <!-- Loop through project files -->
                    <li class="list-group-item">File 1</li>
                    <li class="list-group-item">File 2</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .card-deck .card {
        min-width: 18rem;
    }
</style>
@endsection
