<?php

namespace App\Http\Controllers\Auth\Guest;

use App\Http\Controllers\Controller;
use App\Mail\GuestPasswordRecoveryOtp;
use App\Models\GuestAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * ForgotPasswordController
 *
 * Manages the 3-step guest password recovery flow:
 *
 *   Step 1 — Request:  Guest submits their email or phone number.
 *                      A 6-digit OTP is generated, stored on guest_auths,
 *                      and delivered (email via Mail, phone via Log).
 *                      The guest_auths ID is stored in session("recovery_guest_id").
 *
 *   Step 2 — Verify:   Guest enters the OTP. On success, the session flag
 *                      "recovery_otp_verified" is set to true and the OTP
 *                      fields are cleared from the DB immediately.
 *
 *   Step 3 — Reset:    Guest sets a new password. On success, they are
 *                      automatically logged in and the recovery session is cleared.
 *
 * Security measures:
 *   - User ID never appears in the URL — only in the encrypted session.
 *   - OTP is valid for 10 minutes (matches existing phone-verification flow).
 *   - Routes are throttled (5/min for send, 10/min for verify).
 *   - Verify and reset steps guard against direct URL access via session checks.
 *   - sendOtp() fails silently (same message for found/not-found) to prevent enumeration.
 */
class ForgotPasswordController extends Controller
{
    // ── Step 1: Request OTP ────────────────────────────────────────────────

    /**
     * Show the "forgot password" form (email or phone input).
     */
    public function showRequestForm(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('guest.dashboard');
        }

        return view('auth.guest.forgot-password');
    }

    /**
     * Validate the identifier, generate an OTP, and send it.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $identifier = trim($request->input('identifier'));

        // Determine if the input looks like a phone number (digits, spaces, +, dashes).
        $isPhone = preg_match('/^[\d\+\s\-\(\)]+$/', $identifier) && ! str_contains($identifier, '@');

        // Look up the guest.
        $guestAuth = $isPhone
            ? GuestAuth::where('login_phone', $identifier)->first()
            : GuestAuth::where('email', $identifier)->first();

        if (! $guestAuth) {
            // Fail silently — same message whether account exists or not, prevents enumeration.
            return redirect()->route('guest.forgot-password.verify')
                ->with('info', 'If an account with that identifier exists, a code has been sent.');
        }

        // Generate a 6-digit OTP (consistent with the existing phone-registration flow).
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $guestAuth->update([
            'otp_code'       => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Store the guest auth ID in the session (never in the URL).
        $request->session()->put('recovery_guest_id', $guestAuth->id);

        // Deliver the OTP.
        if ($isPhone) {
            Log::channel('single')->info(
                '[RECOVERY OTP — SMS] Phone: ' . $identifier .
                ' | Code: ' . $otp .
                ' | Expires: ' . now()->addMinutes(10)->toDateTimeString()
            );
        } else {
            Mail::to($guestAuth->email)->send(new GuestPasswordRecoveryOtp($guestAuth, $otp));
        }

        return redirect()->route('guest.forgot-password.verify')
            ->with('info', 'If an account with that identifier exists, a code has been sent.');
    }

    // ── Step 2: Verify OTP ─────────────────────────────────────────────────

    /**
     * Show the OTP entry form.
     * Requires a valid "recovery_guest_id" in session to prevent direct URL access.
     */
    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('recovery_guest_id')) {
            return redirect()->route('guest.forgot-password')
                ->with('error', 'Please request a recovery code first.');
        }

        return view('auth.guest.verify-recovery-otp');
    }

    /**
     * Validate the submitted OTP code.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $guestAuthId = $request->session()->get('recovery_guest_id');

        if (! $guestAuthId) {
            return redirect()->route('guest.forgot-password')
                ->with('error', 'Session expired. Please start again.');
        }

        $guestAuth = GuestAuth::find($guestAuthId);

        if (! $guestAuth) {
            return redirect()->route('guest.forgot-password')
                ->with('error', 'Account not found. Please start again.');
        }

        if (! $guestAuth->isOtpValid($request->input('otp'))) {
            return back()->withErrors([
                'otp' => 'The code is incorrect or has expired. Please try again or request a new code.',
            ]);
        }

        // OTP confirmed — clear it from the DB immediately so it cannot be reused.
        $guestAuth->update([
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        // Set the verified flag so Step 3 knows the guest proved ownership.
        $request->session()->put('recovery_otp_verified', true);

        return redirect()->route('guest.forgot-password.reset');
    }

    // ── Step 3: Reset Password ─────────────────────────────────────────────

    /**
     * Show the new-password form.
     * Requires both session keys — prevents skipping Step 2 by going directly to this URL.
     */
    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (
            ! $request->session()->has('recovery_guest_id') ||
            ! $request->session()->get('recovery_otp_verified')
        ) {
            return redirect()->route('guest.forgot-password')
                ->with('error', 'Please verify your identity first.');
        }

        return view('auth.guest.reset-password');
    }

    /**
     * Update the password, log the guest in, and clear the recovery session.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $guestAuthId = $request->session()->get('recovery_guest_id');
        $verified    = $request->session()->get('recovery_otp_verified');

        if (! $guestAuthId || ! $verified) {
            return redirect()->route('guest.forgot-password')
                ->with('error', 'Session expired. Please start the recovery process again.');
        }

        $guestAuth = GuestAuth::find($guestAuthId);

        if (! $guestAuth) {
            return redirect()->route('guest.forgot-password')
                ->with('error', 'Account not found. Please start again.');
        }

        // Update the password (model cast auto-bcrypts it).
        $guestAuth->update(['passwordhash' => $request->input('password')]);

        // Clear all recovery session data.
        $request->session()->forget(['recovery_guest_id', 'recovery_otp_verified']);

        // Log the user in automatically.
        Auth::guard('web')->login($guestAuth);
        $request->session()->regenerate();

        return redirect()->route('guest.dashboard')
            ->with('success', 'Your password has been reset successfully. Welcome back!');
    }
}
