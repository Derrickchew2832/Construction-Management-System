<?php

namespace App\Http\Controllers\Contractor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ContractorTaskController extends Controller
{
    // Display the tasks related to the contractor (task invitations and submitted task quotes)
    public function indexTasks(Request $request)
    {
        $contractorId = Auth::id();

        // Fetch submitted quotes for tasks
        $quotes = DB::table('task_contractor')
            ->join('tasks', 'task_contractor.task_id', '=', 'tasks.id')
            ->where('task_contractor.contractor_id', $contractorId)
            ->select('task_contractor.*', 'tasks.title as task_title')
            ->get();

        // Fetch pending task invitations where the contractor hasn't submitted a quote yet
        $pendingInvitations = DB::table('task_invitations')
            ->join('tasks', 'task_invitations.task_id', '=', 'tasks.id')
            ->where('task_invitations.contractor_id', $contractorId)
            ->where('task_invitations.status', 'pending') // Only pending invitations
            ->select(
                'tasks.id', 
                'tasks.title', 
                'tasks.description', 
                'tasks.start_date', 
                'tasks.due_date', 
                'task_invitations.status as invitation_status'
            )
            ->get();

        return view('contractor.tasks.index', compact('quotes', 'pendingInvitations'));
    }

    // Function to handle submission of a task quote
    public function submitTaskQuote(Request $request, $taskId)
    {
        // Validate the input data
        $data = $request->validate([
            'quoted_price' => 'required|numeric|min:0',
            'quote_pdf' => 'required|file|mimes:pdf|max:2048', // Accepts only PDF files
        ]);

        // Save the uploaded PDF file
        $pdfPath = $request->file('quote_pdf')->store('task_quotes', 'public');

        // Insert or update the contractor's task quote in task_contractor table
        DB::table('task_contractor')->updateOrInsert(
            ['task_id' => $taskId, 'contractor_id' => Auth::id()],
            [
                'quoted_price' => $data['quoted_price'],
                'quote_pdf' => $pdfPath,
                'status' => 'submitted',
                'updated_at' => now(),
            ]
        );

        // Update the task invitation status to submitted
        DB::table('task_invitations')
            ->where('task_id', $taskId)
            ->where('contractor_id', Auth::id())
            ->update(['status' => 'submitted', 'updated_at' => now()]);

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

        // Optionally, you can perform additional logic for the project state here (e.g., updating the task status)

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
            'new_pdf' => 'required|file|mimes:pdf|max:2048', // Accepts only PDF files
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
