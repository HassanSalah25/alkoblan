<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function orders()
    {
        $orders = Auth::user()->orders()->with('items')->latest()->paginate(10);

        return view('pages.account-orders', ['orders' => $orders]);
    }
}
