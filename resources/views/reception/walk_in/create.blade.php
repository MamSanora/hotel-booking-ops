@extends('layouts.reception')

@section('title', 'New Walk-In Booking')
@section('page_title', 'New Walk-In Booking')

@section('content')

<div class="p-5 md:p-8 max-w-7xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('reception.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center gap-2 font-medium transition-colors w-fit">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
            <i class="bi bi-exclamation-triangle text-xl"></i>
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
            <i class="bi bi-check-circle text-xl"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <div class="font-semibold text-sm mb-1 flex items-center gap-2"><i class="bi bi-exclamation-circle"></i> Please fix the following:</div>
            <ul class="list-disc list-inside text-sm space-y-1 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @livewire('reception.walk-in-rooms')

</div>

@endsection
