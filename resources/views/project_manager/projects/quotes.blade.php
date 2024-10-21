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
                <td>{{ $quote->quote_status }}</td>
                <td>
                    @if ($quote->quote_status === 'submitted')
                    <form method="POST" action="{{ route('project_manager.projects.quotes.action') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="quote_id" value="{{ $quote->id }}">
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>

                    <form method="POST" action="{{ route('project_manager.projects.quotes.action') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="quote_id" value="{{ $quote->id }}">
                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                    </form>

                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#suggestPriceModal" data-quote-id="{{ $quote->id }}" data-contractor-id="{{ $quote->contractor_id }}" data-price="{{ $quote->quoted_price }}" data-status="{{ $quote->quote_status }}" data-pdf-link="{{ Storage::url($quote->quote_pdf) }}" data-suggestion="{{ $quote->quote_suggestion }}">Suggest</button>
                    @elseif ($quote->quote_status === 'approved')
                    <span class="text-success">Quote Approved</span>
                    @elseif ($quote->quote_status === 'rejected')
                    <span class="text-danger">Quote Rejected</span>
                    @elseif ($quote->quote_status === 'suggested')
                    <span class="text-primary">Awaiting Reply</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal for Suggesting a New Price with details moved -->
<div class="modal fade" id="suggestPriceModal" tabindex="-1" role="dialog" aria-labelledby="suggestPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="suggestPriceForm" method="POST" action="{{ route('project_manager.projects.quotes.action') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" value="suggest">
            <input type="hidden" name="quote_id" id="suggestQuoteId">
            <input type="hidden" name="contractor_id" id="suggestContractorId">
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="suggestPriceModalLabel">Provide New Price and Quote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Quoted Price:</strong> $<span id="quotedPrice"></span></p>
                    <p><strong>Status:</strong> <span id="quoteStatus"></span></p>
                    <p><strong>Document:</strong> <a href="#" id="quotePdfLink" target="_blank">View PDF</a></p>
                    <p><strong>Contractor's Suggestion:</strong> <span id="quoteSuggestion"></span></p>
        
                    <div class="form-group">
                        <label for="new_price">New Price:</label>
                        <input type="number" class="form-control" id="new_price" name="new_price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="new_quote">New Quote Description:</label>
                        <textarea class="form-control" id="new_quote" name="new_quote" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="new_pdf">Upload New Quote Document (PDF):</label>
                        <input type="file" class="form-control-file" id="new_pdf" name="new_pdf" accept="application/pdf" required>
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
document.addEventListener('DOMContentLoaded', function () {
    $('#suggestPriceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var quoteId = button.data('quote-id');
        var contractorId = button.data('contractor-id');
        var price = button.data('price');
        var status = button.data('status');
        var pdfLink = button.data('pdf-link');
        var suggestion = button.data('suggestion');

        var modal = $(this);
        modal.find('#suggestQuoteId').val(quoteId);
        modal.find('#suggestContractorId').val(contractorId);
        modal.find('#quotedPrice').text(price || 'N/A');
        modal.find('#quoteStatus').text(status || 'N/A');
        modal.find('#quotePdfLink').attr('href', pdfLink || '#');
        modal.find('#quoteSuggestion').text(suggestion || 'No suggestion provided.');
    });

    $('#actionModal').on('show.bs.modal', function (event) {
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
            var action = '{{ route('project_manager.projects.approveQuote', ['project' => '__project_id__', 'contractor' => '__contractor_id__']) }}'
                .replace('__project_id__', projectId)
                .replace('__contractor_id__', contractorId);
            $('#actionForm').attr('action', action);
            $('#actionForm').submit();
        });

        $('#rejectLink').off('click').on('click', function() {
            var action = '{{ route('project_manager.projects.rejectQuote', ['project' => '__project_id__', 'contractor' => '__contractor_id__']) }}'
                .replace('__project_id__', projectId)
                .replace('__contractor_id__', contractorId);
            $('#actionForm').attr('action', action);
            $('#actionForm').submit();
        });

        $('#suggestLink').off('click').on('click', function() {
            $('#actionModal').modal('hide');
            $('#suggestPriceModal').modal('show');
            $('#suggestQuoteId').val(quoteId);
            $('#suggestContractorId').val(contractorId);
        });
    });
});
</script>
@endsection
