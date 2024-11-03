@extends('layouts.projectmanagerapp')

@section('content')
<div class="container mt-5">
    <h4 class="text-left mb-3">Create New Project</h4>

    <div class="card">
        <div class="card-body">

            <!-- Error Message Display for All Fields -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>There were some issues with your input:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="projectForm" action="{{ route('project_manager.projects.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                <!-- Project Name -->
                <div class="form-group">
                    <label for="name">Project Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                    <span class="text-danger" id="nameError">@error('name'){{ $message }}@enderror</span>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                    <span class="text-danger" id="descriptionError">@error('description'){{ $message }}@enderror</span>
                </div>

                <!-- Start Date -->
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                    <span class="text-danger" id="startDateError">@error('start_date'){{ $message }}@enderror</span>
                </div>

                <!-- End Date -->
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                    <span class="text-danger" id="endDateError">@error('end_date'){{ $message }}@enderror</span>
                </div>

                <!-- Total Budget -->
                <div class="form-group">
                    <label for="total_budget">Total Budget</label>
                    <input type="number" name="total_budget" class="form-control @error('total_budget') is-invalid @enderror" value="{{ old('total_budget') }}">
                    <span class="text-danger" id="budgetError">@error('total_budget'){{ $message }}@enderror</span>
                </div>

                <!-- Location -->
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}">
                    <span class="text-danger" id="locationError">@error('location'){{ $message }}@enderror</span>
                </div>

                <!-- Documents -->
                <div class="form-group">
                    <label for="documents">Related Documents</label>
                    <input type="file" name="documents[]" class="form-control-file @error('documents') is-invalid @enderror" multiple>
                    <span class="text-danger" id="documentsError">
                        @foreach ($errors->get('documents.*') as $message)
                            <li>{{ $message[0] }}</li>
                        @endforeach
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="button" onclick="validateForm()" class="btn btn-primary btn-block">Create Project</button>
            </form>
        </div>
    </div>
</div>

<script>
    function validateForm() {
        // Clear previous error messages
        document.querySelectorAll('.text-danger').forEach(errorSpan => errorSpan.textContent = '');

        // Get form values
        const name = document.querySelector('input[name="name"]').value;
        const description = document.querySelector('textarea[name="description"]').value;
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const totalBudget = document.querySelector('input[name="total_budget"]').value;
        const location = document.querySelector('input[name="location"]').value;
        const documents = document.querySelector('input[name="documents[]"]').files;

        let hasError = false;

        // Project Name Validation
        if (!name) {
            document.getElementById('nameError').textContent = 'The project name is required.';
            hasError = true;
        } else if (name.length > 255) {
            document.getElementById('nameError').textContent = 'The project name must not exceed 255 characters.';
            hasError = true;
        }

        // Description Validation
        if (!description) {
            document.getElementById('descriptionError').textContent = 'The project description is required.';
            hasError = true;
        }

        // Start Date Validation
        if (!startDate) {
            document.getElementById('startDateError').textContent = 'The start date is required.';
            hasError = true;
        } else if (new Date(startDate) < new Date()) {
            document.getElementById('startDateError').textContent = 'The start date must be today or a future date.';
            hasError = true;
        }

        // End Date Validation
        if (!endDate) {
            document.getElementById('endDateError').textContent = 'The end date is required.';
            hasError = true;
        } else if (startDate && new Date(endDate) <= new Date(startDate)) {
            document.getElementById('endDateError').textContent = 'The end date must be after the start date.';
            hasError = true;
        }

        // Total Budget Validation
        if (!totalBudget || totalBudget <= 0) {
            document.getElementById('budgetError').textContent = 'The total budget must be at least 1.';
            hasError = true;
        }

        // Location Validation
        if (!location) {
            document.getElementById('locationError').textContent = 'The location is required.';
            hasError = true;
        } else if (location.length > 255) {
            document.getElementById('locationError').textContent = 'The location must not exceed 255 characters.';
            hasError = true;
        }

        // Documents Validation
        if (documents.length === 0) {
            document.getElementById('documentsError').textContent = 'At least one document is required.';
            hasError = true;
        } else {
            for (let i = 0; i < documents.length; i++) {
                const file = documents[i];
                const allowedExtensions = ['pdf', 'doc', 'docx'];
                const fileSizeLimit = 2 * 1024 * 1024; // 2MB
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    document.getElementById('documentsError').textContent = 'Documents must be a PDF, DOC, or DOCX file.';
                    hasError = true;
                    break;
                }

                if (file.size > fileSizeLimit) {
                    document.getElementById('documentsError').textContent = 'Documents must not exceed 2MB in size.';
                    hasError = true;
                    break;
                }
            }
        }

        // Submit the form if no errors
        if (!hasError) {
            if (confirm("Are you sure you want to create this project?")) {
                document.getElementById('projectForm').submit();
            }
        }
    }
</script>
@endsection
