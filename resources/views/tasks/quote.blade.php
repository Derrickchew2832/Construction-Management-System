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
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                        @if (isset($task->quote))
                            {{-- Ensure there is a quote --}}
                            <tr>
                                <td>{{ $task->title }}</td>
                                <td>${{ number_format($task->quote->quoted_price, 2) }}</td>
                                <td>
                                    <a href="{{ Storage::url($task->quote->quote_pdf) }}" target="_blank">View Document</a>
                                </td>
                                <td>
                                    @if ($task->quote->status == 'submitted')
                                        <span class="text-info">Submitted</span>
                                    @elseif ($task->quote->status == 'suggested')
                                        <span class="text-warning">Suggested</span>
                                    @elseif ($task->quote->status == 'approved')
                                        <span class="text-success">Approved</span>
                                    @elseif ($task->quote->status == 'rejected')
                                        <span class="text-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- Buttons for Accept, Reject, Suggest -->
                                    @if ($task->quote->status == 'submitted')
                                        <!-- Debugging information for suggested_by and current user -->
                                        <p>Suggested by: {{ $task->quote->suggested_by }} | Current User: {{ Auth::id() }}</p> 

                                        @if (Auth::id() != $task->quote->suggested_by)
                                            <!-- Show buttons if the current user didn't make the last suggestion -->
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#quoteModal"
                                                data-task-id="{{ $task->id }}" data-task-title="{{ $task->title }}"
                                                data-task-description="{{ $task->description }}"
                                                data-task-start="{{ $task->start_date }}" data-task-due="{{ $task->due_date }}"
                                                data-quote-price="{{ number_format($task->quote->quoted_price, 2) }}"
                                                data-quote-suggestion="{{ $task->quote->quote_suggestion }}"
                                                data-quote-document="{{ Storage::url($task->quote->quote_pdf) }}"
                                                data-quote-id="{{ $task->quote->id }}">
                                                View Suggestion
                                            </button>
                                            <button class="btn btn-success btn-sm"
                                                onclick="submitAction('accept', '{{ $task->quote->id }}')">Accept</button>
                                            <button class="btn btn-danger btn-sm"
                                                onclick="submitAction('reject', '{{ $task->quote->id }}')">Reject</button>
                                        @else
                                            <!-- Current user already made a suggestion, awaiting other party's response -->
                                            <span class="text-info">Awaiting response from the other party.</span>
                                        @endif
                                    @elseif ($task->quote->status == 'submitted')
                                        <span class="text-info">Awaiting Approval</span>
                                    @elseif ($task->quote->status == 'approved')
                                        <span class="text-success">Quote Approved</span>
                                    @elseif ($task->quote->status == 'rejected')
                                        <span class="text-danger">Quote Rejected</span>
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
                        <p><strong>Quote Document:</strong> <a href="#" id="modalQuoteDocument" target="_blank">View
                                Document</a></p>

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
                        <!-- Only the Suggest Button -->
                        <button type="submit" class="btn btn-warning">Submit Suggestion</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Trigger the modal with task and quote details
            $('#quoteModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
    
                // Extract the data attributes from the clicked button
                var taskId = button.data('task-id');
                var taskTitle = button.data('task-title');
                var taskDescription = button.data('task-description');
                var taskStart = button.data('task-start');
                var taskDue = button.data('task-due');
                var quotePrice = button.data('quote-price');
                var quoteSuggestion = button.data('quote-suggestion');
                var quoteDocument = button.data('quote-document');
                var quoteId = button.data('quote-id');
    
                // Update modal content with the data
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
    
            // Handle Accept and Reject actions
            function submitAction(action, quoteId) {
                if (confirm('Are you sure you want to ' + action + ' this quote?')) {
                    fetch(`/projects/{{ $projectId }}/tasks/${quoteId}/quote/respond`, {
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
            }
    
            // Bind submitAction function globally to handle Accept and Reject button clicks
            window.submitAction = submitAction;
    
            $('#quoteActionForm').on('submit', function(e) {
                e.preventDefault();
    
                let formData = new FormData(this);
                let taskId = document.getElementById('modalTaskId').value;
                let projectId = '{{ $projectId }}'; // Assuming projectId is available in the Blade template
    
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
