<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomManagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * AdminRoomController
 *
 * Full CRUD management of hotel rooms by administrators.
 * Every create and price-update action is logged to room_management
 * for audit trail purposes.
 *
 * Routes: /admin/rooms (index, create, store, edit, update, destroy)
 */
class AdminRoomController extends Controller
{
    /**
     * List all rooms, paginated.
     */
    public function index(): View
    {
        $rooms = Room::orderBy('room_number')->paginate(20);

        return view('admin.rooms.index', compact('rooms'));
    }

    /**
     * Show the create room form.
     */
    public function create(): View
    {
        $roomTypes = Room::ROOM_TYPES;

        return view('admin.rooms.create', compact('roomTypes'));
    }

    /**
     * Store a new room and log the creation action.
     */
    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $room = Room::create($request->validated());

        // Audit log: record who created this room.
        RoomManagement::create([
            'room_id'            => $room->id,
            'managed_by_admin_id' => Auth::guard('admin')->id(),
            'action'             => 'add_room',
        ]);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', "Room {$room->room_number} created successfully.");
    }

    /**
     * Show the edit form for an existing room.
     */
    public function edit(Room $room): View
    {
        $roomTypes = Room::ROOM_TYPES;

        return view('admin.rooms.edit', compact('room', 'roomTypes'));
    }

    /**
     * Update an existing room. Logs a price update if price_per_night changed.
     */
    public function update(StoreRoomRequest $request, Room $room): RedirectResponse
    {
        $previousPrice = $room->price_per_night;

        $room->update($request->validated());

        // Audit log: record price changes for financial transparency.
        if ((float) $previousPrice !== (float) $request->validated()['price_per_night']) {
            RoomManagement::create([
                'room_id'            => $room->id,
                'managed_by_admin_id' => Auth::guard('admin')->id(),
                'action'             => 'update_price',
            ]);
        }

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
}
