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

        return redirect()->back()->with('success', 'Task quote submitted successfully and awaiting main contractor approval.');
    }

    // Function to handle accepting a suggested task quote
    public function acceptTaskQuote(Request $request, $taskId)
    {
        $quoteId = $request->input('quote_id');

        // Mark the task quote as approved in the task_contractor table
        DB::table('task_contractor')
            ->where('id', $quoteId)
            ->update([
                'status' => 'approved',
                'is_final' => 1,  // Mark as final
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Task quote has been accepted.']);
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
                'status' => 'submitted', // Mark the status as 'suggested' after suggesting new price
                'is_final' => 0,  // Reset final status since negotiation is still ongoing
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Your new task quote has been submitted and awaiting approval.']);
    }
}
