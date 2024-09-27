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
            <div class="task-category p-2" style="background-color: transparent; border: none;">
                @php
                    $renderedTasks = [];
                @endphp

                @if ($categorizedTasks['under_negotiation']->isEmpty())
                    <p class="text-muted" style="font-size: 0.75rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['under_negotiation'] as $task)
                        @if (!in_array($task->id, $renderedTasks))
                            <!-- Ensure no duplicates -->
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2" style="background-color: #f9d3d3; border: none;">
                                @include('tasks.partials.task_card', ['task' => $task])
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
            <div class="task-category p-2" style="background-color: transparent; border: none;">
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
            <div class="task-category p-2" style="background-color: transparent; border: none;">
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
            <div class="task-category p-2" style="background-color: transparent; border: none;">
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
            <div class="task-category p-2" style="background-color: transparent; border: none;">
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
            <div class="task-category p-2" style="background-color: transparent; border: none;">
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

<!-- Modal for creating a new task -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="taskForm" action="{{ route('tasks.store', ['projectId' => $project->id]) }}" method="POST" enctype="multipart/form-data">
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
                        <input type="text" name="title" id="title" class="form-control form-control-sm" required>
                    </div>

                    <!-- Task Description -->
                    <div class="form-group">
                        <label for="description">Task Description</label>
                        <textarea name="description" id="description" class="form-control form-control-sm" required></textarea>
                    </div>

                    <!-- Start Date and Due Date -->
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control form-control-sm" required>
                    </div>

                    <!-- Contractor Invitation -->
                    <div class="form-group">
                        <label for="contractor_email">Search Contractor</label>
                        <input type="email" name="contractor_email" id="contractor_email" class="form-control form-control-sm" placeholder="Search contractor by email" required>
                        @if ($errors->has('contractor_email'))
                            <p class="text-danger" style="font-size: 0.75rem;">
                                {{ $errors->first('contractor_email') }}</p>
                        @endif
                        <p id="invitation_status" class="text-muted mt-2" style="font-size: 0.75rem;"></p>
                    </div>

                    <!-- Status Selection -->
                    <div class="form-group">
                        <label for="status">Task Status</label>
                        <select name="status" id="status" class="form-control form-control-sm" required>
                            <option value="under_negotiation">Under Negotiation</option>
                        </select>
                    </div>

                    <!-- Task PDF Upload -->
                    <div class="form-group">
                        <label for="task_pdf">Upload Task PDF</label>
                        <input type="file" name="task_pdf" id="task_pdf" class="form-control form-control-sm" accept=".pdf">
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

<!-- Include necessary scripts for modal and Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
   $('#taskForm').on('submit', function(e) {
    e.preventDefault(); // Prevent default form submission

    $.ajax({
        url: '{{ route('tasks.store', ['projectId' => $project->id]) }}',
        method: 'POST',
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
        error: function(xhr) {
            alert('An unexpected error occurred. Please try again.');
        }
    });
});
</script>

<!-- Task Card Inline CSS -->
<style>
    .task-card {
        background-color: #ffffff;
        padding: 8px;
        /* Reduce padding */
        border-radius: 5px;
        border: none;
        /* Remove borders */
        margin-bottom: 10px;
        font-size: 11px;
        /* Smaller font size */
        height: 130px;
        /* Standardize the height */
        overflow: hidden;
        word-wrap: break-word;
        /* Ensure long words break within the card */
    }

    /* Task Category Styling */
    .task-category {
        background-color: transparent;
        /* Ensure background blends with page */
        padding: 8px;
        border-radius: 5px;
        margin-bottom: 15px;
        height: auto;
    }
</style>