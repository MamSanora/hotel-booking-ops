@extends('layouts.reception')

@section('title', 'Profile Settings - Reception Dashboard')
@section('page_title', 'Profile Settings')

@section('content')

<div class="p-5 md:p-8 space-y-6 bg-gray-50/30 min-h-screen">

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="flex justify-between items-center bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <i class="bi bi-check-circle-fill text-emerald-500"></i>
                </div>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 ml-4 shrink-0 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <div class="max-w-2xl space-y-6">
        
        {{-- Profile Information Form --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 pt-6 pb-4 md:px-8 md:pt-8 md:pb-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-lg mb-1">Profile Information</h3>
                <p class="text-sm text-gray-500">Update your account's profile information and username.</p>
            </div>

            <div class="px-6 pb-6 pt-5 md:px-8 md:pb-8 md:pt-6">
                <form method="post" action="{{ route('reception.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('patch')

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $staff->full_name) }}" required class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-sm">
                        @error('full_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Username</label>
                        <input type="text" name="username" value="{{ old('username', $staff->username) }}" required class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-sm">
                        @error('username') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-hotel-gold hover:bg-[#b8935a] text-white px-6 py-2.5 rounded-xl font-semibold transition-colors shadow-sm text-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Update Password Form --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 pt-6 pb-4 md:px-8 md:pt-8 md:pb-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-lg mb-1">Update Password</h3>
                <p class="text-sm text-gray-500">Ensure your account is using a long, random password to stay secure.</p>
            </div>

            <div class="px-6 pb-6 pt-5 md:px-8 md:pb-8 md:pt-6">
                <form method="post" action="{{ route('reception.profile.password') }}" class="space-y-5">
                    @csrf
                    @method('patch')

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Current Password</label>
                        <input type="password" name="current_password" required class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-sm">
                        @error('current_password', 'updatePassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">New Password</label>
                        <input type="password" name="password" required class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-sm">
                        @error('password', 'updatePassword') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-sm">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-hotel-gold hover:bg-[#b8935a] text-white px-6 py-2.5 rounded-xl font-semibold transition-colors shadow-sm text-sm">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
