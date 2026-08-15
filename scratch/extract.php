<?php
$dashboard = file_get_contents(__DIR__ . '/../resources/views/guest/dashboard.blade.php');
$startLine = 33;
$endLine = 326;
$lines = explode("\n", $dashboard);
$content = implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));
$search = '<form method="POST" action="{{ route(\'guest.booking.cancel\', $booking->id) }}"
                                                      x-data @submit.prevent="$dispatch(\'open-confirm\', { message: \'Cancel this booking?\', action: () => $el.submit() })" class="inline-block">
                                                    @csrf @method(\'PATCH\')
                                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors" title="Cancel">
                                                        <i class="bi bi-x text-lg"></i>
                                                    </button>
                                                </form>';
$replace = '<button type="button"
                                                      x-data @click="$dispatch(\'open-confirm\', { message: \'Cancel this booking?\', action: () => $wire.cancelBooking({{ $booking->id }}) })"
                                                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors" title="Cancel">
                                                    <i class="bi bi-x text-lg"></i>
                                                </button>';
$content = str_replace($search, $replace, $content);
$livewireWrapper = <<<HTML
<div>
    @if(\$flashMessage)
        <div class="mb-6">
            @if(\$flashType === 'success')
                <div class="bg-green-50 text-green-800 border border-green-200 rounded-lg p-4 flex items-start shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                    <i class="bi bi-check-circle-fill text-green-500 text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">{{ \$flashMessage }}</div>
                    <button @click="show = false; \$wire.set('flashMessage', '')" class="text-green-500 hover:text-green-700 focus:outline-none"><i class="bi bi-x-lg"></i></button>
                </div>
            @else
                <div class="bg-red-50 text-red-800 border border-red-200 rounded-lg p-4 flex items-start shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">{{ \$flashMessage }}</div>
                    <button @click="show = false; \$wire.set('flashMessage', '')" class="text-red-500 hover:text-red-700 focus:outline-none"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif
        </div>
    @endif
    
    {$content}
</div>
HTML;
file_put_contents(__DIR__ . '/../resources/views/livewire/guest/booking-list.blade.php', $livewireWrapper);
