<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelBreadcrumbsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_single_breadcrumb_only_shows_the_title(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('intranet.dashboard'));

        $response->assertOk()
            ->assertSee('font-heading', false)
            ->assertDontSee('data-flux-breadcrumbs-item', false);
    }

    public function test_the_layout_links_every_breadcrumb_with_a_route_and_keeps_the_last_as_text(): void
    {
        $view = $this->blade(
            '<x-panel.page-header title="Kits" :breadcrumbs="$breadcrumbs" />',
            ['breadcrumbs' => [
                ['name' => 'Inicio', 'route' => '/inicio'],
                ['name' => 'Kits'],
            ]],
        );

        $view->assertSee('href="/inicio"', false)
            ->assertSeeInOrder(['Inicio', 'Kits'])
            ->assertDontSee('href="/kits"', false);
    }

    public function test_livewire_pages_pass_their_breadcrumbs_to_the_layout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('intranet.profile.edit'))
            ->assertOk()
            ->assertSee('data-flux-breadcrumbs-item', false)
            ->assertSeeInOrder(['Inicio', 'Perfil']);

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('intranet.security.edit'))
            ->assertOk()
            ->assertSee('data-flux-breadcrumbs-item', false)
            ->assertSeeInOrder(['Inicio', 'Seguridad']);
    }
}
