@extends('layouts.contractorapp')

@section('title', 'Project Details')

@section('content')
<div class="container mt-4">
    <h1>Project Details</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $project->name }}</h5>
            <p class="card-text">{{ $project->description }}</p>
            <p><strong>Location:</strong> {{ $project->location }}</p>
            <p><strong>Start Date:</strong> {{ $project->start_date }}</p>
            <p><strong>End Date:</strong> {{ $project->end_date }}</p>
        </div>
    </div>

    @if ($invitation)
        @if ($invitation->status === 'pending')
            <form action="{{ route('contractor.projects.submitQuote', $project->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="quoted_price">Quote Price</label>
                    <input type="number" name="quoted_price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="quote_pdf">Upload Quote (PDF)</label>
                    <input type="file" name="quote_pdf" class="form-control" accept="application/pdf" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit Quote</button>
            </form>
        @elseif ($invitation->status === 'submitted')
            <div class="alert alert-info mt-3">Your quote has been submitted and is awaiting approval.</div>
        @elseif ($invitation->status === 'approved')
            <div class="alert alert-success mt-3">Your quote has been approved. You are now the main contractor for this project.</div>
        @elseif ($invitation->status === 'rejected')
            <div class="alert alert-danger mt-3">Your quote was rejected. Please contact the project manager for more details.</div>
        @endif
    @else
        <div class="alert alert-warning mt-3">You have not been invited to this project.</div>
    @endif
</div>
@endsection
