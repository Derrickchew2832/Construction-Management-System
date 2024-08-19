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
        return view('project_manager.dashboard');
    }

    public function indexProjects()
{
    $projects = DB::table('projects')
        ->where('project_manager_id', Auth::id())
        ->orderBy('name') // Default order by name
        ->get();

    foreach ($projects as $project) {
        // Assuming you have a 'project_invitations' table that tracks contractor invitations
        $project->contractors_invited_count = DB::table('project_invitations')
            ->where('project_id', $project->id)
            ->count();

        // Assuming you have a project_user table to count the members
        $project->members_count = DB::table('project_user')
            ->where('project_id', $project->id)
            ->count();

        // Check if the project is favorited by the current user
        $project->is_favorite = DB::table('project_user_favorites')
            ->where('project_id', $project->id)
            ->where('user_id', Auth::id())
            ->exists();
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
    

    

    public function manageQuotes($projectId)
    {
        $quotes = DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->join('users', 'project_contractor.contractor_id', '=', 'users.id')
            ->select('project_contractor.*', 'users.name as contractor_name')
            ->get();

        $project = DB::table('projects')->where('id', $projectId)->first();

        return view('project_manager.projects.quotes', compact('project', 'quotes'));
    }

    public function approveQuote($projectId, $contractorId)
    {
        DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->update(['status' => 'approved', 'updated_at' => now()]);

        DB::table('projects')
            ->where('id', $projectId)
            ->update(['main_contractor_id' => $contractorId, 'updated_at' => now()]);

        // Add the contractor to the project_user table as the main contractor
        DB::table('project_user')->insert([
            'project_id' => $projectId,
            'user_id' => $contractorId,
            'role' => 'main_contractor',
            'invited_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('project_manager.projects.show', $projectId)->with('success', 'Quote approved successfully!');
    }

    public function rejectQuote($projectId, $contractorId)
    {
        DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        return redirect()->route('project_manager.projects.show', $projectId);
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
