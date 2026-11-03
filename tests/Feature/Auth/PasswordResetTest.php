<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\TestResponse;
use Laravel\Fortify\Features;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const LINK_REQUESTED_MESSAGE = 'Si el correo está registrado, te enviaremos un enlace para crear una contraseña nueva.';

    private const INVALID_LINK_MESSAGE = 'El enlace para restablecer la contraseña no es válido.';

    private const NEW_PASSWORD = 'new-password';

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::resetPasswords());
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.request'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.request'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get(route('password.reset', $notification->token));

            $response->assertOk();

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.request'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login', absolute: false));

            return true;
        });
    }

    public function test_login_screen_links_to_the_password_recovery_screen(): void
    {
        $this->get(route('login'))->assertSee(route('password.request'));
    }

    public function test_unknown_email_gets_the_same_message_as_a_registered_one(): void
    {
        Notification::fake();
        $this->app->setLocale('es');

        $user = User::factory()->create();

        $this->requestResetLink($user->email)->assertSessionHas('status', self::LINK_REQUESTED_MESSAGE)->assertSessionHasNoErrors();
        $this->requestResetLink('unknown@example.com')->assertSessionHas('status', self::LINK_REQUESTED_MESSAGE)->assertSessionHasNoErrors();
    }

    public function test_repeated_request_gets_the_same_message_without_sending_another_link(): void
    {
        Notification::fake();
        $this->app->setLocale('es');

        $user = User::factory()->create();

        $this->requestResetLink($user->email);

        $this->requestResetLink($user->email)->assertSessionHas('status', self::LINK_REQUESTED_MESSAGE)->assertSessionHasNoErrors();

        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_reset_screen_does_not_reveal_whether_an_email_is_registered(): void
    {
        $this->app->setLocale('es');

        $user = User::factory()->create();

        $this->resetPassword($user->email, 'invalid-token')->assertSessionHasErrors(['email' => self::INVALID_LINK_MESSAGE]);
        $this->resetPassword('unknown@example.com', 'invalid-token')->assertSessionHasErrors(['email' => self::INVALID_LINK_MESSAGE]);
    }

    public function test_link_stops_working_after_sixty_minutes(): void
    {
        $this->app->setLocale('es');

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->travel(61)->minutes();

        $this->resetPassword($user->email, $token)->assertSessionHasErrors(['email' => self::INVALID_LINK_MESSAGE]);

        $this->assertPasswordIsUnchanged($user);
    }

    public function test_link_can_only_be_used_once(): void
    {
        $this->app->setLocale('es');

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->resetPassword($user->email, $token)->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, (string) $user->fresh()?->password));

        $this->resetPassword($user->email, $token, 'another-password')->assertSessionHasErrors(['email' => self::INVALID_LINK_MESSAGE]);

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, (string) $user->fresh()?->password));
    }

    public function test_deactivated_user_does_not_receive_a_link_and_gets_the_same_message(): void
    {
        Notification::fake();
        $this->app->setLocale('es');

        $user = User::factory()->create(['is_active' => false]);

        $this->requestResetLink($user->email)->assertSessionHas('status', self::LINK_REQUESTED_MESSAGE)->assertSessionHasNoErrors();

        Notification::assertNotSentTo($user, ResetPassword::class);
    }

    public function test_link_requested_before_deactivation_no_longer_works(): void
    {
        $this->app->setLocale('es');

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $user->forceFill(['is_active' => false])->save();

        $this->resetPassword($user->email, $token)->assertSessionHasErrors(['email' => self::INVALID_LINK_MESSAGE]);

        $this->assertPasswordIsUnchanged($user);
    }

    private function requestResetLink(string $email): TestResponse
    {
        return $this->from(route('password.request'))->post(route('password.email'), ['email' => $email]);
    }

    private function resetPassword(string $email, string $token, string $password = self::NEW_PASSWORD): TestResponse
    {
        return $this->from(route('password.reset', $token))->post(route('password.update'), [
            'token' => $token,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);
    }

    private function assertPasswordIsUnchanged(User $user): void
    {
        $this->assertTrue(Hash::check('password', (string) $user->fresh()?->password));
    }
}
