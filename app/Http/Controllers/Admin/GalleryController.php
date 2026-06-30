<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin GalleryController
 *
 * Manages the hotel's public gallery images.
 * Admins can view, upload, and delete gallery photos.
 *
 * Route prefix: /admin/gallery
 */
class GalleryController extends Controller
{
    /**
     * Display all gallery images.
     */
    public function index(): View
    {
        $images = Gallery::orderByDesc('created_at')->paginate(24);

        return view('admin.gallery.index', compact('images'));
    }

    /**
     * Store a new gallery image uploaded by the admin.
     *
     * Files are saved to public/gallery/ (accessible via asset() in views).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        $imageName = time() . '.' . $request->image->getClientOriginalExtension();
        $request->image->move(public_path('gallery'), $imageName);

        Gallery::create(['image' => $imageName]);

        return back()->with('success', 'Image uploaded successfully.');
    }

    /**
     * Delete a gallery image.
     */
    public function destroy(Gallery $gallery): RedirectResponse
    {
        // Remove file from public disk if it exists.
        $filePath = public_path('gallery/' . $gallery->image);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $gallery->delete();

        return back()->with('success', 'Image removed successfully.');
    }
}
