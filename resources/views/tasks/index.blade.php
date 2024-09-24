@extends('layouts.management')

@section('content')
<div class="container-fluid">

    <div class="task-board row">
        <!-- Role-based Conditional Rendering -->
        @if($userRole == $isMainContractor)
            @include('tasks.partials.task_full_control', ['categorizedTasks' => $categorizedTasks])
        @elseif($userRole == 'contractor')
            @include('tasks.partials.task_limited_control', ['categorizedTasks' => $categorizedTasks])
        @elseif($userRole =='project_manager' || 'client')
            @include('tasks.partials.task_view_only', ['categorizedTasks' => $categorizedTasks])
        @endif
    </div>
</div>
@endsection
