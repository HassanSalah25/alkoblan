<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::topLevel()->active()->with('children')->get();
        $categories->each(function ($cat) {
            $cat->products_count = Product::whereIn('category_id', $cat->descendantIds())->active()->count();
        });

        $query = Product::query()->active()->with(['category', 'mainImage.media']);

        $activeCategory = null;
        if ($request->filled('cat')) {
            $activeCategory = ProductCategory::where('slug', $request->get('cat'))->first();
            if ($activeCategory) {
                $ids = $activeCategory->descendantIds();
                $query->whereIn('category_id', $ids);
            }
        }

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('name_ar', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        if ($request->filled('max_price')) {
            $query->where(function ($w) use ($request) {
                $w->where('sale_price', '<=', $request->get('max_price'))
                    ->orWhere(function ($w2) use ($request) {
                        $w2->whereNull('sale_price')->where('price', '<=', $request->get('max_price'));
                    });
            });
        }

        $selectedAttributes = array_map('intval', $request->get('attribute', []));
        if (! empty($selectedAttributes)) {
            $groups = AttributeValue::whereIn('id', $selectedAttributes)->get()->groupBy('attribute_id');
            foreach ($groups as $valueIds) {
                $ids = $valueIds->pluck('id')->all();
                $query->where(function ($w) use ($ids) {
                    $w->whereHas('attributeValues', fn ($q) => $q->whereIn('attribute_values.id', $ids))
                        ->orWhereHas('variants.attributeValues', fn ($q) => $q->whereIn('attribute_values.id', $ids));
                });
            }
        }

        switch ($request->get('sort')) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) asc');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) desc');
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        return view('pages.shop', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'products' => $products,
            'diameters' => Attribute::where('slug', 'diameter')->first()?->values ?? collect(),
            'pressures' => Attribute::where('slug', 'pressure-rating')->first()?->values ?? collect(),
            'maxPrice' => (int) ($request->get('max_price', 1000)),
            'selectedAttributes' => $selectedAttributes,
        ]);
    }
}
