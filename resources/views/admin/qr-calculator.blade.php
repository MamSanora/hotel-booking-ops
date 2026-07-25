@extends('layouts.admin')

@section('title', 'QR Code Calculator')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">QR Code Calculator</h1>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <p class="text-gray-500 mb-6 text-sm">
            Enter an exact amount to retrieve its corresponding static QR code image. The amount must exactly match one of the generated QR code amounts.
        </p>

        <form action="{{ route('admin.qr-calculator') }}" method="GET" class="flex items-end gap-4 mb-8">
            <div class="flex-1 max-w-sm">
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (USD)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ request('amount') }}" required
                           class="pl-7 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-hotel-gold focus:border-hotel-gold sm:text-sm"
                           placeholder="e.g. 15.00">
                </div>
            </div>
            <button type="submit" class="bg-hotel-gold hover:bg-[#a07840] text-white px-5 py-2.5 rounded-lg shadow-sm font-semibold transition-colors">
                Retrieve QR
            </button>
        </form>

        @if($error)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-start gap-3 mb-6">
                <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                <div>
                    <strong class="block font-bold">Error</strong>
                    <span class="text-sm">{{ $error }}</span>
                </div>
            </div>
        @endif

        @if($qrPath)
            <div class="border-t border-gray-100 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">QR Code for ${{ number_format((float)request('amount'), 2) }}</h3>
                <div class="flex justify-center bg-gray-50 p-6 rounded-xl border border-gray-200">
                    <img src="{{ $qrPath }}" alt="QR Code for ${{ request('amount') }}" class="w-64 h-64 object-contain bg-white p-3 rounded-lg shadow-sm">
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
