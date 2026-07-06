<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Admin StaffController
 *
 * Manages front-desk staff accounts on behalf of the hotel administration.
 * Replaces the old StaffController which used the removed Receptionist model.
 *
 * Route prefix: /admin/staff
 */
class StaffController extends Controller
{
    /**
     * List all staff members, paginated.
     */
    public function index(): View
    {
        $staff = Staff::with('managedBy')
            ->orderBy('full_name')
            ->paginate(15);

        return view('admin.staff.index', compact('staff'));
    }

    /**
     * Show the create staff form.
     */
    public function create(): View
    {
        return view('admin.staff.create');
    }

    /**
     * Store a new staff account.
     * The currently authenticated admin is automatically set as the manager.
     */
    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Auto-assign the currently logged-in admin as manager if not specified.
        $data['managed_by_admin_id'] ??= Auth::guard('admin')->id();

        // Map the plain password to the passwordhash column.
        // The 'hashed' cast on Staff::passwordhash bcrypts this automatically.
        $data['passwordhash'] = $data['password'];
        unset($data['password'], $data['password_confirmation']);

        Staff::create($data);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff account created successfully.');
    }

    /**
     * Show the edit form for a staff member.
     */
    public function edit(Staff $staff): View
    {
        return view('admin.staff.edit', ['member' => $staff, 'staff' => $staff]);
    }

    /**
     * Update an existing staff member's details.
     * Password is only updated if a new value is provided.
     */
    public function update(StoreStaffRequest $request, Staff $staff): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['passwordhash'] = $data['password'];
        }

        unset($data['password'], $data['password_confirmation']);

        $staff->update($data);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "{$staff->full_name}'s account updated successfully.");
    }

    /**
     * Delete a staff member's account.
     */
    public function destroy(Staff $staff): RedirectResponse
    {
        $name = $staff->full_name;
        $staff->delete();

        return back()->with('success', "{$name}'s account removed successfully.");
    }
}
