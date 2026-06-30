@extends('layouts.public')

@section('title', 'Add Staff Member - Admin')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Add Staff Member</h1>
        <p class="text-white/70 text-[0.95rem]">Create a new username-based account for front desk staff.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12 max-w-2xl">

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.staff.index') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Staff List
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

        <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Full Name --}}
            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="e.g. Dara Chan"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Username --}}
            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required placeholder="e.g. dara.chan"
                       autocomplete="username"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] font-mono">
                <p class="text-gray-400 text-xs mt-1">Used to log in. Only letters, numbers, dots, and underscores.</p>
                @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Role</label>
                <select name="role" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    <option value="receptionist" {{ old('role', 'receptionist') === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                </select>
                @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-hotel-dark hover:bg-hotel-accent text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-hotel-dark/20 flex items-center">
                    <i class="bi bi-person-check mr-2"></i> Create Account
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
