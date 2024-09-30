{{-- Project Section --}}
<div class="project-section mt-4">
    <h4 class="section-heading">Project Quotes</h4>

    {{-- Project Invitations --}}
    <h5 class="sub-section-heading">Pending Project Invitations</h5>
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
                            <a href="#" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-target="#submitQuoteModal" data-project-id="{{ $invitation->id }}"
                                data-project-name="{{ $invitation->name }}"
                                data-project-description="{{ $invitation->description }}"
                                data-project-start-date="{{ $invitation->start_date }}"
                                data-project-end-date="{{ $invitation->end_date }}"
                                data-project-location="{{ $invitation->location }}"
                                data-project-documents='@json($invitation->documents)'>
                                Submit Quote
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No pending project invitations.</p>
    @endif

    {{-- Display Submitted Quotes --}}
    @if ($submittedQuotes->isNotEmpty())
        <h5 class="sub-section-heading mt-4">Submitted Project Quotes</h5>
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
                @foreach ($submittedQuotes as $quote)
                    <tr>
                        <td>{{ $quote->project_name }}</td>
                        <td>${{ number_format($quote->quoted_price, 2) }}</td>
                        <td><a href="{{ Storage::url($quote->quote_pdf) }}" target="_blank">View Document</a></td>
                        <td>{{ ucfirst($quote->status) }}</td>
                        <td>
                            @if ($quote->status === 'submitted')
                                <span class="text-info">Awaiting Approval</span>
                            @elseif ($quote->status === 'suggested' && $quote->suggested_by !== Auth::id())
                                <!-- Show action buttons only if the current user didn't suggest the last quote -->
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#suggestionModal" data-quote-id="{{ $quote->id }}"
                                    data-project-id="{{ $quote->project_id }}" data-price="{{ $quote->quoted_price }}"
                                    data-pdf-link="{{ Storage::url($quote->quote_pdf) }}">
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
        <p>No submitted project quotes found.</p>
    @endif
</div>

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
                    <!-- Project Details Section -->
                    <div class="form-group">
                        <label><strong>Project Name:</strong></label>
                        <p id="projectName"></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Start Date:</strong></label>
                        <p id="projectStartDate"></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Due Date:</strong></label>
                        <p id="projectEndDate"></p>
                    </div>
                    <div class="form-group">
                        <label><strong>Location:</strong></label>
                        <p id="projectLocation"></p>
                    </div>

                    <!-- Quoted Price -->
                    <div class="form-group">
                        <label for="quoted_price">Quoted Price:</label>
                        <input type="number" class="form-control" id="quoted_price" name="quoted_price" step="0.01"
                            required>
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
<div class="modal fade" id="suggestionModal" tabindex="-1" role="dialog" aria-labelledby="suggestionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="suggestionForm" method="POST" action="/contractor/projects/{project}/suggest-quote"
            enctype="multipart/form-data">
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
                    <p><strong>Suggestion Document:</strong> <a href="#" id="suggestedPdf" target="_blank">View
                            PDF</a></p>
                    <p><strong>Suggestion Notes:</strong> <span id="suggestedNotes"></span></p>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Submit Quote Modal
        $('#submitQuoteModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var projectId = button.data('project-id');
            $('#submitQuoteForm').attr('action', '/contractor/projects/' + projectId + '/submit-quote');
            var projectName = button.data('project-name');
            var projectStartDate = button.data('project-start-date');
            var projectEndDate = button.data('project-end-date');
            var projectLocation = button.data('project-location');
            var projectDocuments = button.data('project-documents');

            var modal = $(this);
            modal.find('#projectId').val(projectId);
            modal.find('#projectName').text(projectName);
            modal.find('#projectStartDate').text(projectStartDate || 'Not specified');
            modal.find('#projectEndDate').text(projectEndDate || 'Not specified');
            modal.find('#projectLocation').text(projectLocation || 'Not specified');
        });

        // Open Suggestion Modal
        $('#suggestionModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var quoteId = button.data('quote-id');
            var projectId = button.data('project-id');
            var currentPrice = button.data('price');
            var currentPdf = button.data('pdf-link');
            var opponentSuggestionPdf = button.data('suggestion-pdf');
            var opponentSuggestionNotes = button.data('suggestion-notes');

            var modal = $(this);
            modal.find('#suggestQuoteId').val(quoteId);
            modal.find('#suggestProjectId').val(projectId);
            modal.find('#suggestedPrice').text(currentPrice);
            modal.find('#currentPdf').attr('href', currentPdf).text(
                'View PDF'); // Fixed the syntax error

            if (opponentSuggestionPdf) {
                modal.find('#suggestedPdf').attr('href', opponentSuggestionPdf).show();
            } else {
                modal.find('#suggestedPdf').hide();
            }

            if (opponentSuggestionNotes) {
                modal.find('#suggestedNotes').text(opponentSuggestionNotes);
            } else {
                modal.find('#suggestedNotes').text('No notes provided.');
            }
        });

        // Handle Suggestion Submission
        $('#suggestionForm').on('submit', function(e) {
            e.preventDefault();

            // Confirmation before submitting the suggestion
            var confirmSubmit = confirm("Are you sure you want to suggest this new quote?");
            if (!confirmSubmit) {
                return; // If the user cancels, do not proceed
            }

            var formData = new FormData(this);
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
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert(response.message);
                    if (response.success) {
                        $('#suggestionModal').modal('hide');
                        location.reload();
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
                        location.reload();
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
                        location.reload();
                    },
                    error: function(xhr) {
                        alert(
                            'An error occurred while rejecting the quote. Please try again.'
                            );
                    }
                });
            }
        });
    });
</script>
