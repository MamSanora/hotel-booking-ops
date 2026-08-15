import re

with open(r'd:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\resources\views\livewire\reception\dashboard.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# The replacement template
multi_room_html = r'''                            @php  = ->bookingRooms->isNotEmpty(); @endphp
                            @if()
                                {{-- Multi-type: show type list + rooms popover --}}
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
                                        <i class="bi bi-door-closed"></i>
                                        View Rooms
                                    </button>
                                    {{-- Popover --}}
                                    <div x-show="open" x-cloak @click.outside="open = false"
                                         class="absolute left-0 top-full mt-1 z-30 bg-white border border-gray-200 rounded-xl shadow-xl p-3 w-56 text-xs">
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
                                {{-- Standard single-type --}}
                                <div class="text-gray-800 font-medium text-sm">{{ ->room?->displayType() ?? 'N/A' }}</div>
                                <div class="text-gray-400 text-xs mt-0.5">Room {{ ->room?->room_number ?? '—' }}</div>
                            @endif'''

# Pattern to find the td containing the room number display
pattern = r'(<td[^>]*>)\s*<div class="[^"]*text-sm[^"]*">\s*Room\s*\{\{\s*\->room\?->room_number\s*\?\?\s*\'[—\-]\'\s*\}\}\s*<\/div>\s*(<\/td>)'
# Or maybe it has a <span> around it?
pattern2 = r'(<td[^>]*>)\s*<span class="[^"]*text-gray-800[^"]*">\s*Room\s*\{\{\s*\->room\?->room_number\s*\?\?\s*\'[—\-]\'\s*\}\}\s*<\/span>\s*(<\/td>)'

content = re.sub(pattern, r'\1\n' + multi_room_html + r'\n\2', content)
content = re.sub(pattern2, r'\1\n' + multi_room_html + r'\n\2', content)

with open(r'd:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\resources\views\livewire\reception\dashboard.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Replaced!")
