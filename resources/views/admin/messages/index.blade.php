@extends('layouts.public')

@section('title', 'Contact Messages - Admin')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Contact Messages</h1>
        <p class="text-white/70 text-[0.95rem]">View and reply to guest inquiries submitted via the contact form.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-8">
            <div class="flex items-center gap-3">
                <i class="bi bi-check-circle text-green-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-600 hover:text-green-800 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Name</th>
                        <th class="px-5 py-4 font-semibold">Email</th>
                        <th class="px-5 py-4 font-semibold">Message</th>
                        <th class="px-5 py-4 font-semibold">Received</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contacts as $contact)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <strong class="text-hotel-dark font-semibold">{{ $contact->name }}</strong>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-gray-600 text-[0.95rem]">{{ $contact->email }}</div>
                            @if($contact->phone)
                                <div class="text-gray-400 text-[0.8rem] mt-0.5">{{ $contact->phone }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 max-w-xs">
                            <p class="text-gray-700 text-[0.9rem] truncate">{{ $contact->message }}</p>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="text-gray-500 text-[0.9rem]">{{ $contact->created_at->format('M d, Y') }}</div>
                            <div class="text-gray-400 text-[0.78rem]">{{ $contact->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('admin.messages.show', $contact) }}" class="bg-blue-50 hover:bg-blue-100 text-blue-600 px-3 py-1.5 rounded-md text-sm font-semibold transition-colors border border-blue-100">
                                <i class="bi bi-envelope mr-1"></i> Reply
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-gray-500">No messages received yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5 border-t border-gray-100 bg-gray-50">
            {{ $contacts->links() }}
        </div>
    </div>
</div>

@endsection
