@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Project Details</h1>
    <div class="card mb-4">
        <div class="card-header">{{ $project->name }}</div>
        <div class="card-body">
            <p>{{ $project->description }}</p>
            <p><strong>Start Date:</strong> {{ $project->start_date }}</p>
            <p><strong>End Date:</strong> {{ $project->end_date }}</p>
            <p><strong>Total Budget:</strong> {{ $project->total_budget }}</p>
            <p><strong>Budget Remaining:</strong> {{ $project->budget_remaining }}</p>
            <p><strong>Location:</strong> {{ $project->location }}</p>
            <p><strong>Main Contractor:</strong> {{ $project->main_contractor_id ? $project->mainContractor->name : 'Not Appointed' }}</p>
        </div>
    </div>
    <h2>Related Documents</h2>
    <ul>
        @foreach ($project->documents as $document)
            <li><a href="{{ Storage::url($document->document_path) }}" target="_blank">{{ $document->document_path }}</a></li>
        @endforeach
    </ul>
    <h2>Invite Users</h2>
    <a href="{{ route('projectmanager.projects.invite', $project->id) }}" class="btn btn-primary">Invite Users</a>
    <h2>Quotes</h2>
    <form action="{{ route('projectmanager.projects.quote', $project->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="contractor_id">Select Contractor</label>
            <select name="contractor_id" class="form-control" required>
                @foreach ($users->where('role', 'contractor') as $contractor)
                    <option value="{{ $contractor->id }}">{{ $contractor->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="quoted_price">Quoted Price</label>
            <input type="number" name="quoted_price" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit Quote</button>
    </form>
    <h2>Appoint Main Contractor</h2>
    <form action="{{ route('projectmanager.projects.appointMainContractor', $project->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="contractor_id">Select Contractor</label>
            <select name="contractor_id" class="form-control" required>
                @foreach ($project->contractors as $contractor)
                    <option value="{{ $contractor->contractor_id }}">{{ $contractor->contractor->name }} ({{ $contractor->quoted_price }})</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Appoint Main Contractor</button>
    </form>
</div>
@endsection
