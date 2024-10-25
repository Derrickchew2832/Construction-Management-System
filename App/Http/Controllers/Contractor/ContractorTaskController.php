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
                'task_contractor.id as quote_id', 
                'task_contractor.task_id', 
                'task_contractor.quoted_price', 
                'task_contractor.quote_pdf', 
                'task_contractor.status', 
                'task_contractor.quote_suggestion',
                'tasks.title as task_title', 
                'tasks.description as task_description', 
                'tasks.start_date', 
                'tasks.due_date', 
                'tasks.task_pdf'
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
    // Validate the input data with strict rules to ensure required fields and valid data
    $data = $request->validate([
        'quoted_price' => 'required|numeric|min:1', // Price must be positive and greater than zero
        'quote_pdf' => 'required|file|mimes:pdf|max:2048', // Only accept PDF files
        'quote_suggestion' => 'required|string|max:255', // Description must be filled in
    ]);

    // Save the uploaded PDF file
    $pdfPath = $request->file('quote_pdf')->store('task_quotes', 'public');

    // Determine the user (Contractor)
    $suggestedBy = Auth::id();

    // Insert or update the contractor's task quote in the task_contractor table
    DB::table('task_contractor')->updateOrInsert(
        ['task_id' => $taskId, 'contractor_id' => $suggestedBy],
        [
            'quoted_price' => $data['quoted_price'], // Save the quoted price
            'quote_pdf' => $pdfPath, // Save the PDF file path
            'quote_suggestion' => $data['quote_suggestion'], // Save the suggestion/description
            'suggested_by' => $suggestedBy, // Update contractor ID
            'status' => 'submitted', // Set status to 'submitted' for main contractor to act next
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    // Update the tasks table with the 'pending' status when a quote is submitted
    DB::table('tasks')
        ->where('id', $taskId)
        ->update([
            'status' => 'pending',
            'category' => 'under_negotiation',
            'updated_at' => now(),
        ]);

    return redirect()->back()->with('success', 'Task quote submitted successfully and awaiting main contractor approval.');
}



public function acceptTaskQuote(Request $request, $taskId)
{
    // Log the task ID and quote ID for debugging
    \Log::info('Accept Task Quote - Task ID: ' . $taskId . ', Quote ID: ' . $request->input('quote_id'));

    // Fetch the quote ID from the request
    $quoteId = $request->input('quote_id'); 

    // Check if the quote exists
    $taskQuote = DB::table('task_contractor')->where('id', $quoteId)->first();
    if (!$taskQuote) {
        return redirect()->back()->with('error', 'Quote not found.');
    }

    try {
        // Update the task_contractor table to mark the quote as approved
        DB::table('task_contractor')->where('id', $quoteId)->update([
            'status' => 'approved',
            'is_final' => 1,
            'is_sub_contractor' => 1,
            'updated_at' => now(),
        ]);

        // Determine task category based on due date
        $task = DB::table('tasks')->where('id', $taskId)->first();
        $category = (now()->diffInDays($task->due_date) <= 7) ? 'due_date' : 'priority_2';

        // Update tasks table to assign the contractor and set the task category
        DB::table('tasks')->where('id', $taskId)->update([
            'assigned_contractor_id' => $taskQuote->contractor_id,
            'category' => $category,
            'status' => 'approved',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Quote approved successfully!');
    } catch (\Exception $e) {
        \Log::error('Error approving quote: ' . $e->getMessage());
        return redirect()->back()->with('error', 'An error occurred while approving the quote.');
    }
}

public function rejectTaskQuote(Request $request, $taskId)
{
    // Log the task ID and quote ID for debugging
    \Log::info('Reject Task Quote - Task ID: ' . $taskId . ', Quote ID: ' . $request->input('quote_id'));

    // Retrieve the `quote_id` from the request
    $quoteId = $request->input('quote_id');

    // Check if the quote exists
    $taskQuote = DB::table('task_contractor')->where('id', $quoteId)->first();
    if (!$taskQuote) {
        return redirect()->back()->with('error', 'Quote not found.');
    }

    try {
        // Update the `task_contractor` table to mark the quote as rejected
        DB::table('task_contractor')->where('id', $quoteId)->update([
            'status' => 'rejected',
            'is_final' => 1,
            'updated_at' => now(),
        ]);

        // Update the `tasks` table to set `category` to 'under_negotiation'
        DB::table('tasks')->where('id', $taskId)->update([
            'status' => 'rejected',
            'category' => 'under_negotiation',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Quote rejected successfully!');
    } catch (\Exception $e) {
        \Log::error('Error rejecting quote: ' . $e->getMessage());
        return redirect()->back()->with('error', 'An error occurred while rejecting the quote.');
    }
}



    public function suggestTaskQuote(Request $request, $taskId)
{
    $quoteId = $request->input('quote_id');

    // Validate the input data, ensuring all fields are filled and price is valid
    $data = $request->validate([
        'new_price' => 'required|numeric|min:1', // Must be greater than 0
        'new_pdf' => 'required|file|mimes:pdf|max:2048', // Validate the PDF upload
        'quote_suggestion' => 'required|string|max:255', // Quote suggestion is required
    ]);

    // Save the uploaded new PDF file
    $pdfPath = $request->file('new_pdf')->store('task_quotes', 'public');

    // Get the contractor's ID (currently logged-in user)
    $contractorId = Auth::id();

    // Ensure the task quote exists and is being updated
    $taskQuote = DB::table('task_contractor')->where('id', $quoteId)->first();
    if (!$taskQuote) {
        return response()->json(['success' => false, 'message' => 'Task quote not found.'], 404);
    }

    // Update the task quote with the new price, PDF, and suggestion
    DB::table('task_contractor')
        ->where('id', $quoteId)
        ->update([
            'quoted_price' => $data['new_price'], // Save the quoted price
            'quote_pdf' => $pdfPath, // Update PDF file path
            'quote_suggestion' => $data['quote_suggestion'], // Update suggestion
            'suggested_by' => $contractorId, // Update the contractor who suggested
            'status' => 'submitted',
            'is_final' => 0, // Reset final status since negotiation is still ongoing
            'updated_at' => now(), // Update the timestamp
        ]);

    // Update the tasks table to reflect the ongoing negotiation
    DB::table('tasks')
        ->where('id', $taskId)
        ->update([
            'category' => 'under_negotiation', // Set task to 'under negotiation'
            'status' => 'pending', // Reset task status to pending
            'updated_at' => now(),
        ]);

    return response()->json(['success' => true, 'message' => 'Your new task quote has been submitted and is awaiting approval.']);
}


}
