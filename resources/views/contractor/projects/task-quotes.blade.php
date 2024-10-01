<div class="task-section mt-5">
    <h4 class="section-heading">Tasks</h4>

    {{-- Task Invitations --}}
    <h5 class="sub-section-heading">Task Invitations</h5>
    @if ($pendingTaskInvitations && $pendingTaskInvitations->isNotEmpty())
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingTaskInvitations as $taskInvitation)
                    <tr>
                        <td>{{ $taskInvitation->title }}</td>
                        <td>{{ ucfirst($taskInvitation->invitation_status) }}</td>
                        <td>
                            <a href="#" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-target="#submitTaskQuoteModal" data-task-id="{{ $taskInvitation->id }}"
                                data-task-title="{{ $taskInvitation->title }}"
                                data-task-description="{{ $taskInvitation->description ?? 'No description available' }}"
                                data-task-start-date="{{ $taskInvitation->start_date ?? 'Not specified' }}"
                                data-task-due-date="{{ $taskInvitation->due_date ?? 'Not specified' }}"
                                data-task-pdf="{{ Storage::url($taskInvitation->task_pdf) ?? '#' }}">
                                Submit Quote
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No pending task invitations.</p>
    @endif

    {{-- Display Submitted Task Quotes --}}
    @if ($submittedTaskQuotes && $submittedTaskQuotes->isNotEmpty())
        <h5 class="sub-section-heading mt-4">Submitted Task Quotes</h5>
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Quoted Price</th>
                    <th>Quote Document</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($submittedTaskQuotes as $quote)
                    <tr>
                        <td>{{ $quote->task_title }}</td>
                        <td>${{ number_format($quote->quoted_price, 2) }}</td>
                        <td><a href="{{ Storage::url($quote->quote_pdf) }}" target="_blank">View Document</a></td>
                        <td>{{ ucfirst($quote->status) }}</td>
                        <td>
                            @if ($quote->status === 'submitted')
                                <span class="text-info">Awaiting Approval</span>
                            @elseif ($quote->status === 'suggested')
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#taskSuggestionModal" data-quote-id="{{ $quote->id }}"
                                    data-task-id="{{ $quote->task_id }}" data-price="{{ $quote->quoted_price }}"
                                    data-pdf-link="{{ Storage::url($quote->quote_pdf) }}">
                                    View Suggestion
                                </button>

                                <form method="POST"
                                    action="{{ route('contractor.tasks.acceptQuote', ['taskId' => $quote->task_id]) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                </form>

                                <form method="POST"
                                    action="{{ route('contractor.tasks.rejectQuote', ['taskId' => $quote->task_id]) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            @elseif ($quote->status === 'approved')
                                <span class="text-success">Quote Approved</span>
                            @elseif ($quote->status === 'rejected')
                                <span class="text-danger">Quote Rejected</span>
                            @else
                                <button type="button" class="btn btn-link btn-sm" data-toggle="modal"
                                    data-target="#taskActionModal" data-quote-id="{{ $quote->id }}"
                                    data-task-id="{{ $quote->task_id }}" data-price="{{ $quote->quoted_price }}">
                                    View More
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No submitted task quotes found.</p>
    @endif
</div>

<!-- Modal for Submitting Task Quotes -->
<div class="modal fade" id="submitTaskQuoteModal" tabindex="-1" role="dialog"
    aria-labelledby="submitTaskQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="submitTaskQuoteForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="task_id" id="taskId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="submitTaskQuoteModalLabel">Submit Task Quote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Task Details Section -->
                    <h6><strong>Task Details</strong></h6>
                    <div class="form-group">
                        <label><strong>Task Title:</strong></label>
                        <p id="taskTitle"></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Task Description:</strong></label>
                        <p id="taskDescription"></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Start Date:</strong></label>
                        <p id="taskStartDate"></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Due Date:</strong></label>
                        <p id="taskDueDate"></p>
                    </div>

                    <!-- Task PDF for Reference -->
                    <div class="form-group">
                        <label><strong>Task Quote PDF:</strong></label>
                        <p>
                            <a id="taskPdfLink" href="#" target="_blank" class="btn btn-link">View Task PDF</a>
                        </p>
                    </div>

                    <!-- Task Quote Submission Section -->
                    <h6><strong>Submit Your Task Quote</strong></h6>
                    <div class="form-group">
                        <label for="quoted_price">Quoted Price:</label>
                        <input type="number" class="form-control" id="quoted_price" name="quoted_price" step="0.01"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="quote_pdf">Upload Quote (PDF):</label>
                        <input type="file" class="form-control-file" id="quote_pdf" name="quote_pdf"
                            accept="application/pdf" required>
                    </div>
                    <div class="form-group">
                        <label for="quote_suggestion">Description:</label>
                        <textarea class="form-control" id="quote_suggestion" name="quote_suggestion" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Task Quote</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Suggesting a New Price -->
