@extends('layouts.admin')

@section('title', 'Guest Accounts - Admin Dashboard')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Guest Accounts</h1>
        <p class="text-white/70 text-[0.95rem]">Registered online accounts — guests who signed up via the website.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    <div class="mb-6 flex flex-col md:flex-row md:justify-between items-start md:items-center gap-4">
        <a href="{{ route('admin.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3">
                <i class="bi bi-check-circle text-green-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-600 hover:text-green-800 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    {{-- Stats Banner --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl px-6 py-4 mb-6 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-blue-100 border border-blue-200 flex items-center justify-center shrink-0">
            <i class="bi bi-person-check-fill text-blue-600 text-lg"></i>
        </div>
        <div>
            <p class="font-semibold text-blue-900 text-[0.95rem]">{{ $accounts->total() }} Registered Account{{ $accounts->total() !== 1 ? 's' : '' }}</p>
            <p class="text-blue-700 text-[0.82rem]">These guests have login credentials and can manage their bookings online.</p>
        </div>
    </div>

    {{-- Search Form --}}
    <form action="{{ route('admin.guest-accounts.index') }}" method="GET" class="bg-white p-5 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] mb-6 flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Search Accounts</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Email..." class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] px-4 py-2.5 bg-gray-50">
        </div>
        <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
            <button type="submit" class="bg-hotel-gold hover:bg-hotel-gold-hover text-white px-5 py-2.5 rounded-xl font-semibold text-[0.95rem] transition-colors shadow-sm shadow-hotel-gold/20 flex-1 md:flex-none flex items-center justify-center gap-2">
                <i class="bi bi-search"></i> <span>Search</span>
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.guest-accounts.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-semibold text-[0.95rem] transition-colors flex items-center justify-center shrink-0" title="Clear Filters">
                    <i class="bi bi-x-circle"></i>
                </a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Guest</th>
                        <th class="px-5 py-4 font-semibold">Email</th>
                        <th class="px-5 py-4 font-semibold">Total Bookings</th>
                        <th class="px-5 py-4 font-semibold">Registered</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($accounts as $guest)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center shrink-0">
                                    <span class="text-blue-600 font-bold text-sm">{{ strtoupper(substr($guest->full_name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800 text-[0.95rem]">{{ $guest->full_name }}</div>
                                    <div class="text-gray-400 text-[0.78rem] font-mono">#{{ str_pad($guest->id, 5, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-gray-700 text-[0.9rem]">
                                <i class="bi bi-envelope text-gray-400 mr-1"></i>
                                {{ $guest->guestAuth->email }}
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-semibold text-gray-800">{{ $guest->bookings_count }}</span>
                            <span class="text-gray-400 text-[0.8rem] ml-1">booking{{ $guest->bookings_count !== 1 ? 's' : '' }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="text-gray-600 text-[0.9rem]">{{ $guest->created_at->format('M d, Y') }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.guest-accounts.edit', $guest->guestAuth->id) }}" class="bg-blue-50 hover:bg-blue-100 text-blue-600 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors border border-blue-100" title="Manage Account">
                                    <i class="bi bi-gear"></i> Manage
                                </a>
                                <form action="{{ route('admin.guest-accounts.destroy', $guest->guestAuth->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="button" x-data @click.prevent="$dispatch('open-confirm', { message: 'Permanently remove login access for this guest? Their bookings will remain intact.', action: (function(f) { return () => f.submit(); })($el.closest('form')) })" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors border border-red-100" title="Remove Access">
                                        <i class="bi bi-person-x"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400">
                                <i class="bi bi-person-x text-3xl"></i>
                                <p class="font-medium">No registered accounts found.</p>
                                @if(request()->filled('search'))
                                    <p class="text-sm">Try adjusting your search terms.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100 bg-gray-50">
            {{ $accounts->links() }}
        </div>
    </div>
</div>

@endsection
