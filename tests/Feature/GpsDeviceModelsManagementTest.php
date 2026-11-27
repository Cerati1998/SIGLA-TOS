<?php

namespace Tests\Feature;

use App\Enums\GpsDeviceCategory;
use App\Enums\GpsOpeningType;
use App\Livewire\GpsDeviceModels\CreateModal;
use App\Livewire\GpsDeviceModels\EditModal;
use App\Livewire\Providers\CreateModal as ProviderCreateModal;
use App\Livewire\Tables\GpsDeviceModelsTable;
use App\Models\GpsDeviceModel;
use App\Models\Provider;
use App\Models\User;
use Database\Seeders\GpsDeviceModelSeeder;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GpsDeviceModelsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentitySeeder::class);
        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.gps-device-models.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_active_and_deactivated_models(): void
    {
        $brand = Provider::factory()->create(['business_name' => 'Rastreo del Norte S.A.C.']);
        GpsDeviceModel::factory()->for($brand, 'brand')->withMonitoringCodes('BR', '240')->create([
            'name' => 'Hunter Block',
            'category' => GpsDeviceCategory::ContainerLock,
            'opening_type' => GpsOpeningType::Remote,
        ]);
        GpsDeviceModel::factory()->for($brand, 'brand')->trashed()->create([
            'name' => 'Rastreador Viejo',
            'category' => GpsDeviceCategory::PortableTracker,
            'opening_type' => GpsOpeningType::None,
        ]);

        $this->get(route('intranet.gps-device-models.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Tipos de dispositivo', 'Modelos GPS', 'Administración', 'Usuarios', 'Configuración'])
            ->assertSee(route('intranet.gps-device-models.index'))
            ->assertSeeText('Rastreo del Norte S.A.C.')
            ->assertSeeText('Hunter Block')
            ->assertSeeText('Candado para contenedor')
            ->assertSeeText('Remota')
            ->assertSeeText('BR · 240')
            ->assertSeeText('Rastreador Viejo')
            ->assertSeeText('GPS portátil')
            ->assertSeeText('Sin apertura')
            ->assertSeeText('Desactivado')
            ->assertSeeLivewire(CreateModal::class)
            ->assertSeeLivewire(EditModal::class)
            ->assertSeeLivewire(ProviderCreateModal::class);
    }

    public function test_each_row_gets_the_id_of_its_model(): void
    {
        $gpsDeviceModel = GpsDeviceModel::factory()->create();

        Livewire::test(GpsDeviceModelsTable::class)->assertSeeHtml("id: {$gpsDeviceModel->id},");
    }

    public function test_the_list_is_ordered_by_brand_and_name_and_filters_by_search_brand_category_and_status(): void
    {
        $firstBrand = Provider::factory()->create(['business_name' => 'Alfa Rastreo S.A.C.']);
        $secondBrand = Provider::factory()->create(['business_name' => 'Beta Monitoreo S.A.']);
        GpsDeviceModel::factory()->for($secondBrand, 'brand')->create(['name' => 'Aguila Lock']);
        GpsDeviceModel::factory()->for($firstBrand, 'brand')->create(['name' => 'Zeta Block']);
        GpsDeviceModel::factory()->for($firstBrand, 'brand')->withMonitoringCodes('BR', '247')->create([
            'name' => 'Truck Lock',
            'category' => GpsDeviceCategory::DoorLock,
        ]);
        GpsDeviceModel::factory()->for($firstBrand, 'brand')->trashed()->create(['name' => 'Modelo Retirado']);

        Livewire::test(GpsDeviceModelsTable::class)->assertSeeInOrder(['Modelo Retirado', 'Truck Lock', 'Zeta Block', 'Aguila Lock']);

        Livewire::test(GpsDeviceModelsTable::class)
            ->set('search', 'Zeta')
            ->assertSeeText('Zeta Block')
            ->assertDontSeeText('Truck Lock');

        Livewire::test(GpsDeviceModelsTable::class)
            ->set('search', '247')
            ->assertSeeText('Truck Lock')
            ->assertDontSeeText('Zeta Block');

        Livewire::test(GpsDeviceModelsTable::class)
            ->set('filterComponents.brand', (string) $secondBrand->id)
            ->assertSeeText('Aguila Lock')
            ->assertDontSeeText('Zeta Block');

        Livewire::test(GpsDeviceModelsTable::class)
            ->set('filterComponents.category', GpsDeviceCategory::DoorLock->value)
            ->assertSeeText('Truck Lock')
            ->assertDontSeeText('Zeta Block');

        Livewire::test(GpsDeviceModelsTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Modelo Retirado')
            ->assertDontSeeText('Zeta Block');
    }

    public function test_a_model_is_created_with_its_brand_category_opening_and_monitoring_codes(): void
    {
        $brand = Provider::factory()->create();

        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.brandId', $brand->id)
            ->set('form.name', ' Hunter Block ')
            ->set('form.category', GpsDeviceCategory::ContainerLock->value)
            ->set('form.openingType', GpsOpeningType::Remote->value)
            ->set('form.monitoringProductCode', ' br ')
            ->set('form.monitoringDeviceTypeCode', ' 240 ')
            ->set('form.description', ' Candado externo para contenedores. ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Modelo GPS guardado')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('gps_device_models', [
            'brand_id' => $brand->id,
            'name' => 'Hunter Block',
            'category' => GpsDeviceCategory::ContainerLock->value,
            'opening_type' => GpsOpeningType::Remote->value,
            'monitoring_product_code' => 'BR',
            'monitoring_device_type_code' => '240',
            'description' => 'Candado externo para contenedores.',
        ]);
    }

    public function test_the_codes_and_the_description_are_optional_and_stored_empty_when_left_blank(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.brandId', Provider::factory()->create()->id)
            ->set('form.name', 'Block R')
            ->set('form.category', GpsDeviceCategory::PortableLock->value)
            ->set('form.openingType', GpsOpeningType::Manual->value)
            ->set('form.monitoringProductCode', ' ')
            ->set('form.description', ' ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('gps_device_models', [
            'name' => 'Block R',
            'monitoring_product_code' => null,
            'monitoring_device_type_code' => null,
            'description' => null,
        ]);
    }

    public function test_the_fields_of_a_model_are_validated(): void
    {
        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors([
                'form.brandId' => 'required',
                'form.name' => 'required',
                'form.category' => 'required',
                'form.openingType' => 'required',
            ])
            ->assertSee('Elige la marca del modelo.')
            ->assertSee('Escribe el nombre del modelo.')
            ->assertSee('Elige la categoría.')
            ->assertSee('Elige la apertura.')
            ->set('form.category', 'flying_lock')
            ->set('form.openingType', 'by_magic')
            ->call('save')
            ->assertHasErrors(['form.category', 'form.openingType']);

        $this->assertDatabaseCount('gps_device_models', 0);
    }

    public function test_the_brand_must_be_an_active_provider(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.brandId', Provider::factory()->trashed()->create()->id)
            ->set('form.name', 'Hunter Block')
            ->set('form.category', GpsDeviceCategory::ContainerLock->value)
            ->set('form.openingType', GpsOpeningType::Remote->value)
            ->call('save')
            ->assertHasErrors(['form.brandId' => 'exists']);

        $this->assertDatabaseCount('gps_device_models', 0);
    }

    public function test_a_name_cannot_be_repeated_in_the_same_brand_even_if_deactivated_but_can_in_another_brand(): void
    {
        $brand = Provider::factory()->create();
        GpsDeviceModel::factory()->for($brand, 'brand')->trashed()->create(['name' => 'Hunter Block']);

        Livewire::test(CreateModal::class)
            ->set('form.brandId', $brand->id)
            ->set('form.name', 'Hunter Block')
            ->set('form.category', GpsDeviceCategory::ContainerLock->value)
            ->set('form.openingType', GpsOpeningType::Remote->value)
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique'])
            ->assertSee('Ya existe un modelo con este nombre en la marca, aunque esté desactivado.')
            ->set('form.brandId', Provider::factory()->create()->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('gps_device_models', 2);
    }

    public function test_the_two_monitoring_codes_go_together(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.brandId', Provider::factory()->create()->id)
            ->set('form.name', 'Hunter Block')
            ->set('form.category', GpsDeviceCategory::ContainerLock->value)
            ->set('form.openingType', GpsOpeningType::Remote->value)
            ->set('form.monitoringProductCode', 'BR')
            ->call('save')
            ->assertHasErrors(['form.monitoringDeviceTypeCode' => 'required_with'])
            ->assertSee('Escribe también el código de tipo, o deja los dos vacíos.')
            ->set('form.monitoringProductCode', '')
            ->set('form.monitoringDeviceTypeCode', '240')
            ->call('save')
            ->assertHasErrors(['form.monitoringProductCode' => 'required_with'])
            ->assertSee('Escribe también el código de producto, o deja los dos vacíos.');

        $this->assertDatabaseCount('gps_device_models', 0);
    }

    public function test_the_pair_of_monitoring_codes_cannot_be_repeated_even_if_the_other_model_is_deactivated(): void
    {
        GpsDeviceModel::factory()->withMonitoringCodes('BR', '240')->trashed()->create(['name' => 'Hunter Block']);

        Livewire::test(CreateModal::class)
            ->set('form.brandId', Provider::factory()->create()->id)
            ->set('form.name', 'Truck Lock')
            ->set('form.category', GpsDeviceCategory::DoorLock->value)
            ->set('form.openingType', GpsOpeningType::Remote->value)
            ->set('form.monitoringProductCode', 'br')
            ->set('form.monitoringDeviceTypeCode', '240')
            ->call('save')
            ->assertHasErrors(['form.monitoringDeviceTypeCode' => 'unique'])
            ->assertSee('El modelo Hunter Block ya usa estos códigos, aunque esté desactivado.')
            ->set('form.monitoringDeviceTypeCode', '247')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('gps_device_models', ['name' => 'Truck Lock', 'monitoring_product_code' => 'BR', 'monitoring_device_type_code' => '247']);
    }

    public function test_every_field_of_a_model_can_be_edited_keeping_its_own_codes(): void
    {
        $gpsDeviceModel = GpsDeviceModel::factory()->withMonitoringCodes('BR', '240')->trashed()->create(['name' => 'Nombre Anterior']);
        $otherBrand = Provider::factory()->create();

        Livewire::test(EditModal::class)
            ->dispatch('edit-gps-device-model', gpsDeviceModelId: $gpsDeviceModel->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.monitoringDeviceTypeCode', '240')
            ->set('form.brandId', $otherBrand->id)
            ->set('form.name', 'Nombre Nuevo')
            ->set('form.category', GpsDeviceCategory::HopperControl->value)
            ->set('form.openingType', GpsOpeningType::None->value)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Modelo GPS guardado')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('gps_device_models', [
            'id' => $gpsDeviceModel->id,
            'brand_id' => $otherBrand->id,
            'name' => 'Nombre Nuevo',
            'category' => GpsDeviceCategory::HopperControl->value,
            'opening_type' => GpsOpeningType::None->value,
            'monitoring_product_code' => 'BR',
            'monitoring_device_type_code' => '240',
        ]);
    }

    public function test_editing_keeps_the_brand_of_the_model_even_if_it_was_deactivated(): void
    {
        $savedBrand = Provider::factory()->create();
        $otherDeactivatedBrand = Provider::factory()->trashed()->create();
        $gpsDeviceModel = GpsDeviceModel::factory()->for($savedBrand, 'brand')->create();
        $savedBrand->delete();

        Livewire::test(EditModal::class)
            ->dispatch('edit-gps-device-model', gpsDeviceModelId: $gpsDeviceModel->id)
            ->assertSet('form.brandId', $savedBrand->id)
            ->set('form.name', 'Nombre Corregido')
            ->call('save')
            ->assertHasNoErrors()
            ->dispatch('edit-gps-device-model', gpsDeviceModelId: $gpsDeviceModel->id)
            ->set('form.brandId', $otherDeactivatedBrand->id)
            ->call('save')
            ->assertHasErrors(['form.brandId' => 'exists']);

        $this->assertDatabaseHas('gps_device_models', ['id' => $gpsDeviceModel->id, 'name' => 'Nombre Corregido', 'brand_id' => $savedBrand->id]);
    }

    public function test_the_seeder_loads_the_hunter_models_in_the_hunter_provider_without_monitoring_codes(): void
    {
        $hunter = Provider::factory()->create(['business_name' => 'Hunter del Perú S.A.C.']);
        Provider::factory()->create(['business_name' => 'Otro Proveedor S.A.']);

        $this->seed(GpsDeviceModelSeeder::class);

        $this->assertSame(count(GpsDeviceModelSeeder::MODELS), GpsDeviceModel::query()->where('brand_id', $hunter->id)->count());
        $this->assertDatabaseHas('gps_device_models', [
            'brand_id' => $hunter->id,
            'name' => 'Hunter Block',
            'category' => GpsDeviceCategory::ContainerLock->value,
            'opening_type' => GpsOpeningType::Remote->value,
            'monitoring_product_code' => null,
            'monitoring_device_type_code' => null,
        ]);
        $this->assertDatabaseHas('gps_device_models', [
            'name' => 'Block R',
            'category' => GpsDeviceCategory::PortableLock->value,
            'opening_type' => GpsOpeningType::Manual->value,
        ]);
    }

    public function test_the_seeder_leaves_existing_models_as_they_are_and_does_not_repeat_them(): void
    {
        $hunter = Provider::factory()->create(['business_name' => 'Hunter del Perú S.A.C.']);
        $edited = GpsDeviceModel::factory()->for($hunter, 'brand')->withMonitoringCodes('BR', '240')->trashed()->create([
            'name' => 'Hunter Block',
            'description' => 'Descripción del usuario.',
        ]);

        $this->seed(GpsDeviceModelSeeder::class);
        $this->seed(GpsDeviceModelSeeder::class);

        $this->assertSame(count(GpsDeviceModelSeeder::MODELS), GpsDeviceModel::withTrashed()->count());
        $this->assertSoftDeleted($edited);
        $this->assertDatabaseHas('gps_device_models', ['id' => $edited->id, 'monitoring_device_type_code' => '240', 'description' => 'Descripción del usuario.']);
    }

    public function test_the_seeder_loads_nothing_without_a_single_active_hunter_provider(): void
    {
        Provider::factory()->trashed()->create(['business_name' => 'Hunter del Perú S.A.C.']);

        $this->seed(GpsDeviceModelSeeder::class);

        $this->assertDatabaseCount('gps_device_models', 0);

        Provider::factory()->count(2)->sequence(['business_name' => 'Hunter Uno S.A.C.'], ['business_name' => 'Hunter Dos S.A.C.'])->create();

        $this->seed(GpsDeviceModelSeeder::class);

        $this->assertDatabaseCount('gps_device_models', 0);
    }

    public function test_a_model_can_be_deactivated_and_reactivated(): void
    {
        $gpsDeviceModel = GpsDeviceModel::factory()->create();

        Livewire::test(GpsDeviceModelsTable::class)
            ->call('deactivate', $gpsDeviceModel->id)
            ->assertDispatched('toast', icon: 'success', title: 'Modelo GPS desactivado')
            ->call('deactivate', $gpsDeviceModel->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Modelo GPS ya desactivado');

        $this->assertSoftDeleted($gpsDeviceModel);

        Livewire::test(GpsDeviceModelsTable::class)
            ->call('reactivate', $gpsDeviceModel->id)
            ->assertDispatched('toast', icon: 'success', title: 'Modelo GPS reactivado')
            ->call('reactivate', $gpsDeviceModel->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Modelo GPS ya activo');

        $this->assertNotSoftDeleted($gpsDeviceModel);
    }
}
