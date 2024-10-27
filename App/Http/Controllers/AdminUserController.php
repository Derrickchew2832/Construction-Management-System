<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Using DB facade
use Illuminate\Support\Facades\Hash; // Import the Hash facade
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;

class AdminUserController extends Controller
{
    public function index()
    {
        // Retrieve all projects from the database
        $projects = DB::table('projects')->get();
        return view('admin.projects', compact('projects'));
    }

    public function showProjects($id)
    {
        // Retrieve a specific project by ID with related documents
        $project = DB::table('projects')->where('id', $id)->first();
        $documents = DB::table('project_documents')->where('project_id', $id)->get();
        return view('admin.project_details', compact('project', 'documents'));
    }

    public function editProject($id)
    {
        // Return the project data as JSON for the edit modal
        $project = DB::table('projects')->where('id', $id)->first();
        return response()->json($project);
    }

    public function updateProject(Request $request, $id)
    {
        // Validate and update project details
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_budget' => 'required|numeric|min:0',
        ]);

        DB::table('projects')->where('id', $id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_budget' => $request->total_budget,
            'budget_remaining' => $request->budget_remaining,
            'location' => $request->location,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully');
    }

    public function deleteProject($id)
    {
        try {
            // Attempt to delete the project from the database
            DB::table('projects')->where('id', $id)->delete();
            
            // Return a success response
            return response()->json(['success' => 'Project deleted successfully'], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Log::error('Failed to delete project: '.$e->getMessage());
            
            // Return an error response with detailed error
            return response()->json(['error' => 'Failed to delete project: ' . $e->getMessage()], 500);
        }
    }
    


    public function approvePage()
    {
        // Retrieve all users with a pending status
        $users = DB::table('users')->where('status', 'pending')->get();
        return view('admin.approve', compact('users'));
    }

    public function approveUser($id)
    {
        // Update the user's status to approved
        DB::table('users')->where('id', $id)->update(['status' => 'approved']);
        return redirect()->route('admin.approvePage')->with('success', 'User approved successfully');
    }

    public function rejectUser($id)
    {
        // Update the user's status to rejected
        DB::table('users')->where('id', $id)->update(['status' => 'rejected']);
        return redirect()->route('admin.approvePage')->with('success', 'User rejected successfully');
    }

    public function editProfile()
    {
        return view('admin.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        DB::table('users')->where('id', $user->id)->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        DB::table('users')->where('id', Auth::id())->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.profile')->with('success', 'Password updated successfully');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
