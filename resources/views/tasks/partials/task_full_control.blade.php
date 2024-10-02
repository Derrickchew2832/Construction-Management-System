<div class="container mt-3 px-10">
    <!-- Create New Task Button at the top -->
    <div class="mb-4">
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createTaskModal">+ Create New
            Task</button>
    </div>

    <div class="row">
        <!-- Category: Under Negotiation -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.85rem;">Under Negotiation
                ({{ $categorizedTasks['under_negotiation']->count() }})</h6>
            <div class="task-category p-2 category-negotiation">
                @php
                    $renderedTasks = [];
                @endphp

                @if ($categorizedTasks['under_negotiation']->isEmpty())
                    <p class="text-muted" style="font-size: 0.75rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['under_negotiation'] as $task)
                        @if (!in_array($task->id, $renderedTasks))
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2" data-task-id="{{ $task->id ?? 'undefined' }}"
                                data-project-id="{{ $projectId ?? 'undefined' }}">
                                <a href="{{ route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) }}"
                                    class="text-decoration-none">
                                    <h6>{{ $task->title }}</h6>
                                    <p>{{ $task->description }}</p>
                                </a>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Due Date -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.85rem;">Due Date
                ({{ $categorizedTasks['due_date']->count() }})</h6>
            <div class="task-category p-2 category-due-date">
                @php
                    $renderedTasks = [];
                @endphp

                @if ($categorizedTasks['due_date']->isEmpty())
                    <p class="text-muted" style="font-size: 0.75rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['due_date'] as $task)
                        @if (!in_array($task->id, $renderedTasks))
                            <!-- Ensure no duplicates -->
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2" style="background-color: #d9f3f9; border: none;">
                                @include('tasks.partials.task_card', ['task' => $task])
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Priority 1 -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.85rem;">Priority 1
                ({{ $categorizedTasks['priority_1']->count() }})</h6>
            <div class="task-category p-2 category-priority-1">
                @php
                    $renderedTasks = [];
                @endphp

                @if ($categorizedTasks['priority_1']->isEmpty())
                    <p class="text-muted" style="font-size: 0.75rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['priority_1'] as $task)
                        @if (!in_array($task->id, $renderedTasks))
                            <!-- Ensure no duplicates -->
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2" style="background-color: #fdd1c7; border: none;">
                                @include('tasks.partials.task_card', ['task' => $task])
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Priority 2 -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.85rem;">Priority 2
                ({{ $categorizedTasks['priority_2']->count() }})</h6>
            <div class="task-category p-2 category-priority-2">
                @php
                    $renderedTasks = [];
                @endphp

                @if ($categorizedTasks['priority_2']->isEmpty())
                    <p class="text-muted" style="font-size: 0.75rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['priority_2'] as $task)
                        @if (!in_array($task->id, $renderedTasks))
                            <!-- Ensure no duplicates -->
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2" style="background-color: #fcebc1; border: none;">
                                @include('tasks.partials.task_card', ['task' => $task])
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Completed -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.85rem;">Completed
                ({{ $categorizedTasks['completed']->count() }})</h6>
            <div class="task-category p-2 category-completed">
                @php
                    $renderedTasks = [];
                @endphp

                @if ($categorizedTasks['completed']->isEmpty())
                    <p class="text-muted" style="font-size: 0.75rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['completed'] as $task)
                        @if (!in_array($task->id, $renderedTasks))
                            <!-- Ensure no duplicates -->
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2" style="background-color: #c3e6cb; border: none;">
                                @include('tasks.partials.task_card', ['task' => $task])
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Verified -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.85rem;">Verified
                ({{ $categorizedTasks['verified']->count() }})</h6>
            <div class="task-category p-2 category-verified">
                @php
                    $renderedTasks = [];
                @endphp

                @if ($categorizedTasks['verified']->isEmpty())
                    <p class="text-muted" style="font-size: 0.75rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['verified'] as $task)
                        @if (!in_array($task->id, $renderedTasks))
                            <!-- Ensure no duplicates -->
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2" style="background-color: #bee5eb; border: none;">
                                @include('tasks.partials.task_card', ['task' => $task])
                            </div>
                        @endif
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
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
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



<script>
    $(document).ready(function() {
        // Use event delegation to handle dynamically added task-card elements
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

    $(document).ready(function() {
        // Handle task form submission with AJAX
        $('#taskForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // CSRF token is necessary for secure form submission in Laravel
            let csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Ensure that projectId is being passed correctly in the form
            var projectId = $(this).data('project-id');
            var url = '{{ route('tasks.store', ':projectId') }}';
            url = url.replace(':projectId', projectId);

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
                    // Check if the backend returned success or failure
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

<!-- Task Card Inline CSS -->
<style>
    /* Task card general styling */
.task-card {
    background-color: transparent;
    padding: 5px;
    border-radius: 5px;
    margin-bottom: 15px; /* Gap between task cards */
    font-size: 9px;
    height: 80px;
    width: 150px;
    overflow: hidden;
    word-wrap: break-word;
    text-align: left;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
}

/* Task card background colors for each category */
.category-negotiation .task-card {
    background-color: #e8eaf6; /* Light blue for Under Negotiation */
}

.category-due-date .task-card {
    background-color: #fbe9e7; /* Light orange for Due Date */
}

.category-priority-1 .task-card {
    background-color: #fff9c4; /* Light yellow for Priority 1 */
}

.category-priority-2 .task-card {
    background-color: #c8e6c9; /* Light green for Priority 2 */
}

.category-completed .task-card {
    background-color: #cfd8dc; /* Light grey for Completed */
}

.category-verified .task-card {
    background-color: #d1c4e9; /* Light purple for Verified */
}

/* Link text styling */
.task-card a {
    color: inherit;
    text-decoration: none;
}

/* Category styling */
.task-category {
    padding: 8px;
    border-radius: 5px;
    margin-bottom: 15px;
    height: auto;
    border: none;
    background-color: transparent; /* Transparent background for the category */
}

/* Hover effect for task card */
.task-card:hover {
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Add subtle shadow when hovering over a task card */
}

/* Center the task categories and ensure space between task cards */
.task-category {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
}

.row {
    display: flex;
    justify-content: space-around; /* Ensure even spacing between categories */
}

.task-card + .task-card {
    margin-top: 15px; /* Gap between task cards */
}

/* Center task category headers */
.task-category h6 {
    text-align: center;
    margin-bottom: 20px;
    width: 100%;
    display: flex;
    justify-content: center;
}

</style>
