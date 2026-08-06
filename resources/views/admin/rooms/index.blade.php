@extends('layouts.admin')

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

    <div class="mb-6 flex flex-wrap gap-3 justify-between items-center">
        <a href="{{ route('admin.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('admin.room-types.index') }}" class="bg-white hover:bg-gray-50 text-hotel-dark border border-gray-200 px-5 py-2.5 rounded-xl font-semibold transition-colors flex items-center">
                <i class="bi bi-tag mr-2 text-hotel-gold"></i> Room Types
            </a>
            <a href="{{ route('admin.rooms.bulk-create') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-5 py-2.5 rounded-xl font-semibold transition-colors flex items-center shadow-sm">
                <i class="bi bi-copy mr-2"></i> Bulk Generate
            </a>
            <a href="{{ route('admin.rooms.create') }}" class="bg-hotel-gold hover:bg-yellow-600 text-white px-5 py-2.5 rounded-xl font-semibold transition-colors flex items-center shadow-lg shadow-hotel-gold/20">
                <i class="bi bi-plus-lg mr-2"></i> Add Room
            </a>
        </div>
    </div>


    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-5 mb-6">
        <form method="GET" action="{{ route('admin.rooms.index') }}" class="flex flex-wrap gap-4 items-end">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-[0.8rem] uppercase tracking-wider font-semibold text-gray-500 mb-2">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="bi bi-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Room No..." class="pl-10 w-full rounded-xl border-gray-300 focus:border-hotel-gold focus:ring focus:ring-hotel-gold/20 transition-colors">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="w-full md:w-48">
                <label for="status" class="block text-[0.8rem] uppercase tracking-wider font-semibold text-gray-500 mb-2">Status</label>
                <select name="status" id="status" class="w-full rounded-xl border-gray-300 focus:border-hotel-gold focus:ring focus:ring-hotel-gold/20 transition-colors">
                    <option value="">All Statuses</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="cleaning" {{ request('status') === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>

            <!-- Type Filter -->
            <div class="w-full md:w-56">
                <label for="room_type_id" class="block text-[0.8rem] uppercase tracking-wider font-semibold text-gray-500 mb-2">Room Type</label>
                <select name="room_type_id" id="room_type_id" class="w-full rounded-xl border-gray-300 focus:border-hotel-gold focus:ring focus:ring-hotel-gold/20 transition-colors">
                    <option value="">All Types</option>
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}" {{ request('room_type_id') == $type->id ? 'selected' : '' }}>{{ $type->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Keep Sort State -->
            @if(request()->has('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="dir" value="{{ request('dir') }}">
            @endif

            <!-- Submit & Reset -->
            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="bg-hotel-dark hover:bg-black text-white px-5 py-2.5 rounded-xl font-semibold transition-colors flex-1 md:flex-none">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'room_type_id', 'sort']))
                    <a href="{{ route('admin.rooms.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl font-medium transition-colors flex items-center justify-center">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div x-data="{
        selected: [],
        selectAll: false,
        toggleAll() {
            if (this.selectAll) {
                this.selected = {{ $rooms->pluck('id')->toJson() }};
            } else {
                this.selected = [];
            }
        }
    }">
        <div class="mb-4 flex justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-100" x-show="selected.length > 0" x-cloak>
            <span class="text-sm text-gray-600 font-semibold"><span x-text="selected.length"></span> rooms selected</span>
            <form action="{{ route('admin.rooms.bulk-destroy') }}" method="POST" class="inline">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="button" @click="$dispatch('open-confirm', { message: 'Permanently delete ' + selected.length + ' rooms? This cannot be undone.', action: () => $el.closest('form').submit() })" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center shadow-sm">
                    <i class="bi bi-trash mr-2"></i>Delete Selected
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4 w-12 text-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll" class="rounded border-gray-300 text-hotel-gold focus:ring-hotel-gold cursor-pointer w-4 h-4">
                            </th>
                            <th class="px-5 py-4 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'room_number', 'dir' => request('sort') === 'room_number' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800 transition-colors">
                                    Room No.
                                    @if(request('sort', 'room_number') === 'room_number')
                                        <i class="bi bi-chevron-{{ request('dir', 'asc') === 'asc' ? 'up' : 'down' }} text-[0.65rem] text-hotel-gold"></i>
                                    @endif
                                </a>
                            </th>
                        <th class="px-5 py-4 font-semibold">Type</th>
                        <th class="px-5 py-4 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'adult_capacity', 'dir' => request('sort') === 'adult_capacity' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800 transition-colors">
                                    Capacity
                                    @if(request('sort') === 'adult_capacity')
                                        <i class="bi bi-chevron-{{ request('dir') === 'asc' ? 'up' : 'down' }} text-[0.65rem] text-hotel-gold"></i>
                                    @endif
                                </a>
                        </th>
                        <th class="px-5 py-4 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'dir' => request('sort') === 'price' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-800 transition-colors">
                                    Price / Night
                                    @if(request('sort') === 'price')
                                        <i class="bi bi-chevron-{{ request('dir') === 'asc' ? 'up' : 'down' }} text-[0.65rem] text-hotel-gold"></i>
                                    @endif
                                </a>
                        </th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rooms as $room)
                    <tr class="hover:bg-gray-50/50 transition-colors" :class="{'bg-yellow-50/30': selected.includes({{ $room->id }})}">
                        <td class="px-5 py-4 text-center">
                            <input type="checkbox" x-model="selected" value="{{ $room->id }}" class="rounded border-gray-300 text-hotel-gold focus:ring-hotel-gold cursor-pointer w-4 h-4">
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <strong class="font-playfair text-hotel-dark text-lg">{{ $room->room_number }}</strong>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-800 text-[0.95rem]">{{ $room->displayType() }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="text-gray-700">
                                <i class="bi bi-people text-gray-400 mr-1"></i>{{ $room->roomType?->adult_capacity }}A / {{ $room->roomType?->child_capacity }}C
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-hotel-gold">${{ number_format($room->roomType?->price_per_night ?? 0, 2) }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($room->current_status === 'available')
                                <span class="bg-green-100 text-green-800 text-[0.75rem] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Available</span>
                            @elseif($room->current_status === 'occupied')
                                <span class="bg-blue-100 text-blue-800 text-[0.75rem] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Occupied</span>
                            @elseif($room->current_status === 'cleaning')
                                <span class="bg-yellow-100 text-yellow-800 text-[0.75rem] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Cleaning</span>
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
                                    <button type="button" x-data @click.prevent="$dispatch('open-confirm', { message: 'Permanently delete this room?', action: () => $el.closest('form').submit() })" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors border border-red-100" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-gray-500">No rooms found in the system.</td>
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
</div>

@endsection
