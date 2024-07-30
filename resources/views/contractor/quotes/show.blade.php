@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Quote Details</h1>
    <div class="card mb-4">
        <div class="card-header">{{ $quote->project->name }}</div>
        <div class="card-body">
            <p>{{ $quote->project->description }}</p>
            <p><strong>Start Date:</strong> {{ $quote->project->start_date }}</p>
            <p><strong>End Date:</strong> {{ $quote->project->end_date }}</p>
            <p><strong>Total Budget:</strong> {{ $quote->project->total_budget }}</p>
            <p><strong>Budget Remaining:</strong> {{ $quote->project->budget_remaining }}</p>
            <p><strong>Location:</strong> {{ $quote->project->location }}</p>
        </div>
    </div>
    <form action="{{ route('contractor.quotes.update', $quote->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="quoted_price">Quoted Price</label>
            <input type="number" name="quoted_price" class="form-control" value="{{ $quote->quoted_price }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Quote</button>
    </form>
</div>
@endsection
