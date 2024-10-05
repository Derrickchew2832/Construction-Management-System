@extends('layouts.apps')

@section('content')
<div class="container">
    
    <div class="card mb-3">
        <div class="card-header">Task Information</div>
        <div class="card-body">
            <p><strong>Description:</strong> {{ $task->description }}</p>
            <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($task->start_date)->format('d M Y') }}</p>
            <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($task->status) }}</p>
            @if($task->task_pdf)
                <p><strong>Task PDF:</strong> <a href="{{ asset('storage/' . $task->task_pdf) }}" target="_blank">View PDF</a></p>
            @else
                <p><strong>Task PDF:</strong> Not available</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Contractor Information</div>
        <div class="card-body">
            <p><strong>Contractor Name:</strong> {{ $task->contractor_name }}</p>

            <!-- Hide quote information from clients and project managers -->
            @if(!auth()->user()->hasRole('client') && !auth()->user()->hasRole('project_manager'))
                <p><strong>Quoted Price:</strong> ${{ number_format($task->quoted_price, 2) }}</p>
                <p><strong>Quote Suggestion:</strong> {{ $task->quote_suggestion ?? 'No suggestion provided' }}</p>
                @if($task->quote_pdf)
                    <p><strong>Quote PDF:</strong> <a href="{{ asset('storage/' . $task->quote_pdf) }}" target="_blank">View Quote PDF</a></p>
                @else
                    <p><strong>Quote PDF:</strong> Not available</p>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
