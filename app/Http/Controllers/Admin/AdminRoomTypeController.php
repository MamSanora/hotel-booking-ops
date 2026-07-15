<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * AdminRoomTypeController
 *
 * Full CRUD management of hotel room types by administrators.
 * Room types define the price_per_night, capacity, and description
 * that are shared across every physical room of that type.
 *
 * Routes: /admin/room-types (index, create, store, edit, update, destroy)
 */
class AdminRoomTypeController extends Controller
{
    /**
     * List all room types with the count of physical rooms per type.
     */
    public function index(): View
    {
        $roomTypes = RoomType::withCount('rooms')
            ->orderBy('display_name')
            ->paginate(20);

        return view('admin.room-types.index', compact('roomTypes'));
    }

    /**
     * Show the create room type form.
     */
    public function create(): View
    {
        return view('admin.room-types.create');
    }

    /**
     * Store a new room type.
     * The slug is auto-generated from display_name to keep things simple
     * and consistent (e.g. "Standard Twin" => "standard-twin").
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name'    => ['required', 'string', 'max:100', Rule::unique('room_types', 'display_name')],
            'capacity'        => ['required', 'integer', 'min:1', 'max:20'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'description'     => ['nullable', 'string', 'max:2000'],
        ], [
            'display_name.unique'      => 'A room type with this name already exists.',
            'display_name.required'    => 'Please provide a room type name.',
            'capacity.required'        => 'Please specify the guest capacity.',
            'price_per_night.required' => 'Please set a price per night.',
        ]);

        // Auto-generate slug from display_name.
        $slug = Str::slug($validated['display_name']);

        // Ensure slug uniqueness (append a counter if needed).
        $originalSlug = $slug;
        $counter = 1;
        while (RoomType::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        RoomType::create([
            'slug'            => $slug,
            'display_name'    => $validated['display_name'],
            'capacity'        => $validated['capacity'],
            'price_per_night' => $validated['price_per_night'],
            'description'     => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.room-types.index')
            ->with('success', "Room type \"{$validated['display_name']}\" created successfully.");
    }

    /**
     * Show the edit form for an existing room type.
     */
    public function edit(RoomType $roomType): View
    {
        return view('admin.room-types.edit', compact('roomType'));
    }

    /**
     * Update an existing room type.
     * The slug is regenerated from the new display_name if it changed.
     */
    public function update(Request $request, RoomType $roomType): RedirectResponse
    {
        $validated = $request->validate([
            'display_name'    => [
                'required', 'string', 'max:100',
                Rule::unique('room_types', 'display_name')->ignore($roomType->id),
            ],
            'capacity'        => ['required', 'integer', 'min:1', 'max:20'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'description'     => ['nullable', 'string', 'max:2000'],
        ], [
            'display_name.unique'      => 'A room type with this name already exists.',
            'display_name.required'    => 'Please provide a room type name.',
            'capacity.required'        => 'Please specify the guest capacity.',
            'price_per_night.required' => 'Please set a price per night.',
        ]);

        // Regenerate slug only if the display_name changed.
        $slug = $roomType->slug;
        if ($validated['display_name'] !== $roomType->display_name) {
            $slug = Str::slug($validated['display_name']);
            $originalSlug = $slug;
            $counter = 1;
            while (RoomType::where('slug', $slug)->where('id', '!=', $roomType->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
        }

        $roomType->update([
            'slug'            => $slug,
            'display_name'    => $validated['display_name'],
            'capacity'        => $validated['capacity'],
            'price_per_night' => $validated['price_per_night'],
            'description'     => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.room-types.index')
            ->with('success', "Room type \"{$roomType->display_name}\" updated successfully.");
    }

    /**
     * Delete a room type.
     * Blocked if there are any physical rooms still assigned to this type.
     */
    public function destroy(RoomType $roomType): RedirectResponse
    {
        if ($roomType->rooms()->exists()) {
            $count = $roomType->rooms()->count();
            return back()->with(
                'error',
                "Cannot delete \"{$roomType->display_name}\" — it is still assigned to {$count} room(s). Reassign or delete those rooms first."
            );
        }

        $name = $roomType->display_name;
        $roomType->delete();

        return redirect()
            ->route('admin.room-types.index')
            ->with('success', "Room type \"{$name}\" deleted successfully.");
    }
}
