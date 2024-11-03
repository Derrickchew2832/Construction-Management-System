<!-- Task Section -->
<div class="task-section mt-5">
    <h4 class="section-heading text-primary font-weight-bold mb-4">Tasks</h4>

    <!-- Task Invitations -->
    <h5 class="sub-section-heading text-secondary font-weight-bold">Task Invitations</h5>
    @if ($pendingTaskInvitations && $pendingTaskInvitations->isNotEmpty())
        <table class="table table-sm table-bordered table-hover">
            <thead class="thead-light">
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
        <p class="text-muted">No pending task invitations.</p>
    @endif

    <!-- Submitted Task Quotes -->
    @if ($submittedTaskQuotes && $submittedTaskQuotes->isNotEmpty())
        <h5 class="sub-section-heading text-secondary font-weight-bold mt-4">Submitted Task Quotes</h5>
        <table class="table table-sm table-bordered table-hover">
            <thead class="thead-light">
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
                        <td><a href="{{ Storage::url($quote->quote_pdf) }}" target="_blank" class="text-primary">View
                                Document</a></td>
                        <td>{{ ucfirst($quote->status) }}</td>
                        <td>
                            @if ($quote->status === 'submitted')
                                <span class="text-info">Awaiting Approval</span>
                            @elseif ($quote->status === 'suggested')
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#taskSuggestionModal" data-quote-id="{{ $quote->quote_id }}"
                                    data-task-id="{{ $quote->task_id }}" data-task-title="{{ $quote->task_title }}"
                                    data-task-description="{{ $quote->task_description ?? 'No description available' }}"
                                    data-task-start-date="{{ $quote->start_date ?? 'Not specified' }}"
                                    data-quote-suggestion="{{ $quote->quote_suggestion }}"
                                    data-task-due-date="{{ $quote->due_date ?? 'Not specified' }}"
                                    data-price="{{ $quote->quoted_price }}"
                                    data-pdf-link="{{ Storage::url($quote->quote_pdf) }}">
                                    Suggest New Price
                                </button>
                        
                                <!-- Accept and Reject with AJAX and Confirmation -->
                                <button type="button" class="btn btn-success btn-sm accept-task-quote"
                                        data-task-id="{{ $quote->task_id }}" data-quote-id="{{ $quote->quote_id }}">
                                    Accept
                                </button>
                        
                                <button type="button" class="btn btn-danger btn-sm reject-task-quote"
                                        data-task-id="{{ $quote->task_id }}" data-quote-id="{{ $quote->quote_id }}">
                                    Reject
                                </button>
                        
                            @elseif ($quote->status === 'approved')
                                <span class="text-success">Quote Approved</span>
                            @elseif ($quote->status === 'rejected')
                                <span class="text-danger">Quote Rejected</span>
                            @else
                                <button type="button" class="btn btn-link btn-sm" data-toggle="modal"
                                    data-target="#taskActionModal" data-quote-id="{{ $quote->quote_id }}"
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
        <p class="text-muted">No submitted task quotes found.</p>
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
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to submit this task quote?');">Submit Task Quote</button>
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
                    <h5 class="modal-title" id="taskSuggestionModalLabel">Quote for Task: <span
                            id="suggestTaskTitle"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Task Details Section -->
                    <h6><strong>Task Details</strong></h6>
                    <p><strong>Task Title:</strong> <span id="taskTitle"></span></p>
                    <p><strong>Task Description:</strong> <span id="taskDescription"></span></p>
                    <p><strong>Start Date:</strong> <span id="taskStartDate"></span></p>
                    <p><strong>Due Date:</strong> <span id="taskDueDate"></span></p>

                    <!-- Quote Details Section -->
                    <h6><strong>Quote Details</strong></h6>
                    <p><strong>Quoted Price:</strong> $<span id="suggestedTaskPrice"></span></p>
                    <p><strong>Quote Suggestion:</strong> <span id="suggestedTaskNotes">No suggestion provided</span>
                    </p>
                    <p><strong>Quote Document:</strong> <a id="suggestedTaskPdf" href="#" target="_blank">View
                            Document</a></p>

                    <!-- Suggest New Price Section -->
                    <h6><strong>Suggest New Price</strong></h6>
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
                        <label for="description">Quote Description:</label>
                        <textarea class="form-control" id="suggestDescription" name="quote_suggestion" rows="3" required></textarea>
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
            modal.find('#taskTitle').text(taskTitle || 'Not specified');
            modal.find('#taskDescription').text(taskDescription || 'No description available');
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
            var taskId = button.data('task-id');
            var quoteId = button.data('quote-id');

            console.log('Opening Task Suggestion Modal');
            console.log("Task ID:", taskId);
            console.log("Quote ID:", quoteId);

            var taskTitle = button.data('task-title');
            var taskDescription = button.data('task-description');
            var taskStartDate = button.data('task-start-date');
            var taskDueDate = button.data('task-due-date');
            var currentPrice = button.data('price');
            var currentPdf = button.data('pdf-link');
            var quoteSuggestion = button.data('quote-suggestion');

            var modal = $(this);
            modal.find('#suggestTaskId').val(taskId);
            modal.find('#suggestQuoteId').val(quoteId);

            modal.find('#taskTitle').text(taskTitle || 'Not specified');
            modal.find('#taskDescription').text(taskDescription || 'No description available');
            modal.find('#taskStartDate').text(taskStartDate || 'Not specified');
            modal.find('#taskDueDate').text(taskDueDate || 'Not specified');
            modal.find('#suggestedTaskPrice').text(currentPrice || 'N/A');
            modal.find('#suggestedTaskPdf').attr('href', currentPdf).text('View Document');
            modal.find('#suggestedTaskNotes').text(quoteSuggestion || 'No suggestion provided');

            console.log("Setting hidden input suggestQuoteId value:", quoteId);
            $('#taskSuggestionForm').attr('action', '/contractor/tasks/' + taskId + '/suggest-quote');
        });

        $('#taskSuggestionModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('#suggestQuoteId').val('');
        });

        $('#taskSuggestionForm').on('submit', function(e) {
            e.preventDefault();

            var taskId = $('#suggestTaskId').val();
            var quoteId = $('#suggestQuoteId').val();

            console.log("Re-checking Task ID before submission:", taskId);
            console.log("Re-checking Quote ID before submission:", quoteId);

            if (!quoteId) {
                var modalQuoteId = $('#taskSuggestionModal').find('#suggestQuoteId').val();
                $('#suggestQuoteId').val(modalQuoteId);
                quoteId = modalQuoteId;
                console.log("Re-setting missing Quote ID:", quoteId);
            }

            var confirmSubmit = confirm("Are you sure you want to suggest this new task quote?");
            if (!confirmSubmit) {
                return;
            }

            var formData = new FormData(this);

            console.log("Task ID (form):", taskId);
            console.log("Quote ID (form):", quoteId);

            if (taskId && quoteId) {
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
            } else {
                alert('Task ID or Quote ID is missing. Please try again.');
            }
        });

        // Accept Task Quote
        $(document).on('click', '.accept-task-quote', function() {
            var taskId = $(this).data('task-id');
            var quoteId = $(this).data('quote-id');

            console.log("Attempting to Accept Task Quote - Task ID:", taskId, "Quote ID:", quoteId);

            if (!quoteId) {
                alert("Error: Quote ID is missing.");
                return;
            }

            if (confirm("Are you sure you want to accept this task quote?")) {
                $.ajax({
                    url: '/contractor/tasks/' + taskId + '/accept-quote',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quote_id: quoteId,
                    },
                    success: function(response) {
                        console.log("Quote accepted successfully:", response);
                        alert(response.message);
                        location.reload();
                    },
                    error: function(xhr) {
                        console.error("Error during accept task quote:", xhr);
                        alert('An error occurred while accepting the task quote. Please try again.');
                    }
                });
            }
        });

        // Reject Task Quote
        $(document).on('click', '.reject-task-quote', function() {
            var taskId = $(this).data('task-id');
            var quoteId = $(this).data('quote-id');

            console.log("Attempting to Reject Task Quote - Task ID:", taskId, "Quote ID:", quoteId);

            if (!quoteId) {
                alert("Error: Quote ID is missing.");
                return;
            }

            if (confirm("Are you sure you want to reject this task quote?")) {
                $.ajax({
                    url: '/contractor/tasks/' + taskId + '/reject-quote',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quote_id: quoteId,
                    },
                    success: function(response) {
                        console.log("Quote rejected successfully:", response);
                        alert(response.message);
                        location.reload();
                    },
                    error: function(xhr) {
                        console.error("Error during reject task quote:", xhr);
                        alert('An error occurred while rejecting the task quote. Please try again.');
                    }
                });
            }
        });
    });
</script>
