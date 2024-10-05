<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function dashboard()
    {
        return view('client.dashboard');
    }

    public function projects()
    {
        // Get the currently authenticated client
        $clientId = auth()->user()->id;

        // Fetch all projects that the client has access to based on accepted invitations
        $projects = DB::table('projects')
            ->join('project_invitations_client', 'projects.id', '=', 'project_invitations_client.project_id')
            ->where('project_invitations_client.client_id', $clientId)
            ->where('project_invitations_client.status', 'accepted')  // Only show accepted invitations
            ->select('projects.*', 'project_invitations_client.status as invitation_status') // Invitation status
            ->get();

        // For each project, set the ribbon value based on the project status
        foreach ($projects as $project) {
            if ($project->status === 'Completed') {
                $project->ribbon = 'Completed'; // Green ribbon for completed projects
            } else {
                $project->ribbon = 'In Progress'; // Default to In Progress for all other statuses
            }

            // Check if the project is favorited by the client
            $project->is_favorite = DB::table('project_user_favorites')
                ->where('user_id', $clientId)
                ->where('project_id', $project->id)
                ->exists();
        }

        // Pass the projects to the view
        return view('client.projects.dashboard', compact('projects'));
    }

    public function invitations()
    {
        // Get the currently authenticated client
        $clientId = auth()->user()->id;
    
        // Fetch all invitations related to this client
        $invitations = DB::table('project_invitations_client')
            ->where('client_id', $clientId)
            ->get();
    
        // Pass invitations data to the view
        return view('client.invitations', compact('invitations'));
    }

    // Method to update the status of a client's invitation
    public function updateInvitationStatus(Request $request, $invitationId)
    {
        // Validate the status input
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        // Update the status of the invitation
        DB::table('project_invitations_client')
            ->where('id', $invitationId)
            ->update([
                'status' => $request->input('status'),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Invitation status updated successfully!');
    }

    // Edit profile function for clients
    public function editProfile()
    {
        $user = Auth::user();
        return view('client.profile', compact('user'));
    }

    // Update profile function for clients
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

        return redirect()->route('client.profile')->with('success', 'Profile updated successfully!');
    }

    // ClientController.php
public function updateFavoriteStatus(Request $request, $projectId)
{
    try {
        // Get the currently authenticated client
        $clientId = auth()->user()->id;

        // Check if the project is already in the favorites list
        $existingFavorite = DB::table('project_user_favorites')
            ->where('user_id', $clientId)
            ->where('project_id', $projectId)
            ->first();

        if ($existingFavorite) {
            // If it exists, remove it from favorites (unfavorite)
            DB::table('project_user_favorites')
                ->where('user_id', $clientId)
                ->where('project_id', $projectId)
                ->delete();

            return response()->json(['is_favorite' => false, 'message' => 'Project removed from favorites!']);
        } else {
            // If it doesn't exist, add it to favorites
            DB::table('project_user_favorites')->insert([
                'user_id' => $clientId,
                'project_id' => $projectId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['is_favorite' => true, 'message' => 'Project added to favorites!']);
        }
    } catch (\Exception $e) {
        // Catch any error and return a JSON response
        return response()->json(['error' => 'Unable to update favorite status. Please try again later.'], 500);
    }
}

}
