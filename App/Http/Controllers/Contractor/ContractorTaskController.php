<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ContractorTaskController extends Controller
{
    // Display tasks related to the contractor (task invitations and submitted task quotes)
    public function indexTasks(Request $request)
    {
        $contractorId = Auth::id(); // Get the logged-in contractor ID
        
        // Fetch submitted task quotes
        $submittedTaskQuotes = DB::table('task_contractor')
            ->join('tasks', 'task_contractor.task_id', '=', 'tasks.id')
            ->where('task_contractor.contractor_id', $contractorId)
            ->select(
                'task_contractor.*', 
                'tasks.title as task_title', 
                'tasks.description', 
                'tasks.start_date', 
                'tasks.due_date', 
                'tasks.task_pdf'  // Include task_pdf
            )
            ->get();

        // Collect task IDs that have already been submitted
        $submittedTaskIds = $submittedTaskQuotes->pluck('task_id')->toArray();

        // Fetch pending task invitations where the contractor hasn't submitted a quote yet
        $pendingTaskInvitations = DB::table('task_invitations')
            ->join('tasks', 'task_invitations.task_id', '=', 'tasks.id')
            ->where('task_invitations.contractor_id', $contractorId)
            ->where('task_invitations.status', 'pending')
            ->whereNotIn('task_invitations.task_id', $submittedTaskIds) // Exclude tasks already submitted
            ->select(
                'tasks.id', 
                'tasks.title', 
                'tasks.description', 
                'tasks.start_date', 
                'tasks.due_date', 
                'tasks.task_pdf',  // Include task_pdf
                'task_invitations.status as invitation_status'
            )
            ->get();

        // If collections are empty, initialize them as empty collections
        $pendingTaskInvitations = $pendingTaskInvitations->isEmpty() ? collect([]) : $pendingTaskInvitations;
        $submittedTaskQuotes = $submittedTaskQuotes->isEmpty() ? collect([]) : $submittedTaskQuotes;

        // Return data as an array to use in the view
        return [
            'pendingTaskInvitations' => $pendingTaskInvitations,
            'submittedTaskQuotes' => $submittedTaskQuotes,
        ];
    }

    public function submitTaskQuote(Request $request, $taskId)
    {
        // Validate the input data
        $data = $request->validate([
            'quoted_price' => 'required|numeric|min:0',
            'quote_pdf' => 'required|file|mimes:pdf|max:2048', // Accept only PDF files
            'quote_suggestion' => 'nullable|string|max:255',   // Optional description field
        ]);

        // Save the uploaded PDF file
        $pdfPath = $request->file('quote_pdf')->store('task_quotes', 'public');

        // Determine the user (Contractor)
        $suggestedBy = Auth::id();

        // Insert or update the contractor's task quote in the task_contractor table
        DB::table('task_contractor')->updateOrInsert(
            ['task_id' => $taskId, 'contractor_id' => $suggestedBy],
            [
                'quoted_price' => $data['quoted_price'],
                'quote_pdf' => $pdfPath,
                'quote_suggestion' => $data['quote_suggestion'] ?? null,
                'suggested_by' => $suggestedBy,
                'status' => 'submitted', // Set status to 'submitted' for Main Contractor to act next
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Update the tasks table with the 'pending' status when a quote is submitted
        DB::table('tasks')
            ->where('id', $taskId)
            ->update([
                'status' => 'pending', // Mark the task as pending
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Task quote submitted successfully and awaiting main contractor approval.');
    }

    public function acceptTaskQuote(Request $request, $taskId)
{
    try {
        // Get the quote ID from the request
        $quoteId = $request->input('quote_id');
        
        // Log the quote ID to verify it's correct
        \Log::info('Quote ID received', [
            'quote_id' => $quoteId
        ]);

        // Fetch the contractor_id from the task_contractor table (this is the contractor who was invited)
        $taskQuote = DB::table('task_contractor')->where('id', $quoteId)->first();

        if (!$taskQuote) {
            return response()->json(['success' => false, 'message' => 'Quote not found.'], 404);
        }

        // Log the task quote details, including contractor_id
        \Log::info('Task Quote Details', [
            'taskQuote' => $taskQuote
        ]);

        // 1. Update the task_contractor table to mark the quote as approved
        DB::table('task_contractor')
            ->where('id', $quoteId)  // Find the quote by quoteId
            ->update([
                'status' => 'approved',  // Set the quote status to approved
                'is_final' => 1,         // Mark the quote as final (no further negotiation)
                'is_sub_contractor' => 1, // Mark this contractor as the one handling the task
                'updated_at' => now(),   // Update the timestamp
            ]);

        // Log before updating the task to ensure the right contractor ID is being assigned
        \Log::info('Assigning contractor to task', [
            'task_id' => $taskId,
            'assigned_contractor_id' => $taskQuote->contractor_id
        ]);

        // 2. Update the tasks table to assign the contractor who was invited (contractor_id from task_contractor)
        DB::table('tasks')
            ->where('id', $taskId)  // Find the task by taskId
            ->update([
                'assigned_contractor_id' => $taskQuote->contractor_id, // Assign the contractor who was invited
                'category' => 'priority_2',                            // Update task category to 'priority_2'
                'status' => 'approved',                                // Mark task status as approved
                'updated_at' => now(),                                 // Update the timestamp
            ]);

        // Return success response
        return response()->json(['success' => true, 'message' => 'Quote approved successfully!']);
    } catch (\Exception $e) {
        // Log the error and return error response
        \Log::error('Error approving quote: ' . $e->getMessage());

        return response()->json(['success' => false, 'message' => 'Error occurred while approving the quote.'], 500);
    }
}


    // Function to handle rejecting a task quote
    public function rejectTaskQuote(Request $request, $taskId)
    {
        $quoteId = $request->input('quote_id');

        // Mark the task quote as rejected in the task_contractor table
        DB::table('task_contractor')
            ->where('id', $quoteId)
            ->update([
                'status' => 'rejected',
                'is_final' => 1,  // Mark as final
                'updated_at' => now(),
            ]);

        // Update the tasks table to set the task as rejected
        DB::table('tasks')
            ->where('id', $taskId)
            ->update([
                'status' => 'rejected', // Update task status to 'rejected'
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Task quote has been rejected.']);
    }

    // Function to handle suggesting a new price for a task quote
    public function suggestTaskQuote(Request $request, $taskId)
    {
        $quoteId = $request->input('quote_id');

        // Validate the input data
        $data = $request->validate([
            'new_price' => 'required|numeric|min:0',
            'new_pdf' => 'required|file|mimes:pdf|max:2048', // Accept only PDF files
            'quote_suggestion' => 'nullable|string|max:255', // Optional field for suggestions
        ]);

        // Save the uploaded new PDF file
        $pdfPath = $request->file('new_pdf')->store('task_quotes', 'public');

        // Get the contractor's ID (currently logged-in user)
        $contractorId = Auth::id();

        // Update the task quote with the new price and PDF
        DB::table('task_contractor')
            ->where('id', $quoteId)
            ->update([
                'quoted_price' => $data['new_price'],
                'quote_pdf' => $pdfPath,
                'quote_suggestion' => $data['quote_suggestion'] ?? null,
                'suggested_by' => $contractorId,  // Update suggested_by to contractor
                'status' => 'submitted', // Mark the status as 'submitted' after suggesting new price
                'is_final' => 0,  // Reset final status since negotiation is still ongoing
                'updated_at' => now(),
            ]);

        // Update the tasks table to reflect the ongoing negotiation
        DB::table('tasks')
            ->where('id', $taskId)
            ->update([
                'category' => 'under_negotiation', // Set task to 'under negotiation'
                'status' => 'pending', // Reset task status to 'pending'
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Your new task quote has been submitted and awaiting approval.']);
    }
}
