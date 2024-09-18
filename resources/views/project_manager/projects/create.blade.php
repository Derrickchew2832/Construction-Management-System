@extends('layouts.projectmanagerapp')

@section('content')
<div class="container">
    <h1>Create Project</h1>
    <form action="{{ route('project_manager.projects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">Project Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="total_budget">Total Budget</label>
            <input type="number" name="total_budget" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="documents">Related Documents</label>
            <input type="file" name="documents[]" class="form-control" multiple>
        </div>
        <button type="submit" class="btn btn-primary">Create Project</button>
    </form>
</div>
@endsection
