<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectManagerController extends Controller
{
    public function dashboard()
{
    $project = DB::table('projects')
                 ->where('project_manager_id', Auth::id())
                 ->first(); // Ensure you're retrieving the project.

    if (!$project) {
        // Handle the case where no project is found
        return redirect()->route('project_manager.projects.index')->with('error', 'No projects found.');
    }

    return view('project_manager.dashboard', compact('project'));
}



    public function indexProjects()
{
    $projects = DB::table('projects')
        ->where('project_manager_id', Auth::id())
        ->orderBy('name') // Default order by name
        ->get();

    foreach ($projects as $project) {
        // Count the number of contractors invited to the project
        $project->contractors_invited_count = DB::table('project_invitations')
            ->where('project_id', $project->id)
            ->count();

        // Count the number of members in the project
        $project->members_count = DB::table('project_user')
            ->where('project_id', $project->id)
            ->count();

        // Check if the project is favorited by the current user
        $project->is_favorite = DB::table('project_user_favorites')
            ->where('project_id', $project->id)
            ->where('user_id', Auth::id())
            ->exists();

        // Get the contractors invited to the project along with their quote details
        $project->contractors = DB::table('project_invitations')
            ->join('users', 'project_invitations.contractor_id', '=', 'users.id')
            ->leftJoin('project_contractor', function ($join) use ($project) {
                $join->on('project_invitations.project_id', '=', 'project_contractor.project_id')
                     ->on('project_invitations.contractor_id', '=', 'project_contractor.contractor_id');
            })
            ->where('project_invitations.project_id', $project->id)
            ->select(
                'users.id',
                'users.name',
                'project_invitations.status',
                'project_contractor.id as quote_id', // Fetch the quote ID if it exists
                'project_contractor.quoted_price',
                'project_contractor.quote_pdf'
            )
            ->get();

        // Add a property to each project for the first quote to be used in the view (as an example)
        if ($project->contractors->isNotEmpty()) {
            $project->first_quote = $project->contractors->first();
        }
    }

    return view('project_manager.projects.index', compact('projects'));
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
    
        $members = DB::table('project_user')
            ->join('users', 'project_user.user_id', '=', 'users.id')
            ->where('project_user.project_id', $projectId)
            ->select('users.name', 'project_user.role')
            ->get();

        // Check if the project is favorited by the current user
        $project->is_favorite = DB::table('project_user_favorites')
            ->where('project_id', $projectId)
            ->where('user_id', Auth::id())
            ->exists();
    
        return view('project_manager.projects.show', compact('project', 'members'));
    }

    public function storeProject(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'total_budget' => 'required|numeric',
            'budget_remaining' => 'required|numeric',
            'location' => 'required|string|max:255',
        ]);

        $projectId = DB::table('projects')->insertGetId([
            'project_manager_id' => Auth::id(),
            'name' => $data['name'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_budget' => $data['total_budget'],
            'budget_remaining' => $data['budget_remaining'],
            'location' => $data['location'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add the project manager to the project_user table
        DB::table('project_user')->insert([
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'role' => 'project_manager',
            'invited_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('project_manager.projects.show', $projectId)->with('success', 'Project created successfully!');
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
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'total_budget' => 'required|numeric',
            'budget_remaining' => 'required|numeric',
            'location' => 'required|string|max:255',
        ]);

        DB::table('projects')->where('id', $projectId)->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_budget' => $data['total_budget'],
            'budget_remaining' => $data['budget_remaining'],
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
    
        // Correct the table name and ensure columns exist
        $invitedContractors = DB::table('project_invitations')
            ->join('users', 'project_invitations.contractor_id', '=', 'users.id')
            ->where('project_invitations.project_id', $projectId)
            ->select('users.name', 'users.email', 'project_invitations.status')
            ->get();
    
        return view('project_manager.projects.invite', compact('project', 'invitedContractors'));
    }

    public function storeInvite(Request $request, $projectId)
    {
        // Validate the email format
        $request->validate([
            'contractor_email' => 'required|email',
        ]);
    
        // Check if the email exists in the users table
        $contractor = DB::table('users')->where('email', $request->contractor_email)->first();
    
        if (!$contractor) {
            // If the email doesn't exist in the system
            return redirect()->route('project_manager.projects.invite', $projectId)
                ->with('error', 'The email provided does not exist in the system.');
        }
    
        // Fetch the contractor role ID dynamically
        $contractorRoleId = DB::table('roles')->where('name', 'contractor')->value('id');
    
        // Check if the user has a contractor role
        if ($contractor->role_id != $contractorRoleId) {
            // If the user is not a contractor
            return redirect()->route('project_manager.projects.invite', $projectId)
                ->with('error', 'The provided email does not belong to a contractor.');
        }
    
        // Check if the contractor has already been invited to this project
        $existingInvitation = DB::table('project_invitations')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractor->id)
            ->exists();
    
        if ($existingInvitation) {
            // If the contractor has already been invited
            return redirect()->route('project_manager.projects.invite', $projectId)
                ->with('error', 'This contractor has already been invited to the project.');
        }
    
        // Insert the invitation into the database
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


    public function viewQuote($projectId, $quoteId)
    {
        // Fetch the quote details based on the project ID and quote ID
        $quote = DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('id', $quoteId)
            ->first();

        if (!$quote) {
            // Handle case where the quote doesn't exist
            return redirect()->route('project_manager.projects.index')->with('error', 'Quote not found.');
        }

        // Fetch the project and contractor details
        $project = DB::table('projects')->where('id', $projectId)->first();
        $contractor = DB::table('users')->where('id', $quote->contractor_id)->first();

        // Return a view with the quote details
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
                'projects.id as project_id'
            )
            ->get();
    
        return view('project_manager.projects.quotes', compact('quotes'));
    }
    





public function approveQuote($projectId, $contractorId)
{
    DB::table('project_contractor')
        ->where('project_id', $projectId)
        ->where('contractor_id', $contractorId)
        ->update(['status' => 'approved']);

    return redirect()->route('project_manager.projects.quotes', $projectId)->with('success', 'Quote approved successfully.');
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
    // Log the incoming request data
    Log::info('Suggest Price Request', [
        'project_id' => $projectId,
        'new_price' => $request->input('new_price'),
        'new_quote' => $request->input('new_quote'),
        'quote_id' => $request->input('quote_id'),
        'contractor_id' => $request->input('contractor_id'),
        'has_new_pdf' => $request->hasFile('new_pdf')
    ]);

    try {
        $newPrice = $request->input('new_price');
        $newQuote = $request->input('new_quote');
        $quoteId = $request->input('quote_id');
        $contractorId = $request->input('contractor_id');

        // Handling the file upload and saving the new quote price and document.
        if ($request->hasFile('new_pdf')) {
            $filePath = $request->file('new_pdf')->store('quotes', 'public');
            Log::info('File uploaded successfully', ['file_path' => $filePath]);

            $updateCount = DB::table('project_contractor')
                ->where('id', $quoteId)
                ->where('contractor_id', $contractorId)
                ->update([
                    'quoted_price' => $newPrice,
                    'quote_pdf' => $filePath,
                    'status' => 'suggested'
                ]);

            // Log if the update was successful
            Log::info('Database update', [
                'quote_id' => $quoteId,
                'contractor_id' => $contractorId,
                'updated_rows' => $updateCount
            ]);
        } else {
            Log::warning('No file was uploaded');
        }

        return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
            ->with('success', 'New price suggested successfully.');
    } catch (\Exception $e) {
        // Log the exception details
        Log::error('Error in suggestPrice', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Redirect back with an error message
        return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
            ->with('error', 'An error occurred while suggesting the new price. Please try again.');
    }
}






        

    public function toggleFavorite(Request $request, $projectId)
    {
        $user = Auth::user();
    
        // Check if the project is already favorited by the user
        $favorite = DB::table('project_user_favorites')
            ->where('user_id', $user->id)
            ->where('project_id', $projectId)
            ->first();
    
        if ($favorite) {
            // If it is favorited, remove it from the favorites
            DB::table('project_user_favorites')
                ->where('user_id', $user->id)
                ->where('project_id', $projectId)
                ->delete();
            $isFavorite = false;
        } else {
            // If it is not favorited, add it to the favorites
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
        $user = Auth::user();  // Get the authenticated user
        return view('project_manager.profile', compact('user'));  // Load the profile.blade.php view
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        DB::table('users')
            ->where('id', Auth::id())
            ->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'updated_at' => now(),
            ]);

        return redirect()->route('project_manager.profile')->with('success', 'Profile updated successfully!');
    }
}
