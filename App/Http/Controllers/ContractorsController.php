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

    public function indexProjects()
    {
        $projects = DB::table('projects')
            ->join('project_invitations', 'projects.id', '=', 'project_invitations.project_id')
            ->where('project_invitations.contractor_id', Auth::id())
            ->whereIn('project_invitations.status', ['pending', 'submitted', 'approved', 'rejected', 'suggested'])
            ->select('projects.*', 'project_invitations.status as invitation_status')
            ->get();

        return view('contractor.projects.index', compact('projects'));
    }

    public function showProject($projectId)
    {
        $project = DB::table('projects')->where('id', $projectId)->first();
    
        if (!$project) {
            return redirect()->route('contractor.projects.index')->with('error', 'Project not found.');
        }
    
        $invitation = DB::table('project_invitations')
            ->where('project_id', $projectId)
            ->where('contractor_id', Auth::id())
            ->first();
    
        if (!$invitation) {
            return redirect()->route('contractor.projects.index')->with('error', 'You have not been invited to this project.');
        }
    
        $quote = DB::table('project_contractor')
            ->where('project_id', $projectId)
            ->where('contractor_id', Auth::id())
            ->first();
    
        return view('contractor.projects.show', compact('project', 'invitation', 'quote'));
    }
    
    public function respondToSuggestion(Request $request, $projectId)
    {
        $action = $request->input('action');
        $quoteId = $request->input('quote_id');
        $contractorId = Auth::id();
    
        if ($action == 'accept') {
            DB::table('project_contractor')
                ->where('id', $quoteId)
                ->update(['status' => 'approved']);
        } elseif ($action == 'reject') {
            DB::table('project_contractor')
                ->where('id', $quoteId)
                ->update(['status' => 'rejected']);
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
                    'updated_at' => now(),
                ]);
        }
    
        return redirect()->route('contractor.projects.show', $projectId)->with('success', 'Your response has been submitted.');
    }
    


    public function submitQuote(Request $request, $projectId)
    {
        $data = $request->validate([
            'quoted_price' => 'required|numeric|min:0',
            'quote_pdf' => 'required|file|mimes:pdf|max:2048',
        ]);

        // Save the uploaded PDF
        $pdfPath = $request->file('quote_pdf')->store('quotes', 'public');

        // Update or insert the quote
        DB::table('project_contractor')->updateOrInsert(
            ['project_id' => $projectId, 'contractor_id' => Auth::id()],
            ['quoted_price' => $data['quoted_price'], 'quote_pdf' => $pdfPath, 'status' => 'submitted', 'updated_at' => now()]
        );

        // Update the invitation status
        DB::table('project_invitations')
            ->where('project_id', $projectId)
            ->where('contractor_id', Auth::id())
            ->update(['status' => 'submitted', 'updated_at' => now()]);

        return redirect()->route('contractor.projects.show', $projectId)->with('success', 'Quote submitted successfully!');
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
