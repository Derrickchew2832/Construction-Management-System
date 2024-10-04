<div class="task-category 
    @switch($task->category)
        @case('under_negotiation')
            category-negotiation
            @break
        @case('due_date')
            category-due-date
            @break
        @case('priority_1')
            category-priority-1
            @break
        @case('priority_2')
            category-priority-2
            @break
        @case('completed')
            category-completed
            @break
        @case('verified')
            category-verified
            @break
        @default
            category-default
    @endswitch
" data-category="{{ $task->category }}">
    @if(isset($task->id) && isset($projectId))
        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}" data-project-id="{{ $projectId }}">
            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                <h6>{{ $task->title }}</h6>
                <p>{{ $task->description }}</p>
            </a>
        </div>
    @else
        <div class="task-card mb-2" draggable="false">
            <p>Task details missing</p>
        </div>
    @endif
</div>
