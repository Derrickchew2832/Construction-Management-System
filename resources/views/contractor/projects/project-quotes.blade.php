{{-- Project Section --}}
<div class="project-section mt-4">
    <h4 class="section-heading text-primary font-weight-bold mb-4">Project Quotes</h4>

{{-- Project Invitations --}}
<h5 class="sub-section-heading text-secondary font-weight-bold">Pending Project Invitations</h5>
@if ($pendingInvitations->isNotEmpty())
    <table class="table table-sm table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Project Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pendingInvitations as $invitation)
                <tr data-status="{{ $invitation->invitation_status }}">
                    <td>{{ $invitation->name }}</td>
                    <td>{{ ucfirst($invitation->invitation_status) }}</td>
                    <td>
                        @if ($invitation->invitation_status === 'closed')
                            <span class="text-secondary">Project Closed</span>
                        @else
                            <a href="#" class="btn btn-primary btn-sm submit-quote-btn" data-toggle="modal"
                                data-target="#submitQuoteModal" data-project-id="{{ $invitation->id }}"
                                data-project-name="{{ $invitation->name }}"
                                data-project-description="{{ $invitation->description }}"
                                data-project-start-date="{{ $invitation->start_date }}"
                                data-project-end-date="{{ $invitation->end_date }}"
                                data-project-location="{{ $invitation->location }}"
                                data-project-documents='@json([['document_path' => $invitation->document_path, 'original_name' => $invitation->original_name]])'>
                                Submit Quote
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-warning">No pending project invitations.</p>
@endif


    {{-- Submitted Quotes --}}
    @if ($submittedQuotes->isNotEmpty())
        <h5 class="sub-section-heading text-secondary font-weight-bold mt-4">Submitted Project Quotes</h5>
        <table class="table table-sm table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Project Name</th>
                    <th>Quoted Price</th>
                    <th>Quote Document</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($submittedQuotes as $quote)
                    <tr>
                        <td>{{ $quote->project_name }}</td>
                        <td>${{ number_format($quote->quoted_price, 2) }}</td>
                        <td>
                            @if ($quote->quote_pdf)
                                <a href="{{ Storage::url($quote->quote_pdf) }}" target="_blank">View Document</a>
                            @else
                                <span>No documents available</span>
                            @endif
                        </td>
                        <td>{{ ucfirst($quote->status) }}</td>
                        <td>
                            @if ($quote->status === 'closed')
                                <span class="text-secondary">Quote Closed</span>
                            @elseif ($quote->status === 'submitted')
                                <span class="text-info">Awaiting Approval</span>
                            @elseif ($quote->status === 'suggested' && $quote->suggested_by !== Auth::id())
                                <!-- Buttons for Suggestion -->
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#suggestionModal" data-quote-id="{{ $quote->id }}"
                                    data-project-id="{{ $quote->project_id }}" data-price="{{ $quote->quoted_price }}"
                                    data-pdf-link="{{ Storage::url($quote->quote_pdf) }}"
                                    data-suggestion="{{ $quote->quote_suggestion }}">
                                    View Suggestion
                                </button>

                                <button type="button" class="btn btn-success btn-sm accept-quote"
                                    data-project-id="{{ $quote->project_id }}" data-quote-id="{{ $quote->id }}">
                                    Accept
                                </button>
                                <button type="button" class="btn btn-danger btn-sm reject-quote"
                                    data-project-id="{{ $quote->project_id }}" data-quote-id="{{ $quote->id }}">
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
        <p class="text-muted">No submitted project quotes found.</p>
    @endif

    <!-- Modal for Submitting Quotes -->
    <div class="modal fade" id="submitQuoteModal" tabindex="-1" role="dialog" aria-labelledby="submitQuoteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="submitQuoteForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="project_id" id="projectId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="submitQuoteModalLabel">Submit Quote</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Project Details -->
                        <div class="form-group">
                            <label><strong>Project Name:</strong></label>
                            <p id="projectName"></p>
                        </div>
                        <div class="form-group">
                            <label><strong>Project Description:</strong></label>
                            <p id="projectDescription"></p>
                        </div>
                        <div class="form-group">
                            <label><strong>Start Date:</strong></label>
                            <p id="projectStartDate"></p>
                        </div>
                        <div class="form-group">
                            <label><strong>End Date:</strong></label>
                            <p id="projectEndDate"></p>
                        </div>
                        <div class="form-group">
                            <label><strong>Location:</strong></label>
                            <p id="projectLocation"></p>
                        </div>
                        <div class="form-group">
                            <label><strong>Documents:</strong></label>
                            <ul id="projectDocuments"></ul>
                        </div>

                        <!-- Quoted Price -->
                        <div class="form-group">
                            <label for="quoted_price">Quoted Price:</label>
                            <input type="number" class="form-control" id="quoted_price" name="quoted_price"
                                step="0.01" required>
                        </div>

                        <!-- Upload Quote -->
                        <div class="form-group">
                            <label for="quote_pdf">Upload Quote (PDF):</label>
                            <input type="file" class="form-control-file" id="quote_pdf" name="quote_pdf"
                                accept="application/pdf" required>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Quote</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Suggesting a New Price -->
    <div class="modal fade" id="suggestionModal" tabindex="-1" role="dialog"
        aria-labelledby="suggestionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="suggestionForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="quote_id" id="suggestQuoteId">
                <input type="hidden" name="project_id" id="suggestProjectId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="suggestionModalLabel">Respond to Suggested Quote</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Original Price:</strong> $<span id="suggestedPrice"></span></p>
                        <p><strong>Suggestion Document:</strong> <a href="#" id="suggestedPdf"
                                target="_blank">View PDF</a></p>
                        <p><strong>Suggestion Notes:</strong> <span id="suggestedNotes"></span></p>

                        <!-- New price -->
                        <div class="form-group">
                            <label for="new_price">New Suggested Price:</label>
                            <input type="number" class="form-control" id="new_price" name="new_price"
                                step="0.01">
                        </div>

                        <!-- Upload new quote PDF -->
                        <div class="form-group">
                            <label for="new_pdf">Upload New Quote (PDF):</label>
                            <input type="file" class="form-control-file" id="new_pdf" name="new_pdf"
                                accept="application/pdf">
                        </div>

                        <!-- Description -->
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Submit Quote Modal
            $('#submitQuoteModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var projectId = button.data('project-id');
                $('#submitQuoteForm').attr('action', '/contractor/projects/' + projectId + '/submit-quote');

                var projectName = button.data('project-name');
                var projectDescription = button.data('project-description');
                var projectStartDate = button.data('project-start-date');
                var projectEndDate = button.data('project-end-date');
                var projectLocation = button.data('project-location');
                var projectDocuments = button.data('project-documents');

                var modal = $(this);
                modal.find('#projectId').val(projectId);
                modal.find('#projectName').text(projectName);
                modal.find('#projectDescription').text(projectDescription || 'No description provided');
                modal.find('#projectStartDate').text(projectStartDate || 'Not specified');
                modal.find('#projectEndDate').text(projectEndDate || 'Not specified');
                modal.find('#projectLocation').text(projectLocation || 'Not specified');

                var documentsList = modal.find('#projectDocuments');
                documentsList.empty();

                // Add each document as a clickable link
                if (projectDocuments && projectDocuments.length > 0) {
                    projectDocuments.forEach(function(doc) {
                        var documentLink = '<li><a href="/storage/' + doc.document_path +
                            '" target="_blank">' + doc.original_name + '</a></li>';
                        documentsList.append(documentLink);
                    });
                } else {
                    documentsList.append('<li>No documents available.</li>');
                }
            });

            // Confirmation on Submit Quote Form
            $('#submitQuoteForm').on('submit', function(e) {
                e.preventDefault(); // Prevent form submission

                // Show confirmation dialog
                var confirmed = confirm("Are you sure you want to submit this quote?");
                if (confirmed) {
                    // Proceed with form submission if confirmed
                    this.submit();
                }
            });
        });

        // Open Suggestion Modal
        $('#suggestionModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var quoteId = button.data('quote-id');
            var projectId = button.data('project-id');
            var currentPrice = button.data('price');
            var currentPdf = button.data('pdf-link');
            var opponentSuggestion = button.data('suggestion');

            var modal = $(this);
            modal.find('#suggestQuoteId').val(quoteId);
            modal.find('#suggestProjectId').val(projectId);
            modal.find('#suggestedPrice').text(currentPrice);
            modal.find('#suggestedPdf').attr('href', currentPdf).text('View PDF');

            // Set the suggestion notes or provide a default value
            if (opponentSuggestion) {
                modal.find('#suggestedNotes').text(opponentSuggestion);
            } else {
                modal.find('#suggestedNotes').text('No notes provided.');
            }

            // Set the form action URL dynamically
            $('#suggestionForm').attr('action', '/contractor/projects/' + projectId + '/suggest-quote');
        });

        // Handle Suggestion Submission
        $('#suggestionForm').on('submit', function(e) {
            e.preventDefault();

            // Confirmation before submitting the suggestion
            var confirmSubmit = confirm("Are you sure you want to suggest this new quote?");
            if (!confirmSubmit) {
                return;
            }

            var formData = new FormData(this); // Use formData to collect the form inputs
            var projectId = $('#suggestProjectId').val();
            var quoteId = $('#suggestQuoteId').val();

            formData.append('action', 'suggest');

            $.ajax({
                url: '/contractor/projects/' + projectId + '/suggest-quote',
                method: 'POST',
                data: formData,
                contentType: false, // Required for file upload
                processData: false, // Required for file upload
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Ensure the CSRF token is correctly passed
                },
                success: function(response) {
                    alert(response.message);
                    if (response.success) {
                        $('#suggestionModal').modal('hide');
                        location.reload(); // Reload the page after a successful suggestion
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        alert('Error: ' + Object.values(errors).join('\n'));
                    } else {
                        alert('An error occurred while submitting your suggestion.');
                    }
                }
            });
        });

        // Accept Quote
        $('.accept-quote').on('click', function() {
            var projectId = $(this).data('project-id');
            var quoteId = $(this).data('quote-id');

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
                        alert(response.message);
                        location.reload(); // Reload the page to reflect the changes
                    },
                    error: function(xhr) {
                        alert(
                            'An error occurred while accepting the quote. Please try again.');
                    }
                });
            }
        });

        // Reject Quote
        $('.reject-quote').on('click', function() {
            var projectId = $(this).data('project-id');
            var quoteId = $(this).data('quote-id');

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
                        alert(response.message);
                        location.reload(); // Reload the page to reflect the changes
                    },
                    error: function(xhr) {
                        alert(
                            'An error occurred while rejecting the quote. Please try again.');
                    }
                });
            }
        });

        // Hide Buttons and Show "Quote Closed" if Quote is Closed
        $(document).ready(function() {
        // Hide Buttons if the Project Status is "Closed"
        $('tr[data-status="closed"]').each(function() {
            // Hide the Submit Quote button, Accept, and Reject buttons
            $(this).find('.submit-quote-btn').hide(); // Hide the Submit Quote button
            $(this).find('.accept-quote').hide(); // Hide Accept button
            $(this).find('.reject-quote').hide(); // Hide Reject button

            // Update the action column to display "Quote Closed" in gray
            $(this).find('td:last-child').html(
                '<span class="text-secondary">Quote Closed</span>');
        });
        });
        
    </script>
