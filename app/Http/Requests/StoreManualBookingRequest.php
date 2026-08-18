<?php

namespace App\Http\Requests;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Store Manual Booking Request (Reception Proxy Booking)
 *
 * Validates the data required to create a new booking on behalf of a
 * walk-in, phone, or other non-registered guest (Process 1.1 DFD —
 * "Enter booking requirements as proxy for Guests").
 *
 * Collects both guest profile data (since the guest may have no account)
 * and booking/payment details in a single form.
 */
class StoreManualBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated staff members can create proxy bookings.
        return Auth::guard('staff')->check();
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('phone_number') && $this->has('phone')) {
            $this->merge(['phone_number' => $this->input('phone')]);
        }
        if (!$this->has('booking_origin') || empty($this->input('booking_origin'))) {
            $this->merge(['booking_origin' => 'walk-in']);
        }
    }

    public function rules(): array
    {
        // Dynamically compute the max checkout date (7 days from check_in_date).
        $checkin    = $this->input('check_in_date');
        $maxCheckout = $checkin
            ? Carbon::parse($checkin)->addDays(7)->toDateString()
            : null;

        return [
            // ── Guest Profile ────────────────────────────────────────────────
            'full_name'    => ['required', 'string', 'max:50'],
            'gender'       => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'nationality'  => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'email'        => ['nullable', 'email', 'max:255'],
            'adults'       => ['nullable', 'integer', 'min:1', 'max:10'],
            'children'     => ['nullable', 'integer', 'min:0', 'max:10'],

            // ── Booking Details ──────────────────────────────────────────────
            'room_ids'   => ['required', 'array', 'min:1'],
            'room_ids.*' => [
                'integer',
                Rule::exists('rooms', 'id'),
            ],
            'check_in_date'  => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => array_filter([
                'required',
                'date',
                'after:check_in_date',
                $maxCheckout ? 'before_or_equal:' . $maxCheckout : null,
            ]),

            // How the guest arrived or was contacted.
            'booking_origin' => ['required', Rule::in(['walk-in', 'phone', 'other', 'agoda'])],

            // ── Guest Preferences (optional) ─────────────────────────────────
            'bed_type'         => ['nullable', 'string', Rule::in(['twin', 'double'])],
            'floor_preference' => ['nullable', 'string', Rule::in(['2', '3', '4', '5'])],
            'view_preference'  => ['nullable', 'string', Rule::in(['balcony', 'window'])],

            // ── Payment ──────────────────────────────────────────────────────
            'payment_method' => ['required', Rule::in(['cash', 'khqr', 'khqr_aba'])],
            'amount_paid'    => ['required', 'numeric', 'min:0'],
            'payment_reference' => ['required_unless:payment_method,cash', 'nullable', 'string', 'max:255'],

            // The percentage of the total price paid upfront.
            // 20 = 20% deposit, 50 = 50% deposit, 100 = full payment.
            'payment_tier'   => ['required', 'integer', 'in:0,20,50,100'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_ids.required'              => 'Please select at least one room.',
            'room_ids.*.exists'              => 'One or more selected rooms are not available.',
            'check_out_date.after'           => 'Check-out must be at least one night after check-in.',
            'check_in_date.after_or_equal'   => 'Check-in date cannot be in the past.',
            'check_out_date.before_or_equal' => 'Bookings are limited to a maximum of 7 nights.',
        ];
    }
}
