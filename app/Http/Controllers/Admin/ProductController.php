<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use GeneratesSlugs;

    public function index(Request $request)
    {
        $products = Product::query()
            ->with('category')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->q}%")
                    ->orWhere('sku', 'like', "%{$request->q}%");
            }))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('featured'), fn ($q) => $q->where('is_featured', $request->featured === '1'))
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->active === '1'))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $categories = ProductCategory::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $product = new Product();
        $categories = ProductCategory::orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(Product::class, $data['name'], null, $request->input('slug'));
        $data['technical_specifications'] = $this->buildTechnicalSpecs($request);
        $data['specifications'] = $this->decodeJsonField($request->input('specifications'));

        $product = Product::create($data);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created. You can now add images, files, attributes and variants.');
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('name')->get();
        $product->load(['images.media', 'files.media', 'attributeValues', 'variants.attributeValues']);

        return view('admin.products.form', compact('product', 'categories', 'attributes'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product->id);
        $data['slug'] = $this->uniqueSlug(Product::class, $data['name'], $product->id, $request->input('slug'));
        $data['technical_specifications'] = $this->buildTechnicalSpecs($request);
        $data['specifications'] = $this->decodeJsonField($request->input('specifications'), $product->specifications);

        $product->update($data);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /** Sync the filterable attribute-value assignment pivot for this product. */
    public function syncAttributes(Request $request, Product $product)
    {
        $ids = array_filter((array) $request->input('attribute_value_ids', []));
        $product->attributeValues()->sync($ids);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Attributes updated.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'.($ignoreId ? ",{$ignoreId}" : '')],
            'short_description' => ['nullable', 'string'],
            'short_description_ar' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'stock_status' => ['required', 'in:in_stock,out_of_stock,on_backorder'],
            'min_order_qty' => ['nullable', 'integer', 'min:1'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_description_ar' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['currency'] = $data['currency'] ?? 'SAR';
        $data['stock_quantity'] = $data['stock_quantity'] ?? 0;
        $data['min_order_qty'] = $data['min_order_qty'] ?? 1;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['slug']); // handled separately via uniqueSlug()

        return $data;
    }

    /** Rebuild the technical_specifications JSON array from repeatable label/label_ar/value inputs. */
    private function buildTechnicalSpecs(Request $request): array
    {
        $labels = $request->input('tech_spec_label', []);
        $labelsAr = $request->input('tech_spec_label_ar', []);
        $values = $request->input('tech_spec_value', []);

        $rows = [];
        foreach ($labels as $i => $label) {
            if (trim((string) $label) === '' && trim((string) ($values[$i] ?? '')) === '') {
                continue;
            }
            $rows[] = [
                'label' => $label,
                'label_ar' => $labelsAr[$i] ?? '',
                'value' => $values[$i] ?? '',
            ];
        }

        return $rows;
    }

    private function decodeJsonField(?string $raw, $fallback = null)
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $decoded = json_decode($raw, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $fallback;
    }
}
