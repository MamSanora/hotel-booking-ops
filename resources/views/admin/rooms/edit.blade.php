@extends('layouts.public')

@section('title', 'Edit Room — ' . $room->room_number)

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Edit Room</h1>
        <p class="text-white/70 text-[0.95rem]">Update details for Room {{ $room->room_number }}</p>
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

        <form action="{{ route('admin.rooms.update', $room) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Room Number --}}
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Room Number</label>
                    <input type="text" name="room_number" value="{{ old('room_number', $room->room_number) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    @error('room_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Room Type (FK to room_types) --}}
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Room Type</label>
                    <select name="room_type_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ old('room_type_id', $room->room_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->display_name }} — ${{ number_format($type->price_per_night, 2) }}/night ({{ $type->capacity }} guests)
                            </option>
                        @endforeach
                    </select>
                    @error('room_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-1">Price and capacity are defined by the room type. To change them, edit the room type.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-hotel-dark hover:bg-hotel-accent text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-hotel-dark/20 flex items-center">
                    <i class="bi bi-save mr-2"></i> Update Room
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
