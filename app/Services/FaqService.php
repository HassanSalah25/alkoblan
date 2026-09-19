<?php

namespace App\Services;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Support\Collection;

class FaqService
{
    /**
     * Flat active FAQs for a single category slug.
     */
    public function forCategory(string $categorySlug): Collection
    {
        return Faq::active()
            ->whereHas('category', fn ($q) => $q->where('slug', $categorySlug))
            ->with('category')
            ->get();
    }

    /**
     * All active FAQs grouped by category: [{category, faqs: [...]}].
     */
    public function grouped(): Collection
    {
        return FaqCategory::active()
            ->with(['faqs' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->get()
            ->filter(fn (FaqCategory $category) => $category->faqs->isNotEmpty())
            ->values();
    }
}
