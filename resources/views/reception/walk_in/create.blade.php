@extends('layouts.public')

@section('title', 'New Walk-In Booking')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">New Walk-In Booking</h1>
        <p class="text-white/70 text-[0.95rem]">Create an instant reservation for guests at the front desk.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12 max-w-5xl">
    
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('reception.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center">
            <i class="bi bi-exclamation-triangle mr-3 text-xl"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Step 1: Check Availability (Form gets submitted back to itself via GET to filter rooms) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 mb-6 border-t-4 border-hotel-gold">
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-4">1. Search Availability</h3>
                
                <form action="{{ route('reception.walkin.create') }}" method="GET" class="space-y-4">
                    <div>
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-1">Check-in</label>
                        <input type="date" name="checkin" value="{{ $checkinDate }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 text-[0.95rem]" required onchange="this.form.submit()">
                    </div>
                    <div>
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-1">Check-out</label>
                        <input type="date" name="checkout" value="{{ $checkoutDate }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 text-[0.95rem]" required onchange="this.form.submit()">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2.5 rounded-xl transition-colors">
                            Refresh Available Rooms
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Step 2: Guest Details & Select Room -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8">
                <h3 class="font-playfair text-xl font-bold text-hotel-dark mb-6">2. Guest Details & Payment</h3>
                
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('reception.walkin.store') }}" method="POST">
                    @csrf
                    {{-- Dates passed from the availability search --}}
                    <input type="hidden" name="check_in_date" value="{{ $checkinDate }}">
                    <input type="hidden" name="check_out_date" value="{{ $checkoutDate }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Guest Full Name</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                        </div>
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                        </div>
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Email (Optional)</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Adults</label>
                                <input type="number" name="adults" value="{{ old('adults', 1) }}" min="1" max="4" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                            </div>
                            <div>
                                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Children</label>
                                <input type="number" name="children" value="{{ old('children', 0) }}" min="0" max="3" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Select Available Room</label>
                        @if($availableRooms->isEmpty())
                            <div class="p-4 bg-orange-50 border border-orange-200 text-orange-800 rounded-xl">
                                No rooms available for the selected dates.
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-2 border border-gray-100 rounded-xl bg-gray-50/50">
                                @foreach($availableRooms as $room)
                                    <label class="flex items-center p-4 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-hotel-gold hover:shadow-md transition-all has-[:checked]:border-hotel-gold has-[:checked]:ring-1 has-[:checked]:ring-hotel-gold">
                                        <input type="radio" name="room_id" value="{{ $room->id }}" class="text-hotel-gold focus:ring-hotel-gold w-4 h-4" required {{ old('room_id') == $room->id ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <div class="font-bold text-gray-800">Room {{ $room->room_number }}</div>
                                            <div class="text-sm text-gray-500">{{ $room->displayType() }}</div>
                                            <div class="text-hotel-gold font-semibold text-sm mt-1">${{ number_format($room->roomType?->price_per_night ?? 0, 2) }}/night</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Payment Tier</label>
                            <select name="payment_tier" id="payment_tier" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]" required>
                                <option value="100" selected>Full Payment (100%)</option>
                                <option value="50">50% Deposit</option>
                                <option value="20">20% Deposit</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Amount Paid Now</label>
                            <input type="number" name="amount_paid" id="amount_paid" value="{{ old('amount_paid', 0) }}" min="0" step="0.01" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]" required>
                        </div>
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Payment Status</label>
                            <select name="payment_status" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]" required>
                                <option value="full">Paid in Full</option>
                                <option value="half">Partial / Deposit</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Payment Method</label>
                            <select name="payment_method" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]" required>
                                <option value="cash">Cash</option>
                                <option value="khqr">KHQR (Bakong)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Special Requests / Notes <span class="font-normal text-gray-400 lowercase">(optional)</span></label>
                        <textarea name="special_requests" rows="2" placeholder="e.g. Extra pillows, late arrival, wake-up call..." class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] resize-none">{{ old('special_requests') }}</textarea>
                    </div>

                    <div class="pt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-hotel-dark hover:bg-hotel-accent text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-hotel-dark/20 flex items-center" {{ $availableRooms->isEmpty() ? 'disabled' : '' }}>
                            <i class="bi bi-calendar-check mr-2"></i> Confirm Walk-In Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
