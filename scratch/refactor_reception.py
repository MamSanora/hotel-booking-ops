import os, re

old_path = 'resources/views/reception/room-check.blade.php'
new_path = 'resources/views/livewire/reception/room-check.blade.php'

with open(old_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Remove @extends, @section, and @endsection
content = re.sub(r"@extends\('layouts\.reception'\)\n*", "", content)
content = re.sub(r"@section\('title', 'Room Check'\)\n*", "", content)
content = re.sub(r"@section\('page_title', 'Room Check'\)\n*", "", content)
content = re.sub(r"@section\('content'\)\n*", "", content)
content = re.sub(r"@endsection\n*", "", content)

# Add wire:poll.15s to the root div
content = re.sub(r'<div class="p-5 md:p-8 space-y-8">', '<div class="p-5 md:p-8 space-y-8" wire:poll.15s>', content)

# Replace Flash Alerts
flash_alert = """
    @if($flashMessage)
        <div x-data="{ show: true }" x-show="show" x-transition
             class="flex justify-between items-center {{ $flashType === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-red-50 border border-red-200 text-red-800' }} rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full {{ $flashType === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-500' }} flex items-center justify-center shrink-0">
                    <i class="bi {{ $flashType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
                </div>
                <span class="text-sm font-medium">{{ $flashMessage }}</span>
            </div>
            <button @click="show = false" class="{{ $flashType === 'success' ? 'text-emerald-500 hover:text-emerald-700' : 'text-red-400 hover:text-red-600' }} ml-4 shrink-0"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif
"""
content = re.sub(r"\{\{-- FLASH ALERTS --\}\}.*?(?=\{\{-- PAGE HEADER --\}\})", "{{-- FLASH MESSAGES --}}\n" + flash_alert + "\n    ", content, flags=re.DOTALL)

# Replace form tags with wire:click
# Mark Available form
content = re.sub(r'<form action="\{\{ route\(\'reception\.room-check\.mark-available\', \$room->id\) \}\}" method="POST"(.*?)>.*?@csrf @method\(\'PATCH\'\).*?<button type="button" x-data @click\.prevent="let form = \$el\.closest\(\'form\'\); \$dispatch\(\'open-confirm\', \{ message: \'(.*?)\', action: \(\) => form\.submit\(\) \}\)"(.*?)>(.*?)</button>.*?</form>', 
r"""<button type="button" x-data @click="$dispatch('open-confirm', { message: '\2', action: () => Livewire.dispatch('mark-available', { roomId: {{ $room->id }} }) })" \3>\4</button>""", content, flags=re.DOTALL)

# Mark Maintenance form
content = re.sub(r'<form action="\{\{ route\(\'reception\.room-check\.mark-maintenance\', \$room->id\) \}\}" method="POST"(.*?)>.*?@csrf @method\(\'PATCH\'\).*?<button type="button" x-data @click\.prevent="let form = \$el\.closest\(\'form\'\); \$dispatch\(\'open-confirm\', \{ message: \'(.*?)\', action: \(\) => form\.submit\(\) \}\)"(.*?)>(.*?)</button>.*?</form>', 
r"""<button type="button" x-data @click="$dispatch('open-confirm', { message: '\2', action: () => Livewire.dispatch('mark-maintenance', { roomId: {{ $room->id }} }) })" \3>\4</button>""", content, flags=re.DOTALL)

# Replace Filters form with wire:model
content = re.sub(r'<form method="GET" action="\{\{ route\(\'reception\.room-check\.index\'\) \}\}" class="flex items-center gap-3">', '<div class="flex items-center gap-3">', content)
content = re.sub(r'name="status"(.*?)onchange="this\.form\.submit\(\)"', r'wire:model.live="status"\1', content)
content = re.sub(r'\{\{ request\(\'status\'\) === \'.*?\' \? \'selected\' : \'\' \}\}', '', content)
content = re.sub(r'name="type"(.*?)onchange="this\.form\.submit\(\)"', r'wire:model.live="type"\1', content)
content = re.sub(r'\{\{ request\(\'type\'\) == \$type->id \? \'selected\' : \'\' \}\}', '', content)
content = re.sub(r'name="sort"(.*?)onchange="this\.form\.submit\(\)"', r'wire:model.live="sort"\1', content)
content = re.sub(r'\{\{ request\(\'sort\'\) === \'.*?\' \? \'selected\' : \'\' \}\}', '', content)
content = re.sub(r'request\(\)->hasAny\(\[\'status\', \'type\', \'sort\'\]\) && \(request\(\'status\'\) \|\| request\(\'type\'\) \|\| request\(\'sort\'\)\)', '($status || $type || $sort)', content)
content = re.sub(r'href="\{\{ route\(\'reception\.room-check\.index\'\) \}\}"', 'href="#" wire:click.prevent="$set(\'status\', \'\'); $set(\'type\', \'\'); $set(\'sort\', \'\');"', content)
content = re.sub(r'</form>', '</div>', content)

with open(new_path, 'w', encoding='utf-8') as f:
    f.write(content)
