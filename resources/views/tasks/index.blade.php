@extends('layouts.management')

@section('content')
<div class="container-fluid">
    <h1>Task Management Board - {{ $project->name }}</h1>

    <div class="task-board row">
        <!-- Role-based Conditional Rendering -->
        @if($userRole == 'project_manager' || $isMainContractor)
            <!-- Full control for Project Manager and Main Contractor -->
            @include('tasks.partials.task_full_control', ['categorizedTasks' => $categorizedTasks])
        @elseif($userRole == 'contractor')
            <!-- Limited control for Contractors -->
            @include('tasks.partials.task_limited_control', ['categorizedTasks' => $categorizedTasks])
        @elseif($userRole == 'client')
            <!-- View-only for Clients -->
            @include('tasks.partials.task_view_only', ['categorizedTasks' => $categorizedTasks])
        @endif
    </div>
</div>
@endsection
