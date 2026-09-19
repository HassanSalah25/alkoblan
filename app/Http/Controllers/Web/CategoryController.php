<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::topLevel()->active()->with('children')->get();
        $categories->each(function ($cat) {
            $cat->total_products = \App\Models\Product::whereIn('category_id', $cat->descendantIds())->active()->count();
        });

        return view('pages.categories', ['categories' => $categories]);
    }
}
