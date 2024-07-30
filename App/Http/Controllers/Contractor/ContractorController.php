<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\ProjectContractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;

class ContractorController extends Controller
{
    public function dashboard()
    {
        return view('contractor.dashboard');
    }

    public function quotes()
    {
        $quotes = ProjectContractor::where('contractor_id', Auth::id())->get();
        return view('contractor.quotes.index', compact('quotes'));
    }

    public function showQuote($id)
    {
        $quote = ProjectContractor::with('project')->findOrFail($id);
        return view('contractor.quotes.show', compact('quote'));
    }

    public function updateQuote(Request $request, $id)
    {
        $quote = ProjectContractor::findOrFail($id);
        $request->validate([
            'quoted_price' => 'required|numeric|min:0',
        ]);

        $quote->update([
            'quoted_price' => $request->quoted_price,
        ]);

        return redirect()->route('contractor.quotes.index')->with('success', 'Quote updated successfully');
    }

    public function editProfile()
    {
        if (Auth::check() && Auth::user()->role === 'contractor') {
            return view('contractor.profile', ['user' => Auth::user()]);
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function updateProfile(Request $request)
    {
        if (Auth::check() && Auth::user()->role === 'contractor') {
            $user = Auth::user();
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ]);

            DB::table('users')->where('id', $user->id)->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->route('contractor.profile.edit')->with('success', 'Profile updated successfully');
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }

    public function changePassword()
    {
        return view('contractor.change_password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'contractor') {
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

            return redirect()->route('contractor.profile.edit')->with('success', 'Password updated successfully');
        }

        return redirect()->route('contractor.profile.edit')->withErrors(['password' => 'Password validation failed. Please re-enter a valid password.'], 'updatePassword');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
