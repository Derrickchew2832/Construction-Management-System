@extends('layouts.projectmanagerapp')

@section('content')
<div class="container mt-5">
    <h4 class="text-left mb-3">Create New Project</h4>

    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('project_manager.projects.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Project Name -->
                <div class="form-group">
                    <label for="name">Project Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Start Date -->
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                    @error('start_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- End Date -->
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
                    @error('end_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Total Budget -->
                <div class="form-group">
                    <label for="total_budget">Total Budget</label>
                    <input type="number" name="total_budget" class="form-control @error('total_budget') is-invalid @enderror" value="{{ old('total_budget') }}" required>
                    @error('total_budget')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Location -->
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" required>
                    @error('location')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Documents -->
                <div class="form-group">
                    <label for="documents">Related Documents</label>
                    <input type="file" name="documents[]" class="form-control-file @error('documents.*') is-invalid @enderror" multiple>
                    @error('documents.*')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-block">Create Project</button>
            </form>
        </div>
    </div>
</div>
@endsection
