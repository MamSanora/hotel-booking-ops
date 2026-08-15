@extends('layouts.cleaner')

@section('title', 'Profile Settings')
@section('page_title', 'Profile Settings')

@section('content')
<div class="p-5 md:p-8 space-y-6 bg-gray-50/30 min-h-screen">

    <div class="max-w-2xl space-y-6">

        {{-- Profile Information Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-1">Profile Information</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Update your contact details and login username.</p>
            </div>

            <div class="px-6 pb-6 pt-5">
                <form method="POST" action="{{ route('cleaner.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    {{-- Full Name --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $staff->full_name) }}"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Username --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                        <input type="text" name="username" value="{{ old('username', $staff->username) }}"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Phone Number <span class="font-normal text-gray-400">(optional)</span></label>
                        <input type="tel" name="phone" value="{{ old('phone', $staff->phone) }}"
                               placeholder="+855 12 345 678"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Email Address <span class="font-normal text-gray-400">(optional)</span></label>
                        <input type="email" name="email" value="{{ old('email', $staff->email) }}"
                               placeholder="you@example.com"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-1">Change Password</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ensure your account uses a strong password.</p>
            </div>

            <div class="px-6 pb-6 pt-5">
                <form method="POST" action="{{ route('cleaner.profile.password') }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Current Password</label>
                        <input type="password" name="current_password"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        @error('current_password', 'updatePassword') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">New Password</label>
                        <input type="password" name="password"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        @error('password', 'updatePassword') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-gray-700 hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
