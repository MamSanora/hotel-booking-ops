<?php

namespace App\Http\Controllers\Auth\Guest;

use App\Http\Controllers\Controller;
use App\Models\GuestAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Phone Verification Controller
 *
 * Handles the OTP verification step after phone-based registration.
 *
 * Flow:
 *   1. Guest registers with phone → OTP written to laravel.log, redirected here.
 *   2. show()   → displays the 6-digit OTP entry form.
 *   3. verify() → validates the OTP, marks phone_verified_at, logs the user in.
 *   4. resend() → generates a fresh OTP and writes it to the log again.
 *
 * The phone number is stored in session('otp_phone') throughout this flow.
 * Once verified, the session key is flushed.
 *
 * Routes:
 *   GET  /guest/verify-phone         → show()
 *   POST /guest/verify-phone         → verify()
 *   POST /guest/verify-phone/resend  → resend()
 */
class PhoneVerificationController extends Controller
{
    /**
     * Show the OTP verification page.
     * Requires the otp_phone session key (set by RegisterController).
     */
    public function show(Request $request): View|RedirectResponse
    {
        // Already logged in — skip.
        if (Auth::guard('web')->check()) {
            return redirect()->route('guest.dashboard');
        }

        $phone = $request->session()->get('otp_phone');

        if (! $phone) {
            return redirect()->route('guest.register')
                ->with('error', 'Session expired. Please register again.');
        }

        return view('auth.guest.verify-phone', ['phone' => $phone]);
    }

    /**
     * Verify the submitted OTP code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $phone = $request->session()->get('otp_phone');

        if (! $phone) {
            return redirect()->route('guest.register')
                ->with('error', 'Session expired. Please register again.');
        }

        $guestAuth = GuestAuth::where('login_phone', $phone)->first();

        if (! $guestAuth) {
            return redirect()->route('guest.register')
                ->with('error', 'Account not found. Please register again.');
        }

        // Check OTP validity.
        if (! $guestAuth->isOtpValid($request->input('otp'))) {
            return back()->withErrors([
                'otp' => 'The code is incorrect or has expired. Please try again or request a new code.',
            ]);
        }

        // Mark as verified, clear the OTP fields.
        $guestAuth->update([
            'phone_verified_at' => now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
        ]);

        // Flush the OTP session key.
        $request->session()->forget('otp_phone');

        // Log the user in.
        Auth::guard('web')->login($guestAuth);
        $request->session()->regenerate();

        return redirect()->intended(route('guest.dashboard'))
            ->with('success', 'Phone verified! Welcome to Dara Meas Hotel.');
    }

    /**
     * Resend (regenerate) the OTP — writes a fresh code to the log.
     */
    public function resend(Request $request): RedirectResponse
    {
        $phone = $request->session()->get('otp_phone');

        if (! $phone) {
            return redirect()->route('guest.register')
                ->with('error', 'Session expired. Please register again.');
        }

        $guestAuth = GuestAuth::where('login_phone', $phone)->first();

        if (! $guestAuth) {
            return redirect()->route('guest.register')
                ->with('error', 'Account not found. Please register again.');
        }

        // Generate a new 6-digit OTP.
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $guestAuth->update([
            'otp_code'       => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Log::channel('single')->info(
            '[PHONE OTP — RESEND] Phone: ' . $phone .
            ' | Code: ' . $otp .
            ' | Expires: ' . now()->addMinutes(10)->toDateTimeString()
        );

        return back()->with('success', 'A new code has been sent. Check storage/logs/laravel.log.');
    }
}
