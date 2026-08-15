import re

with open(r'd:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\resources\views\livewire\reception\dashboard.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

multi_room_html = r'''@php  = ->bookingRooms->isNotEmpty(); @endphp
                                        @if()
                                            <div x-data="{ open: false }" class="relative">
                                                <div class="text-gray-800 font-medium text-sm leading-snug">
                                                    @foreach(->bookingRooms as )
                                                        <span>{{ ->roomType?->display_name ?? '—' }}
                                                            @if(->quantity > 1)<span class="text-hotel-gold font-bold">×{{ ->quantity }}</span>@endif
                                                        </span>@if(!->last)<span class="text-gray-300 mx-1">+</span>@endif
                                                    @endforeach
                                                </div>
                                                <button type="button" @click="open = !open"
                                                        class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                                    <i class="bi bi-door-closed"></i> View Rooms
                                                </button>
                                                <div x-show="open" x-cloak @click.outside="open = false"
                                                     class="absolute right-0 top-full mt-1 z-30 bg-white border border-gray-200 rounded-xl shadow-xl p-3 w-56 text-xs text-left">
                                                    <div class="font-bold text-gray-700 mb-2 flex items-center gap-1">
                                                        <i class="bi bi-building text-hotel-gold"></i> Assigned Rooms
                                                    </div>
                                                    @foreach(->bookingRooms as )
                                                        <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-0">
                                                            <span class="text-gray-600 font-medium">{{ ->roomType?->display_name ?? '—' }}</span>
                                                            <span class="font-bold text-gray-900">
                                                                @if(->room)
                                                                    Rm {{ ->room->room_number }}
                                                                @else
                                                                    <span class="text-amber-600">TBA</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            '''

# Patterns
# 1. <span class="font-bold text-gray-800">Room {{ ->room?->room_number ?? '-' }}</span>
p1 = r'(<span class="font-bold text-gray-800">)Room \{\{ \->room\?->room_number \?\? \'-\' \}\}(<\/span>)'
content = re.sub(p1, multi_room_html + r'\1Room {{ ->room?->room_number ?? \'-\' }}\2\n                                        @endif', content)

# 2. <div class="text-gray-700 text-sm font-medium">Room {{ ->room?->room_number ?? '-' }}</div>
p2 = r'(<div class="text-gray-700 text-sm font-medium">)Room \{\{ \->room\?->room_number \?\? \'-\' \}\}(<\/div>)'
content = re.sub(p2, multi_room_html + r'\1Room {{ ->room?->room_number ?? \'-\' }}\2\n                                        @endif', content)

# 3. <div class="text-xs font-bold text-gray-700">Rm {{ ->room?->room_number ?? '-' }}</div>
p3 = r'(<div class="text-xs font-bold text-gray-700">)Rm \{\{ \->room\?->room_number \?\? \'-\' \}\}(<\/div>)'
content = re.sub(p3, multi_room_html + r'\1Rm {{ ->room?->room_number ?? \'-\' }}\2\n                                        @endif', content)

with open(r'd:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\resources\views\livewire\reception\dashboard.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Replaced!")
