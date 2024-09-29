@extends('layouts.management')

@section('content')
<div class="container-fluid">
    <!-- Task Quote Header -->
    <h5 class="sub-section-heading mb-4">Received Task Quotes</h5>

    <!-- Table to Display Received Quotes -->
    @if ($tasks && $tasks->isNotEmpty())
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Quoted Price</th>
                    <th>Quote Document</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    @if (isset($task->quote)) {{-- Ensure there is a quote --}}
                    <tr>
                        <td>{{ $task->title }}</td>
                        <td>${{ number_format($task->quote->quoted_price, 2) }}</td>
                        <td>
                            <a href="{{ Storage::url($task->quote->quote_pdf) }}" target="_blank">View PDF</a>
                        </td>
                        <td>
                            <!-- Button to Trigger Modal for Quote Details -->
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#quoteModal" 
                                    data-task-id="{{ $task->id }}" 
                                    data-task-title="{{ $task->title }}" 
                                    data-task-description="{{ $task->description }}" 
                                    data-task-start="{{ $task->start_date }}" 
                                    data-task-due="{{ $task->due_date }}" 
                                    data-quote-price="{{ $task->quote->quoted_price }}"
                                    data-quote-suggestion="{{ $task->quote->quote_suggestion }}"
                                    data-quote-document="{{ Storage::url($task->quote->quote_pdf) }}"
                                    data-quote-id="{{ $task->quote->id }}">
                                Review Quote
                            </button>
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
<div class="modal fade" id="quoteModal" tabindex="-1" role="dialog" aria-labelledby="quoteModalLabel" aria-hidden="true">
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
                    <p><strong>Quote Document:</strong> <a href="#" id="modalQuoteDocument" target="_blank">View PDF</a></p>

                    <hr>

                    <!-- Suggest New Quote Section -->
                    <h6><strong>Suggest New Price</strong></h6>
                    <div class="form-group">
                        <label for="new_price">New Suggested Price:</label>
                        <input type="number" name="new_price" id="new_price" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="new_pdf">Upload New Quote (PDF):</label>
                        <input type="file" name="new_pdf" id="new_pdf" class="form-control-file" accept="application/pdf" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <!-- Accept, Reject and Suggest Buttons -->
                    <button type="button" id="acceptQuote" class="btn btn-success">Accept</button>
                    <button type="button" id="rejectQuote" class="btn btn-danger">Reject</button>
                    <button type="submit" class="btn btn-warning">Suggest New Price</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Trigger the modal with task and quote details
    $('#quoteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var taskId = button.data('task-id');
        var taskTitle = button.data('task-title');
        var taskDescription = button.data('task-description');
        var taskStart = button.data('task-start');
        var taskDue = button.data('task-due');
        var quotePrice = button.data('quote-price');
        var quoteSuggestion = button.data('quote-suggestion');
        var quoteDocument = button.data('quote-document');
        var quoteId = button.data('quote-id');

        var modal = $(this);
        modal.find('#modalTaskId').val(taskId);
        modal.find('#modalQuoteId').val(quoteId);
        modal.find('#modalTaskTitle').text(taskTitle);
        modal.find('#modalTaskTitleDetail').text(taskTitle);
        modal.find('#modalTaskDescription').text(taskDescription);
        modal.find('#modalTaskStart').text(taskStart);
        modal.find('#modalTaskDue').text(taskDue);
        modal.find('#modalQuotePrice').text(quotePrice);
        modal.find('#modalQuoteSuggestion').text(quoteSuggestion || 'No suggestion provided');
        modal.find('#modalQuoteDocument').attr('href', quoteDocument);
    });

    // Handle Accept button click
    document.getElementById('acceptQuote').addEventListener('click', function () {
        let quoteId = document.getElementById('modalQuoteId').value;
        if (confirm('Are you sure you want to accept this quote?')) {
            submitAction('accept', quoteId);
        }
    });

    // Handle Reject button click
    document.getElementById('rejectQuote').addEventListener('click', function () {
        let quoteId = document.getElementById('modalQuoteId').value;
        if (confirm('Are you sure you want to reject this quote?')) {
            submitAction('reject', quoteId);
        }
    });

    // Function to submit Accept/Reject action
    function submitAction(action, quoteId) {
        fetch(`/projects/{{ $projectId }}/tasks/${quoteId}/quote/respond`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: action, quote_id: quoteId }),
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                window.location.reload();
            }
        });
    }

    // Handle Suggest New Price form submission
    document.getElementById('quoteActionForm').addEventListener('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        fetch(`/projects/{{ $projectId }}/tasks/{{ $task->id }}/quote/respond`, {
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
        });
    });
</script>
@endsection
