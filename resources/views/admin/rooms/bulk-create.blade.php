@extends('layouts.admin')

@section('title', 'Bulk Generate Rooms - Admin')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Bulk Generate Rooms</h1>
        <p class="text-white/70 text-[0.95rem]">Sequentially generate multiple physical rooms.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12 max-w-3xl">

    <div class="mb-6">
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

        <form action="{{ route('admin.rooms.bulk-store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Room Type --}}
                <div class="md:col-span-2">
                    <label for="room_type_id" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Room Type <span class="text-red-400">*</span>
                    </label>
                    <select id="room_type_id" name="room_type_id" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] bg-white">
                        <option value="">-- Select Room Type --</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}" {{ old('room_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->display_name }} (${{ $type->price_per_night }}/night)
                            </option>
                        @endforeach
                    </select>
                    @error('room_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Prefix --}}
                <div>
                    <label for="prefix" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Number Prefix <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" id="prefix" name="prefix" value="{{ old('prefix') }}"
                           placeholder="e.g. A, B"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                </div>

                {{-- Count --}}
                <div>
                    <label for="count" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Number of Rooms <span class="text-red-400">*</span>
                    </label>
                    <input type="number" id="count" name="count" value="{{ old('count', 10) }}"
                           required min="1" max="100"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                </div>
                
                {{-- Start Number --}}
                <div class="md:col-span-2">
                    <label for="start_number" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Start Sequence Number <span class="text-red-400">*</span>
                    </label>
                    <input type="number" id="start_number" name="start_number" value="{{ old('start_number', 101) }}"
                           required min="1"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    <p class="text-xs text-gray-400 mt-1">If Prefix is A, Count is 3, and Start is 101, it generates A101, A102, A103.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.rooms.index') }}"
                   class="px-6 py-3 rounded-xl font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-hotel-dark hover:bg-hotel-accent text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-hotel-dark/20 flex items-center">
                    <i class="bi bi-copy mr-2"></i> Generate Rooms
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
