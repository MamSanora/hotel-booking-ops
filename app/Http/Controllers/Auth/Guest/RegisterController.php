<?php

namespace App\Http\Controllers\Auth\Guest;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Guest RegisterController
 *
 * Handles hotel guest self-registration via two paths:
 *
 *   Email path (classic):
 *     full_name + email + password → creates guest + guest_auths row.
 *     Phone number is optional contact info stored in the phones table.
 *
 *   Phone path (new):
 *     full_name + login_phone + password → creates guest + guest_auths row,
 *     then generates a 6-digit OTP written to the Laravel log (mock SMS),
 *     and redirects to the OTP verification page.
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
     * Detects which path the user chose based on which fields are present,
     * then routes to the appropriate registration logic.
     */
    public function register(Request $request): RedirectResponse
    {
        // Detect registration path: phone path is chosen when 'login_phone' is
        // present and non-empty; email path is the default.
        if ($request->filled('login_phone')) {
            return $this->registerWithPhone($request);
        }

        return $this->registerWithEmail($request);
    }

    // ── Email Registration ─────────────────────────────────────────────────

    /**
     * Classic email + password registration (unchanged from original).
     */
    private function registerWithEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name'   => ['required', 'string', 'max:50'],
            'gender'      => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:guest_auths,email'],
            'password'    => ['required', 'confirmed', Password::defaults()],
        ]);

        // Step 1: Create the guest profile.
        $guest = Guest::create([
            'full_name'   => $validated['full_name'],
            'gender'      => $validated['gender'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
        ]);

        // Step 2: Store phone as contact number if provided.
        if (! empty($validated['phone'])) {
            $guest->phones()->create(['phone_number' => $validated['phone']]);
        }

        // Step 3: Create auth credentials.
        $guestAuth = GuestAuth::create([
            'guest_id'     => $guest->id,
            'email'        => $validated['email'],
            'passwordhash' => $validated['password'],
        ]);

        // Step 4: Log in.
        Auth::guard('web')->login($guestAuth);

        return redirect()->intended(route('guest.dashboard'));
    }

    // ── Phone Registration ─────────────────────────────────────────────────

    /**
     * Phone + password registration with mock OTP verification.
     */
    private function registerWithPhone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name'   => ['required', 'string', 'max:50'],
            'gender'      => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'login_phone' => ['required', 'string', 'max:30', 'unique:guest_auths,login_phone'],
            'password'    => ['required', 'confirmed', Password::defaults()],
        ]);

        // Generate a 6-digit OTP.
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Step 1: Create the guest profile.
        $guest = Guest::create([
            'full_name'   => $validated['full_name'],
            'gender'      => $validated['gender'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
        ]);

        // Step 2: Also store the login_phone in the phones contact table.
        $guest->phones()->create(['phone_number' => $validated['login_phone']]);

        // Step 3: Create auth credentials with OTP — NOT logged in yet.
        GuestAuth::create([
            'guest_id'      => $guest->id,
            'login_phone'   => $validated['login_phone'],
            'passwordhash'  => $validated['password'],
            'otp_code'      => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Step 4: Write OTP to the Laravel log (mock SMS).
        Log::channel('single')->info(
            '[PHONE OTP] Phone: ' . $validated['login_phone'] .
            ' | Code: ' . $otp .
            ' | Expires: ' . now()->addMinutes(10)->toDateTimeString()
        );

        // Step 5: Store phone in session so the verify page can display it.
        session(['otp_phone' => $validated['login_phone']]);

        return redirect()->route('guest.verify-phone');
    }
}
