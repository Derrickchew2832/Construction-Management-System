<div class="container mt-5">
    <h3>Task Management Board</h3>
    <div class="row">
        <!-- Iterate over each category like Due Today, Priority 1, Priority 2, etc. -->
        @foreach($categorizedTasks as $category => $tasks)
            <div class="col-md-2">
                <div class="task-category border rounded p-2">
                    <h5>{{ ucfirst($category) }} ({{ $tasks->count() }})</h5>
                    
                    <!-- Check if there are no tasks in this category -->
                    @if($tasks->isEmpty())
                        <p>No tasks available in this category.</p>
                    @else
                        <!-- Loop through each task in the category and include the task card -->
                        @foreach($tasks as $task)
                            @include('tasks.partials.task_card', ['task' => $task])
                        @endforeach
                    @endif

                    <!-- New Task button to create a task in this category -->
                    <a href="#" class="new-task-link" data-toggle="modal" data-target="#newTaskModal" data-category="{{ $category }}">
                        + New Task
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal for creating a new task -->
    <div class="modal fade" id="newTaskModal" tabindex="-1" aria-labelledby="newTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tasks.store', $project->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="newTaskModalLabel">New Task</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Task Title</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Task Description</label>
                            <textarea name="description" id="description" class="form-control" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select name="priority" id="priority" class="form-control" required>
                                <option value="1">Priority 1</option>
                                <option value="2">Priority 2</option>
                                <option value="3">Priority 3</option>
                                <option value="completed">Completed</option>
                                <option value="verified">Verified</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include necessary scripts for modal and Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Optional JavaScript for further modal functionalities -->
<script>
    // When clicking "New Task" link, pass the task category to the modal
    $('.new-task-link').on('click', function () {
        let category = $(this).data('category');
        $('#newTaskModal input[name="category"]').val(category);
    });
</script>
