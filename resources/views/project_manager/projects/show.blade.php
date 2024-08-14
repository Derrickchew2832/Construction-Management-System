<!-- File: resources/views/project_manager/projects/show.blade.php -->

@extends('layouts.projectmanagerapp')

@section('title', 'Project Details')

@section('content')
<div class="container mt-4">
    <h1>{{ $project->name }}</h1>
    <p>{{ $project->description }}</p>
    <p>Location: {{ $project->location }}</p>
    <p>Start Date: {{ $project->start_date }}</p>
    <p>End Date: {{ $project->end_date }}</p>
    <p>Total Budget: ${{ $project->total_budget }}</p>
    <p>Remaining Budget: ${{ $project->budget_remaining }}</p>
</div>
@endsection
