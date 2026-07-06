@extends('layouts.public')

@section('title', 'Contact Us — Dara Meas Hotel')

@section('content')

{{-- ==========================================
     PAGE BANNER
     ========================================== --}}
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 lg:py-16 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1542314831-c6a4d27ece11?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>
    
    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <h1 class="font-playfair text-3xl lg:text-[2.2rem] font-bold text-white mb-2">
            Contact Us
        </h1>
        
        <nav aria-label="breadcrumb">
            <ol class="flex space-x-2 text-sm text-white/60">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a></li>
                <li class="text-white/30">/</li>
                <li class="text-hotel-gold" aria-current="page">Contact Us</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 py-16 lg:py-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
        
        {{-- ==========================================
             LEFT: Contact Info
             ========================================== --}}
        <div class="lg:col-span-5">
            <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-2 block">Get In Touch</span>
            <h2 class="font-playfair text-3xl lg:text-4xl font-extrabold text-hotel-dark mt-2 mb-6 leading-tight">
                We're Here to Help
            </h2>
            <p class="text-gray-600 leading-relaxed mb-10 text-[0.95rem]">
                Whether you have a question about your booking, need assistance planning your itinerary, or want to arrange a special event, our dedicated team at Dara Meas Hotel is at your service 24/7.
            </p>
            
            <div class="space-y-8">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-hotel-light flex items-center justify-center text-hotel-gold text-xl shrink-0">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-hotel-dark text-[1.05rem] mb-1">Our Location</h4>
                        <p class="text-gray-600 text-[0.9rem] leading-relaxed">
                            #123 Street 315, Sangkat Beung Kak 1,<br>
                            Khan Toul Kork, Phnom Penh, Cambodia
                        </p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-hotel-light flex items-center justify-center text-hotel-gold text-xl shrink-0">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-hotel-dark text-[1.05rem] mb-1">Phone Number</h4>
                        <p class="text-gray-600 text-[0.9rem]">
                            <a href="tel:+85523456789" class="hover:text-hotel-gold transition-colors">+855 23 456 789</a><br>
                            <a href="tel:+85593123456" class="hover:text-hotel-gold transition-colors">+855 93 123 456 (Mobile)</a>
                        </p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-hotel-light flex items-center justify-center text-hotel-gold text-xl shrink-0">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-hotel-dark text-[1.05rem] mb-1">Email Address</h4>
                        <p class="text-gray-600 text-[0.9rem]">
                            <a href="mailto:info@darameas.com" class="hover:text-hotel-gold transition-colors">info@darameas.com</a><br>
                            <a href="mailto:booking@darameas.com" class="hover:text-hotel-gold transition-colors">booking@darameas.com</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <hr class="border-gray-200 my-10">
            
            <h4 class="font-bold text-hotel-dark text-lg mb-4">Follow Us</h4>
            <div class="flex space-x-3">
                <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-hotel-gold hover:text-white transition-all duration-300">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-hotel-gold hover:text-white transition-all duration-300">
                    <i class="bi bi-instagram"></i>
                </a>
                <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-hotel-gold hover:text-white transition-all duration-300">
                    <i class="bi bi-twitter-x"></i>
                </a>
            </div>
        </div>

        {{-- ==========================================
             RIGHT: Contact Form
             ========================================== --}}
        <div class="lg:col-span-7">
            <div class="bg-white rounded-[18px] shadow-[0_8px_40px_rgba(0,0,0,0.08)] p-6 md:p-10 border border-gray-100">
                <h3 class="font-playfair text-[1.6rem] font-bold text-hotel-dark mb-6">Send Us a Message</h3>
                
                @if (session()->has('message'))
                    <div x-data="{ show: true }" x-show="show" class="bg-green-50 text-green-800 border border-green-200 rounded-lg p-4 mb-6 flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-circle-fill text-green-600"></i>
                            <span class="text-[0.9rem] font-medium">{{ session()->get('message') }}</span>
                        </div>
                        <button @click="show = false" class="text-green-600 hover:text-green-800"><i class="bi bi-x-lg"></i></button>
                    </div>
                @endif
                
                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">Your Name</label>
                            <input type="text" name="name" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">Phone Number</label>
                            <input type="text" name="phone" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">Email Address</label>
                        <input type="email" name="email" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">Your Message</label>
                        <textarea name="message" rows="5" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all resize-none"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-hotel-dark hover:bg-black text-white font-bold rounded-xl py-4 transition-all duration-300 hover:shadow-lg flex justify-center items-center gap-2 mt-4">
                        <i class="bi bi-send"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</div>

{{-- ==========================================
     MAP SECTION
     ========================================== --}}
<div class="w-full h-[400px] lg:h-[500px]">
    <iframe src="https://maps.google.com/maps?q=Phnom%20Penh,%20Cambodia&t=&z=13&ie=UTF8&iwloc=&output=embed" width="100%" height="100%" frameborder="0" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
</div>

@endsection
