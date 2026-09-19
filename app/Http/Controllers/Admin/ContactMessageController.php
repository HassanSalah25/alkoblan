<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $contactMessages = ContactMessage::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->q}%")
                    ->orWhere('email', 'like', "%{$request->q}%")
                    ->orWhere('subject', 'like', "%{$request->q}%");
            }))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-messages.index', compact('contactMessages'));
    }

    public function show(ContactMessage $contact_message)
    {
        return view('admin.contact-messages.show', ['contactMessage' => $contact_message]);
    }

    public function update(Request $request, ContactMessage $contact_message)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,in_progress,resolved,closed'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $contact_message->update($data);

        return redirect()->route('admin.contact-messages.show', $contact_message)->with('success', 'Contact message updated.');
    }

    public function destroy(ContactMessage $contact_message)
    {
        $contact_message->delete();

        return redirect()->route('admin.contact-messages.index')->with('success', 'Contact message deleted.');
    }
}
