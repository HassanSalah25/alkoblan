<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactAndAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_contact_form_creates_message(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Ali', 'email' => 'ali@example.com', 'phone' => '0500000000',
            'subject' => 'sales', 'message' => 'I need a quote.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ali@example.com', 'status' => 'new',
        ]);
    }

    public function test_contact_form_requires_name_and_message(): void
    {
        $response = $this->post('/contact', ['email' => 'ali@example.com']);

        $response->assertSessionHasErrors(['name', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_customer_can_register(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Ali', 'last_name' => 'Hassan', 'email' => 'ali@example.com',
            'phone' => '0500000000', 'password' => 'password123', 'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', ['email' => 'ali@example.com', 'type' => 'customer']);
        $this->assertAuthenticated();
    }

    public function test_customer_can_login(): void
    {
        $user = User::factory()->create(['password' => 'password123', 'type' => 'customer']);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password123']);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'password123', 'type' => 'customer']);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_cannot_view_order_history(): void
    {
        $this->get('/account/orders')->assertRedirect('/login');
    }
}
