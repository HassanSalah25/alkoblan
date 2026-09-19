<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_shop_page_lists_active_products(): void
    {
        $category = ProductCategory::create(['name' => 'Pipes', 'slug' => 'pipes', 'is_active' => true]);
        Product::create([
            'category_id' => $category->id, 'name' => 'Test Pipe', 'slug' => 'test-pipe',
            'price' => 50, 'is_active' => true, 'stock_status' => 'in_stock',
        ]);
        Product::create([
            'category_id' => $category->id, 'name' => 'Hidden Pipe', 'slug' => 'hidden-pipe',
            'price' => 50, 'is_active' => false, 'stock_status' => 'in_stock',
        ]);

        $response = $this->get('/shop');
        $response->assertOk();
        $response->assertSee('Test Pipe');
        $response->assertDontSee('Hidden Pipe');
    }

    public function test_product_detail_page_shows_product(): void
    {
        $category = ProductCategory::create(['name' => 'Pipes', 'slug' => 'pipes', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Test Pipe', 'slug' => 'test-pipe',
            'price' => 50, 'is_active' => true, 'stock_status' => 'in_stock',
        ]);

        $this->get('/product/'.$product->slug)->assertOk()->assertSee('Test Pipe');
    }

    public function test_shop_category_filter_only_shows_matching_products(): void
    {
        $pipes = ProductCategory::create(['name' => 'Pipes', 'slug' => 'pipes', 'is_active' => true]);
        $valves = ProductCategory::create(['name' => 'Valves', 'slug' => 'valves', 'is_active' => true]);
        Product::create(['category_id' => $pipes->id, 'name' => 'A Pipe', 'slug' => 'a-pipe', 'price' => 10, 'is_active' => true]);
        Product::create(['category_id' => $valves->id, 'name' => 'A Valve', 'slug' => 'a-valve', 'price' => 10, 'is_active' => true]);

        $response = $this->get('/shop?cat=pipes');
        $response->assertSee('A Pipe');
        $response->assertDontSee('A Valve');
    }

    public function test_categories_page_loads(): void
    {
        $this->get('/categories')->assertOk();
    }

    public function test_unknown_route_returns_404(): void
    {
        $this->get('/this-page-does-not-exist')->assertNotFound();
    }
}
