@extends('layouts.management')

@section('title', 'Project Photos')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-muted">Project Photos</h4>
        <!-- Button to trigger modal -->
        <button type="button" id="uploadPhotoButton" class="btn btn-primary" data-toggle="modal" data-target="#uploadPhotoModal">
            <i class="fas fa-upload"></i> Upload Photo
        </button>
    </div>

    @if($photos->isEmpty())
        <p class="text-center text-muted">No photos available for this project.</p>
    @else
        <!-- Display Photos Table -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date & Time Uploaded</th>
                    <th>Uploaded By</th>
                    <th>Related Task</th>
                    <th>Description</th>
                    <th>Photo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($photos as $photo)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($photo->created_at)->format('d M Y, h:i A') }}</td>
                        <td>{{ $photo->uploaded_by_name }}</td>
                        <td>{{ $photo->task_name ?? '-' }}</td>
                        <td>{{ $photo->description }}</td>
                        <td>
                            <a href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="Project Photo" width="80" class="img-thumbnail">
                            </a>
                            <br>
                            <a href="{{ asset('storage/' . $photo->photo_path) }}" download class="btn btn-sm btn-secondary mt-2">Download</a>
                        </td>
                        <td>
                            <!-- Delete button only if the user uploaded the photo -->
                            @if (Auth::id() === $photo->uploaded_by)
                                <form action="{{ route('tasks.photos.delete', ['projectId' => $projectId, 'photoId' => $photo->id]) }}" method="POST" class="d-inline" onsubmit="return confirmDelete()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger delete-photo-button">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<!-- Modal for uploading a new photo -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-labelledby="uploadPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadPhotoModalLabel">Upload New Photo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('tasks.photos.upload', $projectId) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirmUpload()">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="photo">Choose Photo</label>
                        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*" required>
                        @error('photo')
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
                    <button type="submit" class="btn btn-primary">Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const projectStatus = "{{ $project->status }}";

        if (projectStatus === 'completed') {
            // Disable the upload photo button
            const uploadPhotoButton = document.querySelector('[data-target="#uploadPhotoModal"]');
            if (uploadPhotoButton) {
                uploadPhotoButton.disabled = true;
                uploadPhotoButton.style.backgroundColor = '#d3d3d3';
                uploadPhotoButton.style.cursor = 'not-allowed';
                uploadPhotoButton.style.color = '#6c757d';
            }

            // Disable all delete buttons
            const deleteButtons = document.querySelectorAll('.delete-photo-button');
            deleteButtons.forEach(button => {
                button.disabled = true;
                button.style.backgroundColor = '#d3d3d3';
                button.style.cursor = 'not-allowed';
                button.style.color = '#6c757d';
            });
        }
    });

    function confirmUpload() {
        return confirm("Are you sure you want to upload this photo?");
    }

    function confirmDelete() {
        return confirm("Are you sure you want to delete this photo?");
    }
</script>
@endpush
