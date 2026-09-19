<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductCategoryService
{
    /**
     * Top-level active categories with their active children nested, plus a
     * recursive products_count (including descendant categories) on each.
     */
    public function topLevelWithChildren(): Collection
    {
        $categories = ProductCategory::active()->topLevel()
            ->with(['image', 'children.image'])
            ->get();

        $categories->each(function (ProductCategory $category) {
            $this->attachProductsCount($category);
            $category->children->each(fn (ProductCategory $child) => $this->attachProductsCount($child));
        });

        return $categories;
    }

    public function findBySlug(string $slug): ?ProductCategory
    {
        $category = ProductCategory::active()->where('slug', $slug)
            ->with(['image', 'children.image'])
            ->first();

        if ($category) {
            $this->attachProductsCount($category);
        }

        return $category;
    }

    public function directProducts(ProductCategory $category, int $perPage = 12, ?int $page = null): LengthAwarePaginator
    {
        return Product::active()
            ->where('category_id', $category->id)
            ->with(['category', 'images.media'])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    protected function attachProductsCount(ProductCategory $category): void
    {
        $ids = $category->descendantIds();
        $category->products_count = Product::whereIn('category_id', $ids)->active()->count();
    }
}
