@extends('layouts.contractorapp')

@section('title', 'Quote Management')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Quote Management</h1>

        <!-- Include Project Quotes -->
        @include('contractor.projects.project-quotes')

        <!-- Include Task Quotes -->
        @include('contractor.projects.task-quotes')
    </div>
@endsection
