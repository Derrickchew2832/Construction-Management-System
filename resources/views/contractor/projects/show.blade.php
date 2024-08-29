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
        @if ($quote)
            @if ($quote->status === 'submitted')
                <div class="alert alert-info mt-3">Your quote has been submitted and is awaiting approval.</div>
            @elseif ($quote->status === 'suggested' && $quote->suggested_by === 'project_manager')
                <div class="alert alert-warning mt-3">The project manager has suggested a new price.</div>
                <p><strong>Suggested Price:</strong> ${{ number_format($quote->quoted_price, 2) }}</p>
                <form action="{{ route('contractor.respondToSuggestion', $project->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="quote_id" value="{{ $quote->id }}">
                    <div class="form-group">
                        <label for="new_price">New Price (if resubmitting)</label>
                        <input type="number" name="new_price" class="form-control" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="new_pdf">Upload New Quote (PDF, if resubmitting)</label>
                        <input type="file" name="new_pdf" class="form-control" accept="application/pdf">
                    </div>
                    <button type="submit" name="action" value="accept" class="btn btn-success">Accept Suggestion</button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                    <button type="submit" name="action" value="resubmit" class="btn btn-warning">Resubmit with New Quote</button>
                </form>
            @elseif ($quote->status === 'approved')
                <div class="alert alert-success mt-3">Your quote has been approved. You are now the main contractor for this project.</div>
            @elseif ($quote->status === 'rejected')
                <div class="alert alert-danger mt-3">Your quote was rejected. Please contact the project manager for more details.</div>
            @endif
        @else
            @if ($invitation->status === 'pending')
                <form action="{{ route('contractor.projects.submitQuote', $project->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="quoted_price">Quote Price</label>
                        <input type="number" name="quoted_price" class="form-control" required step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="quote_pdf">Upload Quote (PDF)</label>
                        <input type="file" name="quote_pdf" class="form-control" accept="application/pdf" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Quote</button>
                </form>
            @endif
        @endif
    @else
        <div class="alert alert-warning mt-3">You have not been invited to this project.</div>
    @endif
</div>
@endsection
