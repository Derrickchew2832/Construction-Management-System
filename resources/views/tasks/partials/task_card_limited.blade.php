<div class="task-card">
    <h5>{{ $task->title }} @if(isset($task->category)) ({{ $task->category }}) @endif</h5>
    <p>{{ $task->description }}</p>
    <span>Status: {{ $task->status }}</span>
    @if($task->status != 'completed')
        <form action="{{ route('tasks.update', [$project->id, $task->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="completed">
            <button type="submit" class="btn btn-sm btn-success">Mark as Completed</button>
        </form>
    @endif
</div>
