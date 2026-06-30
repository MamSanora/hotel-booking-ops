<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Store / Update Staff Request
 *
 * Validates the form when an admin creates or updates a staff account.
 * Used by Admin\StaffController for both store() and update() actions.
 *
 * On create: password is required and must be confirmed.
 * On update: password is optional — only hashed and saved if provided.
 * Username uniqueness ignores the current record's own ID on update.
 */
class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        // On update, the route model-bound staff ID is used to ignore
        // the current staff member's own username in the unique check.
        $staffId = $this->route('staff')?->id;

        return [
            'full_name' => ['required', 'string', 'max:50'],

            'role' => ['required', Rule::in(['receptionist'])],

            'managed_by_admin_id' => ['nullable', 'integer', 'exists:admins,id'],

            // Username must be unique across the staff table, except for
            // the record currently being updated.
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('staff', 'username')->ignore($staffId),
            ],

            // Password is required on create, optional on update.
            'password' => [
                $staffId ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'password_confirmation' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique'   => 'This username is already taken by another staff member.',
            'password.min'      => 'Password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
