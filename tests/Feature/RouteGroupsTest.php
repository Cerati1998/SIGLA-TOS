<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_enter_the_intranet_or_the_shared_screens(): void
    {
        $this->get('/intranet/dashboard')->assertRedirect(route('login'));
        $this->get('/settings/profile')->assertRedirect(route('login'));
    }

    public function test_the_dashboard_is_only_available_under_the_intranet_prefix(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/intranet/dashboard')->assertOk();
        $this->get('/dashboard')->assertNotFound();
    }

    public function test_the_profile_stays_outside_the_intranet_prefix(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/settings/profile')->assertOk();
        $this->get('/intranet/settings/profile')->assertNotFound();
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
}
