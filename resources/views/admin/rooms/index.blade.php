@extends('layouts.public')

@section('title', 'Manage Rooms - Admin Dashboard')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Manage Rooms</h1>
        <p class="text-white/70 text-[0.95rem]">Add, edit, or remove hotel rooms and update their status.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">
    
    {{-- Alerts --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-8">
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
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-8">
            <div class="flex items-center gap-3">
                <i class="bi bi-exclamation-triangle text-red-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
        <a href="{{ route('admin.rooms.create') }}" class="bg-hotel-gold hover:bg-yellow-600 text-white px-5 py-2.5 rounded-xl font-semibold transition-colors flex items-center shadow-lg shadow-hotel-gold/20">
            <i class="bi bi-plus-lg mr-2"></i> Add New Room
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Room No.</th>
                        <th class="px-5 py-4 font-semibold">Type</th>
                        <th class="px-5 py-4 font-semibold">Capacity</th>
                        <th class="px-5 py-4 font-semibold">Price / Night</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rooms as $room)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <strong class="font-playfair text-hotel-dark text-lg">{{ $room->room_number }}</strong>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-800 text-[0.95rem]">{{ $room->displayType() }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="text-gray-700">
                                <i class="bi bi-people text-gray-400 mr-1"></i>{{ $room->capacity }} guests
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-hotel-gold">${{ number_format($room->price_per_night, 2) }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($room->current_status === 'available')
                                <span class="bg-green-100 text-green-800 text-[0.75rem] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Available</span>
                            @elseif($room->current_status === 'occupied')
                                <span class="bg-blue-100 text-blue-800 text-[0.75rem] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Occupied</span>
                            @else
                                <span class="bg-red-100 text-red-800 text-[0.75rem] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Maintenance</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="bg-blue-50 hover:bg-blue-100 text-blue-600 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors border border-blue-100" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Permanently delete this room?')" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors border border-red-100" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-gray-500">No rooms found in the system.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-5 border-t border-gray-100 bg-gray-50">
            {{ $rooms->links() }}
        </div>
    </div>
</div>

@endsection
