<?php

namespace Tests\Feature;

use App\Livewire\Tables\YardZonesTable;
use App\Livewire\YardZones\CreateModal;
use App\Livewire\YardZones\EditModal;
use App\Models\Branch;
use App\Models\User;
use App\Models\YardZone;
use Database\Seeders\BranchSeeder;
use Database\Seeders\YardZoneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class YardZonesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.yard-zones.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_active_and_deactivated_zones(): void
    {
        $branch = Branch::factory()->create(['name' => 'Depot de Prueba']);
        YardZone::factory()->for($branch)->create(['name' => 'Zona Norte', 'capacity' => 120]);
        YardZone::factory()->for($branch)->trashed()->create(['name' => 'Zona Antigua']);

        $this->get(route('intranet.yard-zones.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Trabajadores', 'Zonas de patio', 'Tipos de dispositivo'])
            ->assertSee(route('intranet.yard-zones.index'))
            ->assertSeeText('Zona Norte')
            ->assertSeeText('Depot de Prueba')
            ->assertSeeText('120')
            ->assertSeeText('Zona Antigua')
            ->assertSeeText('Desactivada');
    }

    public function test_the_list_is_ordered_by_branch_and_name_and_filters_by_search_branch_and_status(): void
    {
        $first = Branch::factory()->create(['name' => 'A Sucursal']);
        $second = Branch::factory()->create(['name' => 'B Sucursal']);
        YardZone::factory()->for($second)->create(['name' => 'Zona Alfa']);
        YardZone::factory()->for($first)->create(['name' => 'Zona Zeta']);
        YardZone::factory()->for($first)->trashed()->create(['name' => 'Zona Media']);

        Livewire::test(YardZonesTable::class)
            ->assertSeeInOrder(['Zona Media', 'Zona Zeta', 'Zona Alfa']);

        Livewire::test(YardZonesTable::class)
            ->set('search', 'alfa')
            ->assertSeeText('Zona Alfa')
            ->assertDontSeeText('Zona Zeta');

        Livewire::test(YardZonesTable::class)
            ->set('filterComponents.branch', (string) $first->id)
            ->assertSeeText('Zona Zeta')
            ->assertDontSeeText('Zona Alfa');

        Livewire::test(YardZonesTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Zona Media')
            ->assertDontSeeText('Zona Zeta');
    }

    public function test_a_zone_is_created_with_its_branch_name_and_capacity(): void
    {
        $branch = Branch::factory()->create();

        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.branchId', (string) $branch->id)
            ->set('form.name', '  Zona de Reparación ')
            ->set('form.capacity', ' 90 ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Zona de patio guardada')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('yard_zones', ['branch_id' => $branch->id, 'name' => 'Zona de Reparación', 'capacity' => 90]);
    }

    public function test_the_fields_of_a_zone_are_validated(): void
    {
        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.branchId' => 'required', 'form.name' => 'required', 'form.capacity' => 'required'])
            ->assertSeeText('Elige la sucursal de la zona.')
            ->assertNotDispatched('toast');

        foreach (['0', '-5', '2.5', 'diez'] as $capacity) {
            Livewire::test(CreateModal::class)
                ->set('form.capacity', $capacity)
                ->call('save')
                ->assertHasErrors('form.capacity')
                ->assertSeeText('La capacidad debe ser un número entero mayor que 0.');
        }

        Livewire::test(CreateModal::class)
            ->set('form.name', str_repeat('A', 101))
            ->call('save')
            ->assertHasErrors(['form.name' => 'max']);

        $this->assertDatabaseCount('yard_zones', 0);
    }

    public function test_a_name_cannot_be_repeated_in_the_branch_even_if_the_other_zone_is_deactivated(): void
    {
        $branch = Branch::factory()->create();
        YardZone::factory()->for($branch)->trashed()->create(['name' => 'Zona Norte']);

        Livewire::test(CreateModal::class)
            ->set('form.branchId', (string) $branch->id)
            ->set('form.name', 'Zona Norte')
            ->set('form.capacity', '40')
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique'])
            ->assertSeeText('Ya existe una zona con este nombre en la sucursal, aunque esté desactivada.');

        Livewire::test(CreateModal::class)
            ->set('form.branchId', (string) Branch::factory()->create()->id)
            ->set('form.name', 'Zona Norte')
            ->set('form.capacity', '40')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, YardZone::withTrashed()->where('name', 'Zona Norte')->count());
    }

    public function test_only_active_branches_can_be_chosen_for_a_new_zone(): void
    {
        Branch::factory()->create(['name' => 'Sucursal Abierta']);
        $closed = Branch::factory()->trashed()->create(['name' => 'Sucursal Cerrada']);

        Livewire::test(CreateModal::class)
            ->assertSeeText('Sucursal Abierta')
            ->assertDontSeeText('Sucursal Cerrada')
            ->set('form.branchId', (string) $closed->id)
            ->set('form.name', 'Zona Norte')
            ->set('form.capacity', '40')
            ->call('save')
            ->assertHasErrors(['form.branchId' => 'exists'])
            ->assertSeeText('Elige una sucursal activa.');
    }

    public function test_a_zone_keeps_its_deactivated_branch_when_it_is_edited(): void
    {
        $branch = Branch::factory()->trashed()->create(['name' => 'Sucursal Cerrada']);
        $yardZone = YardZone::factory()->for($branch)->create(['name' => 'Zona Anterior', 'capacity' => 40]);

        Livewire::test(EditModal::class)
            ->dispatch('edit-yard-zone', yardZoneId: $yardZone->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.name', 'Zona Anterior')
            ->assertSeeText('Sucursal Cerrada (desactivada)')
            ->set('form.name', 'Zona Nueva')
            ->set('form.capacity', '60')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Zona de patio guardada');

        $yardZone->refresh();

        $this->assertSame('Zona Nueva', $yardZone->name);
        $this->assertSame(60, $yardZone->capacity);
        $this->assertSame($branch->id, $yardZone->branch_id);
    }

    public function test_a_zone_can_be_deactivated_and_reactivated(): void
    {
        $yardZone = YardZone::factory()->create();

        Livewire::test(YardZonesTable::class)
            ->call('deactivate', $yardZone->id)
            ->assertDispatched('toast', icon: 'success', title: 'Zona de patio desactivada');

        $this->assertSoftDeleted($yardZone);

        Livewire::test(YardZonesTable::class)
            ->call('reactivate', $yardZone->id)
            ->assertDispatched('toast', icon: 'success', title: 'Zona de patio reactivada');

        $this->assertNotSoftDeleted($yardZone);
    }

    public function test_the_seeder_loads_only_the_default_zone_and_keeps_what_was_managed_on_the_screen(): void
    {
        Branch::factory()->create();
        $mainBranch = Branch::factory()->create(['code' => BranchSeeder::MAIN_BRANCH_CODE]);

        $this->seed(YardZoneSeeder::class);
        $this->seed(YardZoneSeeder::class);

        $yardZone = YardZone::query()->sole();

        $this->assertSame('Zona certificación', $yardZone->name);
        $this->assertSame(50, $yardZone->capacity);
        $this->assertSame($mainBranch->id, $yardZone->branch_id);

        $yardZone->delete();

        $this->seed(YardZoneSeeder::class);

        $this->assertSame(0, YardZone::query()->count());
        $this->assertSame(1, YardZone::withTrashed()->count());
    }
}
