<?php

namespace Tests\Feature;

use App\Livewire\Forms\WorkerForm;
use App\Livewire\Providers\CreateModal as ProviderCreateModal;
use App\Livewire\Tables\WorkersTable;
use App\Livewire\Workers\CreateModal;
use App\Livewire\Workers\EditModal;
use App\Models\Identity;
use App\Models\Provider;
use App\Models\User;
use App\Models\Worker;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class WorkersManagementTest extends TestCase
{
    use RefreshDatabase;

    private const string DNI_URL = 'https://lookup.test/dni';

    private const string FOREIGNER_CARD = '4';

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

        $this->get(route('intranet.workers.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_own_provider_and_deactivated_workers(): void
    {
        $provider = Provider::factory()->create(['business_name' => 'Precintos del Norte S.A.C.']);
        Worker::factory()->create(['document_number' => '10000001', 'full_name' => 'Ana Propia', 'position' => 'Operaria de bodega']);
        Worker::factory()->fromProvider($provider)->create(['full_name' => 'Beto Externo']);
        Worker::factory()->trashed()->create(['full_name' => 'Carla Inactiva']);

        $this->get(route('intranet.workers.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Proveedores', 'Trabajadores', 'Tipos de dispositivo'])
            ->assertSee(route('intranet.workers.index'))
            ->assertSeeText('DNI 10000001')
            ->assertSeeText('Operaria de bodega')
            ->assertSeeText('Propio')
            ->assertSeeText('Precintos del Norte S.A.C.')
            ->assertSeeText('Carla Inactiva')
            ->assertSeeText('Inactivo')
            ->assertSeeLivewire(ProviderCreateModal::class);
    }

    public function test_each_row_gets_the_id_of_its_worker(): void
    {
        $worker = Worker::factory()->create();

        Livewire::test(WorkersTable::class)->assertSeeHtml("id: {$worker->id},");
    }

    public function test_the_list_is_ordered_by_name_and_filters_by_search_origin_and_status(): void
    {
        Worker::factory()->create(['full_name' => 'Zoila Propia']);
        Worker::factory()->fromProvider()->create(['document_number' => '20000002', 'full_name' => 'Alberto Externo']);
        Worker::factory()->trashed()->create(['full_name' => 'Mario Inactivo']);

        Livewire::test(WorkersTable::class)->assertSeeInOrder(['Alberto Externo', 'Mario Inactivo', 'Zoila Propia']);

        Livewire::test(WorkersTable::class)
            ->set('search', 'Zoila')
            ->assertSeeText('Zoila Propia')
            ->assertDontSeeText('Alberto Externo');

        Livewire::test(WorkersTable::class)
            ->set('search', '20000002')
            ->assertSeeText('Alberto Externo')
            ->assertDontSeeText('Zoila Propia');

        Livewire::test(WorkersTable::class)
            ->set('filterComponents.origin', 'provider')
            ->assertSeeText('Alberto Externo')
            ->assertDontSeeText('Zoila Propia');

        Livewire::test(WorkersTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Mario Inactivo')
            ->assertDontSeeText('Zoila Propia');
    }

    public function test_an_own_worker_is_created_without_a_provider(): void
    {
        $provider = Provider::factory()->create();

        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', ' 12345678 ')
            ->set('form.fullName', ' Ana Propia ')
            ->set('form.position', ' Operaria de bodega ')
            ->set('form.origin', WorkerForm::OWN)
            ->set('form.providerId', $provider->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Trabajador creado')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('workers', [
            'identity_id' => Identity::DNI,
            'document_number' => '12345678',
            'full_name' => 'Ana Propia',
            'position' => 'Operaria de bodega',
            'is_own' => true,
            'provider_id' => null,
        ]);
    }

    public function test_the_position_is_optional_and_stored_empty_when_left_blank(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '12345678')
            ->set('form.fullName', 'Ana Propia')
            ->set('form.position', '   ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('workers', ['document_number' => '12345678', 'position' => null]);

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '87654321')
            ->set('form.fullName', 'Beto Propio')
            ->set('form.position', str_repeat('a', 101))
            ->call('save')
            ->assertHasErrors(['form.position' => 'max']);
    }

    public function test_the_provider_window_tells_the_worker_form_which_provider_it_created(): void
    {
        Livewire::test(ProviderCreateModal::class)
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', '20123456789')
            ->set('form.businessName', 'Proveedor Nuevo S.A.C.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('provider-created', providerId: Provider::query()->value('id'));
    }

    public function test_the_provider_select_searches_the_active_providers_without_the_search_in_the_url(): void
    {
        Provider::factory()->create(['document_number' => '20000000011', 'business_name' => 'Precintos del Sur S.A.C.']);
        Provider::factory()->create(['document_number' => '20000000022', 'business_name' => 'Sellos del Norte S.A.C.']);
        Provider::factory()->trashed()->create(['document_number' => '20000000033', 'business_name' => 'Precintos Cerrados S.A.C.']);

        $this->postJson(route('intranet.providers.options'), ['search' => 'precintos'])
            ->assertOk()
            ->assertExactJson([['id' => Provider::query()->where('business_name', 'Precintos del Sur S.A.C.')->value('id'), 'name' => 'Precintos del Sur S.A.C.', 'description' => 'RUC 20000000011']]);

        $this->postJson(route('intranet.providers.options'), ['search' => '20000000022'])
            ->assertOk()
            ->assertJsonPath('0.name', 'Sellos del Norte S.A.C.')
            ->assertJsonCount(1);

        $this->getJson(route('intranet.providers.options').'?search=precintos')->assertMethodNotAllowed();
    }

    public function test_the_provider_select_shows_the_chosen_provider_even_if_it_was_deactivated(): void
    {
        $provider = Provider::factory()->trashed()->create(['business_name' => 'Proveedor Cerrado']);

        $this->postJson(route('intranet.providers.options'), ['selected' => [$provider->id]])
            ->assertOk()
            ->assertJsonPath('0.id', $provider->id)
            ->assertJsonPath('0.name', 'Proveedor Cerrado (desactivado)');
    }

    public function test_guests_cannot_search_providers(): void
    {
        auth()->logout();

        $this->postJson(route('intranet.providers.options'), ['search' => 'precintos'])->assertUnauthorized();
    }

    public function test_a_worker_of_a_provider_is_created_with_an_active_provider(): void
    {
        $provider = Provider::factory()->create();

        Livewire::test(CreateModal::class)
            ->set('form.identityId', self::FOREIGNER_CARD)
            ->set('form.documentNumber', '000000000003')
            ->set('form.fullName', 'Beto Externo')
            ->set('form.origin', WorkerForm::FROM_PROVIDER)
            ->set('form.providerId', $provider->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('workers', [
            'identity_id' => self::FOREIGNER_CARD,
            'document_number' => '000000000003',
            'is_own' => false,
            'provider_id' => $provider->id,
        ]);
    }

    public function test_a_worker_of_a_provider_needs_an_active_provider(): void
    {
        $deactivatedProvider = Provider::factory()->trashed()->create();

        Livewire::test(CreateModal::class)
            ->set('form.documentNumber', '12345678')
            ->set('form.fullName', 'Beto Externo')
            ->set('form.origin', WorkerForm::FROM_PROVIDER)
            ->call('save')
            ->assertHasErrors(['form.providerId' => 'required'])
            ->assertSee('Elige el proveedor del trabajador.')
            ->set('form.providerId', $deactivatedProvider->id)
            ->call('save')
            ->assertHasErrors(['form.providerId' => 'exists']);

        $this->assertDatabaseCount('workers', 0);
    }

    public function test_the_fields_of_a_worker_are_validated(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.identityId', '')
            ->call('save')
            ->assertHasErrors(['form.identityId' => 'required', 'form.documentNumber' => 'required', 'form.fullName' => 'required'])
            ->assertNotDispatched('toast');

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '1234')
            ->set('form.fullName', 'Ana Propia')
            ->set('form.origin', 'other')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'digits', 'form.origin' => 'in'])
            ->assertSee('El DNI debe tener 8 dígitos.');
    }

    public function test_a_document_cannot_be_repeated_even_if_the_other_worker_is_deactivated(): void
    {
        Worker::factory()->trashed()->create(['document_number' => '12345678']);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '12345678')
            ->set('form.fullName', 'Otra Persona')
            ->call('save')
            ->assertHasErrors(['form.documentNumber' => 'unique'])
            ->assertSee('Ya existe un trabajador con este documento, aunque esté desactivado.');

        Livewire::test(CreateModal::class)
            ->set('form.identityId', self::FOREIGNER_CARD)
            ->set('form.documentNumber', '12345678')
            ->set('form.fullName', 'Otra Persona')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_a_worker_can_be_edited_and_changed_to_own(): void
    {
        $worker = Worker::factory()->fromProvider()->trashed()->create(['full_name' => 'Nombre Anterior']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-worker', workerId: $worker->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.origin', WorkerForm::FROM_PROVIDER)
            ->set('form.fullName', 'Nombre Nuevo')
            ->set('form.origin', WorkerForm::OWN)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Trabajador actualizado');

        $this->assertDatabaseHas('workers', ['id' => $worker->id, 'full_name' => 'Nombre Nuevo', 'is_own' => true, 'provider_id' => null]);
    }

    public function test_editing_keeps_the_provider_of_the_worker_even_if_it_was_deactivated(): void
    {
        $savedProvider = Provider::factory()->create(['business_name' => 'Proveedor Cerrado']);
        $otherDeactivatedProvider = Provider::factory()->trashed()->create(['business_name' => 'Otro Cerrado']);
        $worker = Worker::factory()->fromProvider($savedProvider)->create();
        $savedProvider->delete();

        Livewire::test(EditModal::class)
            ->dispatch('edit-worker', workerId: $worker->id)
            ->assertSet('form.providerId', $savedProvider->id)
            ->set('form.fullName', 'Nombre Corregido')
            ->call('save')
            ->assertHasNoErrors()
            ->dispatch('edit-worker', workerId: $worker->id)
            ->set('form.providerId', $otherDeactivatedProvider->id)
            ->call('save')
            ->assertHasErrors(['form.providerId' => 'exists']);

        $this->assertDatabaseHas('workers', ['id' => $worker->id, 'full_name' => 'Nombre Corregido', 'provider_id' => $savedProvider->id]);
    }

    public function test_a_worker_can_be_deactivated_and_reactivated(): void
    {
        $worker = Worker::factory()->create();

        Livewire::test(WorkersTable::class)
            ->call('deactivate', $worker->id)
            ->assertDispatched('toast', icon: 'success', title: 'Trabajador desactivado');

        $this->assertSoftDeleted($worker);

        Livewire::test(WorkersTable::class)
            ->call('reactivate', $worker->id)
            ->assertDispatched('toast', icon: 'success', title: 'Trabajador reactivado');

        $this->assertNotSoftDeleted($worker);
    }

    public function test_a_found_dni_fills_the_name_with_given_names_first(): void
    {
        Http::fake([self::DNI_URL.'*' => Http::response([
            'first_name' => 'ANA MARÍA',
            'first_last_name' => 'PÉREZ',
            'second_last_name' => 'GARCÍA',
            'full_name' => 'PÉREZ GARCÍA ANA MARÍA',
        ])]);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '12345678')
            ->call('lookup')
            ->assertHasNoErrors()
            ->assertSet('form.lookupStatus', 'found')
            ->assertSet('form.fullName', 'Ana María Pérez García');
    }

    public function test_a_dni_not_found_or_a_service_down_lets_the_name_be_filled_by_hand(): void
    {
        Http::fakeSequence(self::DNI_URL.'*')
            ->push(['message' => 'dni no valido'], 422)
            ->push('', 500);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '12345678')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'not_found')
            ->set('form.documentNumber', '87654321')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'unavailable')
            ->set('form.fullName', 'Llenado A Mano')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('workers', ['document_number' => '87654321', 'full_name' => 'Llenado A Mano']);
    }

    public function test_only_a_dni_is_looked_up(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.identityId', self::FOREIGNER_CARD)
            ->set('form.documentNumber', '000000000003')
            ->call('lookup')
            ->assertHasErrors(['form.identityId' => 'in']);

        Http::assertNothingSent();
    }
}
