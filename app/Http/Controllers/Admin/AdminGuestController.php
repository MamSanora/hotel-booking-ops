<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGuestController extends Controller
{
    /**
     * Display a listing of guests.
     */
    public function index(Request $request): View
    {
        $query = Guest::with('guestAuth')->withCount('bookings');

        if ($search = $request->input('search')) {
            $query->where('full_name', 'like', "%{$search}%")
                  ->orWhereHas('guestAuth', function($ga) use ($search) {
                      $ga->where('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('phones', function($p) use ($search) {
                      $p->where('phone_number', 'like', "%{$search}%");
                  });
        }

        $sort = $request->input('sort', 'created_at_desc');

        switch ($sort) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('full_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('full_name', 'desc');
                break;
            case 'bookings_desc':
                $query->orderByDesc('bookings_count')->orderByDesc('created_at');
                break;
            case 'bookings_asc':
                $query->orderBy('bookings_count', 'asc')->orderByDesc('created_at');
                break;
            case 'created_at_desc':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $guests = $query->paginate(20)->withQueryString();

        return view('admin.guests.index', compact('guests'));
    }

    /**
     * Display the specified guest.
     */
    public function show(Guest $guest): View
    {
        $guest->load(['guestAuth', 'phones', 'bookings' => function($q) {
            $q->orderByDesc('created_at')->with(['bookingRooms.roomType', 'bookingRooms.room', 'transactions']);
        }]);

        return view('admin.guests.show', compact('guest'));
    }
}
