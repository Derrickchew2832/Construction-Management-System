<div class="container mt-3 px-10">
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
                                <a href="{{ $task->assigned_to == Auth::id() ? route('tasks.details', ['projectId' => $projectId, 'taskId' => $task->id]) : '#' }}"
                                   class="text-decoration-none {{ $task->assigned_to == Auth::id() ? '' : 'disabled-link' }}">
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
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2 task-due-date" data-task-id="{{ $task->id }}"
                                 data-project-id="{{ $projectId }}">
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
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2 task-priority-1" data-task-id="{{ $task->id }}"
                                 data-project-id="{{ $projectId }}">
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
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2 task-priority-2" data-task-id="{{ $task->id }}"
                                 data-project-id="{{ $projectId }}">
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
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2 task-completed" data-task-id="{{ $task->id }}"
                                 data-project-id="{{ $projectId }}">
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
                            @php
                                $renderedTasks[] = $task->id;
                            @endphp
                            <div class="task-card mb-2 task-verified" data-task-id="{{ $task->id }}"
                                 data-project-id="{{ $projectId }}">
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

<!-- Include necessary scripts for modal and Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>

<script>
    $(document).ready(function() {
        // Use event delegation to handle dynamically added task-card elements
        $(document).on('click', '.task-card', function(event) {
            event.preventDefault();

            // Get the task and project IDs from the clicked task card
            var taskId = $(this).data('task-id');
            var projectId = $(this).data('project-id');

            if (taskId && projectId) {
                $.ajax({
                    url: '/projects/' + projectId + '/tasks/' + taskId + '/details',
                    type: 'GET',
                    success: function(response) {
                        if (response) {
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
            } else {
                alert('Task or Project ID is missing. Please try again.');
            }
        });
    });
</script>

<!-- Task Card Inline CSS -->
<style>
/* Task card styling */
.task-card {
    background-color: transparent;
    padding: 5px;
    border-radius: 5px;
    margin-bottom: 10px;
    font-size: 9px;
    height: 80px;
    width: 150px;
    overflow: hidden;
    word-wrap: break-word;
    text-align: left; /* Ensure text inside is left-aligned */
    display: flex;
    flex-direction: column;
    justify-content: center; /* Center content vertically */
    align-items: flex-start; /* Align content to the left */
}

/* Link styling */
.task-card a {
    color: inherit;
    text-decoration: none;
}

/* Task category container styling */
.task-category {
    padding: 8px;
    border-radius: 5px;
    margin-bottom: 15px;
    height: auto;
    border: none;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center; /* Center task cards */
    background-color: transparent; /* Keep the category background transparent */
}

/* Category-specific task card background colors */
.task-due-date {
    background-color: #d9f3f9;
}

.task-priority-1 {
    background-color: #fdd1c7;
}

.task-priority-2 {
    background-color: #fcebc1;
}

.task-completed {
    background-color: #c3e6cb;
}

.task-verified {
    background-color: #bee5eb;
}

/* Add space between task cards */
.task-card + .task-card {
    margin-top: 20px; /* Ensure proper spacing between task cards */
}

/* Center task category headers */
.task-category h6 {
    text-align: center;
    margin-bottom: 20px;
    width: 100%;
    display: flex;
    justify-content: center;
}

/* Ensure task category and cards are aligned centrally in the column */
.task-category {
    align-items: center;
    justify-content: flex-start;
}

/* Container styling to centralize categories */
.row {
    display: flex;
    justify-content: space-around; /* Ensure even spacing between categories */
}

/* Adjustments to text inside task card */
.task-card h6, .task-card p {
    margin: 0;
    padding: 0;
    text-align: left; /* Left-align the text inside task cards */
}



</style>
