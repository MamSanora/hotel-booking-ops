@extends('layouts.public')

@section('title', 'My Profile')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">My Profile</h1>
        <p class="text-white/70 text-[0.95rem]">Manage your personal information and password</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12 max-w-4xl">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">

        {{-- Sidebar --}}
        <div class="md:col-span-4">
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden border border-[#f0ebe2]">
                <div class="p-6 text-center border-b border-gray-100">
                    <div class="w-20 h-20 bg-hotel-gold/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-person-circle text-4xl text-hotel-gold"></i>
                    </div>
                    <h3 class="font-playfair text-xl font-bold text-hotel-dark">{{ $guest->full_name }}</h3>
                    <p class="text-sm text-gray-500">{{ $guestAuth->email }}</p>
                    @if($guest->nationality)
                        <p class="text-xs text-gray-400 mt-1 flex items-center justify-center gap-1">
                            <i class="bi bi-globe"></i> {{ $guest->nationality }}
                        </p>
                    @endif
                </div>
                <div class="p-2">
                    <a href="{{ route('guest.dashboard') }}" class="block px-4 py-3 text-sm text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">
                        <i class="bi bi-calendar-check mr-2"></i> My Bookings
                    </a>
                    <a href="{{ route('guest.profile.edit') }}" class="block px-4 py-3 text-sm font-semibold text-hotel-dark bg-hotel-gold/10 rounded-xl transition-colors">
                        <i class="bi bi-person-gear mr-2 text-hotel-gold"></i> Profile Settings
                    </a>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="md:col-span-8 space-y-6">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-green-500"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-red-500"></i> {{ session('error') }}
                </div>
            @endif

            {{-- Update Profile Form (updates the guests table) --}}
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8 border border-[#f0ebe2]">
                <h2 class="font-playfair text-xl font-bold text-hotel-dark mb-6">Profile Information</h2>

                <form action="{{ route('guest.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $guest->full_name) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                            @error('full_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $guestAuth->email) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <select name="gender" id="gender"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                                    <option value="">— Not specified —</option>
                                    <option value="male"   {{ old('gender', $guest->gender) === 'male'   ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $guest->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other"  {{ old('gender', $guest->gender) === 'other'  ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="nationality" class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                                <input type="text" name="nationality" id="nationality" value="{{ old('nationality', $guest->nationality) }}"
                                       placeholder="e.g. Cambodian"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="bg-hotel-dark hover:bg-black text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Update Password Form (updates the guest_auths table) --}}
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8 border border-[#f0ebe2]">
                <h2 class="font-playfair text-xl font-bold text-hotel-dark mb-6">Change Password</h2>

                <form action="{{ route('guest.profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                            @error('current_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="password" id="password"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="bg-hotel-dark hover:bg-black text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-colors">
                                Update Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection
