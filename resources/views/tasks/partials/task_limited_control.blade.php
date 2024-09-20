<div class="task-category col">
    <h4>Tasks</h4>
    <div class="task-list border rounded p-2">
        @foreach($categorizedTasks as $category => $tasks)
            <h5>{{ ucfirst($category) }} ({{ $tasks->count() }})</h5>
            @if($tasks->isEmpty())
                <p>No tasks available in this category.</p>
            @else
                @foreach($tasks as $task)
                    @if($task->assigned_to == Auth::id())
                        @include('tasks.partials.task_card_limited', ['task' => $task])
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>
</div>