<div class="modal fade" id="taskSuggestionModal" tabindex="-1" role="dialog"
    aria-labelledby="taskSuggestionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="taskSuggestionForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="quote_id" id="suggestQuoteId">
            <input type="hidden" name="task_id" id="suggestTaskId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskSuggestionModalLabel">Respond to Suggested Task Quote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Original Price:</strong> $<span id="suggestedTaskPrice"></span></p>
                    <p><strong>Suggestion Document:</strong> <a href="#" id="suggestedTaskPdf"
                            target="_blank">View PDF</a></p>
                    <p><strong>Suggestion Notes:</strong> <span id="suggestedTaskNotes"></span></p>
                    <div class="form-group">
                        <label for="new_price">New Suggested Price:</label>
                        <input type="number" class="form-control" id="new_price" name="new_price" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="new_pdf">Upload New Quote (PDF):</label>
                        <input type="file" class="form-control-file" id="new_pdf" name="new_pdf"
                            accept="application/pdf">
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea class="form-control" id="suggestDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Suggest New Price</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Submit Task Quote Modal
        $('#submitTaskQuoteModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var taskId = button.data('task-id');
            $('#submitTaskQuoteForm').attr('action', '/contractor/tasks/' + taskId + '/submit-quote'); 

            var taskTitle = button.data('task-title');
            var taskDescription = button.data('task-description');
            var taskStartDate = button.data('task-start-date');
            var taskDueDate = button.data('task-due-date');
            var taskPdf = button.data('task-pdf'); 

            var modal = $(this);
            modal.find('#taskId').val(taskId);
            modal.find('#taskTitle').text(taskTitle);
            modal.find('#taskDescription').text(taskDescription);
            modal.find('#taskStartDate').text(taskStartDate || 'Not specified');
            modal.find('#taskDueDate').text(taskDueDate || 'Not specified');

            // Set the task PDF link if available
            if (taskPdf) {
                modal.find('#taskPdfLink').attr('href', taskPdf).show();
            } else {
                modal.find('#taskPdfLink').hide();
            }
        });

        // Open Task Suggestion Modal
        $('#taskSuggestionModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var quoteId = button.data('quote-id');
            var taskId = button.data('task-id');
            var currentPrice = button.data('price');
            var currentPdf = button.data('pdf-link');

            var modal = $(this);
            modal.find('#suggestQuoteId').val(quoteId);
            modal.find('#suggestTaskId').val(taskId);
            modal.find('#suggestedTaskPrice').text(currentPrice);
            modal.find('#suggestedTaskPdf').attr('href', currentPdf).text('View PDF');
        });

        // Handle Task Suggestion Submission
        $('#taskSuggestionForm').on('submit', function(e) {
            e.preventDefault();

            var confirmSubmit = confirm("Are you sure you want to suggest this new task quote?");
            if (!confirmSubmit) {
                return;
            }

            var formData = new FormData(this);
            var taskId = $('#suggestTaskId').val();
            var quoteId = $('#suggestQuoteId').val();

            formData.append('action', 'suggest');

            $.ajax({
                url: '/contractor/tasks/' + taskId + '/suggest-quote',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert(response.message);
                    if (response.success) {
                        $('#taskSuggestionModal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        alert('Error: ' + Object.values(errors).join('\n'));
                    } else {
                        alert('An error occurred while submitting your task suggestion.');
                    }
                }
            });
        });

        // Accept Task Quote
        $('.accept-task-quote').on('click', function() {
            var taskId = $(this).data('task-id');
            var quoteId = $(this).data('quote-id');

            if (confirm("Are you sure you want to accept this task quote?")) {
                $.ajax({
                    url: '/contractor/tasks/' + taskId + '/accept-quote',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quote_id: quoteId,
                        action: 'accept'
                    },
                    success: function(response) {
                        // Check if response is JSON
                        try {
                            var jsonResponse = JSON.parse(response);
                            alert(jsonResponse.message);
                            location.reload();
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            console.error('Response:', response);
                            alert('An error occurred while processing the request.');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Check for server-side errors
                        console.error('Request Failed:', status, error);
                        console.error('Response:', xhr.responseText);

                        // Try to display server error message if available
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            alert('Error: ' + xhr.responseJSON.message);
                        } else {
                            alert('An error occurred while accepting the task quote. Please try again.');
                        }
                    }
                });
            }
        });

        // Reject Task Quote
        $('.reject-task-quote').on('click', function() {
            var taskId = $(this).data('task-id');
            var quoteId = $(this).data('quote-id');

            if (confirm("Are you sure you want to reject this task quote?")) {
                $.ajax({
                    url: '/contractor/tasks/' + taskId + '/reject-quote',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quote_id: quoteId,
                        action: 'reject'
                    },
                    success: function(response) {
                        alert(response.message);
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('An error occurred while rejecting the task quote. Please try again.');
                    }
                });
            }
        });
    });
</script>
