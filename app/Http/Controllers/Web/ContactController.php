<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Branch;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact', ['branches' => Branch::active()->get()]);
    }

    public function store(ContactRequest $request)
    {
        ContactMessage::create($request->validated() + ['status' => 'new']);

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم استلام رسالتك بنجاح، سيتواصل معك فريقنا في أقرب وقت.'
            : 'Your message has been received. Our team will contact you shortly.');
    }
}
