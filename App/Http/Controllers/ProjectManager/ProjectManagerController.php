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

        // Check if the project has a main contractor in project_contractor table
        $mainContractorExists = DB::table('project_contractor')
            ->where('project_id', $project->id)
            ->where('main_contractor', true)
            ->exists();

        if (!$mainContractorExists) {
            return redirect()->route('project_manager.projects.index')->with('error', 'Management board is not available until a main contractor is selected.');
        }

        return view('project_manager.dashboard', compact('project'));
    }

    public function indexProjects()
    {
        $projects = DB::table('projects')
            ->where('project_manager_id', Auth::id())
            ->orderBy('name')
            ->get();

        foreach ($projects as $project) {
            $project->contractors_invited_count = DB::table('project_invitations')
                ->where('project_id', $project->id)
                ->count();

            // Calculate the number of members in the project
            $project->members_count = DB::table('project_user')
                ->where('project_id', $project->id)
                ->count();

            // Check if the project is marked as favorite by the current user
            $project->is_favorite = DB::table('project_user_favorites')
                ->where('project_id', $project->id)
                ->where('user_id', Auth::id())
                ->exists();

            // Fetch contractors and include quote_id from project_contractor
            $project->contractors = DB::table('project_invitations')
                ->join('users', 'project_invitations.contractor_id', '=', 'users.id')
                ->leftJoin('project_contractor', function ($join) use ($project) {
                    $join->on('project_invitations.project_id', '=', 'project_contractor.project_id')
                        ->on('project_invitations.contractor_id', '=', 'project_contractor.contractor_id');
                })
                ->where('project_invitations.project_id', $project->id)
                ->select(
                    'users.name',
                    'project_invitations.status',
                    'project_contractor.id as quote_id', // Include quote_id
                    'project_contractor.main_contractor', // Check if they are main contractor
                    'project_contractor.status as quote_status'
                )
                ->get();

            // Set the first contractor's quote as the first_quote property
            if ($project->contractors->isNotEmpty()) {
                $project->first_quote = $project->contractors->first();
            }

            // Fetch tasks for the project
            $project->tasks = DB::table('tasks')->where('project_id', $project->id)->get();

            // Add a flag to indicate whether the management board can be accessed
            $mainContractorExists = DB::table('project_contractor')
                ->where('project_id', $project->id)
                ->where('main_contractor', true)
                ->exists();

            $project->can_access_management = $mainContractorExists;
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

        // Fetch contractors related to the project and include their details
        $project->contractors = DB::table('project_invitations')
            ->join('users', 'project_invitations.contractor_id', '=', 'users.id')
            ->leftJoin('project_contractor', function ($join) use ($project) {
                $join->on('project_invitations.project_id', '=', 'project_contractor.project_id')
                    ->on('project_invitations.contractor_id', '=', 'project_contractor.contractor_id');
            })
            ->where('project_invitations.project_id', $projectId)
            ->select(
                'users.name',
                'users.email',  // Include email for display
                'project_invitations.status',
                'project_contractor.main_contractor', // Check if they are main contractor
                'project_contractor.status as quote_status'
            )
            ->get();

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

        $invitedContractors = DB::table('project_invitations')
            ->join('users', 'project_invitations.contractor_id', '=', 'users.id')
            ->where('project_invitations.project_id', $projectId)
            ->select('users.name', 'users.email', 'project_invitations.status')
            ->get();

        return view('project_manager.projects.invite', compact('project', 'invitedContractors'));
    }

    public function storeInvite(Request $request, $projectId)
    {
        $request->validate([
            'contractor_email' => 'required|email',
        ]);

        $contractor = DB::table('users')->where('email', $request->contractor_email)->first();

        if (!$contractor) {
            return redirect()->route('project_manager.projects.invite', $projectId)
                ->with('error', 'The email provided does not exist in the system.');
        }

        $contractorRoleId = DB::table('roles')->where('name', 'contractor')->value('id');

        if ($contractor->role_id != $contractorRoleId) {
            return redirect()->route('project_manager.projects.invite', $projectId)
                ->with('error', 'The provided email does not belong to a contractor.');
        }

        $existingInvitation = DB::table('project_invitations')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractor->id)
            ->exists();

        if ($existingInvitation) {
            return redirect()->route('project_manager.projects.invite', $projectId)
                ->with('error', 'This contractor has already been invited to the project.');
        }

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
                'project_contractor.status as quote_status' // Ensure quote_status is selected
            )
            ->get();

        return view('project_manager.projects.quotes', compact('quotes'));
    }

    public function approveQuote($projectId, $contractorId)
    {
        DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->update([
                'status' => 'approved',
                'main_contractor' => true, // Mark as main contractor
            ]);

        return redirect()->route('project_manager.projects.quotes', $projectId)->with('success', 'Quote approved and contractor promoted to main contractor.');
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

            $updateData = [
                'quoted_price' => $newPrice,
                'status' => 'suggested',
                'suggested_by' => 'project_manager',
                'updated_at' => now(),
            ];

            if ($request->hasFile('new_pdf')) {
                $filePath = $request->file('new_pdf')->store('quotes', 'public');
                $updateData['quote_pdf'] = $filePath;
                Log::info('File uploaded successfully', ['file_path' => $filePath]);
            }

            // Check if the negotiation has already been rejected
            $existingQuote = DB::table('project_contractor')
                ->where('id', $quoteId)
                ->where('contractor_id', $contractorId)
                ->first();

            if ($existingQuote && $existingQuote->status === 'rejected') {
                return redirect()->route('project_manager.projects.quotes', ['project' => $projectId])
                    ->with('error', 'This negotiation has already been rejected and cannot be continued.');
            }

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
                ->with('success', 'New price suggested successfully. Waiting for contractor response.');
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
        $quote = DB::table('project_contractor')->where('id', $quoteId)->first();

        if ($action === 'approve') {
            // Mark this quote as approved and close the negotiation
            DB::table('project_contractor')->where('id', $quoteId)->update([
                'status' => 'approved',
                'is_final' => true,
                'main_contractor' => true,
                'updated_at' => now(),
            ]);

            // Update the project to indicate it now has a main contractor
            DB::table('projects')->where('id', $quote->project_id)->update([
                'status' => 'started',
                'main_contractor_id' => $quote->contractor_id,
                'updated_at' => now(),
            ]);

            // Reject all other quotes for the project
            DB::table('project_contractor')
                ->where('project_id', $quote->project_id)
                ->where('id', '!=', $quoteId)
                ->update([
                    'status' => 'rejected',
                    'is_final' => true,
                    'updated_at' => now(),
                ]);

            return redirect()->route('project_manager.projects.quotes')->with('success', 'Quote approved and contractor assigned as main contractor. Invitations closed.');
        } elseif ($action === 'reject') {
            DB::table('project_contractor')->where('id', $quoteId)->update([
                'status' => 'rejected',
                'is_final' => true,
                'updated_at' => now(),
            ]);

            return redirect()->route('project_manager.projects.quotes')->with('success', 'Quote rejected and negotiation closed.');
        } elseif ($action === 'suggest') {
            // Suggest a new price
            return $this->suggestPrice($request, $quote->project_id);
        }

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

    public function managementBoard($projectId)
{
    $project = DB::table('projects')->where('id', $projectId)->first();

    if (!$project) {
        return redirect()->route('project_manager.projects.index')->with('error', 'Project not found.');
    }

    $mainContractorExists = DB::table('project_contractor')
        ->where('project_id', $project->id)
        ->where('main_contractor', true)
        ->exists();

    if (!$mainContractorExists) {
        return redirect()->route('project_manager.projects.index')->with('error', 'Management board is not available until a main contractor is assigned.');
    }

    $tasks = DB::table('tasks')->where('project_id', $projectId)->get();
    $contractors = DB::table('users')
        ->join('project_user', 'users.id', '=', 'project_user.user_id')
        ->where('project_user.project_id', $projectId)
        ->where('project_user.role', 'contractor')
        ->select('users.name', 'users.email', 'project_user.status')
        ->get();

    return view('project_manager.management_board', compact('project', 'tasks', 'contractors'));
}

}
