@extends('layouts.projectmanagerapp')

@section('title', 'Edit Project')

@section('content')
<div class="container mt-4">
    <h1>Edit Project</h1>
    <form action="{{ route('project_manager.projects.update', $project->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Project Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $project->name }}" required>
        </div>

        <div class="form-group">
            <label for="description">Project Description</label>
            <textarea name="description" id="description" class="form-control" required>{{ $project->description }}</textarea>
        </div>

        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $project->start_date }}" required>
        </div>

        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $project->end_date }}" required>
        </div>

        <div class="form-group">
            <label for="total_budget">Total Budget</label>
            <input type="number" name="total_budget" id="total_budget" class="form-control" value="{{ $project->total_budget }}" required>
        </div>

        <div class="form-group">
            <label for="budget_remaining">Budget Remaining</label>
            <input type="number" name="budget_remaining" id="budget_remaining" class="form-control" value="{{ $project->budget_remaining }}" required>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" id="location" class="form-control" value="{{ $project->location }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
@endsection
