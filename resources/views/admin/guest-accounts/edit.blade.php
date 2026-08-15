@extends('layouts.admin')

@section('title', 'Manage Account — ' . $guestAccount->guest->full_name)

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Manage Guest Account</h1>
        <p class="text-white/70 text-[0.95rem]">Update login credentials for {{ $guestAccount->guest->full_name }}.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12 max-w-2xl">

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.guest-accounts.index') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Accounts
        </a>
        <a href="{{ route('admin.guests.show', $guestAccount->guest->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
            <i class="bi bi-person-lines-fill"></i> View Full Guest Profile
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

        <form action="{{ route('admin.guest-accounts.update', $guestAccount) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-blue-50/50 rounded-xl p-5 border border-blue-100 mb-2">
                <h3 class="text-blue-900 font-semibold mb-1 flex items-center"><i class="bi bi-info-circle mr-2"></i> About Account Management</h3>
                <p class="text-blue-700 text-[0.85rem]">This page only manages the <strong>login credentials</strong>. To change the guest's name or contact numbers used for reservations, please view their full guest profile.</p>
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $guestAccount->email) }}" placeholder="e.g. guest@example.com"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>



            {{-- New Password (optional) --}}
            <div class="bg-gray-50 rounded-xl p-5 border border-dashed border-gray-200 mt-4">
                <p class="text-[0.85rem] text-gray-500 mb-4"><i class="bi bi-lock mr-1"></i> Leave both fields blank to keep the current password.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">New Password</label>
                        <input type="password" name="password" autocomplete="new-password"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-hotel-dark hover:bg-hotel-accent text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-hotel-dark/20 flex items-center">
                    <i class="bi bi-save mr-2"></i> Update Credentials
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
