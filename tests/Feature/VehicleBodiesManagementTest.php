<?php

namespace Tests\Feature;

use App\Livewire\Tables\VehicleBodiesTable;
use App\Livewire\VehicleBodies\CreateModal;
use App\Livewire\VehicleBodies\EditModal;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBody;
use Database\Seeders\VehicleBodySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VehicleBodiesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');
    }

    public function test_the_seeder_loads_the_initial_bodies_in_capitals_without_repeating_them(): void
    {
        VehicleBody::factory()->trashed()->create(['name' => 'CISTERNA']);

        $this->seed(VehicleBodySeeder::class);
        $this->seed(VehicleBodySeeder::class);

        $this->assertDatabaseCount('vehicle_bodies', 9);
        $this->assertDatabaseHas('vehicle_bodies', ['name' => 'REMOLCADOR 4X2']);
        $this->assertDatabaseHas('vehicle_bodies', ['name' => 'CHASIS GOOSENECK']);
        $this->assertSoftDeleted('vehicle_bodies', ['name' => 'CISTERNA']);
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.vehicle-bodies.index'))->assertRedirect(route('login'));
    }

    public function test_the_list_shows_the_bodies_by_name_with_how_many_vehicles_use_them(): void
    {
        $platform = VehicleBody::factory()->create(['name' => 'PLATAFORMA']);
        Vehicle::factory()->count(2)->for($platform, 'body')->create();
        Vehicle::factory()->for($platform, 'body')->trashed()->create();
        VehicleBody::factory()->create(['name' => 'CAMA BAJA']);
        VehicleBody::factory()->trashed()->create(['name' => 'REMOLCADOR']);

        $this->get(route('intranet.vehicle-bodies.index'))
            ->assertOk()
            ->assertSee(route('intranet.vehicle-bodies.index'));

        Livewire::test(VehicleBodiesTable::class)
            ->assertSeeInOrder(['CAMA BAJA', '0', 'PLATAFORMA', '3', 'REMOLCADOR', 'Desactivada'])
            ->assertSeeHtml("id: {$platform->id},");

        Livewire::test(VehicleBodiesTable::class)
            ->set('search', 'cama')
            ->assertSeeText('CAMA BAJA')
            ->assertDontSeeText('PLATAFORMA');

        Livewire::test(VehicleBodiesTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('REMOLCADOR')
            ->assertDontSeeText('PLATAFORMA');
    }

    public function test_a_body_is_created_in_capitals_and_its_name_is_unique_even_if_deactivated(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.name', '  cama   baja ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Carrocería creada');

        $this->assertDatabaseHas('vehicle_bodies', ['name' => 'CAMA BAJA']);

        VehicleBody::factory()->trashed()->create(['name' => 'PLATAFORMA']);

        Livewire::test(CreateModal::class)
            ->set('form.name', 'plataforma')
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique'])
            ->assertSeeText('Ya existe una carrocería con este nombre, aunque esté desactivada.');

        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.name' => 'required']);
    }

    public function test_a_body_can_be_edited(): void
    {
        $body = VehicleBody::factory()->create(['name' => 'PORTA CONTENEDOR']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-vehicle-body', vehicleBodyId: $body->id)
            ->assertSet('form.name', 'PORTA CONTENEDOR')
            ->set('form.name', 'portacontenedor')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', icon: 'success', title: 'Carrocería actualizada');

        $this->assertSame('PORTACONTENEDOR', $body->refresh()->name);
    }

    public function test_a_deactivated_body_is_kept_by_its_vehicles_and_can_be_reactivated(): void
    {
        $body = VehicleBody::factory()->create();
        $vehicle = Vehicle::factory()->for($body, 'body')->create();

        Livewire::test(VehicleBodiesTable::class)
            ->call('deactivate', $body->id)
            ->assertDispatched('toast', icon: 'success', title: 'Carrocería desactivada');

        $this->assertSoftDeleted($body);
        $this->assertSame($body->id, $vehicle->refresh()->body->id);

        Livewire::test(VehicleBodiesTable::class)
            ->call('reactivate', $body->id)
            ->assertDispatched('toast', icon: 'success', title: 'Carrocería reactivada');

        $this->assertNotSoftDeleted($body);
    }
}
