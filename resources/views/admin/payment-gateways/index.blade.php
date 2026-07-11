@extends('layouts.public')

@section('title', 'Payment Gateway Management')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Payment Gateway Management</h1>
        <p class="text-white/70 text-[0.95rem]">Control the visibility and status of payment gateways shown to guests during checkout.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    {{-- Flash message --}}
    @if(session('success'))
        <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-4 mb-6 shadow-sm">
            <i class="bi bi-check-circle-fill text-emerald-500 text-xl mt-0.5 shrink-0"></i>
            <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
        </div>
    @endif

    {{-- Info box --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 mb-8 text-blue-800 text-sm flex gap-3">
        <i class="bi bi-info-circle-fill text-blue-500 text-lg shrink-0 mt-0.5"></i>
        <div>
            <strong>How this works:</strong> The <em>Admin Status</em> column is your manual control.
            The <em>Effective Status</em> is what guests actually see — the system may automatically override
            <span class="font-semibold">Active → Disabled</span> if credentials are missing from <code class="bg-blue-100 px-1 rounded">.env</code>
            or if the gateway API is unreachable.
        </div>
    </div>

    {{-- Gateway table --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.08)] overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-hotel-light border-b border-[#ede8df]">
                    <th class="text-left px-6 py-4 text-hotel-dark font-semibold">Gateway</th>
                    <th class="text-left px-6 py-4 text-hotel-dark font-semibold">Slug</th>
                    <th class="text-left px-6 py-4 text-hotel-dark font-semibold">Admin Status</th>
                    <th class="text-left px-6 py-4 text-hotel-dark font-semibold">Effective Status</th>
                    <th class="text-left px-6 py-4 text-hotel-dark font-semibold">Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0eee9]">
                @foreach($gateways as $item)
                    @php
                        $gateway = $item['gateway'];
                        $computed = $item['computed_state'];
                        $badgeClass = match($computed) {
                            'active'   => 'bg-green-100 text-green-700',
                            'disabled' => 'bg-amber-100 text-amber-700',
                            'hidden'   => 'bg-gray-100 text-gray-500',
                            default    => 'bg-gray-100 text-gray-500',
                        };
                        $adminBadgeClass = match($gateway->admin_status) {
                            'active'   => 'bg-blue-100 text-blue-700',
                            'disabled' => 'bg-orange-100 text-orange-700',
                            'hidden'   => 'bg-red-100 text-red-700',
                            default    => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <tr class="hover:bg-hotel-light/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="font-semibold text-hotel-dark">{{ $gateway->name }}</div>
                        </td>
                        <td class="px-6 py-5">
                            <code class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs">{{ $gateway->slug }}</code>
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $adminBadgeClass }}">
                                {{ ucfirst($gateway->admin_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ ucfirst($computed) }}
                                </span>
                                @if($gateway->admin_status === 'active' && $computed === 'disabled')
                                    <span class="text-amber-600 text-xs" title="Automatically disabled: credentials missing or API unreachable">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Auto-overridden
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <form method="POST"
                                  action="{{ route('admin.payment-gateways.update', $gateway) }}"
                                  class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="admin_status"
                                        class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-hotel-dark focus:outline-none focus:ring-2 focus:ring-hotel-gold">
                                    <option value="active"   {{ $gateway->admin_status === 'active'   ? 'selected' : '' }}>Active</option>
                                    <option value="disabled" {{ $gateway->admin_status === 'disabled' ? 'selected' : '' }}>Disabled</option>
                                    <option value="hidden"   {{ $gateway->admin_status === 'hidden'   ? 'selected' : '' }}>Hidden</option>
                                </select>
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 bg-hotel-dark text-hotel-gold text-xs font-bold px-4 py-2 rounded-lg hover:bg-hotel-accent transition-colors">
                                    <i class="bi bi-save"></i> Save
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Legend --}}
    <div class="mt-6 text-xs text-gray-500 flex flex-wrap gap-4">
        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span> Active — shown and clickable</div>
        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span> Disabled — shown but greyed out with offline message</div>
        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gray-400 inline-block"></span> Hidden — completely removed from checkout</div>
    </div>

</div>

@endsection
