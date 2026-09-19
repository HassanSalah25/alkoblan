<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ContentBlockResource;
use App\Http\Resources\FamousClientResource;
use App\Http\Resources\HeroSlideResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\TestimonialResource;
use App\Services\HomepageService;

class HomepageController extends Controller
{
    public function __construct(protected HomepageService $homepageService)
    {
    }

    public function index()
    {
        $data = $this->homepageService->aggregate();

        return $this->successResponse([
            'hero_slides' => HeroSlideResource::collection($data['hero_slides']),
            'content_blocks' => [
                'home_about' => $data['content_blocks']['home_about'] ? new ContentBlockResource($data['content_blocks']['home_about']) : null,
                'home_quality' => $data['content_blocks']['home_quality'] ? new ContentBlockResource($data['content_blocks']['home_quality']) : null,
                'home_cta' => $data['content_blocks']['home_cta'] ? new ContentBlockResource($data['content_blocks']['home_cta']) : null,
            ],
            'testimonials' => TestimonialResource::collection($data['testimonials']),
            'famous_clients' => FamousClientResource::collection($data['famous_clients']),
            'featured_categories' => ProductCategoryResource::collection($data['featured_categories']),
            'featured_products' => ProductListResource::collection($data['featured_products']),
        ], 'Homepage data retrieved successfully.');
    }
}
