@extends('layouts.public')

@section('title', 'Hotel Gallery - Admin')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Hotel Gallery</h1>
        <p class="text-white/70 text-[0.95rem]">Manage public-facing hotel images.</p>
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

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-8">
            <div class="flex items-center gap-3">
                <i class="bi bi-exclamation-triangle text-red-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    {{-- Upload Form --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8 mb-10 border border-[#f0ebe2]">
        <h2 class="font-playfair text-xl font-bold text-hotel-dark mb-6">Upload New Image</h2>
        <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <label class="block text-[0.85rem] font-semibold text-gray-700 uppercase tracking-wider mb-2">Select Image</label>
                    <input type="file" name="image" required accept="image/*"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[0.95rem] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-hotel-gold/10 file:text-hotel-dark hover:file:bg-hotel-gold/20 transition-all">
                    @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="bg-hotel-dark hover:bg-hotel-accent text-white px-6 py-2.5 rounded-xl font-semibold transition-colors flex items-center gap-2 whitespace-nowrap">
                    <i class="bi bi-cloud-upload"></i> Upload
                </button>
            </div>
        </form>
    </div>

    {{-- Gallery Grid --}}
    @if($gallery->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
            @foreach($gallery as $item)
                <div class="group relative bg-white rounded-xl overflow-hidden shadow-[0_4px_16px_rgba(0,0,0,0.07)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.13)] transition-all duration-300">
                    <img src="{{ asset('gallery/' . $item->image) }}" alt="Gallery Image" class="w-full h-48 object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <form action="{{ route('admin.gallery.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this image?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold text-sm flex items-center gap-2 transition-colors">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $gallery->links() }}
        </div>
    @else
        <div class="bg-hotel-light rounded-2xl text-center py-16 px-6">
            <i class="bi bi-images text-[3.5rem] text-hotel-gold mb-4 inline-block"></i>
            <h5 class="font-bold text-xl text-hotel-dark mb-2">No Gallery Images</h5>
            <p class="text-gray-500">Upload your first hotel image using the form above.</p>
        </div>
    @endif

</div>

@endsection
