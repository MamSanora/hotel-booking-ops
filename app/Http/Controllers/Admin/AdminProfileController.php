<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminProfileController extends Controller
{
    /**
     * Show the form for editing the admin's profile.
     */
    public function edit(): View
    {
        return view('admin.profile.edit', [
            'admin' => auth()->guard('admin')->user(),
        ]);
    }

    /**
     * Update the admin's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $admin = auth()->guard('admin')->user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50', 'unique:admins,username,' . $admin->id],
        ]);

        $admin->fill($validated);

        $admin->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the admin's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $admin = auth()->guard('admin')->user();
        
        $admin->update([
            'passwordhash' => $validated['password'],
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
