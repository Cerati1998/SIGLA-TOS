<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_sends_guests_to_the_login_screen(): void
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }

    public function test_home_sends_signed_in_users_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->followingRedirects()->get(route('home'));

        $response->assertOk()->assertViewIs('dashboard');
    }
}
