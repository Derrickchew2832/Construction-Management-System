<div class="container mt-3 px-10">
    <div class="row">
        <!-- Category: Under Negotiation -->
        <div class="col-md-2">
            <h6 class="text-muted">UnderNegotiation (<span class="task-count"
                    data-category="under_negotiation">{{ $categorizedTasks['under_negotiation']->count() }}</span>)</h6>
            <div class="task-category category-negotiation" data-category="under_negotiation">
                @if ($categorizedTasks['under_negotiation']->isEmpty())
                    <p class="text-muted">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['under_negotiation'] as $task)
                        <div class="task-card mb-2" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to:{{ $task->contractor_email ?? 'Unassigned' }}</p>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Due Date -->
        <div class="col-md-2">
            <h6 class="text-muted">Due Date (<span class="task-count"
                    data-category="due_date">{{ $categorizedTasks['due_date']->count() }}</span>)</h6>
            <div class="task-category category-due-date" data-category="due_date">
                @if ($categorizedTasks['due_date']->isEmpty())
                    <p class="text-muted">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['due_date'] as $task)
                        <div class="task-card mb-2" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to:{{ $task->contractor_email ?? 'Unassigned' }}</p>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Priority 1 -->
        <div class="col-md-2">
            <h6 class="text-muted">Priority 1 (<span class="task-count"
                    data-category="priority_1">{{ $categorizedTasks['priority_1']->count() }}</span>)</h6>
            <div class="task-category category-priority-1" data-category="priority_1">
                @if ($categorizedTasks['priority_1']->isEmpty())
                    <p class="text-muted">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['priority_1'] as $task)
                        <div class="task-card mb-2" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>{{ $task->contractor_email ?? 'Unassigned' }}</p>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Priority 2 -->
        <div class="col-md-2">
            <h6 class="text-muted">Priority 2 (<span class="task-count"
                    data-category="priority_2">{{ $categorizedTasks['priority_2']->count() }}</span>)</h6>
            <div class="task-category category-priority-2" data-category="priority_2">
                @if ($categorizedTasks['priority_2']->isEmpty())
                    <p class="text-muted">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['priority_2'] as $task)
                        <div class="task-card mb-2" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to:{{ $task->contractor_email ?? 'Unassigned' }}</p>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Completed -->
        <div class="col-md-2">
            <h6 class="text-muted">Completed (<span class="task-count"
                    data-category="completed">{{ $categorizedTasks['completed']->count() }}</span>)</h6>
            <div class="task-category category-completed" data-category="completed">
                @if ($categorizedTasks['completed']->isEmpty())
                    <p class="text-muted">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['completed'] as $task)
                        <div class="task-card mb-2" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to:{{ $task->contractor_email ?? 'Unassigned' }}</p>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Verified -->
        <div class="col-md-2">
            <h6 class="text-muted">Verified (<span class="task-count"
                    data-category="verified">{{ $categorizedTasks['verified']->count() }}</span>)</h6>
            <div class="task-category category-verified" data-category="verified">
                @if ($categorizedTasks['verified']->isEmpty())
                    <p class="text-muted">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['verified'] as $task)
                        <div class="task-card mb-2" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to:{{ $task->contractor_email ?? 'Unassigned' }}</p>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" role="dialog" aria-labelledby="taskDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskDetailsModalLabel">Task Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Task details content will be loaded here dynamically -->
                <div id="task-details-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Task-card click functionality for viewing details
        $(document).on('click', '.task-card', function(event) {
            event.preventDefault();

            // Get the task and project IDs from the clicked task card
            var taskId = $(this).data('task-id');
            var projectId = $(this).data('project-id');

            // Logging the values for debugging
            console.log("Task ID:", taskId);
            console.log("Project ID:", projectId);

            // Check if taskId and projectId are valid before making the AJAX request
            if (taskId && projectId) {
                $.ajax({
                    url: '/projects/' + projectId + '/tasks/' + taskId + '/details',
                    type: 'GET',
                    success: function(response) {
                        if (response) {
                            // If response is valid, show the task details in the modal
                            $('#task-details-content').html(response);
                            $('#taskDetailsModal').modal('show');
                        } else {
                            // Handle case where the server returns an empty or invalid response
                            alert('Task details could not be loaded. Please try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Log detailed error to the console for debugging
                        console.error('Error fetching task details:', xhr.responseText);
                        alert('Error fetching task details. Please try again.');
                    }
                });
            } else {
                // Handle case where taskId or projectId is missing or undefined
                if (!taskId) {
                    console.error('Task ID is missing.');
                    alert('Task ID is not defined. Please try again.');
                }
                if (!projectId) {
                    console.error('Project ID is missing.');
                    alert('Project ID is not defined. Please try again.');
                }
            }
        });
    });
</script>
