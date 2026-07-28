@extends('layouts.reception')

@section('title', 'New Walk-In Booking')
@section('page_title', 'New Walk-In Booking')

@section('content')

<div class="p-5 md:p-8 max-w-4xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('reception.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center gap-2 font-medium transition-colors w-fit">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
            <i class="bi bi-exclamation-triangle text-xl"></i>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <div class="font-semibold text-sm mb-1 flex items-center gap-2"><i class="bi bi-exclamation-circle"></i> Please fix the following:</div>
            <ul class="list-disc list-inside text-sm space-y-1 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Single unified card --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] border border-gray-100 overflow-hidden">

        <div class="border-l-4 border-hotel-gold px-6 py-5 bg-gradient-to-r from-amber-50/40 to-white">
            <h2 class="font-playfair text-xl font-bold text-hotel-dark">New Walk-In Booking</h2>
            <p class="text-sm text-gray-400 mt-0.5">Create a proxy booking for a guest at the front desk.</p>
        </div>

        <form action="{{ route('reception.walkin.store') }}" method="POST" class="p-6 md:p-8 space-y-8">
            @csrf

            {{-- ── SECTION 1: Guest Details ────────────────────────────── --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
                    <i class="bi bi-person-fill text-hotel-gold"></i> Guest Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Guest Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" required
                               placeholder="e.g. Dara Meas"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    </div>
                    <div>
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', old('phone')) }}" required
                               placeholder="e.g. 012 345 678"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    </div>
                    <div>
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Booking Origin <span class="text-red-500">*</span></label>
                        <select name="guest_type" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                            <option value="walk-in" {{ old('guest_type', 'walk-in') == 'walk-in' ? 'selected' : '' }}>Walk-in Guest (Desk)</option>
                            <option value="phone"   {{ old('guest_type') == 'phone'   ? 'selected' : '' }}>Phone Reservation</option>
                            <option value="other"   {{ old('guest_type') == 'other'   ? 'selected' : '' }}>Other Proxy Booking</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Email <span class="font-normal text-gray-400 normal-case">(optional)</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="guest@email.com"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Gender</label>
                            <select name="gender" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                                <option value="">—</option>
                                <option value="male"   {{ old('gender') == 'male'   ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other"  {{ old('gender') == 'other'  ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Adults</label>
                            <input type="number" name="adults" value="{{ old('adults', 1) }}" min="1" max="4" required
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] text-center font-bold">
                        </div>
                        <div>
                            <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Children</label>
                            <input type="number" name="children" value="{{ old('children', 0) }}" min="0" max="3" required
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] text-center font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Nationality</label>
                        <input type="text" name="nationality" value="{{ old('nationality', 'Cambodian') }}"
                               placeholder="e.g. Cambodian"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            {{-- ── SECTION 2: Dates, Rooms & Payment (Livewire) ──────── --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
                    <i class="bi bi-calendar-check-fill text-hotel-gold"></i> Dates, Room & Payment
                </h3>
                @livewire('reception.walk-in-rooms')
            </div>

            <hr class="border-gray-100">

            {{-- ── SECTION 3: Special Requests ─────────────────────────── --}}
            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Special Requests / Notes <span class="font-normal text-gray-400 normal-case">(optional)</span>
                </label>
                <textarea name="special_requests" rows="2"
                          placeholder="e.g. Extra pillows, late arrival, wake-up call..."
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] resize-none">{{ old('special_requests') }}</textarea>
            </div>

            {{-- ── SUBMIT ─────────────────────────────────────────────── --}}
            <div class="pt-2 flex justify-end">
                <button type="submit"
                        class="bg-hotel-dark hover:bg-hotel-accent text-white px-8 py-3.5 rounded-xl font-semibold transition-colors shadow-lg shadow-hotel-dark/20 flex items-center gap-2">
                    <i class="bi bi-calendar-check"></i> Confirm Walk-In Booking
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
