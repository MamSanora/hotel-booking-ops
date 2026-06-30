<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Notifications\SendEmailNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Notification;

/**
 * Admin MessageController
 *
 * Allows administrators to view and respond to guest contact form submissions.
 *
 * Route prefix: /admin/messages
 */
class MessageController extends Controller
{
    /**
     * Display all received contact form messages.
     */
    public function index(): View
    {
        $messages = Contact::orderByDesc('created_at')->paginate(20);

        return view('admin.messages.index', compact('messages'));
    }

    /**
     * Show the reply form for a specific message.
     */
    public function show(Contact $contact): View
    {
        return view('admin.messages.show', compact('contact'));
    }

    /**
     * Send an email reply to the contact who submitted the message.
     * Uses Laravel Notifications (SendEmailNotification) as in the original code.
     */
    public function reply(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'greeting'    => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string'],
            'action_text' => ['nullable', 'string', 'max:255'],
            'action_url'  => ['nullable', 'url'],
            'endline'     => ['nullable', 'string', 'max:255'],
        ]);

        Notification::send($contact, new SendEmailNotification($validated));

        return back()->with('success', "Reply sent to {$contact->email}.");
    }
}
