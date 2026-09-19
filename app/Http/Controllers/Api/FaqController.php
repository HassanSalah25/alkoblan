<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\FaqResource;
use App\Services\FaqService;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(protected FaqService $faqService)
    {
    }

    public function index(Request $request)
    {
        $categorySlug = $request->query('category');

        if ($categorySlug) {
            $faqs = $this->faqService->forCategory($categorySlug);

            return $this->successResponse(FaqResource::collection($faqs), 'FAQs retrieved successfully.');
        }

        $grouped = $this->faqService->grouped()->map(fn ($category) => [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'name_ar' => $category->name_ar,
                'slug' => $category->slug,
            ],
            'faqs' => FaqResource::collection($category->faqs),
        ]);

        return $this->successResponse($grouped, 'FAQs retrieved successfully.');
    }
}
