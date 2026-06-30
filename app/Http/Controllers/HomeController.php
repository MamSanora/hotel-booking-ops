<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Room; // Import the Room model

use App\Models\Booking; // Import the Booking model

use App\Models\Contact; // Import the Contact model

use App\Models\Gallery; // Import the Gallery model

class HomeController extends Controller
{
    public function room_details($id)
    {
        $room = Room::find($id);

        return view('home.room_details', compact('room'));
    }

    public function add_booking(Request $request, $id)
    {
        // Add date validation
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'date|after:startDate',
        ]);

        $data = new Booking;

        $data->room_id = $id; // this comes directly from the URL variable
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;

        $startDate = $request->startDate;
        $endDate = $request->endDate;

        $isBooked = Booking::where('room_id', $id)
        ->where('start_date', '<=', $endDate)
        ->where('end_date', '>=', $startDate)
        ->exists();

        if ($isBooked) {
            return redirect()->back()->with('message', 'Room is already booked, please try different dates');
        }
        else {
            $data->start_date = $request->startDate;
            $data->end_date = $request->endDate;

            $data->save();

            return redirect()->back()->with('message', 'Room Booked Successfully');
        }
    }

    public function contact(Request $request)
    {
        $data = new Contact;

        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->message = $request->message;

        $data->save();

        return redirect()->back()->with('message', 'Message Sent Successfully');
    }

    public function our_rooms()
    {
        $room = Room::all();
        return view('home.our_rooms', compact('room'));
    }


    public function hotel_gallery()
    {
        $gallery = Gallery::all();
        return view('home.hotel_gallery', compact('gallery'));
    }

    public function contact_us()
    {
        return view('home.contact_us');
    }

    public function search_room(Request $request)
    {
        $request->validate([
            'checkin' => 'required|date',
            'checkout' => 'required|date|after_or_equal:checkin',
        ]);

        $checkin_date = $request->checkin;
        $checkout_date = $request->checkout;

        // Find all room IDs that have an overlapping booking
        $booked_room_ids = Booking::where('start_date', '<=', $checkout_date)
                                ->where('end_date', '>=', $checkin_date)
                                ->pluck('room_id')->toArray();

        // Get all rooms that are NOT in the list of booked rooms
        $available_rooms = Room::whereNotIn('id', $booked_room_ids)->get();

        // Return a new view with the available rooms
        return view('home.available_rooms', compact('available_rooms', 'checkin_date', 'checkout_date'));
    }

}
