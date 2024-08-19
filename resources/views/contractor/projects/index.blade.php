@extends('layouts.contractorapp')

@section('title', 'Projects')

@section('content')
<div class="container mt-4">
    <h1>Projects</h1>

    <div class="row">
        @foreach($projects as $project)
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $project->name }}</h5>
                    <p class="card-text">{{ $project->description }}</p>
                    <p><strong>Location:</strong> {{ $project->location }}</p>
                    <p><strong>Start Date:</strong> {{ $project->start_date }}</p>
                    <p><strong>End Date:</strong> {{ $project->end_date }}</p>
                    <a href="{{ route('contractor.projects.show', $project->id) }}" class="btn btn-primary">View Project</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
