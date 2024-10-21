@extends('layouts.management')

@section('title', 'Project Files')

@section('content')
<div class="container mt-4">
    <h1>Project Files</h1>

    <!-- File Upload Form -->
    <form action="{{ route('tasks.photos.upload', $projectId) }}" method="POST" enctype="multipart/form-data">

        @csrf
        <div class="form-group">
            <label for="file">Upload File</label>
            <input type="file" name="file" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <hr>

    <!-- Display Files -->
    <div class="list-group">
        @foreach ($files as $file)
            <div class="list-group-item">
                <h5 class="mb-1">{{ $file->original_name }}</h5>
                <p class="mb-1">{{ $file->description }}</p>
                <p><strong>Uploaded by:</strong> {{ \App\Models\User::find($file->uploaded_by)->name }}</p>
                <a href="{{ asset('storage/' . $file->file_path) }}" class="btn btn-sm btn-outline-primary">Download</a>
            </div>
        @endforeach
    </div>
</div>
@endsection
