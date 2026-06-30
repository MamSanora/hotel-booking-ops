<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Store / Update Room Request
 *
 * Validates the form when an admin creates or updates a room.
 * Used by Admin\AdminRoomController for both store() and update() actions.
 *
 * Room number uniqueness ignores the current record on update.
 */
class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        // On update, ignore the current room's own room_number in the unique check.
        $roomId = $this->route('room')?->id;

        return [
            // Short code displayed to guests and staff, e.g. '101', '2B'.
            'room_number' => [
                'required',
                'string',
                'max:5',
                Rule::unique('rooms', 'room_number')->ignore($roomId),
            ],

            'room_type' => [
                'required',
                Rule::in(array_keys(Room::ROOM_TYPES)),
            ],

            'price_per_night' => ['required', 'numeric', 'min:0', 'max:99999.99'],

            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_number.unique'  => 'A room with this number already exists.',
            'room_type.in'        => 'Please select a valid room type.',
            'price_per_night.min' => 'Price cannot be negative.',
            'capacity.min'        => 'Room must accommodate at least 1 guest.',
        ];
    }
}
