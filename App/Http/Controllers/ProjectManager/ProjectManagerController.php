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
            ->get();
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
    
        // Debugging: Dump the project object to see its structure
        dd($project);
    
        return view('project_manager.projects.show', compact('project'));
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

        return redirect()->route('project_manager.projects.show', $projectId)->with('success', 'Project created successfully!');
    }

    public function inviteContractor($projectId)
    {
        $project = DB::table('projects')->where('id', $projectId)->first();
        $contractors = DB::table('users')->where('role_id', 'contractor')->get();
        return view('project_manager.projects.invite', compact('project', 'contractors'));
    }

    public function storeInvite(Request $request, $projectId)
    {
        $data = $request->validate([
            'contractor_id' => 'required|exists:users,id',
        ]);

        DB::table('project_contractor')->insert([
            'project_id' => $projectId,
            'contractor_id' => $data['contractor_id'],
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('project_manager.projects.show', $projectId);
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

        return redirect()->route('project_manager.projects.show', $projectId);
    }

    public function rejectQuote($projectId, $contractorId)
    {
        DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        return redirect()->route('project_manager.projects.show', $projectId);
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

