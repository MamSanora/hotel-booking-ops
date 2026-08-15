<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RoomCheckController
 *
 * Allows receptionists to manually transition rooms from 'cleaning' or
 * 'maintenance' status back to 'available'. This is a front-desk operation
 * (the receptionist coordinates with housekeeping) and does not require admin
 * access.
 *
 * Route: GET  /reception/room-check
 * Route: PATCH /reception/room-check/{room}/mark-available
 */
class RoomCheckController extends Controller
{
    public function index(Request $request): View
    {
        return view('reception.room-check');
    }

    public function markAvailable(Request $request, Room $room): RedirectResponse
    {
        if ($room->current_status === Room::STATUS_OCCUPIED) {
            return back()->with('error', "Room {$room->room_number} is currently occupied and cannot be marked as available.");
        }

        if ($room->current_status === Room::STATUS_AVAILABLE) {
            return back()->with('error', "Room {$room->room_number} is already available.");
        }

        $previousStatus = $room->current_status;
        $room->update(['current_status' => Room::STATUS_AVAILABLE, 'status_updated_at' => now()]);

        return back()->with('success', "Room {$room->room_number} marked as available (was: {$previousStatus}).");
    }

    public function markMaintenance(Request $request, Room $room): RedirectResponse
    {
        if ($room->current_status === Room::STATUS_OCCUPIED) {
            return back()->with('error', "Room {$room->room_number} is currently occupied and cannot be placed under maintenance.");
        }

        if ($room->current_status === Room::STATUS_MAINTENANCE) {
            return back()->with('error', "Room {$room->room_number} is already under maintenance.");
        }

        $previousStatus = $room->current_status;
        $room->update(['current_status' => Room::STATUS_MAINTENANCE, 'status_updated_at' => now()]);

        return back()->with('success', "Room {$room->room_number} marked for maintenance (was: {$previousStatus}).");
    }
}