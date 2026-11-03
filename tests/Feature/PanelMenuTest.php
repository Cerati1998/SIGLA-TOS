<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PanelMenuTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Render the menu for a signed-in user that only has the "allowed" ability.
     *
     * @param  array<int, array<string, mixed>>  $links
     */
    private function renderMenu(array $links): string
    {
        Gate::define('allowed', fn (): bool => true);
        Gate::define('denied', fn (): bool => false);

        $this->actingAs(User::factory()->create());

        return (string) $this->blade('<x-panel.menu :links="$links" />', ['links' => $links]);
    }

    public function test_it_renders_groups_links_and_submenus_from_the_array(): void
    {
        $html = $this->renderMenu([
            ['header' => 'Operaciones'],
            ['name' => 'Inicio', 'icon' => 'home', 'route' => '/inicio', 'active' => true],
            ['name' => 'Kits', 'icon' => 'box', 'submenu' => [
                ['name' => 'Lista de kits', 'route' => '/kits'],
            ]],
        ]);

        $this->assertStringContainsString('Operaciones', $html);
        $this->assertStringContainsString('href="/inicio"', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('Kits', $html);
        $this->assertStringContainsString('href="/kits"', $html);
    }

    public function test_it_hides_what_the_user_cannot_access(): void
    {
        $html = $this->renderMenu([
            ['header' => 'Grupo oculto', 'can' => 'denied'],
            ['name' => 'Enlace oculto', 'icon' => 'home', 'route' => '/oculto', 'can' => 'denied'],
            ['name' => 'Enlace visible', 'icon' => 'home', 'route' => '/visible', 'can' => ['denied', 'allowed']],
            ['name' => 'Kits', 'icon' => 'box', 'submenu' => [
                ['name' => 'Subenlace oculto', 'route' => '/sub-oculto', 'can' => 'denied'],
                ['name' => 'Subenlace visible', 'route' => '/sub-visible', 'can' => 'allowed'],
            ]],
        ]);

        $this->assertStringNotContainsString('Grupo oculto', $html);
        $this->assertStringNotContainsString('/oculto', $html);
        $this->assertStringContainsString('/visible', $html);
        $this->assertStringNotContainsString('/sub-oculto', $html);
        $this->assertStringContainsString('/sub-visible', $html);
    }

    public function test_the_panel_shows_the_home_link_as_active(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('intranet.dashboard'));

        $response->assertOk()->assertSee('aria-current="page"', false)->assertSeeText('Inicio');
    }
}
