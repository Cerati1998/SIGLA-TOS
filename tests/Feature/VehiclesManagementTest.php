<?php

namespace Tests\Feature;

use App\Enums\VehicleCategory;
use App\Enums\VehicleKind;
use App\Livewire\Tables\VehiclesTable;
use App\Livewire\Vehicles\CreateModal;
use App\Livewire\Vehicles\EditModal;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBody;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VehiclesManagementTest extends TestCase
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

        $this->get(route('intranet.vehicles.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_active_and_deactivated_vehicles(): void
    {
        $body = VehicleBody::factory()->create(['name' => 'PLATAFORMA']);
        Vehicle::factory()->for($body, 'body')->create(['plate' => 'XYZ789', 'kind' => VehicleKind::SemiTrailer, 'category' => VehicleCategory::O4]);
        Vehicle::factory()->trashed()->create(['plate' => 'GHI012']);

        $this->get(route('intranet.vehicles.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Transporte', 'Transportistas', 'Conductores', 'Vehículos', 'Carrocerías', 'Configuración'])
            ->assertSee(route('intranet.vehicles.index'))
            ->assertSeeText('XYZ789')
            ->assertSeeText('Semirremolque')
            ->assertSeeText('PLATAFORMA')
            ->assertSeeText('O4')
            ->assertSeeText('GHI012')
            ->assertSeeText('Desactivado');
    }

    public function test_the_list_is_ordered_by_plate_and_filters_by_search_kind_body_category_and_status(): void
    {
        $platform = VehicleBody::factory()->create(['name' => 'PLATAFORMA']);
        $first = Vehicle::factory()->create(['plate' => 'AAA111', 'brand' => 'VOLVO']);
        Vehicle::factory()->for($platform, 'body')->create(['plate' => 'ZZZ999', 'brand' => 'RANDON', 'kind' => VehicleKind::SemiTrailer, 'category' => VehicleCategory::O3]);
        Vehicle::factory()->trashed()->create(['plate' => 'MMM555', 'brand' => 'SCANIA']);

        Livewire::test(VehiclesTable::class)
            ->assertSeeInOrder(['AAA111', 'MMM555', 'ZZZ999'])
            ->assertSeeHtml("id: {$first->id},");

        foreach ([
            ['search', 'RANDON'],
            ['search', 'zzz999'],
            ['filterComponents.kind', VehicleKind::SemiTrailer->value],
            ['filterComponents.body', (string) $platform->id],
            ['filterComponents.category', VehicleCategory::O3->value],
        ] as [$property, $value]) {
            Livewire::test(VehiclesTable::class)
                ->set($property, $value)
                ->assertSeeText('ZZZ999')
                ->assertDontSeeText('AAA111');
        }

        Livewire::test(VehiclesTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('MMM555')
            ->assertDontSeeText('ZZZ999');
    }

    public function test_a_vehicle_is_created_with_the_plate_in_capitals_without_spaces_or_hyphens(): void
    {
        $body = VehicleBody::factory()->create();

        Livewire::test(CreateModal::class)
            ->assertSet('form.kind', VehicleKind::Tractor->value)
            ->assertSet('form.category', VehicleCategory::N3->value)
            ->set('form.openModal', true)
            ->set('form.plate', ' abc-1 23 ')
            ->set('form.brand', ' VOLVO ')
            ->set('form.kind', VehicleKind::SemiTrailer->value)
            ->set('form.vehicleBodyId', (string) $body->id)
            ->set('form.category', VehicleCategory::Other->value)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Vehículo creado')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('vehicles', [
            'plate' => 'ABC123',
            'brand' => 'VOLVO',
            'kind' => 'semi_trailer',
            'vehicle_body_id' => $body->id,
            'category' => 'other',
        ]);
    }

    public function test_the_fields_of_a_vehicle_are_validated(): void
    {
        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.plate' => 'required', 'form.brand' => 'required', 'form.vehicleBodyId' => 'required'])
            ->assertNotDispatched('toast');

        Livewire::test(CreateModal::class)
            ->set('form.plate', 'ABC.123')
            ->set('form.brand', 'VOLVO')
            ->set('form.kind', 'truck')
            ->set('form.vehicleBodyId', '999')
            ->set('form.category', 'M1')
            ->call('save')
            ->assertHasErrors(['form.plate' => 'alpha_num', 'form.kind', 'form.vehicleBodyId' => 'exists', 'form.category']);

        Livewire::test(CreateModal::class)
            ->set('form.plate', 'ABCDEFG12345')
            ->call('save')
            ->assertHasErrors(['form.plate' => 'max']);

        $this->assertDatabaseCount('vehicles', 0);
    }

    public function test_a_plate_cannot_be_repeated_even_if_the_other_vehicle_is_deactivated(): void
    {
        Vehicle::factory()->trashed()->create(['plate' => 'ABC123']);

        Livewire::test(CreateModal::class)
            ->set('form.plate', 'abc-123')
            ->set('form.brand', 'OTRA MARCA')
            ->set('form.vehicleBodyId', (string) VehicleBody::factory()->create()->id)
            ->call('save')
            ->assertHasErrors(['form.plate' => 'unique'])
            ->assertSeeText('Ya existe un vehículo con esta placa, aunque esté desactivado.');

        $this->assertDatabaseMissing('vehicles', ['brand' => 'OTRA MARCA']);
    }

    public function test_only_active_bodies_can_be_chosen_for_a_new_vehicle(): void
    {
        VehicleBody::factory()->create(['name' => 'ACTIVA']);
        $deactivated = VehicleBody::factory()->trashed()->create(['name' => 'DESACTIVADA']);

        Livewire::test(CreateModal::class)
            ->assertSeeText('ACTIVA')
            ->assertDontSeeText('DESACTIVADA')
            ->set('form.plate', 'ABC123')
            ->set('form.brand', 'VOLVO')
            ->set('form.vehicleBodyId', (string) $deactivated->id)
            ->call('save')
            ->assertHasErrors(['form.vehicleBodyId' => 'exists'])
            ->assertSeeText('Elige una carrocería activa.');
    }

    public function test_a_vehicle_keeps_its_deactivated_body_when_it_is_edited(): void
    {
        $body = VehicleBody::factory()->trashed()->create(['name' => 'CAMA BAJA']);
        $vehicle = Vehicle::factory()->for($body, 'body')->trashed()->create(['brand' => 'MARCA ANTERIOR']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-vehicle', vehicleId: $vehicle->id)
            ->assertSet('form.openModal', true)
            ->assertSeeText('CAMA BAJA (desactivada)')
            ->set('form.brand', 'MARCA NUEVA')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Vehículo actualizado');

        $vehicle->refresh();

        $this->assertSame('MARCA NUEVA', $vehicle->brand);
        $this->assertSame($body->id, $vehicle->vehicle_body_id);
        $this->assertTrue($vehicle->trashed());
    }

    public function test_a_vehicle_can_be_deactivated_and_reactivated(): void
    {
        $vehicle = Vehicle::factory()->create();

        Livewire::test(VehiclesTable::class)
            ->call('deactivate', $vehicle->id)
            ->assertDispatched('toast', icon: 'success', title: 'Vehículo desactivado');

        $this->assertSoftDeleted($vehicle);

        Livewire::test(VehiclesTable::class)
            ->call('reactivate', $vehicle->id)
            ->assertDispatched('toast', icon: 'success', title: 'Vehículo reactivado');

        $this->assertNotSoftDeleted($vehicle);
    }
}
