<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $faqCategories = FaqCategory::orderBy('sort_order')->get();

        $faqs = Faq::query()
            ->with('category')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('question', 'like', "%{$request->q}%")
                    ->orWhere('question_ar', 'like', "%{$request->q}%");
            }))
            ->when($request->filled('category'), fn ($q) => $q->where('faq_category_id', $request->category))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.faqs.index', compact('faqs', 'faqCategories'));
    }

    public function create()
    {
        $faqCategories = FaqCategory::orderBy('sort_order')->get();

        return view('admin.faqs.form', ['faq' => new Faq(), 'faqCategories' => $faqCategories]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Faq::create($data);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created.');
    }

    public function edit(Faq $faq)
    {
        $faqCategories = FaqCategory::orderBy('sort_order')->get();

        return view('admin.faqs.form', compact('faq', 'faqCategories'));
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $this->validated($request);
        $faq->update($data);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted.');
    }

    public function toggle(Faq $faq)
    {
        $faq->update(['is_active' => ! $faq->is_active]);

        return back()->with('success', 'Status updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'faq_category_id' => ['nullable', 'exists:faq_categories,id'],
            'question' => ['required', 'string', 'max:255'],
            'question_ar' => ['nullable', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'answer_ar' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['faq_category_id'] = $data['faq_category_id'] ?? null;

        return $data;
    }
}
