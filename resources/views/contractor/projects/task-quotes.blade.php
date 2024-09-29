<div class="task-section mt-5">
    <h4 class="section-heading">Tasks</h4>

    {{-- Task Invitations --}}
    <h5 class="sub-section-heading">Task Invitations</h5>
    @if ($pendingInvitations && $pendingInvitations->isNotEmpty())
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingInvitations as $taskInvitation)
                    <tr>
                        <td>{{ $taskInvitation->title }}</td>
                        <td>{{ ucfirst($taskInvitation->invitation_status) }}</td>
                        <td>
                            <a href="#" class="btn btn-primary btn-sm" data-toggle="modal"
                               data-target="#submitTaskQuoteModal" 
                               data-task-id="{{ $taskInvitation->id }}">
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
</div>

<!-- Display Submitted Task Quotes -->
@if ($submittedTaskQuotes && $submittedTaskQuotes->isNotEmpty())
    <h5 class="sub-section-heading mt-4">Submitted Task Quotes</h5>
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
        <form id="submitTaskQuoteForm" method="POST"
            action="" {{-- Action will be set dynamically in the script --}}
            enctype="multipart/form-data">
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
                        <label for="description">Description:</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Are you sure you want to submit this task quote?')">Submit Task
                        Quote</button>
                </div>
            </div>
        </form>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Submit Task Quote Modal
        $('#submitTaskQuoteModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var taskId = button.data('task-id');
            $('#submitTaskQuoteForm').attr('action', '/contractor/tasks/' + taskId + '/submit-quote');
            var taskTitle = button.data('task-title');
            var taskDescription = button.data('task-description');
            var taskStartDate = button.data('task-start-date');
            var taskDueDate = button.data('task-due-date');
            var taskDocuments = button.data(
            'task-documents'); // For handling any task-related documents

            var modal = $(this);
            // Populate the modal with the task details
            modal.find('#taskId').val(taskId);
            modal.find('#taskTitle').text(taskTitle);
            modal.find('#taskDescription').text(taskDescription || 'No description available.');
            modal.find('#taskStartDate').text(taskStartDate || 'Not specified');
            modal.find('#taskDueDate').text(taskDueDate || 'Not specified');
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
                        alert(response.message);
                        location.reload();
                    },
                    error: function(xhr) {
                        alert(
                            'An error occurred while accepting the task quote. Please try again.');
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
                        alert(
                            'An error occurred while rejecting the task quote. Please try again.');
                    }
                });
            }
        });
    });
</script>
