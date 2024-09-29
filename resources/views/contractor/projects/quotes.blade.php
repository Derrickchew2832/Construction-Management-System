@extends('layouts.contractorapp')

@section('title', 'Quote Management')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Quote Management</h1>

        <!-- Project Section -->
        <div class="project-section">
            <h4 class="section-heading">Projects</h4>

            <!-- Project Invitations -->
            <h5 class="sub-section-heading">Project Invitations</h5>
            @if ($pendingInvitations->isNotEmpty())
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingInvitations as $invitation)
                            <tr>
                                <td>{{ $invitation->name }}</td>
                                <td>{{ ucfirst($invitation->invitation_status) }}</td>
                                <td>
                                    <!-- Submit Quote Button -->
                                    <a href="#" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#submitQuoteModal" data-project-id="{{ $invitation->id }}"
                                        data-project-name="{{ $invitation->name }}"
                                        data-project-description="{{ $invitation->description }}"
                                        data-project-start-date="{{ $invitation->start_date }}"
                                        data-project-end-date="{{ $invitation->end_date }}"
                                        data-project-location="{{ $invitation->location }}">
                                        Submit Quote
                                    </a>
                                    <!-- View Document button removed as requested -->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No pending project invitations.</p>
            @endif

            <!-- Submitted Quotes -->
            <h5 class="sub-section-heading">Submitted Project Quotes</h5>
            @if ($quotes->isNotEmpty())
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Quoted Price</th>
                            <th>Quote Document</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotes as $quote)
                            <tr>
                                <td>{{ $quote->project_name }}</td>
                                <td>${{ number_format($quote->quoted_price, 2) }}</td>
                                <td><a href="{{ Storage::url($quote->quote_pdf) }}" target="_blank">View Document</a></td>
                                <td>{{ ucfirst($quote->status) }}</td>
                                <td>
                                    @if ($quote->status === 'submitted')
                                        <span class="text-info">Awaiting Approval</span>
                                    @elseif ($quote->status === 'suggested' && $quote->suggested_by === 'project_manager')
                                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                            data-target="#suggestionModal" data-quote-id="{{ $quote->id }}"
                                            data-project-id="{{ $quote->project_id }}"
                                            data-price="{{ $quote->quoted_price }}"
                                            data-pdf-link="{{ Storage::url($quote->quote_pdf) }}">
                                            View Suggestion
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm accept-quote"
                                            data-project-id="{{ $quote->project_id }}"
                                            data-quote-id="{{ $quote->id }}">
                                            Accept
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm reject-quote"
                                            data-project-id="{{ $quote->project_id }}"
                                            data-quote-id="{{ $quote->id }}">
                                            Reject
                                        </button>
                                    @elseif ($quote->status === 'approved')
                                        <span class="text-success">Quote Approved</span>
                                    @elseif ($quote->status === 'rejected')
                                        <span class="text-danger">Quote Rejected</span>
                                    @else
                                        <button type="button" class="btn btn-link btn-sm" data-toggle="modal"
                                            data-target="#actionModal" data-quote-id="{{ $quote->id }}"
                                            data-project-id="{{ $quote->project_id }}"
                                            data-price="{{ $quote->quoted_price }}">
                                            View More
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No submitted project quotes.</p>
            @endif
        </div>

        <!-- Task Invitations -->
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
                                    data-task-description="{{ $taskInvitation->description }}"
                                    data-task-start-date="{{ $taskInvitation->start_date }}"
                                    data-task-due-date="{{ $taskInvitation->due_date }}"
                                    data-task-pdf="{{ Storage::url($taskInvitation->task_pdf) }}">
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



        <h5 class="sub-section-heading mt-4">Submitted Task Quotes</h5>
        @if ($submittedTaskQuotes && $submittedTaskQuotes->isNotEmpty())
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Task Title</th>
                        <th>Quoted Price</th>
                        <th>Quote Document</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($submittedTaskQuotes as $quote)
                        <tr>
                            <td>{{ $quote->task_title }}</td>
                            <td>${{ number_format($quote->quoted_price, 2) }}</td>
                            <td><a href="{{ Storage::url($quote->quote_pdf) }}" target="_blank">View Document</a></td>
                            <td>{{ ucfirst($quote->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No submitted task quotes found.</p>
        @endif


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
                            <div class="form-group">
                                <label><strong>Task Documents:</strong></label>
                                <p id="taskPdfLink">
                                    <!-- Task PDF will be populated here dynamically by JavaScript -->
                                </p>
                            </div>

                            <!-- Quote Submission Section -->
                            <h6><strong>Submit Your Quote</strong></h6>
                            <div class="form-group">
                                <label for="quoted_price">Quoted Price:</label>
                                <input type="number" class="form-control" id="quoted_price" name="quoted_price"
                                    step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="quote_pdf">Upload Quote (PDF):</label>
                                <input type="file" class="form-control-file" id="quote_pdf" name="quote_pdf"
                                    accept="application/pdf" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"
                                onclick="return confirm('Are you sure you want to submit this quote?')">Submit
                                Quote</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>



        <!-- Modal for Suggesting a New Price -->
        <div class="modal fade" id="suggestionModal" tabindex="-1" role="dialog"
            aria-labelledby="suggestionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="suggestionForm" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="quote_id" id="suggestQuoteId">
                    <input type="hidden" name="project_id" id="suggestProjectId">
                    <input type="hidden" name="action" value="suggest"> <!-- Added hidden input for action -->

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="suggestionModalLabel">Respond to Suggested Quote</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Original Price:</strong> $<span id="suggestedPrice"></span></p>
                            <div class="form-group">
                                <label for="new_price">New Suggested Price:</label>
                                <input type="number" class="form-control" id="new_price" name="new_price"
                                    step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="new_pdf">Upload New Quote (PDF):</label>
                                <input type="file" class="form-control-file" id="new_pdf" name="new_pdf"
                                    accept="application/pdf" required>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Submit Quote Modal
                $('#submitQuoteModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    var projectId = button.data('project-id');
                    var projectName = button.data('project-name');
                    var projectDescription = button.data('project-description');
                    var projectStartDate = button.data('project-start-date');
                    var projectEndDate = button.data('project-end-date');
                    var projectLocation = button.data('project-location');

                    var modal = $(this);
                    modal.find('#projectId').val(projectId);
                    modal.find('#projectName').text(projectName);
                    modal.find('#projectDescription').text(projectDescription);
                    modal.find('#projectStartDate').text(projectStartDate);
                    modal.find('#projectEndDate').text(projectEndDate);
                    modal.find('#projectLocation').text(projectLocation);

                    modal.find('#submitQuoteForm').attr('action', '/contractor/projects/' + projectId +
                        '/submit-quote');
                });

                // Handle the Suggestion Form submission
                $('#suggestionForm').on('submit', function(event) {
                    event.preventDefault(); // Prevent the default form submission

                    var formData = new FormData(this);
                    var projectId = $('#suggestProjectId').val();
                    var quoteId = $('#suggestQuoteId').val();
                    var row = $('button[data-quote-id="' + quoteId + '"]').closest(
                        'tr'); // Get the row corresponding to this quote

                    $.ajax({
                        url: $(this).attr('action'),
                        method: "POST",
                        data: formData,
                        processData: false, // Important to allow file upload
                        contentType: false, // Important to allow file upload
                        success: function(response) {
                            if (response.success) {
                                // Update the status and buttons accordingly based on who made the suggestion
                                var suggestedBy = response.suggested_by;

                                if (suggestedBy === 'project_manager') {
                                    row.find('td:nth-child(4)').text('Suggested');
                                    row.find('td:last').html(
                                        `<button type="button" class="btn btn-success btn-sm accept-quote" data-project-id="${projectId}" data-quote-id="${quoteId}">Accept</button>
                         <button type="button" class="btn btn-danger btn-sm reject-quote" data-project-id="${projectId}" data-quote-id="${quoteId}">Reject</button>
                         <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#suggestionModal" data-quote-id="${quoteId}" data-project-id="${projectId}" data-price="${response.new_price}">Reply</button>`
                                    );
                                } else if (suggestedBy === 'contractor') {
                                    row.find('td:nth-child(4)').text('Suggestion Made');
                                    row.find('td:last').html(
                                        '<span class="text-info">Awaiting Project Manager Response</span>'
                                    );
                                }

                                // Close the modal without displaying the JSON message
                                $('#suggestionModal').modal('hide');
                            }
                        },
                        error: function() {
                            alert('There was an error processing your request.');
                        }
                    });
                });


                // Accept Quote
                $('.accept-quote').on('click', function() {
                    var projectId = $(this).data('project-id');
                    var quoteId = $(this).data('quote-id');
                    var row = $(this).closest('tr'); // Get the table row of the clicked button

                    if (confirm("Are you sure you want to accept this quote?")) {
                        $.ajax({
                            url: '/contractor/projects/' + projectId + '/accept-quote',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                quote_id: quoteId,
                                action: 'accept'
                            },
                            success: function(response) {
                                row.find('td:nth-child(4)').text('Approved');
                                row.find('button').hide();
                                row.find('td:last').html(
                                    '<span class="text-success">Quote Approved</span>');
                            },
                            error: function(xhr) {
                                alert(
                                    'An error occurred while accepting the quote. Please try again.'
                                );
                            }
                        });
                    }
                });

                // Reject Quote
                $('.reject-quote').on('click', function() {
                    var projectId = $(this).data('project-id');
                    var quoteId = $(this).data('quote-id');
                    var row = $(this).closest('tr'); // Get the table row of the clicked button

                    if (confirm("Are you sure you want to reject this quote?")) {
                        $.ajax({
                            url: '/contractor/projects/' + projectId + '/reject-quote',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                quote_id: quoteId,
                                action: 'reject'
                            },
                            success: function(response) {
                                row.find('td:nth-child(4)').text('Rejected');
                                row.find('button').hide();
                                row.find('td:last').html(
                                    '<span class="text-danger">Quote Rejected</span>');
                            },
                            error: function(xhr) {
                                alert(
                                    'An error occurred while rejecting the quote. Please try again.'
                                );
                            }
                        });
                    }
                });

                $('#submitTaskQuoteModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var taskId = button.data('task-id');
                    var taskTitle = button.data('task-title');
                    var taskDescription = button.data('task-description');
                    var taskStartDate = button.data('task-start-date');
                    var taskDueDate = button.data('task-due-date');
                    var taskPdf = button.data('task-pdf'); // For handling the task PDF document

                    // Populate the form with task data
                    var modal = $(this);
                    modal.find('#taskId').val(taskId);
                    modal.find('#taskTitle').text(taskTitle);
                    modal.find('#taskDescription').text(taskDescription || 'No description available.');
                    modal.find('#taskStartDate').text(taskStartDate || 'Not specified');
                    modal.find('#taskDueDate').text(taskDueDate || 'Not specified');

                    // Display the task PDF if available
                    var taskPdfLink = modal.find('#taskPdfLink');
                    if (taskPdf) {
                        taskPdfLink.html('<a href="' + taskPdf +
                        '" target="_blank">Download Task Document</a>');
                    } else {
                        taskPdfLink.text('No document available.');
                    }

                    // Update the form action URL to submit the task quote
                    modal.find('#submitTaskQuoteForm').attr('action', '/contractor/tasks/' + taskId +
                        '/submit-quote');
                });


            });
        </script>
    @endsection
