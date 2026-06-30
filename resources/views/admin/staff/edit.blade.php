@extends('layouts.public')

@section('title', 'Edit Staff — ' . $member->full_name)

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Edit Staff Member</h1>
        <p class="text-white/70 text-[0.95rem]">Update details for {{ $member->full_name }}</p>
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

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm mb-6 flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-green-500"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.staff.update', $member) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Full Name --}}
            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name', $member->full_name) }}" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Username --}}
            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username" value="{{ old('username', $member->username) }}" required
                       autocomplete="username"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] font-mono">
                @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Role</label>
                <select name="role" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                    <option value="receptionist" {{ old('role', $member->role) === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                </select>
                @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- New Password (optional) --}}
            <div class="bg-gray-50 rounded-xl p-5 border border-dashed border-gray-200">
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
                    <i class="bi bi-save mr-2"></i> Update Staff Member
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
