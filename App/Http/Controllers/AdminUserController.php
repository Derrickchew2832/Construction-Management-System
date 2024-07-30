<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Import DB facade
use Illuminate\Support\Facades\Hash; // Import the Hash facade
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function approvePage()
    {
        $users = User::where('status', 'pending')->get();
        return view('admin.approve', compact('users'));
    }

    public function approveUser($id)
    {
        $user = User::find($id);
        if ($user) {
            DB::table('users')->where('id', $id)->update(['status' => 'approved']);
        }
        return redirect()->route('admin.approvePage')->with('success', 'User approved successfully');
    }

    public function rejectUser($id)
    {
        $user = User::find($id);
        if ($user) {
            DB::table('users')->where('id', $id)->update(['status' => 'rejected']);
        }
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

        $user = Auth::user();
        DB::table('users')->where('id', $user->id)->update([
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
