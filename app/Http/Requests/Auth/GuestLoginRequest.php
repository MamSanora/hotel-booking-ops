<?php

namespace App\Http\Requests\Auth;

use App\Models\GuestAuth;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Guest Login Request
 *
 * Validates credentials for the 'web' guard (email or phone + password).
 *
 * Input detection:
 *   - If the identifier contains '@' → treat as email → query guest_auths.email
 *   - Otherwise                      → treat as phone → query guest_auths.login_phone
 *
 * We manually perform the credential lookup here rather than using Auth::attempt()
 * with a 'login_phone' key, because Laravel's EloquentUserProvider only queries
 * the column named by getAuthIdentifierName() (which is 'id' for session reloads).
 * Using a custom lookup avoids confusing the provider while keeping rate limiting.
 *
 * Rate limiting: 5 failed attempts per identifier+IP within 60 seconds.
 */
class GuestLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'password'   => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Please enter your email or phone number.',
        ];
    }

    /**
     * Detect whether the identifier is an email or a phone number.
     */
    public function isEmailLogin(): bool
    {
        return str_contains($this->string('identifier')->value(), '@');
    }

    /**
     * Attempt to authenticate the request using the 'web' guard.
     * Throws a ValidationException if authentication fails or is rate-limited.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = $this->string('identifier')->trim()->value();
        $password   = $this->string('password')->value();

        // Resolve the GuestAuth row by the appropriate column.
        if ($this->isEmailLogin()) {
            $guestAuth = GuestAuth::where('email', Str::lower($identifier))->first();
        } else {
            // Normalise phone: strip spaces for matching.
            $guestAuth = GuestAuth::where('login_phone', $identifier)->first();
        }

        // Verify the password hash manually, then log in via the guard.
        if (! $guestAuth || ! Hash::check($password, $guestAuth->getAuthPassword())) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'identifier' => __('auth.failed'),
            ]);
        }

        // Log in using the resolved model directly.
        Auth::guard('web')->login($guestAuth, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate-limited.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'identifier' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Rate limit key: normalised identifier + IP address.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('identifier')).'|'.$this->ip()
        );
    }
}
