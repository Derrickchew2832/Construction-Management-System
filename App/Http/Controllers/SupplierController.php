<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function dashboard()
    {
        return view('supplier.dashboard'); 
    }

    
    public function quotes()
    {
        return view('supplier.quotes.dashboard'); 
    }

    public function delivery()
    {
        return view('supplier.delivery');
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('supplier.profile', compact('user'));
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

        return redirect()->route('supplier.profile')->with('success', 'Profile updated successfully!');
    }
}
