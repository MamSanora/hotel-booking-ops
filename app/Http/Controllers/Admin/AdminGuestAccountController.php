<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestAuth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGuestAccountController extends Controller
{
    /**
     * Display a listing of registered guest accounts only.
     * These are guests who have signed up via the website (have a GuestAuth record).
     */
    public function index(Request $request): View
    {
        $query = Guest::has('guestAuth')
            ->with('guestAuth')
            ->withCount('bookings');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhereHas('guestAuth', function ($ga) use ($search) {
                      $ga->where('email', 'like', "%{$search}%");
                  });
            });
        }

        $accounts = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.guest-accounts.index', compact('accounts'));
    }

    /**
     * Show the form for editing the specified guest account credentials.
     */
    public function edit(GuestAuth $guestAccount): View
    {
        $guestAccount->load('guest');
        return view('admin.guest-accounts.edit', compact('guestAccount'));
    }

    /**
     * Update the specified guest account credentials.
     */
    public function update(Request $request, GuestAuth $guestAccount)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:guest_auths,email,'.$guestAccount->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $guestAccount->email = $request->email;

        if ($request->filled('password')) {
            $guestAccount->passwordhash = $request->password; // Auto-hashed by model cast
        }

        $guestAccount->save();

        return redirect()->route('admin.guest-accounts.index')->with('success', 'Guest account credentials updated successfully.');
    }

    /**
     * Remove the guest account credentials.
     * Note: This does not delete the Guest profile or bookings, only their ability to log in.
     */
    public function destroy(GuestAuth $guestAccount)
    {
        $guestAccount->delete();
        return redirect()->route('admin.guest-accounts.index')->with('success', 'Guest account access removed. The guest profile and bookings remain intact.');
    }
}
