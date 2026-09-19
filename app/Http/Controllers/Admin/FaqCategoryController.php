<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqCategoryController extends Controller
{
    use GeneratesSlugs;

    public function index(Request $request)
    {
        $faqCategories = FaqCategory::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->q}%")
                    ->orWhere('name_ar', 'like', "%{$request->q}%");
            }))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.faq-categories.index', compact('faqCategories'));
    }

    public function create()
    {
        return view('admin.faq-categories.form', ['faqCategory' => new FaqCategory()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(FaqCategory::class, $data['name'], null, $request->input('slug'));
        FaqCategory::create($data);

        return redirect()->route('admin.faq-categories.index')->with('success', 'FAQ category created.');
    }

    public function edit(FaqCategory $faqCategory)
    {
        return view('admin.faq-categories.form', ['faqCategory' => $faqCategory]);
    }

    public function update(Request $request, FaqCategory $faqCategory)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(FaqCategory::class, $data['name'], $faqCategory->id, $request->input('slug'));
        $faqCategory->update($data);

        return redirect()->route('admin.faq-categories.index')->with('success', 'FAQ category updated.');
    }

    public function destroy(FaqCategory $faqCategory)
    {
        $faqCategory->delete();

        return redirect()->route('admin.faq-categories.index')->with('success', 'FAQ category deleted.');
    }

    public function toggle(FaqCategory $faqCategory)
    {
        $faqCategory->update(['is_active' => ! $faqCategory->is_active]);

        return back()->with('success', 'Status updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
