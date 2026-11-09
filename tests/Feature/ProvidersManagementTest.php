<?php

namespace Tests\Feature;

use App\Livewire\Providers\CreateModal;
use App\Livewire\Providers\EditModal;
use App\Livewire\Tables\ProvidersTable;
use App\Models\Identity;
use App\Models\Provider;
use App\Models\User;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ProvidersManagementTest extends TestCase
{
    use RefreshDatabase;

    private const string RUC_URL = 'https://lookup.test/ruc';

    private const string DNI_URL = 'https://lookup.test/dni';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentitySeeder::class);
        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');

        config([
            'services.sunat.ruc_url' => self::RUC_URL,
            'services.sunat.dni_url' => self::DNI_URL,
            'services.sunat.token' => 'test-token',
        ]);
        Http::preventStrayRequests();
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.providers.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_active_and_deactivated_providers(): void
    {
        Provider::factory()->create(['document_number' => '20000000001', 'business_name' => 'Proveedor Activo']);
        Provider::factory()->trashed()->create(['business_name' => 'Proveedor Cerrado']);

        $this->get(route('intranet.providers.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Líneas navieras', 'Proveedores', 'Tipos de dispositivo'])
            ->assertSee(route('intranet.providers.index'))
            ->assertSeeText('RUC 20000000001')
            ->assertSeeText('Proveedor Activo')
            ->assertSeeText('Proveedor Cerrado')
            ->assertSeeText('Inactivo');
    }

    public function test_the_list_is_ordered_by_business_name_and_filters_by_search_and_status(): void
    {
        Provider::factory()->create(['document_number' => '20000000002', 'business_name' => 'Zeta Precintos']);
        Provider::factory()->create(['document_number' => '20000000001', 'business_name' => 'Alfa Sellos']);
        Provider::factory()->withDni()->trashed()->create(['document_number' => '12345678', 'business_name' => 'Persona Inactiva']);

        Livewire::test(ProvidersTable::class)->assertSeeInOrder(['Alfa Sellos', 'Persona Inactiva', 'Zeta Precintos']);

        Livewire::test(ProvidersTable::class)
            ->set('search', 'Zeta')
            ->assertSeeText('Zeta Precintos')
            ->assertDontSeeText('Alfa Sellos');

        Livewire::test(ProvidersTable::class)
            ->set('search', '12345678')
            ->assertSeeText('Persona Inactiva')
            ->assertDontSeeText('Alfa Sellos');

        Livewire::test(ProvidersTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Persona Inactiva')
            ->assertDontSeeText('Zeta Precintos');
    }

    public function test_a_provider_can_be_created_with_its_optional_data(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', ' 20123456789 ')
            ->set('form.businessName', 'Precintos del Perú S.A.C.')
            ->set('form.address', 'Av. Ejemplo 123')
            ->set('form.email', 'ventas@precintos.test')
            ->set('form.phone', '987654321')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Proveedor creado')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('providers', [
            'identity_id' => Identity::RUC,
            'document_number' => '20123456789',
            'business_name' => 'Precintos del Perú S.A.C.',
            'address' => 'Av. Ejemplo 123',
            'email' => 'ventas@precintos.test',
            'phone' => '987654321',
        ]);
    }

    public function test_the_optional_data_is_stored_empty_when_left_blank(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '12345678')
            ->set('form.businessName', 'Nombre Apellido')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('providers', ['document_number' => '12345678', 'address' => null, 'email' => null, 'phone' => null]);
    }

    public function test_the_fields_of_a_provider_are_validated(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'required', 'form.businessName' => 'required'])
            ->assertNotDispatched('toast');

        Livewire::test(CreateModal::class)
            ->set('form.identityId', '4')
            ->set('form.documentNumber', '123')
            ->set('form.businessName', 'Proveedor')
            ->set('form.email', 'no-es-correo')
            ->call('save')
            ->assertHasErrors(['form.identityId' => 'in', 'form.email' => 'email']);

        $this->assertDatabaseCount('providers', 0);
    }

    public function test_a_document_cannot_be_repeated_even_if_the_other_provider_is_deactivated(): void
    {
        Provider::factory()->trashed()->create(['document_number' => '20123456789']);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', '20123456789')
            ->set('form.businessName', 'Otro Proveedor')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'unique'])
            ->assertSeeText('Ya existe un proveedor con este tipo y número de documento, aunque esté desactivado.');

        $this->assertDatabaseMissing('providers', ['business_name' => 'Otro Proveedor']);
    }

    public function test_a_provider_can_be_edited_even_if_it_is_deactivated(): void
    {
        $provider = Provider::factory()->trashed()->create(['business_name' => 'Nombre Anterior']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-provider', providerId: $provider->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.businessName', 'Nombre Anterior')
            ->set('form.businessName', 'Nombre Nuevo')
            ->set('form.phone', '')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Proveedor actualizado');

        $provider->refresh();

        $this->assertSame('Nombre Nuevo', $provider->business_name);
        $this->assertNull($provider->phone);
        $this->assertTrue($provider->trashed());
    }

    public function test_a_provider_can_be_deactivated_and_reactivated(): void
    {
        $provider = Provider::factory()->create();

        Livewire::test(ProvidersTable::class)
            ->call('deactivate', $provider->id)
            ->assertDispatched('toast', icon: 'success', title: 'Proveedor desactivado');

        $this->assertSoftDeleted($provider);

        Livewire::test(ProvidersTable::class)
            ->call('reactivate', $provider->id)
            ->assertDispatched('toast', icon: 'success', title: 'Proveedor reactivado');

        $this->assertNotSoftDeleted($provider);
    }

    public function test_a_found_ruc_fills_the_business_name_and_the_address(): void
    {
        Http::fake([self::RUC_URL.'*' => Http::response([
            'razon_social' => 'PRECINTOS DEL PERU S.A.C.',
            'direccion' => 'AV. EJEMPLO 123',
        ])]);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', '20123456789')
            ->call('lookup')
            ->assertHasNoErrors()
            ->assertSet('form.lookupStatus', 'found')
            ->assertSet('form.businessName', 'PRECINTOS DEL PERU S.A.C.')
            ->assertSet('form.address', 'AV. EJEMPLO 123')
            ->set('form.businessName', 'Precintos del Perú')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('providers', ['document_number' => '20123456789', 'business_name' => 'Precintos del Perú']);
    }

    public function test_a_found_dni_puts_the_full_name_in_the_business_name(): void
    {
        Http::fake([self::DNI_URL.'*' => Http::response([
            'first_name' => 'NOMBRE',
            'first_last_name' => 'PATERNO',
            'second_last_name' => 'MATERNO',
            'full_name' => 'PATERNO MATERNO NOMBRE',
        ])]);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '12345678')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'found')
            ->assertSet('form.businessName', 'PATERNO MATERNO NOMBRE');

        Http::assertSent(fn (Request $request): bool => $request->url() === self::DNI_URL.'?numero=12345678');
    }

    public function test_a_document_not_found_or_a_service_down_lets_the_data_be_filled_by_hand(): void
    {
        Http::fakeSequence(self::RUC_URL.'*')
            ->push(['message' => 'ruc no valido'], 422)
            ->push('', 500);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', '20123456789')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'not_found')
            ->set('form.documentNumber', '20999999999')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'unavailable')
            ->set('form.businessName', 'Llenado a Mano')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('providers', ['document_number' => '20999999999', 'business_name' => 'Llenado a Mano']);
    }

    public function test_a_document_with_a_wrong_format_is_not_looked_up(): void
    {
        Http::fake();

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', '2012345')
            ->call('lookup')
            ->assertHasErrors(['form.documentNumber' => 'digits'])
            ->assertSeeText('El RUC debe tener 11 dígitos.')
            ->set('form.identityId', Identity::DNI)
            ->call('lookup')
            ->assertSeeText('El DNI debe tener 8 dígitos.')
            ->assertSet('form.lookupStatus', null);

        Http::assertNothingSent();
    }

    public function test_editing_only_looks_up_the_document_again_when_its_number_changes(): void
    {
        Http::fake([self::RUC_URL.'*' => Http::response(['razon_social' => 'NUEVA RAZON SOCIAL'])]);
        $provider = Provider::factory()->create(['document_number' => '20123456789', 'business_name' => 'Razón Guardada']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-provider', providerId: $provider->id)
            ->call('lookup')
            ->assertSet('form.businessName', 'Razón Guardada')
            ->assertSet('form.lookupStatus', null)
            ->set('form.documentNumber', '20999999999')
            ->call('lookup')
            ->assertSet('form.businessName', 'NUEVA RAZON SOCIAL');

        Http::assertSentCount(1);
    }

    public function test_the_magnifier_looks_up_the_same_document_again(): void
    {
        Http::fake([self::RUC_URL.'*' => Http::response(['razon_social' => 'RAZON DE SUNAT'])]);
        $provider = Provider::factory()->create(['document_number' => '20123456789']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-provider', providerId: $provider->id)
            ->call('lookup', true)
            ->assertSet('form.lookupStatus', 'found')
            ->assertSet('form.businessName', 'RAZON DE SUNAT');

        Http::assertSentCount(1);
    }
}
