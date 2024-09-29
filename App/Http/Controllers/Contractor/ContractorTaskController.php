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

// Fetch pending task invitations where the contractor hasn't submitted a quote yet
$pendingTaskInvitations = DB::table('task_invitations')
->join('tasks', 'task_invitations.task_id', '=', 'tasks.id')
->where('task_invitations.contractor_id', $contractorId)
->where('task_invitations.status', 'pending')
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

    // Function to handle submission of a task quote
    public function submitTaskQuote(Request $request, $taskId)
    {
        $data = $request->validate([
            'quoted_price' => 'required|numeric|min:0',
            'quote_pdf' => 'required|file|mimes:pdf|max:2048', // Accept only PDF files
        ]);

        // Save the uploaded PDF file
        $pdfPath = $request->file('quote_pdf')->store('task_quotes', 'public');

        // Insert or update the contractor's task quote in task_contractor table
        DB::table('task_contractor')->updateOrInsert(
            ['task_id' => $taskId, 'contractor_id' => Auth::id()],
            [
                'quoted_price' => $data['quoted_price'],
                'quote_pdf' => $pdfPath,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );


        return redirect()->route('contractor.tasks.index')->with('success', 'Task quote submitted successfully!');
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

        // Update the task quote with the new price and PDF
        DB::table('task_contractor')
            ->where('id', $quoteId)
            ->update([
                'quoted_price' => $data['new_price'],
                'quote_pdf' => $pdfPath,
                'status' => 'suggested', // Mark the status as 'suggested'
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Your new task quote has been submitted for review.']);
    }
}
