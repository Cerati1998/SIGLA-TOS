<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logging_out_ends_on_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->followingRedirects()->post(route('logout'));

        $response->assertOk()->assertSee(route('login.store'));

        $this->assertGuest();
    }

    public function test_internal_screens_send_to_login_after_logging_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'));

        $this->get(route('intranet.dashboard'))->assertRedirect(route('login'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function internalScreens(): array
    {
        return [
            'dashboard' => ['intranet.dashboard'],
            'profile' => ['intranet.profile.edit'],
        ];
    }

    #[DataProvider('internalScreens')]
    public function test_the_browser_cannot_keep_a_copy_of_internal_screens(string $routeName): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route($routeName));

        $response->assertOk();

        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
    }
}
