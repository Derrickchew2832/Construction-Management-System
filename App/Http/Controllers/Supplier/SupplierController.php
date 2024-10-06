<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    // Dashboard view
    public function dashboard()
    {
        return view('supplier.dashboard'); 
    }

    // Quotes dashboard view
    public function quotes()
    {
        return view('supplier.quotes.dashboard'); 
    }

    // Delivery view
    public function delivery()
    {
        return view('supplier.delivery');
    }

    // Show the edit profile form
    public function editProfile()
    {
        $user = Auth::user();
        return view('supplier.profile', compact('user'));
    }
    
    // Update the profile
    public function updateProfile(Request $request)
    {
        // Validate the request data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        // Update the user profile in the database
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
