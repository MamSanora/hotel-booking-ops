<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomManagement;
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
    public function index(): View
    {
        $rooms = Room::with('roomType')->orderBy('room_number')->paginate(20);

        return view('admin.rooms.index', compact('rooms'));
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
        ], [
            'room_number.unique'    => 'A room with this number already exists.',
            'room_type_id.required' => 'Please select a room type.',
            'room_type_id.exists'   => 'Please select a valid room type.',
        ]);

        $room = Room::create([
            'room_number'    => $validated['room_number'],
            'room_type_id'   => $validated['room_type_id'],
            'current_status' => 'available',
        ]);

        // Audit log: record who created this room.
        RoomManagement::create([
            'room_id'             => $room->id,
            'managed_by_admin_id' => Auth::guard('admin')->id(),
            'action'              => 'add_room',
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
        ], [
            'room_number.unique'    => 'A room with this number already exists.',
            'room_type_id.required' => 'Please select a room type.',
            'room_type_id.exists'   => 'Please select a valid room type.',
        ]);

        $previousTypeId = $room->room_type_id;

        $room->update([
            'room_number'  => $validated['room_number'],
            'room_type_id' => $validated['room_type_id'],
        ]);

        // Audit log: if the room type changed, the effective price may have changed.
        if ((int) $previousTypeId !== (int) $validated['room_type_id']) {
            RoomManagement::create([
                'room_id'             => $room->id,
                'managed_by_admin_id' => Auth::guard('admin')->id(),
                'action'              => 'update_price',
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
