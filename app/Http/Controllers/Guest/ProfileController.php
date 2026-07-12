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
 * Profile data (full_name, gender, nationality) lives in the guests table.
 * Phone numbers are stored as multiple rows in the phones table (One-to-Many).
 * Auth credentials (email, passwordhash) are in guest_auths.
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
     * Loads the GuestAuth and its related Guest profile + all phones.
     */
    public function edit(): View
    {
        $guestAuth = Auth::user();
        $guestAuth->load(['guest.phones']);
        $guest = $guestAuth->guest;

        return view('guest.profile', compact('guestAuth', 'guest'));
    }

    /**
     * Update the guest's profile, email, and phone numbers.
     *
     * The GuestAuth table owns 'email'.
     * The Guest table owns 'full_name', 'gender', 'nationality'.
     * The phones table holds one row per phone number (One-to-Many).
     *
     * Phone sync strategy:
     *   - The form submits phones[existing_id] = "number" for pre-existing rows
     *     and phones[new_X] = "number" for newly added rows.
     *   - Any phone ID that was in the database but not in the submitted form
     *     is deleted (the guest removed it).
     *   - Empty strings are treated as removal.
     */
    public function update(Request $request): RedirectResponse
    {
        $guestAuth = Auth::user();
        $guest     = $guestAuth->guest;

        $validated = $request->validate([
            'full_name'    => ['required', 'string', 'max:50'],
            'gender'       => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'nationality'  => ['nullable', 'string', 'max:50'],
            'email'        => $guestAuth->isPhoneUser()
                // Phone users: email is optional but must be unique if provided.
                ? ['nullable', 'string', 'email', 'max:255', 'unique:guest_auths,email,' . $guestAuth->id]
                // Email users: email is required.
                : ['required', 'string', 'email', 'max:255', 'unique:guest_auths,email,' . $guestAuth->id],
            'login_phone'  => ['nullable', 'string', 'max:30', 'unique:guest_auths,login_phone,' . $guestAuth->id],
            'phones'       => ['nullable', 'array', 'max:5'],
            'phones.*'     => ['nullable', 'string', 'max:30'],
        ]);

        // ── Update auth credentials ─────────────────────────────────────────
        $authUpdate = [];
        if (! $guestAuth->isPhoneUser() || $request->filled('email')) {
            $authUpdate['email'] = $validated['email'] ?? null;
        }
        if ($request->filled('login_phone')) {
            $authUpdate['login_phone'] = $validated['login_phone'];
        }
        if (! empty($authUpdate)) {
            $guestAuth->update($authUpdate);
        }

        // ── Update profile data ─────────────────────────────────────────────
        $guest->update([
            'full_name'   => $validated['full_name'],
            'gender'      => $validated['gender'] ?? $guest->gender,
            'nationality' => $validated['nationality'] ?? $guest->nationality,
        ]);

        // ── Sync phone numbers ──────────────────────────────────────────────
        $submittedPhones = $validated['phones'] ?? [];
        $existingIds     = $guest->phones()->pluck('id')->all();
        $submittedExistingIds = [];

        foreach ($submittedPhones as $key => $number) {
            if (blank($number)) {
                continue;
            }
            if (is_numeric($key) && in_array((int) $key, $existingIds)) {
                $guest->phones()->where('id', $key)->update(['phone_number' => $number]);
                $submittedExistingIds[] = (int) $key;
            } else {
                $guest->phones()->create(['phone_number' => $number]);
            }
        }

        $idsToDelete = array_diff($existingIds, $submittedExistingIds);
        if (! empty($idsToDelete)) {
            $guest->phones()->whereIn('id', $idsToDelete)->delete();
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
