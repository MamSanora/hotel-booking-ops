@extends('layouts.public')

@section('title', 'Admin Dashboard')

@section('content')

{{-- ==========================================
     DASHBOARD HEADER
     ========================================== --}}
<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Administrator Dashboard</h1>
        <p class="text-white/70 text-[0.95rem]">Hotel Overview & Management</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    {{-- ==========================================
         HOTEL STATISTICS
         ========================================== --}}
    <h2 class="font-playfair text-2xl font-bold text-hotel-dark border-b-2 border-gray-200 pb-3 mb-6 flex items-center">
        <i class="bi bi-graph-up text-blue-500 mr-3"></i>Hotel Statistics
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        {{-- Revenue --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2] h-full">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-[1.4rem] mb-4">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">${{ number_format($monthlyRevenue, 2) }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Monthly Revenue</div>
        </div>
        
        {{-- Occupancy --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2] h-full">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-[1.4rem] mb-4">
                <i class="bi bi-building"></i>
            </div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $occupiedRooms }} / {{ $totalRooms }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider mb-3">Rooms Occupied</div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0 }}%"></div>
            </div>
        </div>

        {{-- Active Bookings --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2] h-full">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-[1.4rem] mb-4">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $activeBookings }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Active Bookings</div>
        </div>

        {{-- Customers --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2] h-full">
            <div class="w-12 h-12 rounded-xl bg-[#fff8ee] text-hotel-gold flex items-center justify-center text-[1.4rem] mb-4">
                <i class="bi bi-people"></i>
            </div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $totalGuests }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Registered Guests</div>
        </div>

        {{-- Today Arrivals --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2] h-full">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-500 flex items-center justify-center text-[1.4rem] mb-4">
                <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $todayArrivals }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Today's Arrivals</div>
        </div>

        {{-- Today Departures --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2] h-full">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-[1.4rem] mb-4">
                <i class="bi bi-box-arrow-right"></i>
            </div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $todayDepartures }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Today's Departures</div>
        </div>
        
        {{-- Rooms Available --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2] h-full">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-[1.4rem] mb-4">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $availableRooms }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Rooms Available</div>
        </div>
    </div>

    {{-- ==========================================
         ANALYTICS CHARTS
         ========================================== --}}
    <h2 class="font-playfair text-2xl font-bold text-hotel-dark border-b-2 border-gray-200 pb-3 mb-6 mt-12 flex items-center">
        <i class="bi bi-bar-chart-line text-blue-500 mr-3"></i>Financial & Booking Analytics
    </h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 border border-[#f0ebe2] flex flex-col">
            <h5 class="font-semibold text-lg mb-4 text-hotel-dark">Revenue (Last 7 Days)</h5>
            <div class="relative w-full" style="height: 250px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 border border-[#f0ebe2] flex flex-col">
            <h5 class="font-semibold text-lg mb-4 text-hotel-dark">Booking Distribution</h5>
            <div class="relative w-full" style="height: 250px;">
                <canvas id="bookingsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ==========================================
         QUICK ACTIONS
         ========================================== --}}
    <h2 class="font-playfair text-2xl font-bold text-hotel-dark border-b-2 border-gray-200 pb-3 mb-6 flex items-center">
        <i class="bi bi-grid text-gray-400 mr-3"></i>Quick Actions
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Room Management --}}
        <a href="{{ route('admin.rooms.index') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-door-open text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">Manage Rooms</h5>
            <p class="text-[0.85rem] text-gray-500">Add, edit, or remove hotel rooms.</p>
        </a>
        
        {{-- Booking Management --}}
        <a href="{{ route('admin.bookings.index') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-journal-bookmark text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">All Bookings</h5>
            <p class="text-[0.85rem] text-gray-500">View and manage all reservations.</p>
        </a>

        {{-- Staff Management --}}
        <a href="{{ route('admin.staff.index') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-person-badge text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">Manage Staff</h5>
            <p class="text-[0.85rem] text-gray-500">Add or remove Receptionists.</p>
        </a>

        {{-- Receptionist View --}}
        <a href="{{ url('reception/dashboard') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-reception-4 text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">Reception Desk</h5>
            <p class="text-[0.85rem] text-gray-500">Go to front desk operations.</p>
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const revenueData = @json($revenueLast7Days);
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: revenueData.map(d => d.date),
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenueData.map(d => d.revenue),
                    borderColor: '#c8a96e', // hotel-gold
                    backgroundColor: 'rgba(200, 169, 110, 0.1)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Bookings Chart
        const bookingsData = @json($bookingsByStatus);
        const statuses = Object.keys(bookingsData);
        const counts = Object.values(bookingsData);
        
        new Chart(document.getElementById('bookingsChart'), {
            type: 'doughnut',
            data: {
                labels: statuses.map(s => s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ')),
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        '#3b82f6', // blue
                        '#10b981', // green
                        '#f59e0b', // yellow
                        '#ef4444', // red
                        '#8b5cf6'  // purple
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    });
</script>
@endpush
