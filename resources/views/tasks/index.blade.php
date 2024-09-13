@extends('layouts.management')

@section('content')
<div class="container-fluid">
    <h1 style="font-size: 1.5rem;">Task Management Board - {{ $project->name }}</h1>

    <div class="task-board row">
        <!-- Due Today Section -->
        <div class="task-category col">
            <h4>Due Today ({{ count($categorizedTasks['due_today']) }})</h4>
            <div class="task-list border rounded p-2">
                @if($categorizedTasks['due_today']->isEmpty())
                    <a href="#" class="new-task-link" data-toggle="modal" data-target="#newTaskModal">+ New Task</a>
                @else
                    @foreach($categorizedTasks['due_today'] as $task)
                        @include('tasks.partials.task_card', ['task' => $task, 'project' => $project])
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Priority 1 Section -->
        <div class="task-category col">
            <h4>Priority 1 ({{ count($categorizedTasks['priority_1']) }})</h4>
            <div class="task-list border rounded p-2">
                @if($categorizedTasks['priority_1']->isEmpty())
                    <a href="#" class="new-task-link" data-toggle="modal" data-target="#newTaskModal">+ New Task</a>
                @else
                    @foreach($categorizedTasks['priority_1'] as $task)
                        @include('tasks.partials.task_card', ['task' => $task, 'project' => $project])
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Priority 2 Section -->
        <div class="task-category col">
            <h4>Priority 2 ({{ count($categorizedTasks['priority_2']) }})</h4>
            <div class="task-list border rounded p-2">
                @if($categorizedTasks['priority_2']->isEmpty())
                    <a href="#" class="new-task-link" data-toggle="modal" data-target="#newTaskModal">+ New Task</a>
                @else
                    @foreach($categorizedTasks['priority_2'] as $task)
                        @include('tasks.partials.task_card', ['task' => $task, 'project' => $project])
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Priority 3 Section -->
        <div class="task-category col">
            <h4>Priority 3 ({{ count($categorizedTasks['priority_3']) }})</h4>
            <div class="task-list border rounded p-2">
                @if($categorizedTasks['priority_3']->isEmpty())
                    <a href="#" class="new-task-link" data-toggle="modal" data-target="#newTaskModal">+ New Task</a>
                @else
                    @foreach($categorizedTasks['priority_3'] as $task)
                        @include('tasks.partials.task_card', ['task' => $task, 'project' => $project])
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Completed Section -->
        <div class="task-category col">
            <h4>Completed ({{ count($categorizedTasks['completed']) }})</h4>
            <div class="task-list border rounded p-2">
                @if($categorizedTasks['completed']->isEmpty())
                    <a href="#" class="new-task-link">+ New Task</a>
                @else
                    @foreach($categorizedTasks['completed'] as $task)
                        @include('tasks.partials.task_card', ['task' => $task, 'project' => $project])
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Verified Section -->
        <div class="task-category col">
            <h4>Verified ({{ count($categorizedTasks['verified']) }})</h4>
            <div class="task-list border rounded p-2">
                @if($categorizedTasks['verified']->isEmpty())
                    <a href="#" class="new-task-link">+ New Task</a>
                @else
                    @foreach($categorizedTasks['verified'] as $task)
                        @include('tasks.partials.task_card', ['task' => $task, 'project' => $project])
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- New Task Modal -->
<div class="modal fade" id="newTaskModal" tabindex="-1" aria-labelledby="newTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTaskModalLabel">Create New Task for {{ $project->name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="taskTitle">Task Title</label>
                        <input type="text" class="form-control" id="taskTitle" placeholder="Enter task title">
                    </div>
                    <div class="form-group">
                        <label for="taskDescription">Task Description</label>
                        <textarea class="form-control" id="taskDescription" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="taskPriority">Priority</label>
                        <select class="form-control" id="taskPriority">
                            <option>Priority 1</option>
                            <option>Priority 2</option>
                            <option>Priority 3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="taskDueDate">Due Date</label>
                        <input type="date" class="form-control" id="taskDueDate">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save Task</button>
            </div>
        </div>
    </div>
</div>
@endsection
