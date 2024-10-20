<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $quotes = DB::table('project_contractor')
            ->join('projects', 'project_contractor.project_id', '=', 'projects.id')
            ->join('users', 'project_contractor.contractor_id', '=', 'users.id')
            ->select(
                'project_contractor.*', 
                'users.name as contractor_name', 
                'projects.name as project_name', 
                'projects.id as project_id',
                'project_contractor.status as quote_status',
                'project_contractor.quote_suggestion'
            )
            ->get();

        return view('project_manager.projects.quotes', compact('quotes'));
    }

    public function approveQuote($projectId, $contractorId)
{
    Log::info('approveQuote method called', ['projectId' => $projectId, 'contractorId' => $contractorId]);

    // Fetch the quoted price for the contractor's quote
    $quote = DB::table('project_contractor')
        ->where('project_id', $projectId)
        ->where('contractor_id', $contractorId)
        ->first();

    if (!$quote || !$quote->quoted_price) {
        Log::error('Quote not found or quoted price missing', ['projectId' => $projectId, 'contractorId' => $contractorId]);
        return redirect()->route('project_manager.projects.quotes', $projectId)
            ->with('error', 'Quote not found or quoted price is missing.');
    }

    Log::info('Quote found', ['quoted_price' => $quote->quoted_price]);

    // Fetch the project
    $project = DB::table('projects')->where('id', $projectId)->first();

    if (!$project) {
        Log::error('Project not found', ['projectId' => $projectId]);
        return redirect()->route('project_manager.projects.index')->with('error', 'Project not found.');
    }

    Log::info('Project details', ['total_budget' => $project->total_budget, 'budget_remaining' => $project->budget_remaining]);

    // Calculate the new remaining budget: total_budget - quoted_price
    $newRemainingBalance = $project->budget_remaining - $quote->quoted_price;

    Log::info('Calculated new remaining balance', ['newRemainingBalance' => $newRemainingBalance]);

    // Ensure the remaining budget does not go negative
    if ($newRemainingBalance < 0) {
        Log::error('Quoted price exceeds the total budget', ['newRemainingBalance' => $newRemainingBalance]);
        return redirect()->route('project_manager.management_board', $projectId)
            ->with('error', 'Quoted price exceeds the total budget.');
    }

    // Update the project's remaining budget in the database
    DB::table('projects')
        ->where('id', $projectId)
        ->update([
            'budget_remaining' => $newRemainingBalance,
            'status' => 'started',
            'updated_at' => now(),
        ]);

    Log::info('Project budget updated', ['budget_remaining' => $newRemainingBalance]);

    // Approve the contractor as the main contractor
    DB::table('project_contractor')
        ->where('project_id', $projectId)
        ->where('contractor_id', $contractorId)
        ->update([
            'status' => 'approved',
            'main_contractor' => true,
            'updated_at' => now(),
        ]);

    Log::info('Contractor approved as main contractor', ['contractorId' => $contractorId]);

    return redirect()->route('project_manager.management_board', $projectId)
        ->with('success', 'Quote approved, contractor promoted to main contractor, and budget updated.');
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
    Log::info('Suggest Price Request', [
        'project_id' => $projectId,
        'new_price' => $request->input('new_price'),
        'new_quote' => $request->input('new_quote'),
        'quote_id' => $request->input('quote_id'),
        'contractor_id' => $request->input('contractor_id'),
        'has_new_pdf' => $request->hasFile('new_pdf')
    ]);

    if ($request->input('contractor_id') === null) {
        Log::error('contractor_id is null, cannot proceed with the update');
        return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
            ->with('error', 'Contractor ID is missing. Please try again.');
    }

    try {
        $newPrice = $request->input('new_price');
        $newQuote = $request->input('new_quote');
        $quoteId = $request->input('quote_id');
        $contractorId = $request->input('contractor_id');

        // Prepare the update data
        $updateData = [
            'quoted_price' => $newPrice,
            'quote_suggestion' => $newQuote, // Update the suggestion
            'status' => 'suggested',
            'suggested_by' => 'project_manager',
            'updated_at' => now(),
        ];

        // Handle the uploaded PDF file if it exists
        if ($request->hasFile('new_pdf')) {
            $filePath = $request->file('new_pdf')->store('quotes', 'public');
            $updateData['quote_pdf'] = $filePath; // Update the quote PDF path
            Log::info('File uploaded successfully', ['file_path' => $filePath]);
        }

        // Update the existing quote in the database
        $updateCount = DB::table('project_contractor')
            ->where('id', $quoteId)
            ->where('contractor_id', $contractorId)
            ->update($updateData);

        Log::info('Database update', [
            'quote_id' => $quoteId,
            'contractor_id' => $contractorId,
            'updated_rows' => $updateCount
        ]);

        if ($updateCount === 0) {
            Log::warning('No rows were updated, check if the query matched any records.');
        }

        return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
            ->with('success', 'New price and suggestion updated successfully. Waiting for contractor response.');
    } catch (\Exception $e) {
        Log::error('Error in suggestPrice', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
            ->with('error', 'An error occurred while suggesting the new price. Please try again.');
    }
}


    public function handleQuoteAction(Request $request)
{
    $action = $request->input('action');
    $quoteId = $request->input('quote_id');

    Log::info('handleQuoteAction called', [
        'action' => $action,
        'quote_id' => $quoteId
    ]);

    // Fetch the quote details
    $quote = DB::table('project_contractor')->where('id', $quoteId)->first();

    if (!$quote) {
        Log::error('Quote not found', ['quote_id' => $quoteId]);
        return redirect()->route('project_manager.projects.quotes')->with('error', 'Quote not found.');
    }

    Log::info('Quote found', ['project_id' => $quote->project_id, 'contractor_id' => $quote->contractor_id]);

    // Approve action
    if ($action === 'approve') {
        Log::info('Approving quote', ['quote_id' => $quoteId]);

        DB::table('project_contractor')->where('id', $quoteId)->update([
            'status' => 'approved',
            'is_final' => true,
            'main_contractor' => true,
            'updated_at' => now(),
        ]);

        Log::info('Quote status updated to approved', ['quote_id' => $quoteId]);

        // Fetch the project to update the budget remaining
        $project = DB::table('projects')->where('id', $quote->project_id)->first();

        if (!$project) {
            Log::error('Project not found', ['project_id' => $quote->project_id]);
            return redirect()->route('project_manager.projects.quotes')->with('error', 'Project not found.');
        }

        Log::info('Project found', ['total_budget' => $project->total_budget, 'budget_remaining' => $project->budget_remaining]);

        // Calculate new remaining budget: total budget minus quoted price
        $newRemainingBalance = $project->budget_remaining - $quote->quoted_price;

        Log::info('Calculated new remaining balance', [
            'total_budget' => $project->total_budget,
            'quoted_price' => $quote->quoted_price,
            'budget_remaining_before' => $project->budget_remaining,
            'budget_remaining_after' => $newRemainingBalance
        ]);

        // Ensure the remaining budget does not go negative
        if ($newRemainingBalance < 0) {
            Log::error('Quoted price exceeds the remaining budget', ['new_remaining_balance' => $newRemainingBalance]);
            return redirect()->route('project_manager.management_board', $quote->project_id)
                ->with('error', 'Quoted price exceeds the remaining budget.');
        }

        // Update project's remaining budget in the database
        $updated = DB::table('projects')->where('id', $quote->project_id)->update([
            'budget_remaining' => $newRemainingBalance,
            'status' => 'started',
            'main_contractor_id' => $quote->contractor_id,
            'updated_at' => now(),
        ]);

        // Log the database update success or failure
        if ($updated) {
            Log::info('Project budget remaining successfully updated', ['project_id' => $quote->project_id, 'new_remaining_balance' => $newRemainingBalance]);
        } else {
            Log::error('Failed to update project budget remaining', ['project_id' => $quote->project_id]);
        }

        // Reject all other quotes for the project
        DB::table('project_contractor')
            ->where('project_id', $quote->project_id)
            ->where('id', '!=', $quoteId)
            ->update([
                'status' => 'rejected',
                'is_final' => true,
                'updated_at' => now(),
            ]);

        Log::info('Other quotes rejected for project', ['project_id' => $quote->project_id]);

        return redirect()->route('project_manager.projects.quotes')->with('success', 'Quote approved and contractor assigned as main contractor. Invitations closed.');
    }

    // Reject action
    elseif ($action === 'reject') {
        Log::info('Rejecting quote', ['quote_id' => $quoteId]);

        DB::table('project_contractor')->where('id', $quoteId)->update([
            'status' => 'rejected',
            'is_final' => true,
            'updated_at' => now(),
        ]);

        Log::info('Quote status updated to rejected', ['quote_id' => $quoteId]);

        return redirect()->route('project_manager.projects.quotes')->with('success', 'Quote rejected and negotiation closed.');
    }

    // Suggest action
    elseif ($action === 'suggest') {
        Log::info('Suggesting new price for quote', ['quote_id' => $quoteId]);
        return $this->suggestPrice($request, $quote->project_id);
    }

    // Invalid action
    Log::error('Invalid action provided', ['action' => $action]);
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