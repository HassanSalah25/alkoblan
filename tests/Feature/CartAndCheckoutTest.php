<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable cookie encryption (both the EncryptCookies middleware and
        // the test client's own pre-encryption step) so the session cookie
        // captured from one simulated response can be replayed verbatim on
        // the next request — Laravel's test client does not carry cookies
        // between calls automatically the way a real browser would.
        $this->withoutMiddleware(\Illuminate\Cookie\Middleware\EncryptCookies::class);
        $this->disableCookieEncryption();
    }

    protected function makeProduct(float $price = 100): Product
    {
        $category = ProductCategory::create(['name' => 'Pipes', 'slug' => 'pipes', 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id, 'name' => 'Test Pipe', 'slug' => 'test-pipe',
            'price' => $price, 'is_active' => true, 'stock_status' => 'in_stock', 'min_order_qty' => 1,
        ]);
    }

    public function test_guest_can_add_product_to_cart(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        $response->assertOk()->assertJson(['success' => true, 'count' => 2]);
    }

    /**
     * Laravel's test client does not automatically carry cookies between
     * separate simulated requests (only real browsers do that), so guest
     * cart tests that span multiple calls must propagate the session
     * cookie from one response into the next request explicitly.
     */
    protected function carryCookies($response): static
    {
        foreach ($response->headers->getCookies() as $cookie) {
            $this->withCookie($cookie->getName(), $cookie->getValue());
        }

        return $this;
    }

    public function test_cart_page_shows_added_item(): void
    {
        $product = $this->makeProduct();
        $response = $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $this->carryCookies($response)->get('/cart')->assertOk()->assertSee('Test Pipe');
    }

    public function test_checkout_redirects_to_cart_when_empty(): void
    {
        $this->get('/checkout')->assertRedirect('/cart');
    }

    public function test_full_checkout_creates_order_with_correct_total(): void
    {
        $product = $this->makeProduct(100);
        $addResponse = $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->carryCookies($addResponse)->post('/checkout', [
            'first_name' => 'Test', 'last_name' => 'Customer', 'email' => 'test@example.com',
            'phone' => '0500000000', 'city' => 'riyadh', 'address' => 'Some street 123',
            'payment_method' => 'cash',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(1, $order->items()->count());
        $this->assertEquals(200, (float) $order->subtotal);
        $this->assertEquals(230, (float) $order->total); // +15% VAT
        $response->assertRedirect(route('checkout.success', $order->order_number));
    }

    public function test_cart_is_cleared_after_checkout(): void
    {
        $product = $this->makeProduct();
        $addResponse = $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->carryCookies($addResponse);

        $checkoutResponse = $this->post('/checkout', [
            'first_name' => 'Test', 'last_name' => 'Customer', 'email' => 'test@example.com',
            'phone' => '0500000000', 'city' => 'riyadh', 'address' => 'Some street 123',
            'payment_method' => 'cash',
        ]);

        $this->carryCookies($checkoutResponse)->get('/cart')->assertSee('سلتك فارغة');
    }
}
