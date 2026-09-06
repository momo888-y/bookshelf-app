<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_mypage(): void
    {
        $response = $this->get('/mypage');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_favorites(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_genres(): void
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }

    public function test_authenticated_user_is_redirected_from_register_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/');
    }
}
