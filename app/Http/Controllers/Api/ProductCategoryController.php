<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductListResource;
use App\Services\ProductCategoryService;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function __construct(protected ProductCategoryService $categoryService)
    {
    }

    public function index()
    {
        $categories = $this->categoryService->topLevelWithChildren();

        return $this->successResponse(ProductCategoryResource::collection($categories), 'Categories retrieved successfully.');
    }

    public function show(Request $request, string $slug)
    {
        $category = $this->categoryService->findBySlug($slug);

        if (! $category) {
            return $this->errorResponse('Category not found.', [], 404);
        }

        $perPage = min(48, max(1, (int) $request->query('per_page', 12)));
        $page = $request->query('page');

        $products = $this->categoryService->directProducts($category, $perPage, $page ? (int) $page : null);

        $payload = (new ProductCategoryResource($category))->toArray($request);
        $payload['products'] = ProductListResource::collection($products);

        return $this->successResponse($payload, 'Category retrieved successfully.', 200, $this->paginationMeta($products));
    }
}
