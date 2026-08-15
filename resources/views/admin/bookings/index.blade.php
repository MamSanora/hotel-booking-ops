@extends('layouts.admin')

@section('title', 'Manage Bookings - Admin Dashboard')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Manage Bookings</h1>
        <p class="text-white/70 text-[0.95rem]">View, approve, and manage all hotel reservations.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    {{-- ── Pending Refunds Alert ─────────────────────────────────────────────── --}}
    @if($pendingRefundCount > 0)
        <div class="flex items-start gap-4 bg-amber-50 border border-amber-300 text-amber-900 rounded-xl p-4 mb-6 shadow-sm">
            <div class="shrink-0 mt-0.5">
                <i class="bi bi-exclamation-triangle-fill text-amber-500 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-[0.95rem]">
                    {{ $pendingRefundCount }} booking{{ $pendingRefundCount > 1 ? 's' : '' }} {{ $pendingRefundCount > 1 ? 'require' : 'requires' }} a refund
                </p>
                <p class="text-[0.85rem] text-amber-800 mt-0.5">
                    These guests paid successfully but their room was taken by another guest moments before their payment was processed.
                    Please transfer the money back through ABA/Bakong, then click <strong>"Mark as Refunded"</strong> on the booking below.
                </p>
            </div>
        </div>
    @endif

    {{-- Alerts --}}
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

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3">
                <i class="bi bi-exclamation-triangle text-red-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @livewire('admin.bookings-list')
</div>

@endsection
