<div class="task-category col">
    <h4>Tasks</h4>
    <div class="task-list border rounded p-2">
        @foreach($categorizedTasks as $category => $tasks)
            <h5>{{ ucfirst($category) }} ({{ $tasks->count() }})</h5>
            @if($tasks->isEmpty())
                <p>No tasks available in this category.</p>
            @else
                @foreach($tasks as $task)
                    @include('tasks.partials.task_card', ['task' => $task])
                @endforeach
            @endif
            <a href="#" class="new-task-link" data-toggle="modal" data-target="#newTaskModal">+ New Task</a>
        @endforeach
    </div>
</div>
