<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectContractor;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Notifications\ProjectInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;

class ProjectManagerController extends Controller
{
    public function dashboard()
    {
        return view('projectmanager.dashboard');
    }

    public function index()
    {
        $projects = Project::all();
        return view('projectmanager.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projectmanager.projects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_budget' => 'required|numeric|min:0',
            'budget_remaining' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_budget' => $request->total_budget,
            'budget_remaining' => $request->budget_remaining,
            'location' => $request->location,
        ]);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $path = $document->store('project_documents');
                ProjectDocument::create([
                    'project_id' => $project->id,
                    'document_path' => $path,
                ]);
            }
        }

        return redirect()->route('projectmanager.projects.index')->with('success', 'Project created successfully');
    }

    public function show($id)
    {
        $project = Project::with(['documents', 'contractors'])->findOrFail($id);
        return view('projectmanager.projects.show', compact('project'));
    }

    public function invite($id)
    {
        $project = Project::findOrFail($id);
        return view('projectmanager.projects.invite', compact('project'));
    }

    public function sendInvitation(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $request->validate([
            'email' => 'required|email',
        ]);

        $token = Str::random(32);
        ProjectInvitation::create([
            'project_id' => $project->id,
            'email' => $request->email,
            'token' => $token,
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->notify(new ProjectInvitationNotification($project, $token));
        }

        return redirect()->route('projectmanager.projects.show', $project->id)->with('success', 'Invitation sent successfully');
    }

    public function quote(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $request->validate([
            'contractor_id' => 'required|exists:users,id',
            'quoted_price' => 'required|numeric|min:0',
        ]);

        ProjectContractor::create([
            'project_id' => $project->id,
            'contractor_id' => $request->contractor_id,
            'quoted_price' => $request->quoted_price,
        ]);

        return redirect()->route('projectmanager.projects.show', $project->id)->with('success', 'Quote submitted successfully');
    }

    public function appointMainContractor(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $request->validate([
            'contractor_id' => 'required|exists:users,id',
        ]);

        $project->main_contractor_id = $request->contractor_id;
        $project->save();

        return redirect()->route('projectmanager.projects.show', $project->id)->with('success', 'Main contractor appointed successfully');
    }

    public function editProfile()
    {
        if (Auth::check() && Auth::user()->role === 'projectmanager') {
            return view('projectmanager.profile', ['user' => Auth::user()]);
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function updateProfile(Request $request)
    {
        if (Auth::check() && Auth::user()->role === 'projectmanager') {
            $user = Auth::user();
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ]);

            DB::table('users')->where('id', $user->id)->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->route('projectmanager.profile.edit')->with('success', 'Profile updated successfully');
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function changePassword()
    {
        return view('projectmanager.change_password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'projectmanager') {
            $validated = $request->validateWithBag('updatePassword', [
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
                ],
            ]);

            $user = Auth::user();
            DB::table('users')->where('id', $user->id)->update([
                'password' => Hash::make($validated['password']),
            ]);

            return redirect()->route('projectmanager.profile.edit')->with('success', 'Password updated successfully');
        }

        return redirect()->route('projectmanager.profile.edit')->withErrors(['password' => 'Password validation failed. Please re-enter a valid password.'], 'updatePassword');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
