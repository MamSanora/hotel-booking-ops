<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReceptionProfileController extends Controller
{
    /**
     * Show the form for editing the staff profile.
     */
    public function edit(): View
    {
        return view('reception.profile.edit', [
            'staff' => auth()->guard('staff')->user(),
        ]);
    }

    /**
     * Update the staff profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $staff = auth()->guard('staff')->user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50', 'unique:staff,username,' . $staff->id],
        ]);

        $staff->fill($validated);
        $staff->save();

        return redirect()->route('reception.profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the staff password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password:staff'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $staff = auth()->guard('staff')->user();
        
        $staff->update([
            'passwordhash' => $validated['password'],
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
