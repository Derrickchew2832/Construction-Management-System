<div class="task-category 
    @if($task->status == 'under_negotiation') category-negotiation
    @elseif($task->status == 'due_date') category-due-date
    @elseif($task->status == 'priority_1') category-priority-1
    @elseif($task->status == 'priority_2') category-priority-2
    @elseif($task->status == 'completed') category-completed
    @elseif($task->status == 'verified') category-verified
    @endif
">
    <div class="task-card mb-2" data-task-id="{{ $task->id ?? 'undefined' }}" data-project-id="{{ $projectId ?? 'undefined' }}">
        <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
            <h6>{{ $task->title }}</h6>
            <p>{{ $task->description }}</p>
        </a>
    </div>
</div>
