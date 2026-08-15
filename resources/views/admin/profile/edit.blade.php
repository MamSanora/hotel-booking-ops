@extends('layouts.admin')

@section('title', 'Profile Settings - Admin Dashboard')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Profile Settings</h1>
        <p class="text-white/70 text-[0.95rem]">Update your personal information and change your password.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">
    <div class="max-w-2xl mx-auto space-y-8">
        
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-2">
                <div class="flex items-center gap-3">
                    <i class="bi bi-check-circle text-green-600 text-lg"></i>
                    <span class="text-[0.95rem] font-medium">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-green-600 hover:text-green-800 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-2">
                <div class="flex items-center gap-3">
                    <i class="bi bi-exclamation-triangle text-red-600 text-lg"></i>
                    <span class="text-[0.95rem] font-medium">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-red-600 hover:text-red-800 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        {{-- Profile Information Form --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
            <div class="px-6 pt-6 pb-4 md:px-8 md:pt-8 md:pb-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-lg mb-1">Profile Information</h3>
                <p class="text-sm text-gray-500">Update your account's profile information and username.</p>
            </div>

            <div class="px-6 pb-6 pt-5 md:px-8 md:pb-8 md:pt-6">
                <form method="post" action="{{ route('admin.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('patch')

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $admin->full_name) }}" required class="w-full border-gray-300 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold">
                        @error('full_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Username</label>
                        <input type="text" name="username" value="{{ old('username', $admin->username) }}" required class="w-full border-gray-300 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold">
                        @error('username') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4 flex items-center gap-4">
                        <button type="submit" class="bg-hotel-gold hover:bg-hotel-gold-hover text-white px-6 py-2.5 rounded-xl font-semibold transition-colors shadow-sm shadow-hotel-gold/20">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Update Password Form --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
            <div class="px-6 pt-6 pb-4 md:px-8 md:pt-8 md:pb-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-lg mb-1">Update Password</h3>
                <p class="text-sm text-gray-500">Ensure your account is using a long, random password to stay secure.</p>
            </div>

            <div class="px-6 pb-6 pt-5 md:px-8 md:pb-8 md:pt-6">
                <form method="post" action="{{ route('admin.profile.password') }}" class="space-y-5">
                    @csrf
                    @method('patch')

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Current Password</label>
                        <input type="password" name="current_password" required class="w-full border-gray-300 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold">
                        @error('current_password', 'updatePassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">New Password</label>
                        <input type="password" name="password" required class="w-full border-gray-300 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold">
                        @error('password', 'updatePassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="w-full border-gray-300 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold">
                    </div>

                    <div class="pt-4 flex items-center gap-4">
                        <button type="submit" class="bg-hotel-gold hover:bg-hotel-gold-hover text-white px-6 py-2.5 rounded-xl font-semibold transition-colors shadow-sm shadow-hotel-gold/20">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
