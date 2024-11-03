@extends('layouts.apps')

@section('content')
    <div class="container">
        <!-- Display an error message if the user doesn't have permission -->
        @if (!$hasAccess)
            <div class="alert alert-danger">
                You do not have permission to view this task.
            </div>
        @else
            <div class="card mb-3">
                <div class="card-header">Task Information</div>
                <div class="card-body">
                    <p><strong>Description:</strong> {{ $task->description }}</p>
                    <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($task->start_date)->format('d M Y') }}</p>
                    <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}</p>
                    <p><strong>Task Status:</strong> {{ ucfirst($task->status) }}</p>

                    @if ($task->task_pdf)
                        <p><strong>Task PDF:</strong> <a href="{{ asset('storage/' . $task->task_pdf) }}" target="_blank">View
                                PDF</a></p>
                    @else
                        <p><strong>Task PDF:</strong> Not available</p>
                    @endif
                </div>
            </div>

            <!-- Display Contractor Information if the task category is not 'under_negotiation' -->
            @if (!in_array($task->category, ['under_negotiation']))
                <div class="card mb-3">
                    <div class="card-header">Contractor Information</div>
                    <div class="card-body">
                        <p><strong>Contractor Name:</strong> {{ $task->contractor_name ?? 'N/A' }}</p>

                        <!-- Only show quoted price, quote suggestion, and quote PDF if user is not a client -->
                        @if (!$isClient)
                            @if ($isMainContractor || $isProjectManagerOrClient)
                                <p><strong>Quoted Price:</strong> ${{ number_format($task->quoted_price, 2) ?? '0.00' }}</p>
                            @endif
                            <p><strong>Quote Suggestion:</strong> {{ $task->quote_suggestion ?? 'No suggestion provided' }}
                            </p>
                            @if ($task->quote_pdf)
                                <p><strong>Quote PDF:</strong> <a href="{{ asset('storage/' . $task->quote_pdf) }}"
                                        target="_blank">View Quote PDF</a></p>
                            @else
                                <p><strong>Quote PDF:</strong> Not available</p>
                            @endif
                        @endif
                    </div>
                </div>
            @endif


            <!-- Edit and Delete Buttons for Main Contractor only if category is 'under_negotiation' -->
            @if ($isMainContractor && $task->category === 'under_negotiation')
                <div class="mt-3">
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editTaskModal">Edit
                        Task</button>
                    <button class="btn btn-danger btn-sm" onclick="confirmDelete()">Delete Task</button>
                </div>
            @endif
        @endif
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('tasks.updateTask', ['projectId' => $projectId, 'taskId' => $task->id]) }}"
                    method="POST" id="editTaskForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" required>{{ $task->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ $task->start_date }}" required>
                        </div>
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" name="due_date" id="due_date" class="form-control"
                                value="{{ $task->due_date }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="confirmEdit()">Update Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this task?')) {
                let deleteForm = document.createElement('form');
                deleteForm.method = 'POST';
                deleteForm.action = '{{ route('tasks.deleteTask', ['projectId' => $projectId, 'taskId' => $task->id]) }}';
                deleteForm.innerHTML = '@csrf @method('DELETE')';
                document.body.appendChild(deleteForm);
                deleteForm.submit();
            }
        }

        function confirmEdit() {
            if (confirm('Are you sure you want to update this task?')) {
                document.getElementById('editTaskForm').submit();
            }
        }
    </script>
@endsection
