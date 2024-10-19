@extends('layouts.projectmanagerapp')

@section('title', 'Edit Project')

@section('content')
<div class="container mt-4">
    <h1 class="text-center mb-4">Edit Project</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('project_manager.projects.update', $project->id) }}" method="POST" id="editProjectForm">
                @csrf
                @method('PUT')

                <!-- Project Name -->
                <div class="form-group">
                    <label for="name">Project Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $project->name) }}" required>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Project Description -->
                <div class="form-group">
                    <label for="description">Project Description</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description', $project->description) }}</textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Start Date -->
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $project->start_date) }}" required>
                    @error('start_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- End Date -->
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $project->end_date) }}" required>
                    @error('end_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Total Budget -->
                <div class="form-group">
                    <label for="total_budget">Total Budget</label>
                    <input type="number" name="total_budget" id="total_budget" class="form-control @error('total_budget') is-invalid @enderror" value="{{ old('total_budget', $project->total_budget) }}" required>
                    @error('total_budget')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Budget Remaining -->
                <div class="form-group">
                    <label for="budget_remaining">Budget Remaining</label>
                    <input type="number" name="budget_remaining" id="budget_remaining" class="form-control @error('budget_remaining') is-invalid @enderror" value="{{ old('budget_remaining', $project->budget_remaining) }}" required>
                    @error('budget_remaining')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Location -->
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $project->location) }}" required>
                    @error('location')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button with Confirmation -->
                <button type="submit" class="btn btn-primary btn-block" onclick="return confirmSubmit()">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmSubmit() {
        return confirm("Are you sure you want to update the project?");
    }
</script>
@endsection
