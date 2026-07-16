<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Store Walk-In Booking Request (Reception Proxy Booking)
 *
 * Validates the form when a receptionist creates a booking on behalf of a
 * walk-in, phone, or other non-registered guest (Process 1.1 DFD —
 * "Enter booking requirements as proxy for walk-in Guests").
 *
 * Collects both guest profile data (since the guest may have no account)
 * and booking/payment details in a single form.
 */
class StoreWalkInBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated staff members can create proxy bookings.
        return Auth::guard('staff')->check();
    }

    public function rules(): array
    {
        return [
            // ── Guest Profile ────────────────────────────────────────────────
            'full_name' => ['required', 'string', 'max:50'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'nationality' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:30'],

            // ── Booking Details ──────────────────────────────────────────────
            'room_id' => [
                'required',
                'integer',
                Rule::exists('rooms', 'id')->where('current_status', Room::STATUS_AVAILABLE),
            ],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],

            // How the guest arrived or was contacted.
            'guest_type' => ['required', Rule::in(['walk-in', 'phone', 'other'])],

            // ── Payment ──────────────────────────────────────────────────────
            'payment_method' => ['required', Rule::in(['cash', 'khqr'])],
            'amount_paid'    => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', Rule::in(['pending', 'half', 'full'])],
            // The percentage of the total price paid upfront.
            // 20 = 20% deposit, 50 = 50% deposit, 100 = full payment.
            'payment_tier'   => ['required', 'integer', 'in:20,50,100'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.exists' => 'The selected room is not available.',
            'check_out_date.after' => 'Check-out must be at least one night after check-in.',
            'check_in_date.after_or_equal' => 'Check-in date cannot be in the past.',
        ];
    }
}
