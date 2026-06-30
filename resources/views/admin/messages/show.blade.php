@extends('layouts.public')

@section('title', 'Reply to ' . $contact->name)

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Reply to Message</h1>
        <p class="text-white/70 text-[0.95rem]">Sending email reply to {{ $contact->name }}</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12 max-w-3xl">

    <div class="mb-6">
        <a href="{{ route('admin.messages.index') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Messages
        </a>
    </div>

    {{-- Original Message --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8 mb-8 border border-[#f0ebe2]">
        <h2 class="font-playfair text-xl font-bold text-hotel-dark mb-5 flex items-center gap-2">
            <i class="bi bi-chat-quote text-hotel-gold"></i> Original Message
        </h2>
        <div class="space-y-3 text-[0.95rem]">
            <div class="flex gap-3">
                <span class="text-gray-500 font-semibold w-16 shrink-0">From:</span>
                <span class="text-hotel-dark font-semibold">{{ $contact->name }}</span>
            </div>
            <div class="flex gap-3">
                <span class="text-gray-500 font-semibold w-16 shrink-0">Email:</span>
                <a href="mailto:{{ $contact->email }}" class="text-hotel-gold hover:underline">{{ $contact->email }}</a>
            </div>
            @if($contact->phone)
                <div class="flex gap-3">
                    <span class="text-gray-500 font-semibold w-16 shrink-0">Phone:</span>
                    <span class="text-gray-700">{{ $contact->phone }}</span>
                </div>
            @endif
            <div class="flex gap-3">
                <span class="text-gray-500 font-semibold w-16 shrink-0">Date:</span>
                <span class="text-gray-700">{{ $contact->created_at->format('D, M d, Y — H:i') }}</span>
            </div>
            <div class="border-t border-gray-100 pt-4 mt-4">
                <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $contact->message }}</p>
            </div>
        </div>
    </div>

    {{-- Reply Form --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8 border border-[#f0ebe2]">
        <h2 class="font-playfair text-xl font-bold text-hotel-dark mb-6 flex items-center gap-2">
            <i class="bi bi-envelope-paper text-hotel-gold"></i> Compose Reply
        </h2>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.messages.reply', $contact) }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Greeting</label>
                <input type="text" name="greeting" value="{{ old('greeting', 'Dear ' . $contact->name . ',') }}" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
            </div>

            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Message Body</label>
                <textarea name="body" rows="6" required
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem] resize-none">{{ old('body', 'Thank you for contacting Dara Meas Hotel.') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Call-to-Action Text (Optional)</label>
                    <input type="text" name="action_text" value="{{ old('action_text') }}" placeholder="e.g. Book Now"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                </div>
                <div>
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Call-to-Action URL (Optional)</label>
                    <input type="url" name="action_url" value="{{ old('action_url') }}" placeholder="https://..."
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
                </div>
            </div>

            <div>
                <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Closing Line</label>
                <input type="text" name="endline" value="{{ old('endline', 'Warm regards, Dara Meas Hotel Team') }}" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-hotel-gold focus:ring-2 focus:ring-hotel-gold/20 transition-all text-[0.95rem]">
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-hotel-dark hover:bg-hotel-accent text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-hotel-dark/20 flex items-center">
                    <i class="bi bi-send mr-2"></i> Send Reply
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
