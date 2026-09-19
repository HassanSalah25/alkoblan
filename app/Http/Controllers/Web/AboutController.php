<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;

class AboutController extends Controller
{
    public function index()
    {
        return view('pages.about', ['page' => Page::where('slug', 'about')->first()]);
    }
}
