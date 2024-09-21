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
        return view('client.projects.dashboard');
    }

    public function invitations()
    {
        return view('client.invitations');
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
}
