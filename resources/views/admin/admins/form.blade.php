@extends('layouts.admin')

@section('title', ($admin->exists ? 'Edit Admin' : 'Add Admin') . ' - Admin Dashboard')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">{{ $admin->exists ? 'Edit Admin' : 'Add Admin Account' }}</h1>
        <p class="text-white/70 text-[0.95rem]">
            {{ $admin->exists ? 'Update the details for ' . $admin->full_name . '.' : 'Create a new master administrator account.' }}
        </p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">
    <div class="max-w-2xl mx-auto">
        
        <div class="mb-6">
            <a href="{{ route('admin.admins.index') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors w-fit">
                <i class="bi bi-arrow-left mr-2"></i> Back to Admins
            </a>
        </div>

        <form action="{{ $admin->exists ? route('admin.admins.update', $admin->id) : route('admin.admins.store') }}" method="POST" class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8">
            @csrf
            @if($admin->exists)
                @method('PUT')
            @endif

            @if ($errors->any())
                <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4 mb-6">
                    <div class="font-semibold mb-1 flex items-center gap-2"><i class="bi bi-exclamation-triangle"></i> Please fix the following errors:</div>
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $admin->full_name) }}" required class="w-full border-gray-300 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Username</label>
                    <input type="text" name="username" value="{{ old('username', $admin->username) }}" required class="w-full border-gray-300 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold">
                </div>

                {{-- Role selection removed to enforce single superadmin policy. New accounts are always created as standard admins. --}}

                <div class="border-t border-gray-100 pt-5 mt-5">
                    <h3 class="font-bold text-gray-800 mb-4">{{ $admin->exists ? 'Change Password (Optional)' : 'Set Password' }}</h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                            <input type="password" name="password" {{ !$admin->exists ? 'required' : '' }} class="w-full border-gray-300 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold">
                            @if($admin->exists)
                                <p class="text-xs text-gray-500 mt-1">Leave blank to keep the current password.</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                            <input type="password" name="password_confirmation" {{ !$admin->exists ? 'required' : '' }} class="w-full border-gray-300 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.admins.index') }}" class="px-5 py-2.5 rounded-xl font-semibold text-gray-600 hover:bg-gray-100 transition-colors">Cancel</a>
                <button type="submit" class="bg-hotel-gold hover:bg-hotel-gold-hover text-white px-6 py-2.5 rounded-xl font-semibold transition-colors shadow-sm shadow-hotel-gold/20 flex items-center gap-2">
                    <i class="bi bi-save"></i> 
                    <span>{{ $admin->exists ? 'Update Admin' : 'Create Admin' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
