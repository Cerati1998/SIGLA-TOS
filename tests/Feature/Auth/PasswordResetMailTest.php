<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Mail\Markdown;
use Tests\TestCase;

class PasswordResetMailTest extends TestCase
{
    private const TOKEN = 'test-token';

    public function test_mail_uses_the_shared_layout(): void
    {
        $html = $this->renderMailFor($this->makeUser());

        $this->assertStringContainsString('images/sigla-logo-mail.png', $html);
        $this->assertStringContainsString('Este es un mensaje automático de SIGLA.', $html);
    }

    public function test_mail_greets_the_user_by_name(): void
    {
        $html = $this->renderMailFor($this->makeUser());

        $this->assertStringContainsString('Hola, María Torres.', $html);
    }

    public function test_button_links_to_the_reset_screen_and_the_url_appears_nowhere_else(): void
    {
        $user = $this->makeUser();
        $url = route('password.reset', ['token' => self::TOKEN, 'email' => $user->email]);

        $html = $this->renderMailFor($user);

        $this->assertMatchesRegularExpression('/<a href="'.preg_quote($url, '/').'"[^>]*>\s*Restablecer contraseña\s*<\/a>/u', $html);
        $this->assertSame(1, substr_count($html, $url));
        $this->assertStringNotContainsString('Si tienes problemas', $html);
    }

    public function test_plain_text_version_does_not_print_the_url(): void
    {
        $user = $this->makeUser();
        $message = (new ResetPassword(self::TOKEN))->toMail($user);

        $text = (string) app(Markdown::class)->renderText($message->markdown, $message->data());

        $this->assertStringNotContainsString('reset-password', $text);
        $this->assertStringContainsString('Restablecer contraseña: abre este correo en una aplicación que muestre HTML', $text);
    }

    public function test_panel_shows_the_account_the_lima_request_time_and_the_configured_expiry(): void
    {
        config(['auth.passwords.users.expire' => 30]);
        $this->travelTo('2026-11-03 22:38:00');

        $html = $this->renderMailFor($this->makeUser());

        $this->assertStringContainsString('maria.torres@empresa.com', $html);
        $this->assertStringContainsString('3 de noviembre de 2026, 5:38 p. m.', $html);
        $this->assertStringContainsString('30 minutos', $html);
    }

    public function test_user_name_is_shown_as_plain_text_without_html_or_markdown(): void
    {
        $user = $this->makeUser(['name' => '<b>Ana</b> [clic](https://malicioso.test)']);

        $html = $this->renderMailFor($user);

        $this->assertStringNotContainsString('<b>Ana</b>', $html);
        $this->assertStringNotContainsString('href="https://malicioso.test"', $html);
        $this->assertStringContainsString('[clic](https://malicioso.test)', $html);
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function makeUser(array $attributes = []): User
    {
        return User::factory()->make([
            'name' => 'María Torres',
            'email' => 'maria.torres@empresa.com',
            ...$attributes,
        ]);
    }

    private function renderMailFor(User $user): string
    {
        return (string) (new ResetPassword(self::TOKEN))->toMail($user)->render();
    }
}
