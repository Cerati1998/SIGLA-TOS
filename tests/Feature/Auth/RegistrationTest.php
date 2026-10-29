<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Tests\TestCase;

/**
 * El registro público está desactivado: los usuarios se crean desde dentro del sistema.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_feature_is_disabled(): void
    {
        $this->assertFalse(Features::enabled(Features::registration()));
        $this->assertFalse(Route::has('register'));
        $this->assertFalse(Route::has('register.store'));
    }

    public function test_registration_screen_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_users_can_not_register(): void
    {
        $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_login_screen_does_not_link_to_registration(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Sign up');
    }
}
