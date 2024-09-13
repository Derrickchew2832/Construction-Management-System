<div class="task-card card mb-3 shadow-sm">
    <div class="card-body">
        <div class="task-header">
            <h5 class="card-title">{{ $task->title }}</h5>
            <p class="card-text">{{ $task->description }}</p>
        </div>
        <div class="task-meta">
            <p class="mb-1"><strong>Due:</strong> {{ $task->due_date }}</p>
            <p class="mb-1"><strong>Priority:</strong> {{ $task->priority }}</p>
            <p class="mb-1"><strong>Status:</strong> {{ ucfirst($task->status) }}</p>
        </div>
        <div class="task-actions d-flex justify-content-between mt-2">
            <a href="{{ route('tasks.edit', ['projectId' => $projectId, 'taskId' => $task->id]) }}" class="btn btn-sm btn-warning">Edit</a>
            <form action="{{ route('tasks.destroy', ['projectId' => $projectId, 'taskId' => $task->id]) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>
