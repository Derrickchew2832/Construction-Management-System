@extends('layouts.management')

@section('title', 'Project Files')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-muted">Project Files</h4>
        <!-- Button to trigger file upload modal -->
        <button type="button" class="btn btn-primary" id="uploadFileButton" data-toggle="modal" data-target="#uploadFileModal">
            <i class="fas fa-upload"></i> Upload File
        </button>
    </div>

    @if($files->isEmpty())
        <p class="text-center text-muted">No files available for this project.</p>
    @else
        <!-- Display Files Table -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Description</th>
                    <th>Task Tag</th>
                    <th>Uploaded By</th>
                    <th>Date Uploaded</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($files as $file)
                    <tr>
                        <td>{{ $file->original_name }}</td>
                        <td>{{ $file->description }}</td>
                        <td>{{ $file->task_name ?? '-' }}</td>
                        <td>{{ $file->uploaded_by_name }}</td>
                        <td>{{ $file->upload_date }}</td>
                        <td>
                            @php
                                $fileExtension = pathinfo($file->file_path, PATHINFO_EXTENSION);
                                $isViewable = in_array($fileExtension, ['pdf', 'jpg', 'jpeg', 'png']);
                            @endphp
                            @if ($isViewable)
                                <!-- View File in Browser -->
                                <a href="{{ asset('storage/' . $file->file_path) }}" class="btn btn-sm btn-primary" target="_blank">View</a>
                            @endif
                            <!-- Download Option for All Files -->
                            <a href="{{ asset('storage/' . $file->file_path) }}" class="btn btn-sm btn-secondary" download>Download</a>

                            <!-- Delete Button for Uploader Only -->
                            @if (Auth::id() === $file->uploaded_by)
                                <form action="{{ route('tasks.files.delete', ['projectId' => $projectId, 'fileId' => $file->id]) }}" method="POST" class="d-inline" onsubmit="return confirmDelete()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger delete-file-button">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<!-- Modal for uploading a new file -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadFileModalLabel">Upload New File</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('tasks.files.upload', $projectId) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirmUpload()">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Choose File</label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">Supported formats: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG, etc.</small>
                        @error('file')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="task_id">Related Task (Optional)</label>
                        <select name="task_id" class="form-control @error('task_id') is-invalid @enderror">
                            <option value="">No Task Tag</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->title }}</option>
                            @endforeach
                        </select>
                        @error('task_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" required></textarea>
                        @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadFileConfirmButton">Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.onload = function () {
    const projectStatus = "{{ $project->status }}";

    if (projectStatus === 'completed') {
        // Disable the upload file button
        const uploadFileButton = document.getElementById('uploadFileButton');
        if (uploadFileButton) {
            uploadFileButton.disabled = true;
            uploadFileButton.style.backgroundColor = '#d3d3d3'; // Gray background
            uploadFileButton.style.cursor = 'not-allowed';       // Change cursor to not allowed
            uploadFileButton.style.color = '#6c757d';            // Gray text color
        }

        // Disable all delete buttons
        const deleteButtons = document.querySelectorAll('.delete-file-button');
        deleteButtons.forEach(button => {
            button.disabled = true;
            button.style.backgroundColor = '#d3d3d3'; // Gray background
            button.style.cursor = 'not-allowed';       // Change cursor to not allowed
            button.style.color = '#6c757d';            // Gray text color
        });
    }
};


    function confirmUpload() {
        return confirm("Are you sure you want to upload this file?");
    }

    function confirmDelete() {
        return confirm("Are you sure you want to delete this file?");
    }
</script>

@endpush
