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
                                    <a href="#" class="btn btn-primary btn-sm" data-toggle="modal"
                                       data-target="#submitQuoteModal"
                                       data-project-id="{{ $invitation->id }}"
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
                                                data-project-id="{{ $quote->project_id }}" data-price="{{ $quote->quoted_price }}">
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

        <!-- Task Section -->
        <div class="task-section mt-5">
            <h4 class="section-heading">Tasks</h4>

            <!-- Task Invitations -->
            <h5 class="sub-section-heading">Task Invitations</h5>
            <p>No pending task invitations.</p> <!-- This can be dynamically updated when task data is added -->

            <!-- Submitted Task Quotes -->
            <h5 class="sub-section-heading">Submitted Task Quotes</h5>
            <p>No submitted task quotes.</p> <!-- This can be dynamically updated when task data is added -->
        </div>
    </div>

    <!-- Modal for Submitting Quotes -->
    <div class="modal fade" id="submitQuoteModal" tabindex="-1" role="dialog" aria-labelledby="submitQuoteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="submitQuoteForm" method="POST" action="" enctype="multipart/form-data">
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
                        <h6><strong>Project Details</strong></h6>
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

                        <!-- Quote Submission Section -->
                        <h6><strong>Submit Your Quote</strong></h6>
                        <div class="form-group">
                            <label for="quoted_price">Quoted Price:</label>
                            <input type="number" class="form-control" id="quoted_price" name="quoted_price" step="0.01" required>
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
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to submit this quote?')">Submit Quote</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Suggesting a New Price -->
    <div class="modal fade" id="suggestionModal" tabindex="-1" role="dialog" aria-labelledby="suggestionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="suggestionForm" method="POST" action="" enctype="multipart/form-data">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Submit Quote Modal
            $('#submitQuoteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var projectId = button.data('project-id');
                var projectName = button.data('project-name');
                var projectDescription = button.data('project-description');
                var projectStartDate = button.data('project-start-date');
                var projectEndDate = button.data('project-end-date');
                var projectLocation = button.data('project-location');
                var projectDocuments = button.data('project-documents');

                var modal = $(this);
                modal.find('#projectId').val(projectId);
                modal.find('#projectName').text(projectName);
                modal.find('#projectDescription').text(projectDescription);
                modal.find('#projectStartDate').text(projectStartDate);
                modal.find('#projectEndDate').text(projectEndDate);
                modal.find('#projectLocation').text(projectLocation);

                // Show project documents
                var docList = modal.find('#projectDocuments');
                docList.empty();
                if (projectDocuments.length > 0) {
                    projectDocuments.forEach(function (doc) {
                        var listItem = '<li><a href="' + doc.document_path + '" target="_blank">' + doc.original_name + '</a></li>';
                        docList.append(listItem);
                    });
                } else {
                    docList.append('<li>No documents</li>');
                }

                modal.find('#submitQuoteForm').attr('action', '/contractor/projects/' + projectId + '/submit-quote');
            });

            // Suggest Price Modal
            $('#suggestionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var quoteId = button.data('quote-id');
                var projectId = button.data('project-id');
                var price = button.data('price');
                var modal = $(this);
                modal.find('#suggestQuoteId').val(quoteId);
                modal.find('#suggestProjectId').val(projectId);
                modal.find('#suggestedPrice').text(price || 'N/A');
                modal.find('#suggestionForm').attr('action', '/contractor/projects/' + projectId + '/suggest-quote');
            });

            // Accept Quote
            $('.accept-quote').on('click', function () {
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
                        success: function (response) {
                            alert(response.message);
                            location.reload();
                        },
                        error: function (xhr) {
                            alert('An error occurred while accepting the quote. Please try again.');
                        }
                    });
                }
            });

            // Reject Quote
            $('.reject-quote').on('click', function () {
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
                        success: function (response) {
                            alert(response.message);
                            location.reload();
                        },
                        error: function (xhr) {
                            alert('An error occurred while rejecting the quote. Please try again.');
                        }
                    });
                }
            });
        });
    </script>

@endsection
