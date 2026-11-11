<?php

namespace Tests\Feature;

use App\Livewire\Carriers\DriversModal;
use App\Livewire\Drivers\CreateModal;
use App\Livewire\Drivers\EditModal;
use App\Livewire\Tables\CarrierDriversTable;
use App\Models\Carrier;
use App\Models\CarrierDriver;
use App\Models\Driver;
use App\Models\Identity;
use App\Models\User;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class DriversManagementTest extends TestCase
{
    use RefreshDatabase;

    private const string DNI_URL = 'https://lookup.test/dni';

    private Carrier $carrier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentitySeeder::class);
        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');
        $this->carrier = Carrier::factory()->create(['business_name' => 'TRANSPORTES DEL SUR S.A.C.', 'document_number' => '20123456789']);

        config([
            'services.sunat.ruc_url' => 'https://lookup.test/ruc',
            'services.sunat.dni_url' => self::DNI_URL,
            'services.sunat.token' => 'test-token',
        ]);
        Http::preventStrayRequests();
    }

    /**
     * Link a new driver to the given carrier, deactivated in it when asked.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function driverOf(Carrier $carrier, array $attributes = [], bool $deactivated = false): Driver
    {
        $driver = Driver::factory()->create($attributes);
        $link = CarrierDriver::query()->create(['carrier_id' => $carrier->id, 'driver_id' => $driver->id]);

        if ($deactivated) {
            $link->delete();
        }

        return $driver;
    }

    public function test_drivers_are_reached_from_each_carrier_and_not_from_the_menu(): void
    {
        $this->get(route('intranet.carriers.index'))
            ->assertOk()
            ->assertSeeInOrder(['Transporte', 'Transportistas', 'Vehículos'])
            ->assertDontSee('/intranet/drivers')
            ->assertSeeHtml("show-carrier-drivers', { carrierId: id }");

        $this->get('/intranet/drivers')->assertNotFound();

        Livewire::test(DriversModal::class)
            ->dispatch('show-carrier-drivers', carrierId: $this->carrier->id)
            ->assertSet('openModal', true)
            ->assertSeeText('TRANSPORTES DEL SUR S.A.C. · RUC 20123456789');
    }

    public function test_the_table_shows_only_the_drivers_of_the_carrier_by_name_with_their_state_in_it(): void
    {
        $alvarez = $this->driverOf($this->carrier, ['document_number' => '10000001', 'full_name' => 'ALVAREZ DIAZ LUIS', 'license' => 'B10000001']);
        $this->driverOf($this->carrier, ['document_number' => '10000002', 'full_name' => 'ZAPATA RIOS JOSE', 'license' => 'Q10000002']);
        $this->driverOf($this->carrier, ['document_number' => '10000003', 'full_name' => 'MEDINA SOTO EVA', 'license' => 'A10000003'], deactivated: true);
        $this->driverOf(Carrier::factory()->create(), ['full_name' => 'DE OTRA EMPRESA']);

        Livewire::test(CarrierDriversTable::class, ['carrierId' => $this->carrier->id])
            ->assertSeeInOrder(['ALVAREZ DIAZ LUIS', 'MEDINA SOTO EVA', 'Desactivado', 'ZAPATA RIOS JOSE'])
            ->assertDontSeeText('DE OTRA EMPRESA')
            ->assertSeeHtml("driverId: {$alvarez->id},");

        foreach (['ZAPATA', '10000002', 'Q10000002'] as $search) {
            Livewire::test(CarrierDriversTable::class, ['carrierId' => $this->carrier->id])
                ->set('search', $search)
                ->assertSeeText('ZAPATA RIOS JOSE')
                ->assertDontSeeText('ALVAREZ DIAZ LUIS');
        }

        Livewire::test(CarrierDriversTable::class, ['carrierId' => $this->carrier->id])
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('MEDINA SOTO EVA')
            ->assertDontSeeText('ZAPATA RIOS JOSE');
    }

    public function test_the_search_does_not_go_in_the_url_so_a_dni_is_not_left_there(): void
    {
        $this->assertFalse(Livewire::test(CarrierDriversTable::class, ['carrierId' => $this->carrier->id])->instance()->queryStringIsEnabled());
    }

    public function test_a_new_driver_is_created_and_added_with_the_license_in_capitals_and_without_spaces(): void
    {
        Http::fake([self::DNI_URL.'*' => Http::response(['message' => 'dni no valido'], 422)]);

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->assertSeeText('a TRANSPORTES DEL SUR S.A.C.')
            ->set('form.openModal', true)
            ->set('form.documentNumber', ' 46099190 ')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'not_found')
            ->set('form.fullName', ' PEREZ ROJAS JUAN ')
            ->set('form.license', ' b 46099190 ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertSet('form.carrierId', $this->carrier->id)
            ->assertDispatched('toast', icon: 'success', title: 'Conductor agregado')
            ->assertDispatched('refreshDatatable');

        $driver = Driver::query()->sole();

        $this->assertSame(Identity::DNI, $driver->identity_id);
        $this->assertSame('PEREZ ROJAS JUAN', $driver->full_name);
        $this->assertSame('B46099190', $driver->license);
        $this->assertDatabaseHas('carrier_driver', ['carrier_id' => $this->carrier->id, 'driver_id' => $driver->id, 'deleted_at' => null]);
    }

    public function test_a_driver_registered_in_another_carrier_is_added_with_its_data_and_without_looking_it_up(): void
    {
        Http::fake();
        $driver = $this->driverOf(Carrier::factory()->create(), ['document_number' => '46099190', 'full_name' => 'PEREZ ROJAS JUAN', 'license' => 'B46099190']);

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->set('form.documentNumber', '46099190')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'registered')
            ->assertSet('form.fullName', 'PEREZ ROJAS JUAN')
            ->assertSet('form.license', 'B46099190')
            ->set('form.fullName', 'OTRO NOMBRE')
            ->call('save')
            ->assertHasNoErrors();

        Http::assertNothingSent();
        $this->assertDatabaseCount('drivers', 1);
        $this->assertSame('PEREZ ROJAS JUAN', $driver->refresh()->full_name);
        $this->assertDatabaseHas('carrier_driver', ['carrier_id' => $this->carrier->id, 'driver_id' => $driver->id]);
        $this->assertDatabaseCount('carrier_driver', 2);
    }

    public function test_a_driver_already_active_in_the_carrier_is_not_added_again(): void
    {
        $this->driverOf($this->carrier, ['document_number' => '46099190']);

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->set('form.documentNumber', '46099190')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'in_carrier')
            ->assertSeeText('Este conductor ya está en este transportista.')
            ->call('save')
            ->assertHasErrors(['form.documentNumber'])
            ->assertNotDispatched('toast');

        $this->assertDatabaseCount('carrier_driver', 1);
    }

    public function test_a_driver_deactivated_in_the_carrier_is_reactivated_when_added(): void
    {
        $driver = $this->driverOf($this->carrier, ['document_number' => '46099190'], deactivated: true);

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->set('form.documentNumber', '46099190')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'inactive_in_carrier')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('carrier_driver', ['carrier_id' => $this->carrier->id, 'driver_id' => $driver->id, 'deleted_at' => null]);
        $this->assertDatabaseCount('carrier_driver', 1);
    }

    public function test_changing_from_a_registered_dni_to_a_new_one_clears_its_data(): void
    {
        Http::fake([self::DNI_URL.'*' => Http::response('', 500)]);
        $this->driverOf(Carrier::factory()->create(), ['document_number' => '46099190', 'full_name' => 'PEREZ ROJAS JUAN']);

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->set('form.documentNumber', '46099190')
            ->call('lookup')
            ->assertSet('form.fullName', 'PEREZ ROJAS JUAN')
            ->set('form.documentNumber', '46099191')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'unavailable')
            ->assertSet('form.fullName', '')
            ->assertSet('form.license', '')
            ->assertSet('form.registeredDriverId', null);
    }

    public function test_the_fields_of_a_new_driver_are_validated(): void
    {
        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'required'])
            ->assertNotDispatched('toast');

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->set('form.documentNumber', '46099190')
            ->set('form.license', 'B460991901234567')
            ->call('save')
            ->assertHasErrors(['form.fullName' => 'required', 'form.license' => 'max']);

        Driver::factory()->create(['license' => 'B46099190']);

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->set('form.documentNumber', '46099191')
            ->set('form.fullName', 'OTRO CONDUCTOR')
            ->set('form.license', 'b46099190')
            ->call('save')
            ->assertHasErrors(['form.license' => 'unique'])
            ->assertSeeText('Ya existe un conductor con este brevete, aunque esté desactivado.');

        $this->assertDatabaseCount('carrier_driver', 0);
    }

    public function test_the_carrier_and_the_document_type_cannot_be_changed_from_the_browser(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])->set('form.carrierId', Carrier::factory()->create()->id);
    }

    public function test_editing_a_driver_changes_its_data_in_every_carrier(): void
    {
        $driver = $this->driverOf($this->carrier, ['full_name' => 'NOMBRE ANTERIOR', 'license' => 'B46099190']);
        CarrierDriver::query()->create(['carrier_id' => Carrier::factory()->create()->id, 'driver_id' => $driver->id]);

        Livewire::test(EditModal::class)
            ->dispatch('edit-driver', driverId: $driver->id)
            ->assertSet('form.openModal', true)
            ->set('form.fullName', 'NOMBRE NUEVO')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', icon: 'success', title: 'Conductor actualizado');

        $this->assertSame('NOMBRE NUEVO', $driver->refresh()->full_name);
        $this->assertSame('B46099190', $driver->license);
    }

    public function test_a_driver_is_deactivated_and_reactivated_only_in_the_carrier(): void
    {
        $driver = $this->driverOf($this->carrier);
        $otherLink = CarrierDriver::query()->create(['carrier_id' => Carrier::factory()->create()->id, 'driver_id' => $driver->id]);
        $link = CarrierDriver::query()->where('carrier_id', $this->carrier->id)->sole();

        Livewire::test(CarrierDriversTable::class, ['carrierId' => $this->carrier->id])
            ->call('deactivate', $link->id)
            ->assertDispatched('toast', icon: 'success', title: 'Conductor desactivado');

        $this->assertSoftDeleted($link);
        $this->assertNotSoftDeleted($otherLink);
        $this->assertNotSoftDeleted($driver);

        Livewire::test(CarrierDriversTable::class, ['carrierId' => $this->carrier->id])
            ->call('reactivate', $link->id)
            ->assertDispatched('toast', icon: 'success', title: 'Conductor reactivado');

        $this->assertNotSoftDeleted($link);
    }

    public function test_the_link_of_another_carrier_cannot_be_deactivated_from_this_one(): void
    {
        $this->driverOf($this->carrier);
        $otherDriver = $this->driverOf(Carrier::factory()->create());
        $otherLink = CarrierDriver::query()->where('driver_id', $otherDriver->id)->sole();

        Livewire::test(CarrierDriversTable::class, ['carrierId' => $this->carrier->id])
            ->call('deactivate', $otherLink->id)
            ->assertNotFound();

        $this->assertNotSoftDeleted($otherLink);
    }

    public function test_a_found_dni_fills_the_full_name_that_can_be_corrected(): void
    {
        Http::fake([self::DNI_URL.'*' => Http::response(['full_name' => 'PEREZ ROJAS JUAN'])]);

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->set('form.documentNumber', '46099190')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'found')
            ->assertSet('form.fullName', 'PEREZ ROJAS JUAN')
            ->set('form.fullName', 'PÉREZ ROJAS JUAN')
            ->set('form.license', 'B46099190')
            ->call('save')
            ->assertHasNoErrors();

        Http::assertSent(fn (Request $request): bool => $request->url() === self::DNI_URL.'?numero=46099190');
        $this->assertDatabaseHas('drivers', ['document_number' => '46099190', 'full_name' => 'PÉREZ ROJAS JUAN']);
    }

    public function test_a_dni_with_a_wrong_format_is_not_looked_up(): void
    {
        Http::fake();

        Livewire::test(CreateModal::class, ['carrierId' => $this->carrier->id])
            ->set('form.documentNumber', '460991')
            ->call('lookup')
            ->assertHasErrors(['form.documentNumber' => 'digits'])
            ->assertSeeText('El DNI debe tener 8 dígitos.')
            ->assertSet('form.lookupStatus', null);

        Http::assertNothingSent();
    }

    public function test_editing_only_looks_up_the_dni_again_when_it_changes(): void
    {
        Http::fake([self::DNI_URL.'*' => Http::response(['full_name' => 'NOMBRE DE RENIEC'])]);
        $driver = $this->driverOf($this->carrier, ['document_number' => '46099190', 'full_name' => 'NOMBRE GUARDADO']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-driver', driverId: $driver->id)
            ->call('lookup')
            ->assertSet('form.fullName', 'NOMBRE GUARDADO')
            ->assertSet('form.lookupStatus', null)
            ->set('form.documentNumber', '46099191')
            ->call('lookup')
            ->assertSet('form.fullName', 'NOMBRE DE RENIEC');

        Http::assertSentCount(1);
    }
}
