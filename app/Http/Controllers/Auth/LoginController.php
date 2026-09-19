<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login', ['activeTab' => 'login']);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            if (auth()->user()->type !== 'customer') {
                Auth::logout();

                return back()->withErrors(['email' => 'يرجى استخدام حساب عميل لتسجيل الدخول من هنا.']);
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
