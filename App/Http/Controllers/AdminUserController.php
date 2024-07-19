<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function index()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return view('admin.dashboard');
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function approvePage()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            $users = User::where('status', 'pending')->get();
            return view('admin.approve', compact('users'));
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function approveUser($id)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            $user = User::find($id);
            if ($user && $user->role !== 'admin') {
                DB::table('users')->where('id', $id)->update(['status' => 'approved']);
            }
            return redirect()->route('admin.approvePage')->with('success', 'User approved successfully');
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function rejectUser($id)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            $user = User::find($id);
            if ($user && $user->role !== 'admin') {
                DB::table('users')->where('id', $id)->update(['status' => 'rejected']);
            }
            return redirect()->route('admin.approvePage')->with('success', 'User rejected successfully');
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function editProfile()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return view('admin.profile', ['user' => Auth::user()]);
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function updateProfile(Request $request)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
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

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
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

        return redirect()->route('admin.profile')->withErrors(['password' => 'Password validation failed. Please re-enter a valid password.'], 'updatePassword');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
