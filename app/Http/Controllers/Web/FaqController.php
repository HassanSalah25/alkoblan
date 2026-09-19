<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;

class FaqController extends Controller
{
    public function index()
    {
        return view('pages.faq', [
            'categories' => FaqCategory::active()->with('faqs')->get(),
        ]);
    }
}
