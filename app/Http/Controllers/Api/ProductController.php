<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    public function index(Request $request)
    {
        $attributes = $request->query('attributes', $request->query('attribute'));

        $filters = [
            'category' => $request->query('category'),
            'q' => $request->query('q'),
            'attributes' => $attributes,
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'featured' => $request->query('featured'),
            'sort' => $request->query('sort', 'newest'),
            'per_page' => $request->query('per_page', 12),
            'page' => $request->query('page'),
        ];

        $products = $this->productService->paginate($filters);

        return $this->successResponse(
            ProductListResource::collection($products),
            'Products retrieved successfully.',
            200,
            $this->paginationMeta($products)
        );
    }

    public function show(string $slug)
    {
        $product = $this->productService->findBySlug($slug);

        if (! $product) {
            return $this->errorResponse('Product not found.', [], 404);
        }

        return $this->successResponse(new ProductResource($product), 'Product retrieved successfully.');
    }
}
