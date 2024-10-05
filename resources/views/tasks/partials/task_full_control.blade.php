<div class="container mt-3 px-10">
    <!-- Create New Task Button at the top -->
    <div class="mb-4">
        <button class="btn btn-primary btn-sm btn-create-task" data-toggle="modal" data-target="#createTaskModal">
            + Create New Task
        </button>
    </div>

    <div class="row">
        <!-- Category: Under Negotiation -->
        <div class="col-md-2">
            <h6 class="text-muted">Under Negotiation (<span class="task-count"
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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
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
                        <div class="task-card mb-2" draggable="true" data-task-id="{{ $task->id }}"
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

<!-- Modal for creating a new task -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="taskForm" action="{{ route('tasks.store', ['projectId' => $project->id]) }}" method="POST"
                enctype="multipart/form-data" data-project-id="{{ $project->id }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createTaskModalLabel">Create New Task</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Task Title -->
                    <div class="form-group">
                        <label for="title">Task Title</label>
                        <input type="text" name="title" id="title" class="form-control form-control-sm"
                            required>
                    </div>

                    <!-- Task Description -->
                    <div class="form-group">
                        <label for="description">Task Description</label>
                        <textarea name="description" id="description" class="form-control form-control-sm" required></textarea>
                    </div>

                    <!-- Start Date and Due Date -->
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control form-control-sm"
                            required>
                    </div>

                    <!-- Contractor Invitation -->
                    <div class="form-group">
                        <label for="contractor_email">Search Contractor</label>
                        <input type="email" name="contractor_email" id="contractor_email"
                            class="form-control form-control-sm" placeholder="Search contractor by email" required>
                        @if ($errors->has('contractor_email'))
                            <p class="text-danger" style="font-size: 0.75rem;">
                                {{ $errors->first('contractor_email') }}</p>
                        @endif
                        <p id="invitation_status" class="text-muted mt-2" style="font-size: 0.75rem;"></p>
                    </div>

                    <!-- Category Selection -->
                    <div class="form-group">
                        <label for="category">Task Category</label>
                        <select name="category" id="category" class="form-control form-control-sm" required>
                            <option value="under_negotiation">Under Negotiation</option>
                        </select>
                    </div>

                    <!-- Task PDF Upload -->
                    <div class="form-group">
                        <label for="task_pdf">Upload Task PDF</label>
                        <input type="file" name="task_pdf" id="task_pdf" class="form-control form-control-sm"
                            accept=".pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Task & Invite</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for confirmation when moving to verified -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Task Verification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to move this task to verified? This action cannot be undone.
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
            currentCategory = draggedTask.closest('.task-category').data(
                'category'); // Save the current category globally

            // Log the values for debugging
            console.log('taskId:', taskId);
            console.log('currentCategory:', currentCategory);
            console.log('projectId:', projectId);

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

            console.log(
                `Dragging Task ID: ${taskId} from Category: ${currentCategory} in Project: ${projectId}`
            );
        });

        // On drop
        $('.task-category').on('drop', function(event) {
            event.preventDefault();

            const taskId = event.originalEvent.dataTransfer.getData('taskId');
            const newCategory = $(this).data('category');
            const projectId = event.originalEvent.dataTransfer.getData(
                'projectId'); // Retrieve projectId from dragged task

            console.log('Dropped task info:', {
                taskId,
                currentCategory,
                newCategory,
                projectId
            });

            if (!taskId || !currentCategory || !newCategory || !projectId) {
        console.error('Task ID, Current Category, New Category, or Project ID is missing during drop.');
        alert('An error occurred while dropping the task.');
        return;
    }

    // Allow moving from Completed to Verified with confirmation
    if (currentCategory === 'completed' && newCategory === 'verified') {
        // Confirm action before moving
        if (confirm("Is the task completed? Are you sure you want to move this task to Verified?")) {
            updateTaskCategory(projectId, taskId, newCategory); // Pass projectId to the function
        }
    } else if ((currentCategory === 'priority_1' && newCategory === 'priority_2') ||
               (currentCategory === 'priority_2' && newCategory === 'priority_1')) {
        // Move the task to the new category
        updateTaskCategory(projectId, taskId, newCategory); // Pass projectId to the function
    } else {
        alert('You can only move tasks between Priority 1 and Priority 2, or from Completed to Verified.');
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

                // Log the response to check if the contractor email is being received
                console.log("Server response:", response);

                // Clone the task card to avoid event listener loss, and append to the new category
                const taskClone = taskCard.clone(true, true);

                // Update the contractor email in the cloned task card
                if (response.task && response.task.contractor_email) {
                    taskClone.find('.text-muted').html('Assigned to: ' + response.task.contractor_email);
                } else {
                    taskClone.find('.text-muted').html('Assigned to: Unassigned');
                }

                // Remove the task card from its current category
                taskCard.remove();

                // Append the cloned task card to the new category container
                const newCategoryContainer = $(`[data-category="${newCategory}"]`);
                newCategoryContainer.append(taskClone);

                // Show the cloned task card with a fade-in effect
                taskClone.fadeIn();

                // **Ensure the category message is correctly updated**
                updateCategoryMessage(currentCategory);
                updateCategoryMessage(newCategory);

                // **Ensure the task count is correctly updated**
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


        // Update the category message dynamically
        function updateCategoryMessage(category) {
            const categoryContainer = $(`[data-category="${category}"]`);
            const tasksInCategory = categoryContainer.find('.task-card').length;

            if (tasksInCategory === 0) {
                // If no tasks, show the "No tasks available" message
                categoryContainer.html('<p class="text-muted">No tasks available in this category.</p>');
            } else {
                // Remove the "No tasks available" message
                categoryContainer.find('p.text-muted').remove();
            }
        }

        function updateTaskCount(category) {
            const count = $(`[data-category="${category}"]`).find('.task-card').length;
            $(`.task-count[data-category="${category}"]`).text(count);
        }

        // Original task-card click functionality (do not remove)
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

        $('#taskForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // CSRF token is necessary for secure form submission in Laravel
            let csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Ensure that projectId is being passed correctly in the form
            var projectId = $(this).data('project-id');
            var url = '{{ route('tasks.store', ':projectId') }}';
            url = url.replace(':projectId', projectId); // Replace projectId in the URL

            // Submit the form via AJAX
            $.ajax({
                url: url,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: new FormData($('#taskForm')[0]), // Include files in form data
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.message); // Display success message
                        $('#taskForm')[0].reset(); // Clear the form fields after success
                        location.reload(); // Reload the page after successful creation
                    } else {
                        alert(response.message); // Display error message from backend
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error during task submission:', xhr.responseText);
                    alert('An unexpected error occurred. Please try again.');
                }
            });
        });

    });
</script>
