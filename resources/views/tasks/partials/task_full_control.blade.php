<div class="container mt-3 px-10">
    <!-- Create New Task Button at the top -->
    <div class="mb-4">
        <button class="btn btn-primary" data-toggle="modal" data-target="#createTaskModal">+ Create New Task</button>
    </div>

    <div class="row">
        <!-- Category: Under Negotiation -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.9rem;">Under Negotiation
                ({{ $categorizedTasks['under_negotiation']->count() }})</h6>
            <div class="task-category border rounded p-2" style="background-color: #f2f2f2;">
                <!-- Tasks in Under Negotiation category -->
                @if ($categorizedTasks['under_negotiation']->isEmpty())
                    <p class="text-muted" style="font-size: 0.85rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['under_negotiation'] as $task)
                        <div class="task-card p-2 mb-2" style="background-color: #f9d3d3;">
                            @include('tasks.partials.task_card', ['task' => $task])
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Due Date -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.9rem;">Due Date
                ({{ $categorizedTasks['due_date']->count() }})</h6>
            <div class="task-category border rounded p-2" style="background-color: #e7f7fa;">
                <!-- Tasks in Due Date category -->
                @if ($categorizedTasks['due_date']->isEmpty())
                    <p class="text-muted" style="font-size: 0.85rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['due_date'] as $task)
                        <div class="task-card p-2 mb-2" style="background-color: #d9f3f9;">
                            @include('tasks.partials.task_card', ['task' => $task])
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Priority 1 -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.9rem;">Priority 1
                ({{ $categorizedTasks['priority_1']->count() }})</h6>
            <div class="task-category border rounded p-2" style="background-color: #fae3d9;">
                <!-- Tasks in Priority 1 category -->
                @if ($categorizedTasks['priority_1']->isEmpty())
                    <p class="text-muted" style="font-size: 0.85rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['priority_1'] as $task)
                        <div class="task-card p-2 mb-2" style="background-color: #fdd1c7;">
                            @include('tasks.partials.task_card', ['task' => $task])
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Priority 2 -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.9rem;">Priority 2
                ({{ $categorizedTasks['priority_2']->count() }})</h6>
            <div class="task-category border rounded p-2" style="background-color: #f9f0c6;">
                <!-- Tasks in Priority 2 category -->
                @if ($categorizedTasks['priority_2']->isEmpty())
                    <p class="text-muted" style="font-size: 0.85rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['priority_2'] as $task)
                        <div class="task-card p-2 mb-2" style="background-color: #fcebc1;">
                            @include('tasks.partials.task_card', ['task' => $task])
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Completed -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.9rem;">Completed
                ({{ $categorizedTasks['completed']->count() }})</h6>
            <div class="task-category border rounded p-2" style="background-color: #d4edda;">
                <!-- Tasks in Completed category -->
                @if ($categorizedTasks['completed']->isEmpty())
                    <p class="text-muted" style="font-size: 0.85rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['completed'] as $task)
                        <div class="task-card p-2 mb-2" style="background-color: #c3e6cb;">
                            @include('tasks.partials.task_card', ['task' => $task])
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Category: Verified -->
        <div class="col-md-2">
            <h6 class="text-muted mb-3" style="font-size: 0.9rem;">Verified
                ({{ $categorizedTasks['verified']->count() }})</h6>
            <div class="task-category border rounded p-2" style="background-color: #d1ecf1;">
                <!-- Tasks in Verified category -->
                @if ($categorizedTasks['verified']->isEmpty())
                    <p class="text-muted" style="font-size: 0.85rem;">No tasks available in this category.</p>
                @else
                    @foreach ($categorizedTasks['verified'] as $task)
                        <div class="task-card p-2 mb-2" style="background-color: #bee5eb;">
                            @include('tasks.partials.task_card', ['task' => $task])
                        </div>
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
            <form action="{{ route('tasks.store', $project->id) }}" method="POST">
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
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>

                    <!-- Task Description -->
                    <div class="form-group">
                        <label for="description">Task Description</label>
                        <textarea name="description" id="description" class="form-control" required></textarea>
                    </div>

                    <!-- Start Date and Due Date -->
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" required>
                    </div>

                    <!-- Contractor Invitation -->
                    <div class="form-group">
                        <label for="contractor_emails">Invite Contractors</label>
                        <input type="email" name="contractor_emails" id="contractor_emails" class="form-control"
                            placeholder="Enter contractor email addresses" multiple required>
                        <small class="text-muted">Only contractors will be accepted.</small>
                    </div>

                    <!-- Status after Invitation -->
                    <div class="form-group">
                        <label>Invitation Status</label>
                        <p id="invitation_status" class="text-muted">Contractor has been invited.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include necessary scripts for modal and Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        $('#contractor_emails').on('blur', function() {
            var email = $(this).val();
            $.ajax({
                url: '{{ route('tasks.validateContractor', ['projectId' => $project->id]) }}',
                method: 'POST',
                data: {
                    email: email
                },
                success: function(response) {
                    if (!response.valid) {
                        $('#invitation_status').text('Invalid contractor email.');
                    } else {
                        $('#invitation_status').text('Contractor has been invited.');
                    }
                }
            });

        });
    });
</script>
