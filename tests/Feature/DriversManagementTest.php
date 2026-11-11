<?php

namespace Tests\Feature;

use App\Livewire\Drivers\CreateModal;
use App\Livewire\Drivers\EditModal;
use App\Livewire\Tables\DriversTable;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentitySeeder::class);
        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');

        config([
            'services.sunat.ruc_url' => 'https://lookup.test/ruc',
            'services.sunat.dni_url' => self::DNI_URL,
            'services.sunat.token' => 'test-token',
        ]);
        Http::preventStrayRequests();
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.drivers.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_active_and_deactivated_drivers(): void
    {
        Driver::factory()->create(['document_number' => '46099190', 'full_name' => 'PEREZ ROJAS JUAN', 'license' => 'B46099190']);
        Driver::factory()->trashed()->create(['full_name' => 'QUISPE MAMANI ANA']);

        $this->get(route('intranet.drivers.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Secciones de certificación', 'Transporte', 'Transportistas', 'Conductores', 'Configuración'])
            ->assertSee(route('intranet.drivers.index'))
            ->assertSeeText('46099190')
            ->assertSeeText('PEREZ ROJAS JUAN')
            ->assertSeeText('B46099190')
            ->assertSeeText('QUISPE MAMANI ANA')
            ->assertSeeText('Desactivado');
    }

    public function test_the_list_is_ordered_by_name_and_filters_by_search_and_status(): void
    {
        $alvarez = Driver::factory()->create(['document_number' => '10000001', 'full_name' => 'ALVAREZ DIAZ LUIS', 'license' => 'B10000001']);
        Driver::factory()->create(['document_number' => '10000002', 'full_name' => 'ZAPATA RIOS JOSE', 'license' => 'Q10000002']);
        Driver::factory()->trashed()->create(['document_number' => '10000003', 'full_name' => 'MEDINA SOTO EVA', 'license' => 'A10000003']);

        Livewire::test(DriversTable::class)
            ->assertSeeInOrder(['ALVAREZ DIAZ LUIS', 'MEDINA SOTO EVA', 'ZAPATA RIOS JOSE'])
            ->assertSeeHtml("id: {$alvarez->id},");

        foreach (['ZAPATA', '10000002', 'Q10000002'] as $search) {
            Livewire::test(DriversTable::class)
                ->set('search', $search)
                ->assertSeeText('ZAPATA RIOS JOSE')
                ->assertDontSeeText('ALVAREZ DIAZ LUIS');
        }

        Livewire::test(DriversTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('MEDINA SOTO EVA')
            ->assertDontSeeText('ZAPATA RIOS JOSE');
    }

    public function test_the_search_does_not_go_in_the_url_so_a_dni_is_not_left_there(): void
    {
        $this->assertFalse(Livewire::test(DriversTable::class)->instance()->queryStringForSearchEnabled());
    }

    public function test_a_driver_is_created_with_the_license_in_capitals_and_without_spaces(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.documentNumber', ' 46099190 ')
            ->set('form.fullName', ' PEREZ ROJAS JUAN ')
            ->set('form.license', ' b 46099190 ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Conductor creado')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('drivers', [
            'identity_id' => Identity::DNI,
            'document_number' => '46099190',
            'full_name' => 'PEREZ ROJAS JUAN',
            'license' => 'B46099190',
        ]);
    }

    public function test_the_document_type_cannot_be_changed_from_dni(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(CreateModal::class)->set('form.identityId', Identity::RUC);
    }

    public function test_the_fields_of_a_driver_are_validated(): void
    {
        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'required', 'form.fullName' => 'required', 'form.license' => 'required'])
            ->assertNotDispatched('toast');

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '4609919')
            ->set('form.fullName', 'PEREZ ROJAS JUAN')
            ->set('form.license', 'B460991901234567')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'digits', 'form.license' => 'max']);

        $this->assertDatabaseCount('drivers', 0);
    }

    public function test_a_dni_or_a_license_cannot_be_repeated_even_if_the_other_driver_is_deactivated(): void
    {
        Driver::factory()->trashed()->create(['document_number' => '46099190', 'license' => 'B46099190']);

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '46099190')
            ->set('form.fullName', 'OTRO CONDUCTOR')
            ->set('form.license', 'b46099190')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'unique', 'form.license' => 'unique'])
            ->assertSeeText('Ya existe un conductor con este DNI, aunque esté desactivado.')
            ->assertSeeText('Ya existe un conductor con este brevete, aunque esté desactivado.');

        $this->assertDatabaseMissing('drivers', ['full_name' => 'OTRO CONDUCTOR']);
    }

    public function test_a_driver_can_be_edited_even_if_it_is_deactivated(): void
    {
        $driver = Driver::factory()->trashed()->create(['full_name' => 'NOMBRE ANTERIOR', 'license' => 'B46099190']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-driver', driverId: $driver->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.fullName', 'NOMBRE ANTERIOR')
            ->set('form.fullName', 'NOMBRE NUEVO')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Conductor actualizado');

        $driver->refresh();

        $this->assertSame('NOMBRE NUEVO', $driver->full_name);
        $this->assertSame('B46099190', $driver->license);
        $this->assertTrue($driver->trashed());
    }

    public function test_a_driver_can_be_deactivated_and_reactivated(): void
    {
        $driver = Driver::factory()->create();

        Livewire::test(DriversTable::class)
            ->call('deactivate', $driver->id)
            ->assertDispatched('toast', icon: 'success', title: 'Conductor desactivado');

        $this->assertSoftDeleted($driver);

        Livewire::test(DriversTable::class)
            ->call('reactivate', $driver->id)
            ->assertDispatched('toast', icon: 'success', title: 'Conductor reactivado');

        $this->assertNotSoftDeleted($driver);
    }

    public function test_a_found_dni_fills_the_full_name_that_can_be_corrected(): void
    {
        Http::fake([self::DNI_URL.'*' => Http::response([
            'first_name' => 'JUAN',
            'first_last_name' => 'PEREZ',
            'second_last_name' => 'ROJAS',
            'full_name' => 'PEREZ ROJAS JUAN',
        ])]);

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '46099190')
            ->call('lookup')
            ->assertHasNoErrors()
            ->assertSet('form.lookupStatus', 'found')
            ->assertSet('form.fullName', 'PEREZ ROJAS JUAN')
            ->set('form.fullName', 'PÉREZ ROJAS JUAN')
            ->set('form.license', 'B46099190')
            ->call('save')
            ->assertHasNoErrors();

        Http::assertSent(fn (Request $request): bool => $request->url() === self::DNI_URL.'?numero=46099190');
        $this->assertDatabaseHas('drivers', ['document_number' => '46099190', 'full_name' => 'PÉREZ ROJAS JUAN']);
    }

    public function test_a_dni_not_found_or_a_service_down_lets_the_data_be_filled_by_hand(): void
    {
        Http::fakeSequence(self::DNI_URL.'*')
            ->push(['message' => 'dni no valido'], 422)
            ->push('', 500);

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '46099190')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'not_found')
            ->set('form.documentNumber', '46099191')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'unavailable')
            ->set('form.fullName', 'LLENADO A MANO')
            ->set('form.license', 'B46099191')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('drivers', ['document_number' => '46099191', 'full_name' => 'LLENADO A MANO']);
    }

    public function test_a_dni_with_a_wrong_format_is_not_looked_up(): void
    {
        Http::fake();

        Livewire::test(CreateModal::class)
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
        $driver = Driver::factory()->create(['document_number' => '46099190', 'full_name' => 'NOMBRE GUARDADO']);

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
