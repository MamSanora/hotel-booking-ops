<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Guest ProfileController
 *
 * Manages a guest's profile data and login credentials.
 *
 * Profile data (full_name, gender, nationality, phone) lives in the guests
 * and phones tables. Auth credentials (email, passwordhash) are in guest_auths.
 * We update each through their own relationship.
 *
 * Routes:
 *   GET   /guest/profile          → edit()
 *   PATCH /guest/profile          → update()
 *   PATCH /guest/profile/password → updatePassword()
 */
class ProfileController extends Controller
{
    /**
     * Show the guest's profile edit page.
     * Loads the GuestAuth and its related Guest profile.
     */
    public function edit(): View
    {
        $guestAuth = Auth::user();
        $guestAuth->load(['guest.phones']);
        $guest = $guestAuth->guest;

        return view('guest.profile', compact('guestAuth', 'guest'));
    }

    /**
     * Update the guest's profile and email.
     *
     * The GuestAuth table owns 'email'.
     * The Guest table owns 'full_name', 'gender', 'nationality'.
     * The Phone table stores phone_number records.
     */
    public function update(Request $request): RedirectResponse
    {
        $guestAuth = Auth::user();
        $guest     = $guestAuth->guest;

        $validated = $request->validate([
            'full_name'    => ['required', 'string', 'max:50'],
            'gender'       => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'nationality'  => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'email'        => [
                'required', 'string', 'email', 'max:255',
                'unique:guest_auths,email,' . $guestAuth->id,
            ],
        ]);

        // Update auth credentials.
        $guestAuth->update(['email' => $validated['email']]);

        // Update profile data.
        $guest->update([
            'full_name'   => $validated['full_name'],
            'gender'      => $validated['gender'] ?? $guest->gender,
            'nationality' => $validated['nationality'] ?? $guest->nationality,
        ]);

        // Replace the primary phone number (create or update the first record).
        if (! empty($validated['phone_number'])) {
            $guest->phones()->updateOrCreate(
                ['id' => $guest->phones()->first()?->id],
                ['phone_number' => $validated['phone_number']]
            );
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the guest's password.
     * Verifies the current password before saving the new one.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $guestAuth = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        // The 'hashed' cast on passwordhash auto-bcrypts the plain value.
        $guestAuth->update(['passwordhash' => $validated['password']]);

        return back()->with('success', 'Password updated successfully.');
    }
}
