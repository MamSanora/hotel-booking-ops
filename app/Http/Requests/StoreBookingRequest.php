<?php

namespace App\Http\Requests;

use App\Models\Transaction;
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
        return [
            'check_in_date'    => ['required', 'date', 'after_or_equal:today'],
            'check_out_date'   => ['required', 'date', 'after:check_in_date'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'payment_method'   => [
                'required',
                'string',
                'in:' . Transaction::METHOD_KHQR . ',' . Transaction::METHOD_ABA . ',' . Transaction::METHOD_TELEGRAM,
            ],
            // The percentage of the total price paid upfront.
            // 20 = 20% deposit, 50 = 50% deposit, 100 = full payment.
            'payment_tier' => ['required', 'integer', 'in:20,50,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'check_in_date.after_or_equal' => 'Check-in date cannot be in the past.',
            'check_out_date.after'          => 'Check-out must be at least one night after check-in.',
            'payment_method.in'             => 'Please select a valid payment method.',
        ];
    }
}
