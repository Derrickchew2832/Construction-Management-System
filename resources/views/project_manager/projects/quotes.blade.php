@extends('layouts.projectmanagerapp')

@section('title', 'Manage Quotes')

@section('content')
    <div class="container mt-4">
        <h1>Manage Quotes</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Contractor</th>
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
                        <td>{{ $quote->contractor_name }}</td>
                        <td>${{ number_format($quote->quoted_price, 2) }}</td>
                        <td><a href="{{ Storage::url($quote->quote_pdf) }}" target="_blank">View Document</a></td>
                        <td>{{ ucfirst($quote->status) }}</td>
                        <td>
                            <!-- View More Button -->
                            <button type="button" class="btn btn-link" data-toggle="modal" data-target="#actionModal"
                                data-quote-id="{{ $quote->id }}" data-contractor-id="{{ $quote->contractor_id }}"
                                data-project-id="{{ $quote->project_id }}" data-price="{{ $quote->quoted_price }}"
                                data-status="{{ $quote->status }}" data-pdf-link="{{ Storage::url($quote->quote_pdf) }}">
                                View More
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal for Approve/Reject/Suggest Actions -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="actionForm" method="POST">
                @csrf
                <input type="hidden" name="quote_id" id="quoteId">
                <input type="hidden" name="contractor_id" id="contractorId">
                <input type="hidden" name="project_id" id="projectId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="actionModalLabel">Quote Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Quoted Price:</strong> $<span id="quotedPrice"></span></p>
                        <p><strong>Status:</strong> <span id="quoteStatus"></span></p>
                        <p><strong>Document:</strong> <a href="#" id="quotePdfLink" target="_blank">View PDF</a></p>
                        <!-- Options -->
                        <div class="mt-3">
                            <a href="#" id="approveLink">Approve</a> |
                            <a href="#" id="rejectLink">Reject</a> |
                            <a href="#" id="suggestLink">Suggest a New Price</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Suggesting a New Price -->
    <div class="modal fade" id="suggestPriceModal" tabindex="-1" role="dialog" aria-labelledby="suggestPriceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="suggestPriceForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="quote_id" id="suggestQuoteId">
                <input type="hidden" name="contractor_id" id="suggestContractorId">
                <input type="hidden" name="project_id" id="suggestProjectId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="suggestPriceModalLabel">Provide New Price and Quote</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="new_price">New Price:</label>
                            <input type="number" class="form-control" id="new_price" name="new_price" step="0.01"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="new_quote">New Quote Description:</label>
                            <textarea class="form-control" id="new_quote" name="new_quote" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="new_pdf">Upload New Quote Document (PDF):</label>
                            <input type="file" class="form-control-file" id="new_pdf" name="new_pdf"
                                accept="application/pdf" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $('#actionModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var quoteId = button.data('quote-id');
            var contractorId = button.data('contractor-id');
            var projectId = button.data('project-id');
            var price = button.data('price');
            var status = button.data('status');
            var pdfLink = button.data('pdf-link');

            var modal = $(this);
            modal.find('#quoteId').val(quoteId);
            modal.find('#contractorId').val(contractorId);
            modal.find('#projectId').val(projectId);
            modal.find('#quotedPrice').text(price || 'N/A');
            modal.find('#quoteStatus').text(status || 'N/A');
            modal.find('#quotePdfLink').attr('href', pdfLink || '#');

            // Set form actions for approve and reject
            $('#approveLink').off('click').on('click', function() {
                var action = '{{ url('project_manager/projects') }}/' + projectId + '/quotes/' +
                    contractorId + '/approve';
                $('#actionForm').attr('action', action);
                $('#actionForm').submit();
            });

            $('#rejectLink').off('click').on('click', function() {
                var action = '{{ url('project_manager/projects') }}/' + projectId + '/quotes/' +
                    contractorId + '/reject';
                $('#actionForm').attr('action', action);
                $('#actionForm').submit();
            });

            $('#suggestLink').off('click').on('click', function() {
                $('#actionModal').modal('hide');
                $('#suggestPriceModal').modal('show');
                $('#suggestQuoteId').val(quoteId);
                $('#suggestContractorId').val(contractorId);
                $('#suggestProjectId').val(projectId);

                // Set the correct action for the suggestion form
                $('#suggestPriceForm').attr('action', '{{ url('project_manager/projects') }}/' +
                    projectId + '/quotes/suggest');
            });

        });
    </script>

@endsection
