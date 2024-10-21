@extends('layouts.management')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-muted">Project Photos</h4>
        <!-- Button to trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadPhotoModal">
            <i class="fas fa-upload"></i> Upload Photo
        </button>
    </div>

    @if($photos->isEmpty())
        <p class="text-center text-muted">No photos available for this project.</p>
    @else
        <div class="row">
            @foreach($photos as $photo)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <img src="{{ asset('storage/' . $photo->photo_path) }}" class="card-img-top" alt="Project Photo">
                        <div class="card-body">
                            <p class="card-text">{{ $photo->description ?? 'No description provided.' }}</p>
                            <p class="card-text">
                                <small class="text-muted">Uploaded by: {{ $photo->uploaded_by }}</small>
                                <br>
                                <small class="text-muted">Related Task: {{ $photo->task_name ?? 'No task associated' }}</small>
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
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
                        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" required>
                        @error('photo')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="task_id">Related Task</label>
                        <select name="task_id" class="form-control @error('task_id') is-invalid @enderror" required>
                            <option value="">Select Task</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->title }}</option> <!-- Updated to use 'title' -->
                            @endforeach
                        </select>
                        @error('task_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Description (optional)</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"></textarea>
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
    function confirmUpload() {
        return confirm("Are you sure you want to upload this photo?");
    }
</script>
@endpush
