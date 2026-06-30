<?php

use App\Models\Admin;
use App\Models\GuestAuth;
use App\Models\Staff;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Default guard is 'web', covering hotel guests who register online.
    | Admin and Staff guards are accessed explicitly via Auth::guard().
    | The default password broker points to guest_auths (email-based reset).
    |
    */

    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'guest_auths',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Three completely isolated session guards:
    |   - web   → Registered guests   (guest_auths table, email + passwordhash)
    |   - admin → Administrators       (admins table, username + passwordhash)
    |   - staff → Front-desk staff     (staff table, username + passwordhash)
    |
    */

    'guards' => [
        // Registered hotel guests — default guard
        'web' => [
            'driver'   => 'session',
            'provider' => 'guest_auths',
        ],

        // Hotel administrators — isolated guard
        'admin' => [
            'driver'   => 'session',
            'provider' => 'admins',
        ],

        // Hotel front-desk staff (receptionists) — isolated guard
        // Renamed from 'receptionist' to align with the new 'staff' table.
        'staff' => [
            'driver'   => 'session',
            'provider' => 'staff',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Each guard has its own Eloquent model and database table.
    | Sessions from one guard cannot access another guard's resources.
    |
    */

    'providers' => [
        // Registered guests — guest_auths table (email + passwordhash)
        'guest_auths' => [
            'driver' => 'eloquent',
            'model'  => GuestAuth::class,
        ],

        // Administrators — admins table (username + passwordhash)
        'admins' => [
            'driver' => 'eloquent',
            'model'  => Admin::class,
        ],

        // Front-desk staff — staff table (username + passwordhash)
        'staff' => [
            'driver' => 'eloquent',
            'model'  => Staff::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Only guests (GuestAuth) support email-based password reset, because
    | guests log in with an email address. Admins and staff log in with a
    | username and have no email column, so no broker is defined for them.
    | Their passwords must be reset by a superadmin directly.
    |
    */

    'passwords' => [
        // Registered guests — keyed by email, stored in password_reset_tokens
        'guest_auths' => [
            'provider' => 'guest_auths',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
