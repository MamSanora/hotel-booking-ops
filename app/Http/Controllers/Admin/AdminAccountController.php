<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminAccountController extends Controller implements HasMiddleware
{
    /**
     * Ensure only superadmins can access this controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                if (!auth('admin')->user()->isSuperAdmin()) {
                    abort(403, 'Unauthorized access. Only super administrators can manage admin accounts.');
                }
                return $next($request);
            }),
        ];
    }

    /**
     * Display a listing of admin accounts.
     */
    public function index(Request $request): View
    {
        $admins = Admin::orderBy('full_name')->paginate(20);
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create(): View
    {
        return view('admin.admins.form', ['admin' => new Admin()]);
    }

    /**
     * Store a newly created admin in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50', 'unique:admins,username'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $admin = Admin::create([
            'full_name'    => $validated['full_name'],
            'role'         => 'admin',
            'username'     => $validated['username'],
            'passwordhash' => $validated['password'],
        ]);

        return redirect()->route('admin.admins.index')->with('success', 'Admin account created successfully.');
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Admin $admin): View
    {
        return view('admin.admins.form', compact('admin'));
    }

    /**
     * Update the specified admin in storage.
     */
    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50', 'unique:admins,username,' . $admin->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $admin->full_name = $validated['full_name'];
        $admin->username = $validated['username'];
        
        if (! empty($validated['password'])) {
            $admin->passwordhash = $validated['password'];
        }

        $admin->save();

        return redirect()->route('admin.admins.index')->with('success', 'Admin account updated successfully.');
    }

    /**
     * Remove the specified admin from storage.
     */
    public function destroy(Admin $admin): RedirectResponse
    {
        // Prevent deleting the currently authenticated admin
        if (auth()->guard('admin')->id() === $admin->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();
        return redirect()->route('admin.admins.index')->with('success', 'Admin account deleted successfully.');
    }
}
