<?php

namespace Tests\Feature;

use App\Enums\PaymentCondition;
use App\Livewire\Clients\CreateModal;
use App\Livewire\Clients\EditModal;
use App\Livewire\Tables\ClientsTable;
use App\Models\Client;
use App\Models\Identity;
use App\Models\User;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ClientsManagementTest extends TestCase
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

        $this->get(route('intranet.clients.index'))->assertRedirect(route('login'));
    }

    public function test_the_terceros_menu_leads_to_the_list_with_the_payment_condition_and_the_deactivated_clients(): void
    {
        Client::factory()->create(['document_number' => '20000000001', 'business_name' => 'Cliente Contado']);
        Client::factory()->onCredit()->trashed()->create(['business_name' => 'Cliente Cerrado']);

        $this->get(route('intranet.clients.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Líneas navieras', 'Terceros', 'Proveedores', 'Clientes', 'Trabajadores'])
            ->assertSee(route('intranet.providers.index'))
            ->assertSee(route('intranet.clients.index'))
            ->assertSeeText('RUC 20000000001')
            ->assertSeeInOrder(['Cliente Cerrado', 'Crédito · 30 días', 'Inactivo', 'Cliente Contado', 'Contado', 'Activo']);
    }

    public function test_the_list_is_ordered_by_business_name_and_filters_by_search_and_status(): void
    {
        Client::factory()->create(['document_number' => '20000000002', 'business_name' => 'Zeta Exportaciones']);
        Client::factory()->create(['document_number' => '20000000001', 'business_name' => 'Alfa Agrícola']);
        Client::factory()->withDni()->trashed()->create(['document_number' => '12345678', 'business_name' => 'Persona Inactiva']);

        Livewire::test(ClientsTable::class)->assertSeeInOrder(['Alfa Agrícola', 'Persona Inactiva', 'Zeta Exportaciones']);

        Livewire::test(ClientsTable::class)
            ->set('search', 'Zeta')
            ->assertSeeText('Zeta Exportaciones')
            ->assertDontSeeText('Alfa Agrícola');

        Livewire::test(ClientsTable::class)
            ->set('search', '12345678')
            ->assertSeeText('Persona Inactiva')
            ->assertDontSeeText('Alfa Agrícola');

        Livewire::test(ClientsTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Persona Inactiva')
            ->assertDontSeeText('Zeta Exportaciones');
    }

    public function test_a_client_is_created_paying_in_cash_unless_credit_is_chosen(): void
    {
        Livewire::test(CreateModal::class)
            ->assertSet('form.paymentCondition', PaymentCondition::Cash->value)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '12345678')
            ->set('form.businessName', 'Nombre Apellido')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', icon: 'success', title: 'Cliente creado')
            ->assertDispatched('refreshDatatable')
            ->assertSet('form.paymentCondition', PaymentCondition::Cash->value);

        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', ' 20123456789 ')
            ->set('form.businessName', 'Agroexportadora del Perú S.A.C.')
            ->set('form.address', 'Av. Ejemplo 123')
            ->set('form.email', 'compras@agro.test')
            ->set('form.phone', '987654321')
            ->set('form.paymentCondition', PaymentCondition::Credit->value)
            ->set('form.creditDays', ' 30 ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false);

        $this->assertDatabaseHas('clients', ['document_number' => '12345678', 'address' => null, 'email' => null, 'phone' => null, 'payment_condition' => 'cash', 'credit_days' => null]);
        $this->assertDatabaseHas('clients', [
            'identity_id' => Identity::RUC,
            'document_number' => '20123456789',
            'business_name' => 'Agroexportadora del Perú S.A.C.',
            'address' => 'Av. Ejemplo 123',
            'email' => 'compras@agro.test',
            'phone' => '987654321',
            'payment_condition' => 'credit',
            'credit_days' => 30,
        ]);
    }

    public function test_credit_asks_for_its_days_and_cash_does_not_keep_them(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '20123456789')
            ->set('form.businessName', 'Cliente a Crédito')
            ->set('form.paymentCondition', PaymentCondition::Credit->value)
            ->call('save')
            ->assertHasErrors(['form.creditDays' => 'required'])
            ->set('form.creditDays', '0')
            ->call('save')
            ->assertHasErrors(['form.creditDays' => 'between'])
            ->set('form.creditDays', '366')
            ->call('save')
            ->assertHasErrors(['form.creditDays' => 'between'])
            ->set('form.creditDays', 'treinta')
            ->call('save')
            ->assertHasErrors(['form.creditDays' => 'integer']);

        $this->assertDatabaseCount('clients', 0);

        $client = Client::factory()->onCredit()->create();

        Livewire::test(EditModal::class)
            ->dispatch('edit-client', clientId: $client->id)
            ->assertSet('form.creditDays', '30')
            ->set('form.paymentCondition', PaymentCondition::Cash->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($client->refresh()->credit_days);
    }

    public function test_the_fields_of_a_client_are_validated(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '')
            ->set('form.paymentCondition', '')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'required', 'form.businessName' => 'required', 'form.paymentCondition' => 'required'])
            ->assertNotDispatched('toast');

        Livewire::test(CreateModal::class)
            ->set('form.identityId', '4')
            ->set('form.documentNumber', '123')
            ->set('form.businessName', 'Cliente')
            ->set('form.email', 'no-es-correo')
            ->set('form.paymentCondition', 'monthly')
            ->call('save')
            ->assertHasErrors(['form.identityId' => 'in', 'form.email' => 'email', 'form.paymentCondition']);

        $this->assertDatabaseCount('clients', 0);
    }

    public function test_a_document_cannot_be_repeated_even_if_the_other_client_is_deactivated(): void
    {
        Client::factory()->trashed()->create(['document_number' => '20123456789']);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', '20123456789')
            ->set('form.businessName', 'Otro Cliente')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'unique'])
            ->assertSeeText('Ya existe un cliente con este tipo y número de documento, aunque esté desactivado.');

        $this->assertDatabaseMissing('clients', ['business_name' => 'Otro Cliente']);
    }

    public function test_a_client_and_its_payment_condition_can_be_edited_even_if_it_is_deactivated(): void
    {
        $client = Client::factory()->trashed()->create(['business_name' => 'Nombre Anterior']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-client', clientId: $client->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.businessName', 'Nombre Anterior')
            ->assertSet('form.paymentCondition', PaymentCondition::Cash->value)
            ->set('form.businessName', 'Nombre Nuevo')
            ->set('form.phone', '')
            ->set('form.paymentCondition', PaymentCondition::Credit->value)
            ->set('form.creditDays', '45')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Cliente actualizado');

        $client->refresh();

        $this->assertSame('Nombre Nuevo', $client->business_name);
        $this->assertNull($client->phone);
        $this->assertSame(PaymentCondition::Credit, $client->payment_condition);
        $this->assertSame(45, $client->credit_days);
        $this->assertTrue($client->trashed());
    }

    public function test_a_client_can_be_deactivated_and_reactivated(): void
    {
        $client = Client::factory()->create();

        Livewire::test(ClientsTable::class)
            ->call('deactivate', $client->id)
            ->assertDispatched('toast', icon: 'success', title: 'Cliente desactivado');

        $this->assertSoftDeleted($client);

        Livewire::test(ClientsTable::class)
            ->call('reactivate', $client->id)
            ->assertDispatched('toast', icon: 'success', title: 'Cliente reactivado');

        $this->assertNotSoftDeleted($client);
    }

    public function test_a_found_ruc_fills_the_business_name_and_the_address(): void
    {
        Http::fake([self::RUC_URL.'*' => Http::response([
            'razon_social' => 'AGROEXPORTADORA DEL PERU S.A.C.',
            'direccion' => 'AV. EJEMPLO 123',
        ])]);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', '20123456789')
            ->call('lookup')
            ->assertHasNoErrors()
            ->assertSet('form.lookupStatus', 'found')
            ->assertSet('form.businessName', 'AGROEXPORTADORA DEL PERU S.A.C.')
            ->assertSet('form.address', 'AV. EJEMPLO 123');
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

        $this->assertDatabaseHas('clients', ['document_number' => '20999999999', 'business_name' => 'Llenado a Mano']);
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
            ->assertSet('form.lookupStatus', null);

        Http::assertNothingSent();
    }
}
