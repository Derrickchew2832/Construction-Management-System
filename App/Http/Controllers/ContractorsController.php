<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Contractor\ContractorTaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ContractorsController extends Controller
{
    public function indexProjects(Request $request)
    {
        $contractorId = Auth::id(); // Get the logged-in contractor's ID
    
        // Fetch projects where the contractor is the main contractor and the project status is 'started' or 'completed'
        $mainContractorProjects = DB::table('projects')
            ->where('main_contractor_id', $contractorId)
            ->whereIn('projects.status', ['started', 'completed'])
            ->select(
                'projects.id',
                'projects.name',
                'projects.description',
                'projects.start_date',
                'projects.end_date',
                'projects.total_budget',
                'projects.budget_remaining',
                'projects.location',
                'projects.status',
                DB::raw('IFNULL(projects.is_favorite, 0) as is_favorite'),
                'projects.main_contractor_id'
            )
            ->groupBy(
                'projects.id',
                'projects.name',
                'projects.description',
                'projects.start_date',
                'projects.end_date',
                'projects.total_budget',
                'projects.budget_remaining',
                'projects.location',
                'projects.status',
                'projects.is_favorite',
                'projects.main_contractor_id'
            )
            ->get()
            ->map(function ($project) {
                // Default values for additional fields
                $project->accepted_task_count = 0;
                $project->ribbon = ($project->status === 'completed') ? 'Completed' : 'In Progress';
                $project->can_access_management = true; // Set as true if the contractor is the main contractor for the project
                return $project;
            });
    
        $acceptedTaskProjects = DB::table('projects')
            ->join('tasks', 'projects.id', '=', 'tasks.project_id')
            ->join('task_contractor', 'tasks.id', '=', 'task_contractor.task_id')
            ->where('task_contractor.contractor_id', $contractorId)
            ->whereIn('task_contractor.status', ['approved', 'completed'])
            ->select(
                'projects.id',
                'projects.name',
                'projects.description',
                'projects.start_date',
                'projects.end_date',
                'projects.total_budget',
                'projects.budget_remaining',
                'projects.location',
                'projects.status',
                DB::raw('0 as is_favorite'),
                DB::raw('COUNT(DISTINCT tasks.id) as accepted_task_count')
            )
            ->groupBy(
                'projects.id',
                'projects.name',
                'projects.description',
                'projects.start_date',
                'projects.end_date',
                'projects.total_budget',
                'projects.budget_remaining',
                'projects.location',
                'projects.status'
            )
            ->get()
            ->map(function ($project) {
                $project->ribbon = ($project->status === 'completed') ? 'Completed' : 'In Progress';
                $project->can_access_management = ($project->accepted_task_count > 0); // Access allowed if tasks are accepted
                return $project;
            });
    
        // Merge collections
        $projects = $mainContractorProjects->merge($acceptedTaskProjects);
    
        // Pass the $projects variable to the view
        return view('contractor.projects.index', compact('projects'));
    }
    

    

    public function showQuotes(Request $request)
{
    // Get the authenticated contractor ID
    $contractorId = Auth::id();

    // Fetch submitted quotes for the contractor
    $submittedQuotes = DB::table('project_contractor')
        ->join('projects', 'project_contractor.project_id', '=', 'projects.id')
        ->where('project_contractor.contractor_id', $contractorId)
        ->select('project_contractor.*', 'projects.name as project_name','project_contractor.quote_pdf', 'project_contractor.quote_suggestion')
        ->get();

    // Fetch pending invitations where contractor hasn't submitted a quote yet
    $pendingInvitations = DB::table('project_invitations')
        ->join('projects', 'project_invitations.project_id', '=', 'projects.id')
        ->leftJoin('project_documents', 'project_invitations.project_id', '=', 'project_documents.project_id')
        ->where('project_invitations.contractor_id', $contractorId)
        ->whereNotIn('project_invitations.project_id', function($query) use ($contractorId) {
            $query->select('project_id')
                  ->from('project_contractor')
                  ->where('contractor_id', $contractorId);
        })
        ->select(
            'projects.*', 
            'project_invitations.status as invitation_status', 
            'project_documents.document_path',
        'project_documents.original_name'
        )
        ->get();
    
    // Get task-related data from ContractorTaskController
    $taskController = new ContractorTaskController();
    $taskData = $taskController->indexTasks($request);
    
    // Merge the project data with task data and pass to the view
    return view('contractor.projects.quotes', array_merge(compact('submittedQuotes', 'pendingInvitations'), $taskData));
}


public function submitQuote(Request $request, $projectId)
{
    // Validate the quote data
    $data = $request->validate([
        'quoted_price' => 'required|numeric|min:0',
        'quote_pdf' => 'required|file|mimes:pdf|max:2048',
        'description' => 'required|string|max:1000', // Validate the description field
    ]);

    // Save the uploaded PDF file
    $pdfPath = $request->file('quote_pdf')->store('quotes', 'public');

    // Insert or update the contractor's quote in project_contractor table
    DB::table('project_contractor')->updateOrInsert(
        ['project_id' => $projectId, 'contractor_id' => Auth::id()],
        [
            'quoted_price' => $data['quoted_price'],
            'quote_pdf' => $pdfPath,
            'quote_suggestion' => $data['description'], // Save the description as the quote suggestion
            'status' => 'submitted',
            'suggested_by' => 'contractor',
            'updated_at' => now(),
        ]
    );

    // Update the invitation status
    DB::table('project_invitations')
        ->where('project_id', $projectId)
        ->where('contractor_id', Auth::id())
        ->update(['status' => 'submitted', 'updated_at' => now()]);

    return redirect()->route('contractor.projects.quotes')->with('success', 'Quote submitted successfully!');
}


public function respondToSuggestion(Request $request, $projectId)
{
    $action = $request->input('action');
    $quoteId = $request->input('quote_id');
    $contractorId = Auth::id(); // Get the authenticated contractor's user ID

    if ($action == 'accept') {
        // Accept the quote and set the contractor as the main contractor
        DB::transaction(function () use ($quoteId, $projectId, $contractorId) {
            // Fetch the contractor's quote details
            $quote = DB::table('project_contractor')
                ->where('id', $quoteId)
                ->first();

            // Update the accepted quote status
            DB::table('project_contractor')
                ->where('id', $quoteId)
                ->update([
                    'status' => 'approved',
                    'is_final' => true,
                    'main_contractor' => true,
                    'updated_at' => now(),
                ]);

            // Fetch the project to calculate the new remaining budget
            $project = DB::table('projects')->where('id', $projectId)->first();

            if ($project && $quote) {
                // Calculate the new remaining budget
                $newRemainingBalance = $project->budget_remaining - $quote->quoted_price;

                // Update the project to reflect that the contractor is now the main contractor and update the budget
                DB::table('projects')
                    ->where('id', $projectId)
                    ->update([
                        'status' => 'started', // Set project status to 'started'
                        'main_contractor_id' => $contractorId, // Set contractor as the main contractor
                        'budget_remaining' => $newRemainingBalance, // Update remaining budget
                        'updated_at' => now(),
                    ]);

                // Reject all other pending quotes for the project
                DB::table('project_contractor')
                    ->where('project_id', $projectId)
                    ->where('id', '!=', $quoteId) // Reject all other quotes
                    ->update([
                        'status' => 'rejected',
                        'is_final' => true,
                        'updated_at' => now(),
                    ]);

                // Update the project invitations to 'closed' status
                DB::table('project_invitations')
                    ->where('project_id', $projectId)
                    ->where('contractor_id', '!=', $contractorId) // Close invitations for all other contractors
                    ->update([
                        'status' => 'closed', // Mark invitations as closed
                        'updated_at' => now(),
                    ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'You have accepted the quote, and the project has been updated.']);
    } elseif ($action == 'reject') {
        // Reject the suggestion and mark it as final
        DB::table('project_contractor')
            ->where('id', $quoteId)
            ->update([
                'status' => 'rejected',
                'is_final' => true,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'You have rejected the quote.']);
    } elseif ($action == 'suggest') {
        // Resubmit the quote with new data
        $data = $request->validate([
            'new_price' => 'required|numeric|min:0',
            'new_pdf' => 'required|file|mimes:pdf|max:2048',
            'description' => 'required|string',
        ]);

        // Save the new PDF file
        $pdfPath = $request->file('new_pdf')->store('quotes', 'public');

        // Update the status to 'submitted' after suggesting a new price
        DB::table('project_contractor')
            ->where('id', $quoteId)
            ->update([
                'quoted_price' => $data['new_price'],
                'quote_pdf' => $pdfPath,
                'quote_suggestion' => $data['description'],
                'status' => 'submitted', // Change status to 'submitted' after suggesting a new price
                'suggested_by' => 'contractor', // The logged-in user who suggested the new quote
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Your new quote has been submitted for review.']);
    }

    return response()->json(['success' => false, 'message' => 'Invalid action.']);
}


    
    public function toggleFavorite(Request $request, $projectId)
    {
        // Toggle the project favorite status
        $isFavorite = $request->input('is_favorite', false);
        $userId = Auth::id();

        if ($isFavorite) {
            DB::table('project_user_favorites')->updateOrInsert(
                ['user_id' => $userId, 'project_id' => $projectId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        } else {
            DB::table('project_user_favorites')
                ->where('user_id', $userId)
                ->where('project_id', $projectId)
                ->delete();
        }

        return response()->json(['is_favorite' => $isFavorite]);
    }

    public function manageProject($projectId)
    {
        // Fetch the project to manage it
        $project = DB::table('projects')->where('id', $projectId)->first();
        return view('contractor.projects.manage', compact('project'));
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('contractor.profile', compact('user'));
    }

    // Update contractor profile
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validate input with custom error messages
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ], [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
        ]);

        // Update the user's name and email
        DB::table('users')->where('id', $user->id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'updated_at' => now(),
        ]);

        // Redirect back with a success message
        return redirect()->route('contractor.profile.edit')->with('success', 'Profile updated successfully.');
    }

    // Change contractor's password page
    public function changePassword()
    {
        return view('contractor.change_password');
    }

    // Update contractor's password
    public function updatePassword(Request $request)
    {
        // Validate the password fields with custom error messages
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Please enter a new password.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        // Update the password
        $user = Auth::user();
        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($request->password),
            'updated_at' => now(),
        ]);

        // Redirect back with a success message
        return redirect()->route('contractor.profile.edit')->with('status', 'password-updated');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
