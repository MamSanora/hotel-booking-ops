<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CleanerRoomCheckController
 *
 * Allows cleaning staff to view rooms that require attention (cleaning
 * or maintenance) and to mark them as available once work is complete.
 *
 * Cleaners can mark BOTH cleaning and maintenance rooms as available.
 * The assumption is that a supervisor (receptionist or manager) has
 * verbally authorized the cleaner to clear the maintenance status after
 * the contracted maintenance team finishes their work.
 *
 * Route: GET   /cleaner/room-check
 * Route: PATCH /cleaner/room-check/{room}/mark-available
 */
class CleanerRoomCheckController extends Controller
{
    public function index(): View
    {
        return view('cleaner.room-check');
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
        $room->update([
            'current_status'   => Room::STATUS_AVAILABLE,
            'status_updated_at' => now(),
        ]);

        return back()->with('success', "Room {$room->room_number} marked as available (was: {$previousStatus}).");
    }
}
