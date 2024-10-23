@extends('layouts.contractorapp')

@section('title', 'Quote Management')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4 text-primary font-weight-bold">Quote Management</h3>

        <!-- Include Project Quotes -->
        @include('contractor.projects.project-quotes')

        <!-- Include Task Quotes -->
        @include('contractor.projects.task-quotes')
    </div>
@endsection
