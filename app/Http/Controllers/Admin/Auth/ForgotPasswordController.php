<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create()
    {
        return view('admin.auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Scope the lookup to admin-type users only. EloquentUserProvider will
        // add every credential key (besides "password"/token) as a where clause,
        // so this ensures reset links are only ever generated for admins.
        $status = Password::sendResetLink([
            'email' => $request->email,
            'type' => 'admin',
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'A password reset link has been sent to your email address (check the log if using the local mail driver).');
        }

        // Do not reveal whether the email exists / is an admin account.
        return back()->with('success', 'If that email belongs to an admin account, a reset link has been sent.');
    }
}
