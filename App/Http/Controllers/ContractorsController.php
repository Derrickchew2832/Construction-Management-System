<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractorsController extends Controller
{
    public function dashboard()
    {
        return view('contractor.dashboard');
    }


    public function indexProjects(Request $request)
{
    // Fetch projects with members count (project manager, contractors, etc.)
    $projects = DB::table('projects')
        ->join('project_invitations', 'projects.id', '=', 'project_invitations.project_id')
        ->leftJoin('project_user', 'projects.id', '=', 'project_user.project_id') // Join project_user to count members
        ->where('project_invitations.contractor_id', Auth::id())
        ->whereIn('project_invitations.status', ['pending', 'submitted', 'approved', 'rejected', 'suggested'])
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
            'projects.is_favorite',
            'projects.main_contractor_id',
            'project_invitations.status as invitation_status',
            DB::raw('COUNT(DISTINCT project_user.user_id) as members_count') // Count members (project manager, contractors)
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
            'projects.main_contractor_id',
            'project_invitations.status'
        )
        ->orderBy('projects.is_favorite', 'desc') // Sort favorited projects on top
        ->get();

    // Fetch contractors for each project and determine ribbon status
    foreach ($projects as $project) {
        // Fetch the current contractor's quote status for the project
        $quoteStatus = DB::table('project_contractor')
            ->where('project_id', $project->id)
            ->where('contractor_id', Auth::id())
            ->value('status');

        // Define ribbon logic based on project status and contractor assignment
        if ($quoteStatus === null || $quoteStatus === 'pending') {
            // No quote submitted yet or quote still pending, show "Quote Required"
            $project->ribbon = 'Quote Required';
        } elseif ($quoteStatus === 'submitted') {
            // Quote has been submitted, but not yet approved, show "Quote Submitted"
            $project->ribbon = 'Quote Submitted';
        } elseif ($quoteStatus === 'approved') {
            // Quote is approved, project is now "In Progress"
            $project->ribbon = 'In Progress';
        } elseif ($quoteStatus === 'rejected') {
            // Quote was rejected
            $project->ribbon = 'Declined';
        } elseif ($project->status === 'completed') {
            // Project is completed
            $project->ribbon = 'Completed';
        } else {
            $project->ribbon = 'Quote Required';
        }

        // If the main contractor exists, count it
        $mainContractorExists = DB::table('project_contractor')
            ->where('project_id', $project->id)
            ->where('main_contractor', true)
            ->exists();

        if ($mainContractorExists) {
            $project->members_count += 2; // Count both Project Manager and Main Contractor
        }

        // Define access management
        $project->can_access_management = ($project->status === 'started' && $mainContractorExists);
    }

    return view('contractor.projects.index', compact('projects'));
}


    public function showProject($projectId)
    {
        // Fetch the project details
        $project = DB::table('projects')->where('id', $projectId)->first();

        if (!$project) {
            return redirect()->route('contractor.projects.index')->with('error', 'Project not found.');
        }

        // Fetch the contractor's invitation for the project
        $invitation = DB::table('project_invitations')
            ->where('project_id', $projectId)
            ->where('contractor_id', Auth::id())
            ->first();

        if (!$invitation) {
            return redirect()->route('contractor.projects.index')->with('error', 'You have not been invited to this project.');
        }

        // Fetch the contractor's quote for the project
        $quote = DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', Auth::id())
            ->first();

        return view('contractor.projects.show', compact('project', 'invitation', 'quote'));
    }


    public function submitQuote(Request $request, $projectId)
    {
        $data = $request->validate([
            'quoted_price' => 'required|numeric|min:0',
            'quote_pdf' => 'required|file|mimes:pdf|max:2048',
        ]);

        // Save the uploaded PDF
        $pdfPath = $request->file('quote_pdf')->store('quotes', 'public');

        // Update or insert the quote in the project_contractor table
        DB::table('project_contractor')->updateOrInsert(
            ['project_id' => $projectId, 'contractor_id' => Auth::id()],
            [
                'quoted_price' => $data['quoted_price'],
                'quote_pdf' => $pdfPath,
                'status' => 'submitted',
                'suggested_by' => 'contractor', // Mark as suggested by contractor
                'updated_at' => now()
            ]
        );

        // Update the invitation status in project_invitations
        DB::table('project_invitations')
            ->where('project_id', $projectId)
            ->where('contractor_id', Auth::id())
            ->update(['status' => 'submitted', 'updated_at' => now()]);

        return redirect()->route('contractor.projects.show', $projectId)->with('success', 'Quote submitted successfully!');
    }

    public function respondToSuggestion(Request $request, $projectId)
    {
        $action = $request->input('action');
        $quoteId = $request->input('quote_id');
        $contractorId = Auth::id();

        if ($action == 'accept') {
            DB::table('project_contractor')
                ->where('id', $quoteId)
                ->update([
                    'status' => 'approved',
                    'is_final' => true,  // Mark as final to stop the negotiation loop
                    'main_contractor' => true, // Mark this contractor as the main contractor
                    'updated_at' => now(),
                ]);

            // Update the project to indicate the main contractor and start the project
            DB::table('projects')
                ->where('id', $projectId)
                ->update([
                    'status' => 'started',
                    'updated_at' => now(),
                ]);

            return redirect()->route('contractor.projects.show', $projectId)
                ->with('success', 'You have accepted the quote. The project has started.');
        } elseif ($action == 'reject') {
            DB::table('project_contractor')
                ->where('id', $quoteId)
                ->update([
                    'status' => 'rejected',
                    'is_final' => true,  // Mark as final to end the negotiation
                    'updated_at' => now(),
                ]);

            return redirect()->route('contractor.projects.show', $projectId)
                ->with('success', 'You have rejected the quote. The negotiation has ended.');
        } elseif ($action == 'resubmit') {
            $data = $request->validate([
                'new_price' => 'required|numeric|min:0',
                'new_pdf' => 'required|file|mimes:pdf|max:2048',
            ]);

            // Save the uploaded PDF
            $pdfPath = $request->file('new_pdf')->store('quotes', 'public');

            DB::table('project_contractor')
                ->where('id', $quoteId)
                ->update([
                    'quoted_price' => $data['new_price'],
                    'quote_pdf' => $pdfPath,
                    'status' => 'submitted',
                    'suggested_by' => 'contractor',  // Mark as suggested by contractor
                    'updated_at' => now(),
                ]);

            return redirect()->route('contractor.projects.show', $projectId)
                ->with('success', 'Your new quote has been submitted for review.');
        }

        return redirect()->route('contractor.projects.show', $projectId)
            ->with('error', 'Invalid action. Please try again.');
    }

    public function toggleFavorite(Request $request, $projectId)
    {
        $isFavorite = $request->input('is_favorite', false);
        $userId = Auth::id();

        if ($isFavorite) {
            // Add favorite entry if not exists
            DB::table('project_user_favorites')->updateOrInsert(
                ['user_id' => $userId, 'project_id' => $projectId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        } else {
            // Remove favorite entry
            DB::table('project_user_favorites')
                ->where('user_id', $userId)
                ->where('project_id', $projectId)
                ->delete();
        }

        return response()->json(['is_favorite' => $isFavorite]);
    }

    public function manageProject($projectId)
    {
        // Fetch the project and manage logic
        $project = DB::table('projects')->where('id', $projectId)->first();
        return view('contractor.projects.manage', compact('project'));
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('contractor.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        DB::table('users')->where('id', Auth::id())->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'updated_at' => now(),
        ]);

        return redirect()->route('contractor.profile.edit')->with('success', 'Profile updated successfully');
    }

    public function changePassword()
    {
        return view('contractor.change_password');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'password' => [
                'required',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        DB::table('users')->where('id', Auth::id())->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'updated_at' => now(),
        ]);

        return redirect()->route('contractor.profile.edit')->with('success', 'Password updated successfully');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
