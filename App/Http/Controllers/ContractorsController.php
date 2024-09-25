<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ContractorsController extends Controller
{
    public function dashboard()
    {
        return view('contractor.dashboard');
    }

    public function indexProjects(Request $request)
{
    $contractorId = Auth::id(); // Get the logged-in contractor's ID

    // Fetch projects with invitations related to the contractor
    $projects = DB::table('projects')
        ->join('project_invitations', 'projects.id', '=', 'project_invitations.project_id')
        ->leftJoin('project_user', 'projects.id', '=', 'project_user.project_id') // Join to count members
        ->where('project_invitations.contractor_id', $contractorId)
        ->where('project_invitations.status', 'submitted') // Only show submitted projects
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
            DB::raw('COUNT(DISTINCT project_user.user_id) as members_count'), // Count members (project manager, contractors)
            DB::raw("IF(projects.main_contractor_id = {$contractorId}, true, false) as can_access_management") // Check if contractor is the main contractor
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
        ->orderBy('projects.is_favorite', 'desc') // Sort favorited projects on top
        ->get();

    // Loop through projects to assign the ribbon property based on project status
    foreach ($projects as $project) {
        if ($project->status === 'completed') {
            $project->ribbon = 'Completed';
        } elseif ($project->status === 'started') {
            $project->ribbon = 'In Progress';
        } else {
            $project->ribbon = 'Pending'; // Default ribbon if no specific status
        }

        // Check if the project has a main contractor and add a ribbon
        $mainContractorExists = DB::table('project_contractor')
            ->where('project_id', $project->id)
            ->where('main_contractor', true)
            ->exists();

        // Optionally, you can set another ribbon based on the presence of a main contractor
        if ($mainContractorExists) {
            $project->ribbon = 'Has Main Contractor';
        }
    }

    return view('contractor.projects.index', compact('projects'));
}

    

    public function showQuotes()
{
    $contractorId = Auth::id();

    // Fetch submitted quotes for the contractor
    $quotes = DB::table('project_contractor')
        ->join('projects', 'project_contractor.project_id', '=', 'projects.id')
        ->where('project_contractor.contractor_id', $contractorId)
        ->select('project_contractor.*', 'projects.name as project_name')
        ->get();

    // Fetch pending invitations where contractor hasn't submitted a quote yet
    $pendingInvitations = DB::table('project_invitations')
        ->join('projects', 'project_invitations.project_id', '=', 'projects.id')
        ->leftJoin('project_documents', 'projects.id', '=', 'project_documents.project_id')
        ->where('project_invitations.contractor_id', $contractorId)
        ->whereNotIn('project_invitations.project_id', function($query) use ($contractorId) {
            $query->select('project_id')
                ->from('project_contractor')
                ->where('contractor_id', $contractorId);
        })
        ->select(
            'projects.id', 
            'projects.name', 
            'projects.description', 
            'projects.start_date', 
            'projects.end_date', 
            'projects.location', 
            'project_invitations.status as invitation_status',
            DB::raw('GROUP_CONCAT(project_documents.original_name, "::", project_documents.document_path) as documents')
        )
        ->groupBy('projects.id', 'projects.name', 'projects.description', 'projects.start_date', 'projects.end_date', 'projects.location', 'project_invitations.status')
        ->get();

    // Format documents for each invitation
    foreach ($pendingInvitations as $invitation) {
        $invitation->documents = collect(explode(',', $invitation->documents))->map(function($doc) {
            $parts = explode('::', $doc);
            if (count($parts) === 2) {
                return ['original_name' => $parts[0], 'document_path' => $parts[1]];
            }
            return null;  // Return null for invalid entries
        })->filter()->all();  // Filter out null values
    }

    return view('contractor.projects.quotes', compact('quotes', 'pendingInvitations'));
}




    public function submitQuote(Request $request, $projectId)
    {
        // Validate the quote data
        $data = $request->validate([
            'quoted_price' => 'required|numeric|min:0',
            'quote_pdf' => 'required|file|mimes:pdf|max:2048',
        ]);

        // Save the uploaded PDF file
        $pdfPath = $request->file('quote_pdf')->store('quotes', 'public');

        // Insert or update the contractor's quote in project_contractor table
        DB::table('project_contractor')->updateOrInsert(
            ['project_id' => $projectId, 'contractor_id' => Auth::id()],
            [
                'quoted_price' => $data['quoted_price'],
                'quote_pdf' => $pdfPath,
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
        $contractorId = DB::table('project_contractor')->where('id', $quoteId)->value('contractor_id');

        if ($action == 'accept') {
            // Accept the suggestion and set contractor as the main contractor
            DB::table('project_invitations')
                ->where('project_id', $projectId)
                ->where('contractor_id', $contractorId)
                ->update([
                    'status' => 'submitted', // Since you mentioned there's no 'accepted', we use 'submitted'
                    'updated_at' => now(),
                ]);

            // Update project status to 'started' and assign the main contractor
            DB::table('projects')
                ->where('id', $projectId)
                ->update([
                    'status' => 'started',
                    'main_contractor_id' => $contractorId,
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true, 'message' => 'You have accepted the quote.']);
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
            ]);

            // Save the new PDF file
            $pdfPath = $request->file('new_pdf')->store('quotes', 'public');

            DB::table('project_contractor')
                ->where('id', $quoteId)
                ->update([
                    'quoted_price' => $data['new_price'],
                    'quote_pdf' => $pdfPath,
                    'status' => 'submitted',
                    'suggested_by' => 'contractor',
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
            'password' => Hash::make($validated['password']),
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
