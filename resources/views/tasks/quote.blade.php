@extends('layouts.management')

@section('content')
<div class="container-fluid p-4 rounded shadow-sm">
    <!-- Task Quote Header -->
    <h5 class="sub-section-heading mb-4 font-weight-bold text-secondary">Project Files</h5>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Table to Display Received Quotes -->
    @if ($tasks && $tasks->isNotEmpty())
        <table class="table table-bordered table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th>Task Title</th>
                    <th>Contractor Name</th>
                    <th>Quoted Price</th>
                    <th>Quote Document</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    @if (isset($task->quote))
                        <tr>
                            <td class="align-middle">{{ $task->title }}</td>
                            <td class="align-middle">{{ $task->contractor->name ?? 'N/A' }}</td> <!-- Contractor Name Column -->
                            <td class="align-middle">${{ number_format($task->quote->quoted_price, 2) }}</td>
                            <td class="align-middle">
                                <a href="{{ Storage::url($task->quote->quote_pdf) }}" target="_blank" class="text-primary">View Document</a>
                            </td>
                            <td class="align-middle">
                                @if ($task->quote->status == 'approved')
                                    <span class="badge badge-success">Quote Accepted</span>
                                @elseif ($task->quote->status == 'rejected')
                                    <span class="badge badge-danger">Quote Rejected</span>
                                @elseif ($task->quote->status == 'suggested')
                                    <span class="badge badge-warning text-dark">Waiting for reply</span>
                                @elseif ($task->quote->status == 'submitted')
                                    <span class="badge badge-info text-white">Submitted</span>
                                @else
                                    <span class="badge badge-secondary">Pending</span>
                                @endif
                            </td>

                            <td class="align-middle">
                                @if ($task->quote->status === 'approved')
                                    <span class="badge badge-success">Quote Accepted</span>
                                @elseif ($task->quote->status === 'rejected')
                                    <span class="badge badge-danger">Quote Rejected</span>
                                @elseif ($task->quote->status === 'suggested')
                                    <span class="badge badge-warning text-dark">Waiting for reply</span>
                                @else
                                    <button class="btn btn-outline-primary btn-sm open-modal" data-task-id="{{ $task->id }}"
                                        data-task-title="{{ $task->title }}"
                                        data-task-description="{{ $task->description }}"
                                        data-task-start="{{ $task->start_date }}" data-task-due="{{ $task->due_date }}"
                                        data-quote-price="{{ number_format($task->quote->quoted_price, 2) }}"
                                        data-quote-suggestion="{{ $task->quote->quote_suggestion }}"
                                        data-quote-document="{{ Storage::url($task->quote->quote_pdf) }}"
                                        data-quote-id="{{ $task->quote->id }}">
                                        View Suggestion
                                    </button>
                                    <button class="btn btn-outline-success btn-sm"
                                        onclick="confirmAction('accept', '{{ $task->quote->id }}', '{{ $task->id }}')">Accept</button>
                                    <button class="btn btn-outline-danger btn-sm"
                                        onclick="confirmAction('reject', '{{ $task->quote->id }}', '{{ $task->id }}')">Reject</button>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @else
        <p>No received task quotes available.</p>
    @endif
</div>

<!-- Modal for Viewing and Responding to Quote -->
<div class="modal fade" id="quoteModal" tabindex="-1" role="dialog" aria-labelledby="quoteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="quoteActionForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="quote_id" id="modalQuoteId">
            <input type="hidden" name="task_id" id="modalTaskId">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quoteModalLabel">Quote for Task: <span id="modalTaskTitle"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Task Details -->
                    <h6><strong>Task Details</strong></h6>
                    <p><strong>Task Title:</strong> <span id="modalTaskTitleDetail"></span></p>
                    <p><strong>Task Description:</strong> <span id="modalTaskDescription"></span></p>
                    <p><strong>Start Date:</strong> <span id="modalTaskStart"></span></p>
                    <p><strong>Due Date:</strong> <span id="modalTaskDue"></span></p>

                    <hr>

                    <!-- Quote Details -->
                    <h6><strong>Quote Details</strong></h6>
                    <p><strong>Quoted Price:</strong> $<span id="modalQuotePrice"></span></p>
                    <p><strong>Quote Suggestion:</strong> <span id="modalQuoteSuggestion"></span></p>
                    <p><strong>Quote Document:</strong> <a href="#" id="modalQuoteDocument" target="_blank">View Document</a></p>

                    <hr>

                    <!-- Suggest New Quote Section -->
                    <h6><strong>Suggest New Price</strong></h6>
                    <div class="form-group">
                        <label for="new_price">New Suggested Price:</label>
                        <input type="number" name="new_price" id="new_price" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="quote_description">Quote Description:</label>
                        <textarea name="quote_description" id="quote_description" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="new_pdf">Upload New Quote (PDF):</label>
                        <input type="file" name="new_pdf" id="new_pdf" class="form-control-file"
                            accept="application/pdf" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to submit this suggestion?')">Submit Suggestion</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        $('.open-modal').on('click', function(event) {
            var button = $(this);
            var taskId = button.data('task-id');
            var taskTitle = button.data('task-title');
            var taskDescription = button.data('task-description');
            var taskStart = button.data('task-start');
            var taskDue = button.data('task-due');
            var quotePrice = button.data('quote-price');
            var quoteSuggestion = button.data('quote-suggestion');
            var quoteDocument = button.data('quote-document');
            var quoteId = button.data('quote-id');

            var modal = $('#quoteModal');
            modal.find('#modalTaskId').val(taskId);
            modal.find('#modalQuoteId').val(quoteId);
            modal.find('#modalTaskTitle').text(taskTitle || 'No Title Available');
            modal.find('#modalTaskTitleDetail').text(taskTitle || 'No Title Available');
            modal.find('#modalTaskDescription').text(taskDescription || 'No description available');
            modal.find('#modalTaskStart').text(taskStart || 'No start date available');
            modal.find('#modalTaskDue').text(taskDue || 'No due date available');
            modal.find('#modalQuotePrice').text(quotePrice || 'No price available');
            modal.find('#modalQuoteSuggestion').text(quoteSuggestion || 'No suggestion provided');
            modal.find('#modalQuoteDocument').attr('href', quoteDocument || '#').text(quoteDocument ? 'View Document' : 'No document available');

            modal.modal('show');
        });

        function confirmAction(action, quoteId, taskId) {
            if (confirm('Are you sure you want to ' + action + ' this quote?')) {
                submitAction(action, quoteId, taskId);
            }
        }

        function submitAction(action, quoteId, taskId) {
            fetch(`/projects/{{ $projectId }}/tasks/${taskId}/quote/respond`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: action,
                        quote_id: quoteId
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred.');
                });
        }

        window.confirmAction = confirmAction;

        $('#quoteActionForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let taskId = document.getElementById('modalTaskId').value;
            let projectId = '{{ $projectId }}';

            fetch(`/projects/${projectId}/tasks/${taskId}/quote/respond`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred.');
                });
        });
    });
</script>
@endsection
