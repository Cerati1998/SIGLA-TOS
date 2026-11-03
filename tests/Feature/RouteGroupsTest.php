<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_enter_the_intranet(): void
    {
        $this->get('/intranet/dashboard')->assertRedirect(route('login'));
        $this->get('/intranet/settings/profile')->assertRedirect(route('login'));
    }

    public function test_internal_screens_are_only_available_under_the_intranet_prefix(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/intranet/dashboard')->assertOk();
        $this->get('/intranet/settings/profile')->assertOk();

        $this->get('/dashboard')->assertNotFound();
        $this->get('/settings/profile')->assertNotFound();
    }

    public function test_settings_redirects_to_the_intranet_profile(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/intranet/settings')->assertRedirect(route('intranet.profile.edit'));
    }

    public function test_logged_in_users_visiting_the_login_screen_go_to_the_intranet(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('login'))->assertRedirect(route('intranet.dashboard'));
    }

    public function test_the_extranet_has_no_routes_yet(): void
    {
        $this->get('/extranet')->assertNotFound();
    }

    public function test_passkey_endpoints_stay_at_the_root_and_point_to_the_intranet(): void
    {
        $this->getJson('/.well-known/passkey-endpoints')->assertOk()->assertExactJson([
            'enroll' => route('intranet.security.edit'),
            'manage' => route('intranet.security.edit'),
        ]);
    }
}
