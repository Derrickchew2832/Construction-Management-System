<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProjectManagerController extends Controller
{
    public function dashboard()
    {
        $project = DB::table('projects')
                     ->where('project_manager_id', Auth::id())
                     ->first();

        if (!$project) {
            return redirect()->route('project_manager.projects.index')->with('error', 'No projects found.');
        }

        $mainContractorExists = DB::table('project_contractor')
            ->where('project_id', $project->id)
            ->where('main_contractor', true)
            ->exists();

        if (!$mainContractorExists) {
            return redirect()->route('project_manager.projects.index')->with('error', 'Management board is not available until a main contractor is selected.');
        }

        return view('project_manager.dashboard', compact('project'));
    }

    public function indexProjects(Request $request)
{
    $sortOrder = $request->query('sort', 'asc');

    $projects = DB::table('projects')
        ->where('project_manager_id', Auth::id())
        ->orderBy('name', $sortOrder)
        ->get();

    foreach ($projects as $project) {
        // Start the user count with the project manager
        $project->members_count = 1;

        // Count accepted contractors
        $contractors_count = DB::table('project_contractor')
            ->where('project_id', $project->id)
            ->where('status', 'approved')
            ->count();

       $clients_count = DB::table('project_invitations_client')
            ->where('project_id', $project->id)
            ->where('status', 'accepted')
            ->count();
        

        // Add contractors and clients to members count
        $project->members_count += ($contractors_count + $clients_count);

        // Check if the project has a main contractor
        $project->main_contractor = DB::table('project_contractor')
            ->where('project_id', $project->id)
            ->where('main_contractor', true)
            ->exists();

        // Determine if the current user has favorited the project
        $project->is_favorite = DB::table('project_user_favorites')
            ->where('project_id', $project->id)
            ->where('user_id', Auth::id())
            ->exists();

        // Fetch related contractors and tasks for the project
        $project->contractors = DB::table('project_invitations')
            ->join('users', 'project_invitations.contractor_id', '=', 'users.id')
            ->where('project_invitations.project_id', $project->id)
            ->select('users.name', 'project_invitations.status')
            ->get();

        $project->tasks = DB::table('tasks')
            ->where('project_id', $project->id)
            ->get();

        $project->can_access_management = $project->main_contractor;
    }

    return view('project_manager.projects.index', compact('projects', 'sortOrder'));
}


    public function createProject()
    {
        return view('project_manager.projects.create');
    }

    public function showProject($projectId)
    {
        $project = DB::table('projects')->where('id', $projectId)->first();

        if (!$project) {
            return redirect()->route('project_manager.projects.index')->with('error', 'Project not found.');
        }

        // Fetch project documents
        $documents = DB::table('project_documents')
            ->where('project_id', $projectId)
            ->get();

        $project->contractors = DB::table('project_invitations')
            ->join('users', 'project_invitations.contractor_id', '=', 'users.id')
            ->leftJoin('project_contractor', function ($join) use ($project) {
                $join->on('project_invitations.project_id', '=', 'project_contractor.project_id')
                     ->on('project_invitations.contractor_id', '=', 'project_contractor.contractor_id');
            })
            ->where('project_invitations.project_id', $projectId)
            ->select(
                'users.name',
                'users.email',  
                'project_invitations.status',
                'project_contractor.main_contractor', 
                'project_contractor.status as quote_status'
            )
            ->get();

        return view('project_manager.projects.show', compact('project', 'documents'));
    }


    public function storeProject(Request $request)
{
    // Validate the inputs with custom error messages
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'start_date' => 'required|date|after_or_equal:today', // Start date must be today or in the future
        'end_date' => 'required|date|after:start_date', // End date must be after the start date
        'total_budget' => 'required|numeric|min:0',
        'location' => 'required|string|max:255',
        'documents' => 'required|array', // Ensure at least one document is uploaded
        'documents.*' => 'mimes:pdf,doc,docx|max:2048' // Validate file types and size for each file
    ], [
        'name.required' => 'The project name is required.',
        'name.string' => 'The project name must be a valid string.',
        'name.max' => 'The project name must not exceed 255 characters.',
        
        'description.required' => 'The project description is required.',
        'description.string' => 'The project description must be a valid string.',
        
        'start_date.required' => 'The start date is required.',
        'start_date.date' => 'The start date must be a valid date.',
        'start_date.after_or_equal' => 'The start date must be today or a future date.',

        'end_date.required' => 'The end date is required.',
        'end_date.date' => 'The end date must be a valid date.',
        'end_date.after' => 'The end date must be after the start date.',

        'total_budget.required' => 'The total budget is required.',
        'total_budget.numeric' => 'The total budget must be a numeric value.',
        'total_budget.min' => 'The total budget must be at least 0.',

        'location.required' => 'The location is required.',
        'location.string' => 'The location must be a valid string.',
        'location.max' => 'The location must not exceed 255 characters.',

        'documents.required' => 'At least one document is required.',
        'documents.*.mimes' => 'Documents must be a PDF, DOC, or DOCX file.',
        'documents.*.max' => 'Documents must not exceed 2MB in size.'
    ]);

    // Insert the new project
    $projectId = DB::table('projects')->insertGetId([
        'project_manager_id' => Auth::id(),
        'name' => $data['name'],
        'description' => $data['description'],
        'start_date' => $data['start_date'],
        'end_date' => $data['end_date'],
        'total_budget' => $data['total_budget'],
        'budget_remaining' => $data['total_budget'], // Set the remaining budget to the total budget initially
        'location' => $data['location'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Handle multiple file uploads
    if ($request->hasFile('documents')) {
        foreach ($request->file('documents') as $file) {
            $filePath = $file->store('project_documents', 'public'); // Save file to public storage
            $originalFileName = $file->getClientOriginalName(); // Get the original file name

            // Insert the document path and original file name into the project_documents table
            DB::table('project_documents')->insert([
                'project_id' => $projectId,
                'document_path' => $filePath,
                'original_name' => $originalFileName,  // Save the original file name
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // Insert the project manager as a project user
    DB::table('project_user')->insert([
        'project_id' => $projectId,
        'user_id' => Auth::id(),
        'role' => 'project_manager',
        'invited_by' => Auth::id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Redirect to the project details page with a success message
    return redirect()->route('project_manager.projects.show', $projectId)->with('success', 'Project created successfully');
}

public function editProject($projectId)
{
    $project = DB::table('projects')->where('id', $projectId)->first();
    if (!$project) {
        return redirect()->route('project_manager.projects.index')->with('error', 'Project not found.');
    }

    return view('project_manager.projects.edit', compact('project'));
}

public function updateProject(Request $request, $projectId)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'start_date' => 'required|date|after_or_equal:today', // Start date must be today or in the future
        'end_date' => 'required|date|after:start_date', // End date must be after the start date
        'total_budget' => 'required|numeric|min:0',
        'location' => 'required|string|max:255',
    ], [
        'name.required' => 'The project name is required.',
        'name.string' => 'The project name must be a valid string.',
        'name.max' => 'The project name must not exceed 255 characters.',
        
        'description.required' => 'The project description is required.',
        'description.string' => 'The project description must be a valid string.',

        'start_date.required' => 'The start date is required.',
        'start_date.date' => 'The start date must be a valid date.',
        'start_date.after_or_equal' => 'The start date must be today or a future date.',

        'end_date.required' => 'The end date is required.',
        'end_date.date' => 'The end date must be a valid date.',
        'end_date.after' => 'The end date must be after the start date.',

        'total_budget.required' => 'The total budget is required.',
        'total_budget.numeric' => 'The total budget must be a numeric value.',
        'total_budget.min' => 'The total budget must be at least 0.',

        'location.required' => 'The location is required.',
        'location.string' => 'The location must be a valid string.',
        'location.max' => 'The location must not exceed 255 characters.'
    ]);

    DB::table('projects')->where('id', $projectId)->update([
        'name' => $data['name'],
        'description' => $data['description'],
        'start_date' => $data['start_date'],
        'end_date' => $data['end_date'],
        'total_budget' => $data['total_budget'],
        'location' => $data['location'],
        'updated_at' => now(),
    ]);

    return redirect()->route('project_manager.projects.show', $projectId)->with('success', 'Project updated successfully!');
}


    public function deleteProject($projectId)
    {
        DB::table('projects')->where('id', $projectId)->delete();
        return redirect()->route('project_manager.projects.index')->with('success', 'Project deleted successfully!');
    }

    public function inviteContractor($projectId)
{
    $project = DB::table('projects')->where('id', $projectId)->first();

    // Fetch contractors who have been invited to the project
    $invitedContractors = DB::table('project_invitations')
        ->join('users', 'project_invitations.contractor_id', '=', 'users.id')
        ->leftJoin('project_contractor', function($join) use ($projectId) {
            $join->on('project_invitations.contractor_id', '=', 'project_contractor.contractor_id')
                ->where('project_contractor.project_id', '=', $projectId);
        })
        ->where('project_invitations.project_id', $projectId)
        ->select(
            'users.name', 
            'users.email', 
            'project_invitations.status as invitation_status',
            'project_contractor.status as quote_status'
        )
        ->get();

    return view('project_manager.projects.invite', compact('project', 'invitedContractors'));
}


public function storeInvite(Request $request, $projectId)
{
    $request->validate([
        'contractor_email' => 'required|email',
    ]);

    // Get contractor based on the provided email
    $contractor = DB::table('users')->where('email', $request->contractor_email)->first();

    if (!$contractor) {
        return redirect()->route('project_manager.projects.invite', $projectId)
            ->with('error', 'The email provided does not exist in the system.');
    }

    // Get the contractor role ID to validate they are indeed a contractor
    $contractorRoleId = DB::table('roles')->where('name', 'contractor')->value('id');

    if ($contractor->role_id != $contractorRoleId) {
        return redirect()->route('project_manager.projects.invite', $projectId)
            ->with('error', 'The provided email does not belong to a contractor.');
    }

    // Check if the contractor has an entry in the project_contractor table for this project
    $contractorStatus = DB::table('project_contractor')
        ->where('project_id', $projectId)
        ->where('contractor_id', $contractor->id)
        ->value('status'); // Fetch only the status

    // If there is no record (null status), this is a new invite, so proceed with invitation
    if (is_null($contractorStatus)) {
        // Insert the invitation into the project_invitations table
        DB::table('project_invitations')->insert([
            'project_id' => $projectId,
            'contractor_id' => $contractor->id,
            'invited_by' => Auth::id(),
            'email' => $request->contractor_email,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('project_manager.projects.invite', $projectId)
            ->with('success', 'Contractor invited successfully!');
    }

    // Prevent re-invitation if the status is 'pending' or 'submitted'
    if ($contractorStatus === 'pending' || $contractorStatus === 'submitted') {
        return redirect()->route('project_manager.projects.invite', $projectId)
            ->with('error', 'This contractor has already been invited and is awaiting a response.');
    }

    // Only allow re-invitation if the status is 'rejected'
    if ($contractorStatus === 'rejected') {
        // Delete the contractor's existing record from project_contractor when reinviting
        DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractor->id)
            ->delete();

        // Insert or update the invitation in the project_invitations table
        DB::table('project_invitations')->updateOrInsert(
            ['project_id' => $projectId, 'contractor_id' => $contractor->id],
            [
                'invited_by' => Auth::id(),
                'email' => $request->contractor_email,
                'status' => 'pending', // Set status to pending after reinvitation
                'updated_at' => now(),
            ]
        );

        return redirect()->route('project_manager.projects.invite', $projectId)
            ->with('success', 'Contractor re-invited successfully!');
    }

    // If the contractor is neither rejected nor a new invite, prevent re-invitation
    return redirect()->route('project_manager.projects.invite', $projectId)
        ->with('error', 'This contractor has already been invited.');
}

    public function viewQuote($projectId, $quoteId)
    {
        $quote = DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('id', $quoteId)
            ->first();

        if (!$quote) {
            return redirect()->route('project_manager.projects.index')->with('error', 'Quote not found.');
        }

        $project = DB::table('projects')->where('id', $projectId)->first();
        $contractor = DB::table('users')->where('id', $quote->contractor_id)->first();

        return view('project_manager.projects.view_quote', compact('quote', 'project', 'contractor'));
    }

    public function manageQuotes()
{
    // Fetch the projects managed by the current logged-in project manager
    $projects = DB::table('projects')
        ->where('project_manager_id', Auth::id()) // Filter by project manager ID
        ->get();

    // Fetch the quotes related to the projects managed by the current project manager
    $quotes = DB::table('project_contractor')
        ->join('projects', 'project_contractor.project_id', '=', 'projects.id')
        ->join('users', 'project_contractor.contractor_id', '=', 'users.id')
        ->where('projects.project_manager_id', Auth::id()) // Filter quotes by the project manager ID
        ->select(
            'project_contractor.*', 
            'users.name as contractor_name', 
            'projects.name as project_name', 
            'projects.id as project_id',
            'projects.budget_remaining', // Include remaining budget
            'project_contractor.status as quote_status',
            'project_contractor.quote_suggestion'
        )
        ->get();

    return view('project_manager.projects.quotes', compact('quotes', 'projects'));
}



public function approveQuote($projectId, $contractorId)
{
    // Fetch the contractor's quote
    $quote = DB::table('project_contractor')
        ->where('project_id', $projectId)
        ->where('contractor_id', $contractorId)
        ->first();

    if (!$quote || !$quote->quoted_price) {
        return redirect()->route('project_manager.projects.quotes', $projectId)
            ->with('error', 'Quote not found or quoted price is missing.');
    }

    // Fetch the project
    $project = DB::table('projects')->where('id', $projectId)->first();

    if (!$project) {
        return redirect()->route('project_manager.projects.index')->with('error', 'Project not found.');
    }

    // Calculate the new remaining budget
    $newRemainingBalance = $project->budget_remaining - $quote->quoted_price;

    // Prevent approval if the new remaining balance is negative
    if ($newRemainingBalance < 0) {
        // Redirect back with an error message
        return redirect()->route('project_manager.projects.quotes', $projectId)
            ->with('error', 'The quoted price exceeds the available project budget. Please reject or suggest a new price.');
    }

    // Perform updates in a transaction to ensure data consistency
    DB::transaction(function() use ($projectId, $contractorId, $newRemainingBalance) {
        // Update project's remaining budget
        DB::table('projects')->where('id', $projectId)->update([
            'budget_remaining' => $newRemainingBalance,
            'status' => 'started',
            'updated_at' => now(),
        ]);

        // Approve the contractor as the main contractor
        DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->update([
                'status' => 'approved',
                'main_contractor' => true,
                'updated_at' => now(),
            ]);

        // Reject all other pending quotes for the project
        DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', '!=', $contractorId) // Reject all other contractors
            ->update([
                'status' => 'rejected',
                'is_final' => true,
                'updated_at' => now(),
            ]);

        // Update the accepted contractor's invitation status to 'accepted'
        DB::table('project_invitations')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->update([
                'status' => 'accepted',
                'updated_at' => now(),
            ]);

        // Reject all other pending invitations for the project
        DB::table('project_invitations')
            ->where('project_id', $projectId)
            ->where('contractor_id', '!=', $contractorId) // Only close invitations not for the selected contractor
            ->where('status', 'pending') // Only close pending invitations
            ->update([
                'status' => 'closed',  // Mark invitations as closed
                'updated_at' => now(),
            ]);
    });

    return redirect()->route('project_manager.projects.quotes', $projectId)
        ->with('success', 'Quote approved, contractor promoted to main contractor, and project started. All other invitations closed.');
}




    public function rejectQuote($projectId, $contractorId)
    {
        DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->update(['status' => 'rejected']);

        return redirect()->route('project_manager.projects.quotes', $projectId)->with('success', 'Quote rejected successfully.');
    }

    public function suggestPrice(Request $request, $projectId)
{
    // Fetch the project
    $project = DB::table('projects')->where('id', $projectId)->first();

    if (!$project) {
        return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
            ->with('error', 'Project not found.');
    }

    // Fetch the current quote
    $quote = DB::table('project_contractor')->where('id', $request->input('quote_id'))->first();

    if (!$quote) {
        return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
            ->with('error', 'Quote not found.');
    }

    // Calculate the new remaining budget after suggesting the new price
    $newPrice = $request->input('new_price');
    $newRemainingBudget = $project->budget_remaining - $newPrice;

    // Prevent suggestion if the new remaining balance is negative
    if ($newRemainingBudget < 0) {
        return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
            ->with('error', 'The suggested price exceeds the available project budget. Please suggest a lower price.');
    }

    // Update the quote details
    DB::transaction(function() use ($request, $newPrice) {
        $updateData = [
            'quoted_price' => $newPrice,
            'quote_suggestion' => $request->input('new_quote'),
            'status' => 'suggested',
            'suggested_by' => 'project_manager',
            'updated_at' => now(),
        ];

        // Handle the uploaded PDF file if it exists
        if ($request->hasFile('new_pdf')) {
            $filePath = $request->file('new_pdf')->store('quotes', 'public');
            $updateData['quote_pdf'] = $filePath;
        }

        // Update the quote in the database
        DB::table('project_contractor')
            ->where('id', $request->input('quote_id'))
            ->where('contractor_id', $request->input('contractor_id'))
            ->update($updateData);
    });

    return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
        ->with('success', 'New price and suggestion updated successfully. Waiting for contractor response.');
}

public function handleQuoteAction(Request $request)
{
    // Extract the action (approve/reject/suggest) and quote ID from the request
    $action = $request->input('action');
    $quoteId = $request->input('quote_id');

    // Fetch the quote details
    $quote = DB::table('project_contractor')->where('id', $quoteId)->first();

    if (!$quote) {
        // If the quote doesn't exist, return with an error
        return redirect()->route('project_manager.projects.quotes')->with('error', 'Quote not found.');
    }

    // Approve action
    if ($action === 'approve') {
        // Update the contractor's quote to 'approved'
        DB::table('project_contractor')->where('id', $quoteId)->update([
            'status' => 'approved',
            'is_final' => true,
            'main_contractor' => true,
            'updated_at' => now(),
        ]);

        // Fetch the project to update the remaining budget
        $project = DB::table('projects')->where('id', $quote->project_id)->first();

        if (!$project) {
            return redirect()->route('project_manager.projects.quotes')->with('error', 'Project not found.');
        }

        // Calculate the new remaining budget after approval
        $newRemainingBalance = $project->budget_remaining - $quote->quoted_price;

        // Ensure the remaining budget does not go negative
        if ($newRemainingBalance < 0) {
            return redirect()->route('project_manager.projects.quotes', $quote->project_id)
                ->with('error', 'Quoted price exceeds the remaining budget.');
        }

        // Update the project's remaining budget and assign the main contractor
        DB::table('projects')->where('id', $quote->project_id)->update([
            'budget_remaining' => $newRemainingBalance,
            'status' => 'started',
            'main_contractor_id' => $quote->contractor_id,
            'updated_at' => now(),
        ]);

        // Update the accepted contractor's invitation status to 'accepted'
        DB::table('project_invitations')
            ->where('project_id', $quote->project_id)
            ->where('contractor_id', $quote->contractor_id)
            ->update([
                'status' => 'accepted',
                'updated_at' => now(),
            ]);

        // Reject all other pending quotes for the project
        DB::table('project_contractor')
            ->where('project_id', $quote->project_id)
            ->where('id', '!=', $quoteId)
            ->update([
                'status' => 'rejected',
                'is_final' => true,
                'updated_at' => now(),
            ]);

        // Close any other pending invitations
        DB::table('project_invitations')
            ->where('project_id', $quote->project_id)
            ->where('contractor_id', '!=', $quote->contractor_id)
            ->where('status', 'pending')
            ->update([
                'status' => 'closed',
                'updated_at' => now(),
            ]);

        // Return with success message
        return redirect()->route('project_manager.projects.quotes')
            ->with('success', 'Quote approved and contractor assigned as main contractor. Invitations closed.');
    }

    // Reject action
    elseif ($action === 'reject') {
        // Update the quote status to 'rejected'
        DB::table('project_contractor')->where('id', $quoteId)->update([
            'status' => 'rejected',
            'is_final' => true,
            'updated_at' => now(),
        ]);

        // Return with success message
        return redirect()->route('project_manager.projects.quotes')->with('success', 'Quote rejected and negotiation closed.');
    }

    // Suggest action
    elseif ($action === 'suggest') {
        // Redirect to the suggest price method for handling price suggestions
        return $this->suggestPrice($request, $quote->project_id);
    }

    // Invalid action provided
    return redirect()->route('project_manager.projects.quotes')->with('error', 'Invalid action.');
}



    public function toggleFavorite(Request $request, $projectId)
    {
        $user = Auth::user();

        $favorite = DB::table('project_user_favorites')
            ->where('user_id', $user->id)
            ->where('project_id', $projectId)
            ->first();

        if ($favorite) {
            DB::table('project_user_favorites')
                ->where('user_id', $user->id)
                ->where('project_id', $projectId)
                ->delete();

            $isFavorite = false;
        } else {
            DB::table('project_user_favorites')->insert([
                'user_id' => $user->id,
                'project_id' => $projectId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $isFavorite = true;
        }

        return response()->json(['is_favorite' => $isFavorite]);
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('project_manager.profile', compact('user'));
    }

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
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    // Update Project Manager's Password
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
        $user->password = Hash::make($request->password);
        $user->save();

        // Redirect back with a success message
        return redirect()->back()->with('status', 'password-updated');
    }


public function managementBoard($projectId)
{
    // Fetch project details
    $project = DB::table('projects')->where('id', $projectId)->first();

    if (!$project) {
        return redirect()->route('project_manager.projects.index')->with('error', 'Project not found.');
    }

    // Convert the dates to Carbon instances
    $project->start_date = Carbon::parse($project->start_date);
    $project->end_date = Carbon::parse($project->end_date);

    // Check if the project has a main contractor
    $main_contractor = DB::table('users')
        ->join('project_contractor', 'users.id', '=', 'project_contractor.contractor_id')
        ->where('project_contractor.project_id', $projectId)
        ->where('project_contractor.main_contractor', true)
        ->select('users.name', 'users.email')
        ->first();

    // Fetch tasks related to the project
    $tasks = DB::table('tasks')->where('project_id', $projectId)->get();

    return view('project_manager.management_board', compact('project', 'tasks', 'main_contractor'));
}

}