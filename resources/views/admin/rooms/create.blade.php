@extends('layouts.public')

@section('title', 'Add New Room - Admin')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Add New Room</h1>
        <p class="text-white/70 text-[0.95rem]">Create a new room in the hotel inventory.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12 max-w-4xl">

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.rooms.index') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Rooms
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8">

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.rooms.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Room Number --}}
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Room Number</label>
                    <input type="text" name="room_number" value="{{ old('room_number') }}" required placeholder="e.g. 101"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    @error('room_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Room Type (Enum) --}}
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Room Type</label>
                    <select name="room_type" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                        <option value="">Select a Room Type...</option>
                        <option value="standard_twin"   {{ old('room_type') === 'standard_twin'   ? 'selected' : '' }}>Standard Twin</option>
                        <option value="standard_double" {{ old('room_type') === 'standard_double' ? 'selected' : '' }}>Standard Double</option>
                        <option value="deluxe_double"   {{ old('room_type') === 'deluxe_double'   ? 'selected' : '' }}>Deluxe Double</option>
                    </select>
                    @error('room_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Capacity --}}
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Capacity (Max Guests)</label>
                    <input type="number" name="capacity" value="{{ old('capacity', 2) }}" required min="1" max="10"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    @error('capacity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Price Per Night --}}
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Price Per Night (USD)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                        <input type="number" name="price_per_night" value="{{ old('price_per_night') }}" required step="0.01" min="0"
                               class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    </div>
                    @error('price_per_night') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Initial Status --}}
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Initial Status</label>
                    <select name="current_status" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                        <option value="available"    {{ old('current_status', 'available') === 'available'    ? 'selected' : '' }}>Available</option>
                        <option value="occupied"     {{ old('current_status') === 'occupied'     ? 'selected' : '' }}>Occupied</option>
                        <option value="maintenance"  {{ old('current_status') === 'maintenance'  ? 'selected' : '' }}>Maintenance</option>
                    </select>
                    @error('current_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Description (Optional)</label>
                <textarea name="description" rows="4"
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">{{ old('description') }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-hotel-dark hover:bg-hotel-accent text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-hotel-dark/20 flex items-center">
                    <i class="bi bi-save mr-2"></i> Save Room
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
