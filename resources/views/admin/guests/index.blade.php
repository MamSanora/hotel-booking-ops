@extends('layouts.admin')

@section('title', 'Guests List - Admin Dashboard')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Guests List</h1>
        <p class="text-white/70 text-[0.95rem]">All guests — including walk-ins, phone bookings, and registered accounts.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    <div class="mb-6 flex flex-col md:flex-row md:justify-between items-start md:items-center gap-4">
        <a href="{{ route('admin.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    {{-- Search Form --}}
    <form action="{{ route('admin.guests.index') }}" method="GET" class="bg-white p-5 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] mb-6 flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Search Guests</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Email, or Phone..." class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] px-4 py-2.5 bg-gray-50">
        </div>
        <div class="w-full md:w-56">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sort By</label>
            <select name="sort" class="w-full border-gray-200 rounded-xl focus:ring-hotel-gold focus:border-hotel-gold text-[0.95rem] px-4 py-2.5 bg-gray-50">
                <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>Newest Profiles</option>
                <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>Oldest Profiles</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Guest Name (A-Z)</option>
                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Guest Name (Z-A)</option>
                <option value="bookings_desc" {{ request('sort') == 'bookings_desc' ? 'selected' : '' }}>Most Bookings</option>
                <option value="bookings_asc" {{ request('sort') == 'bookings_asc' ? 'selected' : '' }}>Fewest Bookings</option>
            </select>
        </div>
        <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
            <button type="submit" class="bg-hotel-gold hover:bg-hotel-gold-hover text-white px-5 py-2.5 rounded-xl font-semibold text-[0.95rem] transition-colors shadow-sm shadow-hotel-gold/20 flex-1 md:flex-none flex items-center justify-center gap-2">
                <i class="bi bi-search"></i> <span>Search</span>
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.guests.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-semibold text-[0.95rem] transition-colors flex items-center justify-center shrink-0" title="Clear Filters">
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
                        <th class="px-5 py-4 font-semibold">Guest ID</th>
                        <th class="px-5 py-4 font-semibold">Name & Contact</th>
                        <th class="px-5 py-4 font-semibold">Account Type</th>
                        <th class="px-5 py-4 font-semibold">Total Bookings</th>
                        <th class="px-5 py-4 font-semibold">Profile Created</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($guests as $guest)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-mono text-gray-600">#{{ str_pad($guest->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-800 text-[0.95rem]">
                                {{ $guest->full_name }}
                            </div>
                            @if($guest->guestAuth)
                                <div class="text-gray-500 text-[0.8rem] mt-0.5">
                                    <i class="bi bi-envelope mr-1"></i> {{ $guest->guestAuth->email }}
                                </div>
                            @endif
                            @if($phone = $guest->primaryPhone())
                                <div class="text-gray-500 text-[0.8rem]">
                                    <i class="bi bi-telephone mr-1"></i> {{ $phone }}
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($guest->hasAccount())
                                <span class="bg-blue-50 text-blue-700 text-[0.75rem] font-bold px-3 py-1 rounded-full border border-blue-200">Registered</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 text-[0.75rem] font-bold px-3 py-1 rounded-full border border-gray-200">Walk-in / Phone</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-semibold text-gray-800">{{ $guest->bookings_count }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="text-gray-600 text-[0.9rem]">{{ $guest->created_at->format('M d, Y') }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('admin.guests.show', $guest->id) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors inline-flex items-center gap-1">
                                <i class="bi bi-eye"></i> View Profile
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-gray-500">No guests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100 bg-gray-50">
            {{ $guests->links() }}
        </div>
    </div>
</div>

@endsection
