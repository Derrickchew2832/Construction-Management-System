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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to: {{ $task->contractor_email ?? 'Unassigned' }}</p>
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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to: {{ $task->contractor_email ?? 'Unassigned' }}</p>
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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to: {{ $task->contractor_email ?? 'Unassigned' }}</p>
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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to: {{ $task->contractor_email ?? 'Unassigned' }}</p>
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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to: {{ $task->contractor_email ?? 'Unassigned' }}</p>
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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
                            data-project-id="{{ $projectId }}">
                            <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}">
                                <h6>{{ $task->title }}</h6>
                                <p>{{ $task->description }}</p>
                                <p>Assigned to: {{ $task->contractor_email ?? 'Unassigned' }}</p>
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
                <div id="task-details-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for confirmation when moving to completed -->
<div class="modal fade" id="confirmMoveModal" tabindex="-1" aria-labelledby="confirmMoveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmMoveModalLabel">Confirm Move to Completed</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to move this task to Completed? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmMoveButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let draggedTask = null;
        let currentCategory = null; // Define currentCategory outside the event listener

        // Dragging the task card
        $('.task-card').on('dragstart', function(event) {
            draggedTask = $(this); // jQuery object for the dragged element

            // Fetch the task ID and project ID directly from the dragged task card
            const taskId = draggedTask.data('task-id');
            const projectId = draggedTask.data('project-id'); // Get the project ID from the task card
            currentCategory = draggedTask.closest('.task-category').data('category'); // Save the current category globally

            // Check if any of the required data is missing
            if (!taskId || !currentCategory || !projectId) {
                console.error('Task ID, Category, or Project ID is missing on dragstart.');
                event.preventDefault();
                return;
            }

            // Transfer the data for drag and drop
            event.originalEvent.dataTransfer.setData('taskId', taskId);
            event.originalEvent.dataTransfer.setData('category', currentCategory);
            event.originalEvent.dataTransfer.setData('projectId', projectId); // Pass projectId
        });

        // On drop
        $('.task-category').on('drop', function(event) {
            event.preventDefault();

            const taskId = event.originalEvent.dataTransfer.getData('taskId');
            const newCategory = $(this).data('category');
            const projectId = event.originalEvent.dataTransfer.getData('projectId'); // Retrieve projectId from dragged task

            // Check if the task can be moved to Completed
            const canMoveToCompleted = (currentCategory === 'due_date' || currentCategory === 'priority_1' || currentCategory === 'priority_2') && newCategory === 'completed';

            if (canMoveToCompleted) {
                // Show confirmation modal
                $('#confirmMoveModal').modal('show');
                $('#confirmMoveButton').off('click').on('click', function() {
                    updateTaskCategory(projectId, taskId, newCategory);
                    $('#confirmMoveModal').modal('hide');
                });
            } else {
                alert('You can only move tasks from Due Date, Priority 1, or Priority 2 to Completed.');
            }
        });

        // On drag over, allow dropping
        $('.task-category').on('dragover', function(event) {
            event.preventDefault(); // Allow the drop action
        });

        function updateTaskCategory(projectId, taskId, newCategory) {
    console.log(`Updating Task ID: ${taskId} to Category: ${newCategory} in Project: ${projectId}`);

    $.ajax({
        url: `/projects/${projectId}/tasks/${taskId}/update-category`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            category: newCategory
        },
        success: function(response) {
            if (response.success) {
                // Find the task card element
                const taskCard = $(`[data-task-id="${taskId}"]`);

                // Remove the task card from its current category
                const oldCategoryContainer = taskCard.closest('.task-category');
                taskCard.remove();

                // Update the old category's message if no tasks remain
                if (oldCategoryContainer.find('.task-card').length === 0) {
                    oldCategoryContainer.html('<p class="text-muted">No tasks available in this category.</p>');
                }

                // Append the task card to the new category container
                const newCategoryContainer = $(`[data-category="${newCategory}"]`);
                newCategoryContainer.append(taskCard);

                // Show the task card with a fade-in effect
                taskCard.fadeIn();

                // Remove the "No tasks available" message if the new category has tasks
                newCategoryContainer.find('p.text-muted').remove();

                // Update the task counts for the affected categories
                updateTaskCount(currentCategory);
                updateTaskCount(newCategory);
            } else {
                alert('Failed to update the task category.');
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            alert('An error occurred while updating the task category.');
        }
    });
}


        function updateTaskCount(category) {
            const taskCountElement = $(`.task-count[data-category="${category}"]`);
            const taskCount = $(`.task-category[data-category="${category}"] .task-card`).length;

            taskCountElement.text(taskCount); // Update the task count in the UI
        }

        // Original task-card click functionality (do not remove)
        $(document).on('click', '.task-card', function(event) {
            event.preventDefault();

            // Get the task and project IDs from the clicked task card
            var taskId = $(this).data('task-id');
            var projectId = $(this).data('project-id');

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
                            alert('Task details could not be loaded. Please try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching task details:', xhr.responseText);
                        alert('Error fetching task details. Please try again.');
                    }
                });
            }
        });
    });
</script>
