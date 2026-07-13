<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Gallery;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public PageController
 *
 * Serves the publicly accessible static pages of the hotel website
 * and handles the contact form submission.
 *
 * Replaces the legacy HomeController routes that were not booking-related.
 */
class PageController extends Controller
{
    /**
     * About Us page.
     */
    public function about(): View
    {
        return view('public.about');
    }

    /**
     * Hotel Gallery page — displays all uploaded gallery images.
     */
    public function gallery(): View
    {
        $gallery = $images = Gallery::orderByDesc('created_at')->get();

        return view('public.gallery', compact('gallery', 'images'));
    }

    /**
     * Contact Us page — renders the contact form.
     */
    public function contact(): View
    {
        return view('public.contact');
    }

    /**
     * Hotel Blog page (static placeholder).
     */
    public function blog(): View
    {
        return view('public.blog');
    }

    /**
     * Process a contact form submission.
     */
    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Contact::create($validated);

        return back()->with('success', 'Your message has been sent. We will get back to you soon!');
    }
}
