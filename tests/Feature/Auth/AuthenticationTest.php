<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_login_screen_is_in_spanish_and_has_no_sign_up_link(): void
    {
        $response = $this->get(route('login'));

        $response
            ->assertSeeText('Iniciar sesión')
            ->assertSee(route('password.request'))
            ->assertDontSee('/register');
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function rejectedCredentials(): array
    {
        return [
            'wrong password' => ['user@example.com', 'wrong-password', true],
            'unknown email' => ['nobody@example.com', 'password', true],
            'deactivated account' => ['user@example.com', 'password', false],
        ];
    }

    #[DataProvider('rejectedCredentials')]
    public function test_failed_login_keeps_the_user_out_with_the_same_message(string $email, string $password, bool $isActive): void
    {
        $this->app->setLocale('es');

        User::factory()->create(['email' => 'user@example.com', 'is_active' => $isActive]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $email,
            'password' => $password,
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos, o la cuenta no está activa.']);

        $this->assertGuest();
    }

    public function test_new_users_are_active_by_default(): void
    {
        $user = User::query()->create([
            'name' => 'Nuevo usuario',
            'email' => 'nuevo@example.com',
            'password' => 'password',
        ]);

        $this->assertTrue($user->fresh()?->is_active);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('intranet.dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrorsIn('email');

        $this->assertGuest();
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge(): void
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->withTwoFactor()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
