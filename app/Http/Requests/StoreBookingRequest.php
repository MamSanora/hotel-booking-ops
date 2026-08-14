<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Booking Request (Guest Self-Booking)
 *
 * Validates the form when a registered guest books a room online.
 * The target room is resolved via route model binding ({room} in the URL),
 * so only the date fields and payment method are needed in the request body.
 * Guest identity comes from Auth::user()->guest_id (the authenticated session).
 */
class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        // Dynamically compute the max checkout date (7 days from check_in_date).
        $checkin    = $this->input('check_in_date');
        $maxCheckout = $checkin
            ? Carbon::parse($checkin)->addDays(7)->toDateString()
            : null;

        return [
            'check_in_date'    => ['required', 'date', 'after_or_equal:today'],
            'check_out_date'   => array_filter([
                'required',
                'date',
                'after:check_in_date',
                $maxCheckout ? 'before_or_equal:' . $maxCheckout : null,
            ]),
            'rooms'            => ['required', 'integer', 'min:1', 'max:10'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'payment_method'   => [
                'required',
                'string',
                'in:' . implode(',', [
                    Transaction::METHOD_KHQR_ABA,
                    Transaction::METHOD_KHQR,
                    Transaction::METHOD_ABA,
                    Transaction::METHOD_TELEGRAM,
                ]),
            ],
            // The percentage of the total price paid upfront.
            // 20 = 20% deposit, 50 = 50% deposit, 100 = full payment.
            'payment_tier' => ['required', 'integer', 'in:0,20,50,100'],
            // Guest preference fields (optional, used as hints for reception).
            'bed_type'         => ['nullable', 'string', 'max:50'],
            'floor_preference' => ['nullable', 'string', 'max:50'],
            'view_preference'  => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'check_in_date.after_or_equal'  => 'Check-in date cannot be in the past.',
            'check_out_date.after'           => 'Check-out must be at least one night after check-in.',
            'check_out_date.before_or_equal' => 'Bookings are limited to a maximum of 7 nights. For longer stays, please contact the hotel directly.',
            'payment_method.in'              => 'Please select a valid payment method.',
        ];
    }
}
