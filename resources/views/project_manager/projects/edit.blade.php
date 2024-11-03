@extends('layouts.projectmanagerapp')

@section('title', 'Edit Project')

@section('content')
    <div class="container mt-5">
        <h4 class="text-left mb-3">Edit Project</h4>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('project_manager.projects.update', $project->id) }}" method="POST"
                    enctype="multipart/form-data" id="editProjectForm" novalidate>
                    @csrf
                    @method('PUT')

                    <!-- Project Name -->
                    <div class="form-group">
                        <label for="name">Project Name</label>
                        <input type="text" name="name" id="name" class="form-control"
                            value="{{ old('name', $project->name) }}">
                        <span class="text-danger" id="nameError"></span>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $project->description) }}</textarea>
                        <span class="text-danger" id="descriptionError"></span>
                    </div>

                    <!-- Start Date -->
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="{{ old('start_date', $project->start_date) }}">
                        <span class="text-danger" id="startDateError"></span>
                    </div>

                    <!-- End Date -->
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                            value="{{ old('end_date', $project->end_date) }}">
                        <span class="text-danger" id="endDateError"></span>
                    </div>

                    <!-- Total Budget -->
                    <div class="form-group">
                        <label for="total_budget">Total Budget</label>
                        <input type="number" name="total_budget" id="total_budget" class="form-control"
                            value="{{ old('total_budget', $project->total_budget) }}">
                        <span class="text-danger" id="budgetError"></span>
                    </div>

                    <!-- Location -->
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" name="location" id="location" class="form-control"
                            value="{{ old('location', $project->location) }}">
                        <span class="text-danger" id="locationError"></span>
                    </div>

                    <!-- Related Documents -->
                    <div class="form-group">
                        <label for="documents">Related Document</label>
                        <input type="file" name="documents[]" class="form-control-file" id="documents"
                            accept=".pdf, .doc, .docx">
                        <small class="form-text text-muted">Only one document can be uploaded at a time. Uploading a new
                            document will replace the old one.</small>
                        <span class="text-danger" id="documentsError"></span>
                    </div>

                    <!-- Existing Document -->
                    @if ($currentDocument)
                        <div class="form-group">
                            <label>Current Document</label>
                            <ul>
                                <li>
                                    <a href="{{ Storage::url($currentDocument->document_path) }}"
                                        target="_blank">{{ $currentDocument->original_name }}</a>
                                </li>
                            </ul>
                        </div>
                    @endif


                    <!-- Submit Button with Confirmation -->
                    <button type="button" onclick="validateForm()" class="btn btn-primary btn-block mt-3">Save
                        Changes</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function validateForm() {
            // Clear previous error messages
            document.querySelectorAll('.text-danger').forEach(errorSpan => errorSpan.textContent = '');

            // Get form values
            const name = document.getElementById('name').value;
            const description = document.getElementById('description').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const totalBudget = document.getElementById('total_budget').value;
            const location = document.getElementById('location').value;
            const documents = document.getElementById('documents').files;

            let hasError = false;

            // Validate each field
            if (!name) {
                document.getElementById('nameError').textContent = 'The project name is required.';
                hasError = true;
            } else if (name.length > 255) {
                document.getElementById('nameError').textContent = 'The project name must not exceed 255 characters.';
                hasError = true;
            }

            if (!description) {
                document.getElementById('descriptionError').textContent = 'The project description is required.';
                hasError = true;
            }

            if (!startDate) {
                document.getElementById('startDateError').textContent = 'The start date is required.';
                hasError = true;
            } else if (new Date(startDate) < new Date()) {
                document.getElementById('startDateError').textContent = 'The start date must be today or a future date.';
                hasError = true;
            }

            if (!endDate) {
                document.getElementById('endDateError').textContent = 'The end date is required.';
                hasError = true;
            } else if (startDate && new Date(endDate) <= new Date(startDate)) {
                document.getElementById('endDateError').textContent = 'The end date must be after the start date.';
                hasError = true;
            }

            if (!totalBudget || totalBudget <= 0) {
                document.getElementById('budgetError').textContent = 'The total budget must be a positive number.';
                hasError = true;
            }

            if (!location) {
                document.getElementById('locationError').textContent = 'The location is required.';
                hasError = true;
            } else if (location.length > 255) {
                document.getElementById('locationError').textContent = 'The location must not exceed 255 characters.';
                hasError = true;
            }

            if (documents.length > 1) {
                document.getElementById('documentsError').textContent = 'Only one document can be uploaded at a time.';
                hasError = true;
            } else if (documents.length === 1) {
                const file = documents[0];
                const allowedExtensions = ['pdf', 'doc', 'docx'];
                const fileSizeLimit = 2 * 1024 * 1024; // 2MB
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    document.getElementById('documentsError').textContent = 'Documents must be a PDF, DOC, or DOCX file.';
                    hasError = true;
                } else if (file.size > fileSizeLimit) {
                    document.getElementById('documentsError').textContent = 'Documents must not exceed 2MB in size.';
                    hasError = true;
                }
            }

            // Submit the form if no errors
            if (!hasError) {
                if (confirm("Are you sure you want to update the project?")) {
                    document.getElementById('editProjectForm').submit();
                }
            }
        }
    </script>
@endsection
