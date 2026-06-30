<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Staff Login Request
 *
 * Validates credentials for the 'staff' guard (username + password).
 * Identical pattern to AdminLoginRequest but targets the 'staff' guard
 * and staff table. Rate-limited to 5 attempts per 60 seconds.
 */
class StaffLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate via the 'staff' guard using username + passwordhash.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // The key must be 'password' — see AdminLoginRequest for the same explanation.
        $credentials = [
            'username' => $this->string('username')->value(),
            'password' => $this->string('password')->value(),
        ];

        if (! Auth::guard('staff')->attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Rate limit key: username (lowercased) + IP address.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('username')).'|'.$this->ip()
        );
    }
}
