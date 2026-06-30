<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Store Room Service Request
 *
 * Validates the form when a checked-in guest submits a request or complaint
 * during their stay (Process 6.0 Room Services in the DFD).
 *
 * Guests can optionally attach catalog items (e.g. "2 towels, 1 pillow")
 * alongside their free-text notes.
 */
class StoreRoomServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated guests can submit room service requests.
        return Auth::guard('web')->check();
    }

    public function rules(): array
    {
        return [
            // Whether this is a service request or a complaint.
            'request_type' => ['required', Rule::in(['request', 'complaint'])],

            // The guest's description — required so staff can act on it.
            'guest_notes' => ['required', 'string', 'max:1000'],

            // Optional array of catalog items. Only validated when present.
            'items'                    => ['nullable', 'array', 'max:10'],
            'items.*.catalog_id'       => ['required_with:items', 'integer', 'exists:items_catalogs,id'],
            'items.*.amount_per_item'  => ['required_with:items', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_notes.required'              => 'Please describe your request or complaint.',
            'items.*.catalog_id.exists'         => 'One or more selected items are not in the catalog.',
            'items.*.amount_per_item.min'       => 'Quantity must be at least 1.',
            'items.*.amount_per_item.max'       => 'You may request up to 10 of each item at a time.',
        ];
    }
}
