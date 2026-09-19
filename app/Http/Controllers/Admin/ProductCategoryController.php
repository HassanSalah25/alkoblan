<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use GeneratesSlugs;

    public function index(Request $request)
    {
        $categories = ProductCategory::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->q}%")
                    ->orWhere('name_ar', 'like', "%{$request->q}%");
            }))
            ->orderByRaw('parent_id IS NOT NULL, parent_id')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('admin.product-categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = ProductCategory::whereNull('parent_id')->orderBy('sort_order')->get();

        return view('admin.product-categories.form', [
            'productCategory' => new ProductCategory(),
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(ProductCategory::class, $data['name'], null, $request->input('slug'));
        ProductCategory::create($data);

        return redirect()->route('admin.product-categories.index')->with('success', 'Category created.');
    }

    public function edit(ProductCategory $productCategory)
    {
        $parentCategories = ProductCategory::whereNull('parent_id')
            ->where('id', '!=', $productCategory->id)
            ->orderBy('sort_order')
            ->get();

        return view('admin.product-categories.form', [
            'productCategory' => $productCategory,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(ProductCategory::class, $data['name'], $productCategory->id, $request->input('slug'));
        $productCategory->update($data);

        return redirect()->route('admin.product-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->children()->exists() || $productCategory->products()->exists()) {
            return back()->with('error', 'Cannot delete: this category has subcategories or products assigned to it.');
        }

        $productCategory->delete();

        return redirect()->route('admin.product-categories.index')->with('success', 'Category deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'image_id' => ['nullable', 'exists:media,id'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ]);

        $data['parent_id'] = $data['parent_id'] ?: null;
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
