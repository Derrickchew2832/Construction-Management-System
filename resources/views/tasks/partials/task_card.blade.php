<div class="task-card">
    <h5>{{ $task->title }} ({{ $task->priority }})</h5>
    <p>{{ $task->description }}</p>
    <span>Status: {{ $task->status }}</span>
    <a href="{{ route('tasks.edit', [$project->id, $task->id]) }}" class="btn btn-sm btn-primary">Edit</a>
</div>