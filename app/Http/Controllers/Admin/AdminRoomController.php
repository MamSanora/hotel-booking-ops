<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * AdminRoomController
 *
 * Full CRUD management of hotel rooms by administrators.
 * Every create and price-update action is logged to room_management
 * for audit trail purposes.
 *
 * Since normalization, individual rooms no longer store price/capacity/
 * description. These are managed on the RoomType. When creating or updating
 * a room, admins select a room_type_id (FK to room_types).
 *
 * Routes: /admin/rooms (index, create, store, edit, update, destroy)
 */
class AdminRoomController extends Controller
{
    /**
     * List all rooms, paginated.
     */
    public function index(Request $request): View
    {
        $query = Room::with('roomType');

        // Filtering
        if ($request->filled('search')) {
            $query->where('room_number', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        // Sorting
        $sort = $request->input('sort', 'room_number');
        $dir = $request->input('dir', 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sort === 'price') {
            $query->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                  ->select('rooms.*')
                  ->orderBy('room_types.price_per_night', $dir);
        } elseif ($sort === 'capacity') {
            $query->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                  ->select('rooms.*')
                  ->orderBy('room_types.capacity', $dir);
        } else {
            $query->orderBy('room_number', $dir);
        }

        $rooms = $query->paginate(20)->withQueryString();
        $roomTypes = RoomType::orderBy('display_name')->get();

        return view('admin.rooms.index', compact('rooms', 'roomTypes'));
    }

    /**
     * Show the create room form.
     */
    public function create(): View
    {
        $roomTypes = RoomType::orderBy('display_name')->get();

        return view('admin.rooms.create', compact('roomTypes'));
    }

    /**
     * Store a new room and log the creation action.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_number' => [
                'required', 'string', 'max:5',
                Rule::unique('rooms', 'room_number'),
            ],
            'room_type_id' => ['required', 'integer', Rule::exists('room_types', 'id')],
            'bed_configuration' => ['nullable', Rule::in(['twin', 'double', 'triple'])],
            'view_type'      => ['nullable', Rule::in(['city', 'pool', 'garden', 'ocean', 'none'])],
        ], [
            'room_number.unique'    => 'A room with this number already exists.',
            'room_type_id.required' => 'Please select a room type.',
            'room_type_id.exists'   => 'Please select a valid room type.',
        ]);

        $room = Room::create([
            'room_number'    => $validated['room_number'],
            'room_type_id'   => $validated['room_type_id'],
            'bed_configuration' => $validated['bed_configuration'] ?? null,
            'view_type'      => $validated['view_type'] ?? null,
            'current_status' => 'available',
        ]);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', "Room {$room->room_number} created successfully.");
    }

    /**
     * Show the bulk create form.
     */
    public function bulkCreate(): View
    {
        $roomTypes = RoomType::orderBy('display_name')->get();
        return view('admin.rooms.bulk-create', compact('roomTypes'));
    }

    /**
     * Store multiple sequentially numbered rooms.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_type_id'    => ['required', 'integer', Rule::exists('room_types', 'id')],
            'start_number'    => ['required', 'integer', 'min:1'],
            'count'           => ['required', 'integer', 'min:1', 'max:100'],
            'prefix'          => ['nullable', 'string', 'max:2'],
        ]);

        $prefix = $validated['prefix'] ?? '';
        $start = (int) $validated['start_number'];
        $count = (int) $validated['count'];
        $roomTypeId = $validated['room_type_id'];

        $createdCount = 0;
        $adminId = Auth::guard('admin')->id();

        for ($i = 0; $i < $count; $i++) {
            $roomNumber = $prefix . ($start + $i);
            
            // Skip if room number already exists
            if (Room::where('room_number', (string) $roomNumber)->exists()) {
                continue;
            }

            $room = Room::create([
                'room_number'    => (string) $roomNumber,
                'room_type_id'   => $roomTypeId,
                'current_status' => Room::STATUS_AVAILABLE,
            ]);

            $createdCount++;
        }

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', "Successfully generated {$createdCount} rooms.");
    }

    /**
     * Quick status update from the dashboard modal.
     */
    public function quickStatus(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'current_status' => ['required', Rule::in([Room::STATUS_AVAILABLE, Room::STATUS_CLEANING, Room::STATUS_MAINTENANCE])],
        ]);

        if ($room->current_status !== $validated['current_status']) {
            $room->update([
                'current_status' => $validated['current_status'],
                'status_updated_at' => now(),
            ]);
        }

        return back()->with('success', "Room {$room->room_number} status updated.");
    }

    /**
     * Show the edit form for an existing room.
     */
    public function edit(Room $room): View
    {
        $room->load('roomType');
        $roomTypes = RoomType::orderBy('display_name')->get();

        return view('admin.rooms.edit', compact('room', 'roomTypes'));
    }

    /**
     * Update an existing room. Logs a type change (which implies a price change)
     * to the room_management audit log.
     */
    public function update(Request $request, Room $room): RedirectResponse
    {
        $roomId = $room->id;

        $validated = $request->validate([
            'room_number' => [
                'required', 'string', 'max:5',
                Rule::unique('rooms', 'room_number')->ignore($roomId),
            ],
            'room_type_id' => ['required', 'integer', Rule::exists('room_types', 'id')],
            'bed_configuration' => ['nullable', Rule::in(['twin', 'double', 'triple'])],
            'view_type'      => ['nullable', Rule::in(['city', 'pool', 'garden', 'ocean', 'none'])],
        ], [
            'room_number.unique'    => 'A room with this number already exists.',
            'room_type_id.required' => 'Please select a room type.',
            'room_type_id.exists'   => 'Please select a valid room type.',
        ]);

        $previousTypeId = $room->room_type_id;

        $room->update([
            'room_number'  => $validated['room_number'],
            'room_type_id' => $validated['room_type_id'],
            'bed_configuration' => $validated['bed_configuration'] ?? null,
            'view_type'      => $validated['view_type'] ?? null,
        ]);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', "Room {$room->room_number} updated successfully.");
    }

    /**
     * Delete a room. Blocked if the room has any active bookings.
     */
    public function destroy(Room $room): RedirectResponse
    {
        $hasActiveBookings = $room->bookings()
            ->whereIn('booking_status', [
                Booking::STATUS_PENDING,
                Booking::STATUS_BOOKED,
                Booking::STATUS_CHECKED_IN,
            ])
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', 'Cannot delete this room — it has active bookings.');
        }

        $roomNumber = $room->room_number;
        $room->delete();

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', "Room {$roomNumber} deleted successfully.");
    }

    /**
     * Delete multiple rooms. Blocked for any rooms that have active bookings.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', Rule::exists('rooms', 'id')],
        ]);

        $rooms = Room::whereIn('id', $validated['ids'])->get();
        $deletedCount = 0;
        $failedCount = 0;

        foreach ($rooms as $room) {
            $hasActiveBookings = $room->bookings()
                ->whereIn('booking_status', [
                    Booking::STATUS_PENDING,
                    Booking::STATUS_BOOKED,
                    Booking::STATUS_CHECKED_IN,
                ])
                ->exists();

            if ($hasActiveBookings) {
                $failedCount++;
            } else {
                $room->delete();
                $deletedCount++;
            }
        }

        $message = "{$deletedCount} room(s) deleted successfully.";
        if ($failedCount > 0) {
            $message .= " {$failedCount} room(s) could not be deleted because they have active bookings.";
        }

        return redirect()
            ->route('admin.rooms.index')
            ->with($failedCount > 0 && $deletedCount === 0 ? 'error' : 'success', $message);
    }
}
