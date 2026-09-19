<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:150']);

        ContactMessage::create([
            'name' => 'Newsletter Subscriber',
            'email' => $data['email'],
            'subject' => 'Newsletter Subscription',
            'message' => 'طلب اشتراك في النشرة البريدية.',
            'status' => 'new',
        ]);

        return back()->with('success', 'تم تسجيل اشتراكك في النشرة البريدية بنجاح!');
    }
}
