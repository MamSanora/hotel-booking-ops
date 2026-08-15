@extends('layouts.admin')

@section('title', 'Admin Accounts - Admin Dashboard')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Admin Accounts</h1>
        <p class="text-white/70 text-[0.95rem]">Manage master administrator accounts and their access.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    <div class="mb-6 flex flex-col md:flex-row md:justify-between items-start md:items-center gap-4">
        <a href="{{ route('admin.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
        <a href="{{ route('admin.admins.create') }}" class="bg-hotel-gold hover:bg-hotel-gold-hover text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors flex items-center gap-2 shadow-sm">
            <i class="bi bi-plus-circle"></i> Add Admin
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Name & Role</th>
                        <th class="px-5 py-4 font-semibold">Username</th>
                        <th class="px-5 py-4 font-semibold">Created Date</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($admins as $admin)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-800 text-[0.95rem] flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($admin->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    {{ $admin->full_name }}
                                    @if(auth()->guard('admin')->id() === $admin->id)
                                        <span class="bg-green-100 text-green-700 text-[0.65rem] font-bold px-2 py-0.5 rounded-full ml-1">You</span>
                                    @endif
                                    <div class="text-[0.75rem] text-gray-500 capitalize mt-0.5">{{ $admin->role }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-gray-600">
                            {{ $admin->username }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-gray-600 text-[0.9rem]">
                            {{ $admin->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.admins.edit', $admin->id) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors" title="Edit Admin">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if(auth()->guard('admin')->id() !== $admin->id)
                                    <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" x-data @click.prevent="$dispatch('open-confirm', { message: 'Delete this admin account?', action: (function(f) { return () => f.submit(); })($el.closest('form')) })" class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors" title="Delete Admin">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-gray-500">No admin accounts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100 bg-gray-50">
            {{ $admins->links() }}
        </div>
    </div>
</div>

@endsection
