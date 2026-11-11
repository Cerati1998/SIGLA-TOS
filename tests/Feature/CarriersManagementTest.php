<?php

namespace Tests\Feature;

use App\Livewire\Carriers\CreateModal;
use App\Livewire\Carriers\EditModal;
use App\Livewire\Tables\CarriersTable;
use App\Models\Carrier;
use App\Models\Identity;
use App\Models\User;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class CarriersManagementTest extends TestCase
{
    use RefreshDatabase;

    private const string RUC_URL = 'https://lookup.test/ruc';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentitySeeder::class);
        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');

        config([
            'services.sunat.ruc_url' => self::RUC_URL,
            'services.sunat.dni_url' => 'https://lookup.test/dni',
            'services.sunat.token' => 'test-token',
        ]);
        Http::preventStrayRequests();
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.carriers.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_active_and_deactivated_carriers(): void
    {
        Carrier::factory()->create(['document_number' => '20000000011', 'business_name' => 'Transportes Activos', 'phone' => null]);
        Carrier::factory()->trashed()->create(['business_name' => 'Transportes Cerrados']);

        $this->get(route('intranet.carriers.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Secciones de certificación', 'Transporte', 'Transportistas', 'Configuración'])
            ->assertSee(route('intranet.carriers.index'))
            ->assertSeeText('20000000011')
            ->assertSeeText('Transportes Activos')
            ->assertSeeText('Transportes Cerrados')
            ->assertSeeText('Desactivado');
    }

    public function test_the_list_is_ordered_by_business_name_and_filters_by_search_and_status(): void
    {
        Carrier::factory()->create(['document_number' => '20000000002', 'business_name' => 'Zeta Cargo']);
        $alfa = Carrier::factory()->create(['document_number' => '20000000001', 'business_name' => 'Alfa Transportes']);
        Carrier::factory()->trashed()->create(['document_number' => '20999999999', 'business_name' => 'Media Inactiva']);

        Livewire::test(CarriersTable::class)
            ->assertSeeInOrder(['Alfa Transportes', 'Media Inactiva', 'Zeta Cargo'])
            ->assertSeeHtml("id: {$alfa->id},");

        Livewire::test(CarriersTable::class)
            ->set('search', 'Zeta')
            ->assertSeeText('Zeta Cargo')
            ->assertDontSeeText('Alfa Transportes');

        Livewire::test(CarriersTable::class)
            ->set('search', '20999999999')
            ->assertSeeText('Media Inactiva')
            ->assertDontSeeText('Alfa Transportes');

        Livewire::test(CarriersTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Media Inactiva')
            ->assertDontSeeText('Zeta Cargo');
    }

    public function test_a_carrier_is_created_with_its_ruc_and_its_optional_data(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.documentNumber', ' 20123456789 ')
            ->set('form.businessName', 'Transportes del Sur S.A.C.')
            ->set('form.address', 'Av. Ejemplo 123')
            ->set('form.email', 'flota@transportes.test')
            ->set('form.phone', '987654321')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Transportista creado')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('carriers', [
            'identity_id' => Identity::RUC,
            'document_number' => '20123456789',
            'business_name' => 'Transportes del Sur S.A.C.',
            'address' => 'Av. Ejemplo 123',
            'email' => 'flota@transportes.test',
            'phone' => '987654321',
        ]);
    }

    public function test_the_optional_data_is_stored_empty_when_left_blank(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '20123456789')
            ->set('form.businessName', 'Transportes Mínimos')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('carriers', ['document_number' => '20123456789', 'address' => null, 'email' => null, 'phone' => null]);
    }

    public function test_the_document_type_cannot_be_changed_from_ruc(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(CreateModal::class)->set('form.identityId', Identity::DNI);
    }

    public function test_the_fields_of_a_carrier_are_validated(): void
    {
        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'required', 'form.businessName' => 'required'])
            ->assertNotDispatched('toast');

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '12345678')
            ->set('form.businessName', 'Transportista')
            ->set('form.email', 'no-es-correo')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'digits', 'form.email' => 'email']);

        $this->assertDatabaseCount('carriers', 0);
    }

    public function test_a_ruc_cannot_be_repeated_even_if_the_other_carrier_is_deactivated(): void
    {
        Carrier::factory()->trashed()->create(['document_number' => '20123456789']);

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '20123456789')
            ->set('form.businessName', 'Otro Transportista')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'unique'])
            ->assertSeeText('Ya existe un transportista con este RUC, aunque esté desactivado.');

        $this->assertDatabaseMissing('carriers', ['business_name' => 'Otro Transportista']);
    }

    public function test_a_carrier_can_be_edited_even_if_it_is_deactivated(): void
    {
        $carrier = Carrier::factory()->trashed()->create(['business_name' => 'Nombre Anterior']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-carrier', carrierId: $carrier->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.businessName', 'Nombre Anterior')
            ->set('form.businessName', 'Nombre Nuevo')
            ->set('form.phone', '')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Transportista actualizado');

        $carrier->refresh();

        $this->assertSame('Nombre Nuevo', $carrier->business_name);
        $this->assertNull($carrier->phone);
        $this->assertTrue($carrier->trashed());
    }

    public function test_a_carrier_can_be_deactivated_and_reactivated(): void
    {
        $carrier = Carrier::factory()->create();

        Livewire::test(CarriersTable::class)
            ->call('deactivate', $carrier->id)
            ->assertDispatched('toast', icon: 'success', title: 'Transportista desactivado');

        $this->assertSoftDeleted($carrier);

        Livewire::test(CarriersTable::class)
            ->call('reactivate', $carrier->id)
            ->assertDispatched('toast', icon: 'success', title: 'Transportista reactivado');

        $this->assertNotSoftDeleted($carrier);
    }

    public function test_a_found_ruc_fills_the_business_name_and_the_address(): void
    {
        Http::fake([self::RUC_URL.'*' => Http::response([
            'razon_social' => 'TRANSPORTES DEL SUR S.A.C.',
            'direccion' => 'AV. EJEMPLO 123',
        ])]);

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '20123456789')
            ->call('lookup')
            ->assertHasNoErrors()
            ->assertSet('form.lookupStatus', 'found')
            ->assertSet('form.businessName', 'TRANSPORTES DEL SUR S.A.C.')
            ->assertSet('form.address', 'AV. EJEMPLO 123')
            ->set('form.businessName', 'Transportes del Sur')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('carriers', ['document_number' => '20123456789', 'business_name' => 'Transportes del Sur']);
    }

    public function test_a_ruc_not_found_or_a_service_down_lets_the_data_be_filled_by_hand(): void
    {
        Http::fakeSequence(self::RUC_URL.'*')
            ->push(['message' => 'ruc no valido'], 422)
            ->push('', 500);

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '20123456789')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'not_found')
            ->set('form.documentNumber', '20999999999')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'unavailable')
            ->set('form.businessName', 'Llenado a Mano')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('carriers', ['document_number' => '20999999999', 'business_name' => 'Llenado a Mano']);
    }

    public function test_a_ruc_with_a_wrong_format_is_not_looked_up(): void
    {
        Http::fake();

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '2012345')
            ->call('lookup')
            ->assertHasErrors(['form.documentNumber' => 'digits'])
            ->assertSeeText('El RUC debe tener 11 dígitos.')
            ->assertSet('form.lookupStatus', null);

        Http::assertNothingSent();
    }

    public function test_editing_only_looks_up_the_ruc_again_when_it_changes(): void
    {
        Http::fake([self::RUC_URL.'*' => Http::response(['razon_social' => 'NUEVA RAZON SOCIAL'])]);
        $carrier = Carrier::factory()->create(['document_number' => '20123456789', 'business_name' => 'Razón Guardada']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-carrier', carrierId: $carrier->id)
            ->call('lookup')
            ->assertSet('form.businessName', 'Razón Guardada')
            ->assertSet('form.lookupStatus', null)
            ->set('form.documentNumber', '20999999999')
            ->call('lookup')
            ->assertSet('form.businessName', 'NUEVA RAZON SOCIAL');

        Http::assertSentCount(1);
    }
}
