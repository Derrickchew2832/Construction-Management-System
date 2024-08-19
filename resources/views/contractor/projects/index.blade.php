@extends('layouts.contractorapp')

@section('title', 'My Projects')

@section('content')
<div class="container mt-4">
    <h1>My Projects</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($projects->isEmpty())
        <p>You have no projects assigned.</p>
    @else
        <div class="row">
            @foreach ($projects as $project)
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">{{ $project->name }}</h5>
                            <p class="card-text">{{ $project->description }}</p>
                            <p><strong>Location:</strong> {{ $project->location }}</p>
                            <p><strong>Start Date:</strong> {{ $project->start_date }}</p>
                            <p><strong>End Date:</strong> {{ $project->end_date }}</p>
                            <a href="{{ route('contractor.quotes.show', $project->id) }}" class="btn btn-primary">View Quote</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
