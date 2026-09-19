<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Media;
use App\Models\Page;

class CompanyProfileController extends Controller
{
    public function index()
    {
        return view('pages.company-profile', [
            'page' => Page::where('slug', 'company-profile')->first(),
            'branches' => Branch::active()->get(),
            'catalog' => Media::where('path', 'like', '%catalogs/catalog.%')->first(),
            'priceLists' => Media::where('path', 'like', '%catalogs/%')
                ->where('path', 'not like', '%catalogs/catalog.%')
                ->get(),
        ]);
    }
}
