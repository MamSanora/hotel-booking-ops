@extends('layouts.reception')

@section('title', 'Edit Booking - Reception')
@section('page_title', 'Edit Booking')

@section('content')
<div class="p-5 md:p-8">
    @livewire('reception.edit-manual-booking', ['booking' => $booking])
</div>
@endsection
