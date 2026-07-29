@extends('layouts.admin')

@section('title', 'Add New Room Type - Admin')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Add New Room Type</h1>
        <p class="text-white/70 text-[0.95rem]">Define a new room category with its pricing and capacity.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12 max-w-3xl">

    <div class="mb-6">
        <a href="{{ route('admin.room-types.index') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Room Types
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

        <form action="{{ route('admin.room-types.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Display Name --}}
                <div class="md:col-span-2">
                    <label for="display_name" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Room Type Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="display_name" name="display_name" value="{{ old('display_name') }}"
                           required placeholder="e.g. Deluxe Double"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    <p class="text-xs text-gray-400 mt-1">A URL-friendly slug will be generated automatically from this name.</p>
                    @error('display_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Capacities --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="adult_capacity" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                            Adults <span class="text-red-400">*</span>
                        </label>
                        <input type="number" id="adult_capacity" name="adult_capacity" value="{{ old('adult_capacity', 2) }}"
                               required min="1" max="10"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                        @error('adult_capacity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="child_capacity" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                            Children <span class="text-red-400">*</span>
                        </label>
                        <input type="number" id="child_capacity" name="child_capacity" value="{{ old('child_capacity', 0) }}"
                               required min="0" max="10"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                        @error('child_capacity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Price Per Night --}}
                <div>
                    <label for="price_per_night" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Price Per Night (USD) <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">$</span>
                        <input type="number" id="price_per_night" name="price_per_night"
                               value="{{ old('price_per_night') }}"
                               required min="0" step="0.01" placeholder="0.00"
                               class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    </div>
                    @error('price_per_night') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label for="description" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Description <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe the room type, amenities, view, etc."
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] resize-none">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Images --}}
                <div class="md:col-span-2">
                    <label for="images" class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Room Images <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-hotel-gold/10 file:text-hotel-dark hover:file:bg-hotel-gold/20">
                    <p class="text-xs text-gray-400 mt-1">You can upload multiple images (max 2MB each).</p>
                    @error('images.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Visibility --}}
                <div class="md:col-span-2 mt-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_visible" value="1" class="peer sr-only" {{ old('is_visible', true) ? 'checked' : '' }}>
                            <div class="block h-6 w-10 rounded-full bg-gray-300 transition peer-checked:bg-hotel-gold"></div>
                            <div class="dot absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-4"></div>
                        </div>
                        <div class="text-[0.95rem] font-medium text-gray-700">
                            Show on Guest Page <span class="text-gray-400 font-normal ml-1">(uncheck to hide this room type from the public booking page)</span>
                        </div>
                    </label>
                </div>

                {{-- Mam Sanora QR --}}
                <div class="md:col-span-2 mt-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="use_mam_sanora_qr" value="1" class="peer sr-only" {{ old('use_mam_sanora_qr', false) ? 'checked' : '' }}>
                            <div class="block h-6 w-10 rounded-full bg-gray-300 transition peer-checked:bg-hotel-gold"></div>
                            <div class="dot absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-4"></div>
                        </div>
                        <div class="text-[0.95rem] font-medium text-gray-700">
                            Use Mam Sanora Dynamic QR <span class="text-gray-400 font-normal ml-1">(check to use Mam Sanora's KHQR instead of the default payment flow)</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.room-types.index') }}"
                   class="px-6 py-3 rounded-xl font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-hotel-dark hover:bg-hotel-accent text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-hotel-dark/20 flex items-center">
                    <i class="bi bi-save mr-2"></i> Save Room Type
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
