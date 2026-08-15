<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * CleanerProfileController
 *
 * Allows cleaning staff to update their own profile information,
 * including their phone number, email, and password.
 *
 * Route: GET   /cleaner/profile
 * Route: PATCH /cleaner/profile
 * Route: PATCH /cleaner/profile/password
 */
class CleanerProfileController extends Controller
{
    public function edit(): View
    {
        return view('cleaner.profile', [
            'staff' => Auth::guard('staff')->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $staff = Auth::guard('staff')->user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:50'],
            'username'  => ['required', 'string', 'max:50', 'unique:staff,username,' . $staff->id],
            'phone'     => ['nullable', 'string', 'max:30'],
            'email'     => ['nullable', 'email', 'max:100'],
        ]);

        $staff->fill($validated);
        $staff->save();

        return redirect()->route('cleaner.profile.edit')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password:staff'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $staff = Auth::guard('staff')->user();
        $staff->update(['passwordhash' => $validated['password']]);

        return back()->with('success', 'Password updated successfully.');
    }
}
