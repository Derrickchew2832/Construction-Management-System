<!-- File: resources/views/project_manager/projects/index.blade.php -->

@extends('layouts.projectmanagerapp')

@section('title', 'Projects')

@section('content')
<div class="container mt-4">
    <h1>Projects</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->description }}</td>
                    <td>
                        <!-- Correctly pass the project ID to the show route -->
                        <a href="{{ route('project_manager.projects.show', $project->id) }}" class="btn btn-primary">View</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
