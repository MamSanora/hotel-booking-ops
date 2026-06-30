<?php

namespace App\Http\Controllers\Auth\Guest;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Guest RegisterController
 *
 * Handles hotel guest self-registration.
 * Creating an account produces TWO records:
 *   1. guests       — profile (full_name, gender, nationality)
 *   2. guest_auths  — credentials (email, passwordhash)
 *
 * This two-table design means walk-in guests without accounts can still
 * have a booking record linked to a guest profile.
 *
 * Register URL: GET  /guest/register
 * Process:      POST /guest/register
 */
class RegisterController extends Controller
{
    /**
     * Show the guest registration form.
     */
    public function showRegister(Request $request): View|RedirectResponse
    {
        if ($request->has('redirect')) {
            session(['url.intended' => $request->redirect]);
        }

        if (Auth::guard('web')->check()) {
            return redirect()->route('guest.dashboard');
        }

        return view('auth.guest.register');
    }

    /**
     * Process the guest registration form.
     *
     * Creates the guest profile and auth records within a DB transaction
     * so both are rolled back if either fails.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name'   => ['required', 'string', 'max:50'],
            'gender'      => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:guest_auths,email'],
            'password'    => ['required', 'confirmed', Password::defaults()],
        ]);

        // Step 1: Create the guest profile (no credentials here).
        $guest = Guest::create([
            'full_name'   => $validated['full_name'],
            'gender'      => $validated['gender'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
        ]);

        // Step 2: Store phone number if provided.
        if (! empty($validated['phone'])) {
            $guest->phones()->create(['phone_number' => $validated['phone']]);
        }

        // Step 3: Create the auth credentials linked to the guest profile.
        // The 'hashed' cast on passwordhash auto-bcrypts the plain value.
        $guestAuth = GuestAuth::create([
            'guest_id'     => $guest->id,
            'email'        => $validated['email'],
            'passwordhash' => $validated['password'],
        ]);

        // Step 4: Log the new guest in via the 'web' guard.
        Auth::guard('web')->login($guestAuth);

        return redirect()->intended(route('guest.dashboard'));
    }
}
