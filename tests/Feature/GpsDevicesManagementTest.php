<?php

namespace Tests\Feature;

use App\Enums\GpsDeviceCategory;
use App\Enums\GpsDeviceOwnership;
use App\Enums\GpsOpeningType;
use App\Livewire\GpsDeviceModels\CreateModal as GpsDeviceModelCreateModal;
use App\Livewire\GpsDevices\CreateModal;
use App\Livewire\GpsDevices\EditModal;
use App\Livewire\Providers\CreateModal as ProviderCreateModal;
use App\Livewire\Tables\GpsDevicesTable;
use App\Models\Branch;
use App\Models\GpsDevice;
use App\Models\GpsDeviceModel;
use App\Models\Provider;
use App\Models\User;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GpsDevicesManagementTest extends TestCase
{
    use RefreshDatabase;

    private GpsDeviceModel $hunterBlock;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentitySeeder::class);
        $this->actingAs(User::factory()->create());
        Storage::fake('local');
        $this->app->setLocale('es');

        $brand = Provider::factory()->create(['business_name' => 'AUTOSAFE SAC']);
        $this->hunterBlock = GpsDeviceModel::factory()->for($brand, 'brand')->create(['name' => 'Hunter Block']);
        $this->branch = Branch::factory()->create(['name' => 'Sucursal Paita']);
    }

    public function test_guests_cannot_open_the_screen_nor_the_photos_nor_the_model_search(): void
    {
        $gpsDevice = GpsDevice::factory()->create();
        auth()->logout();

        $this->get(route('intranet.gps-devices.index'))->assertRedirect(route('login'));
        $this->get(route('intranet.gps-devices.photo', $gpsDevice))->assertRedirect(route('login'));
        $this->postJson(route('intranet.gps-device-models.options'), ['search' => 'Hunter'])->assertUnauthorized();
    }

    public function test_the_menu_leads_to_the_list_with_available_and_deactivated_devices(): void
    {
        GpsDevice::factory()->for($this->hunterBlock)->for($this->branch)->rented(Provider::query()->firstOrFail())->create([
            'code' => 'HBR140',
            'monitoring_asset_id' => 282463,
        ]);
        GpsDevice::factory()->trashed()->create(['code' => 'HBR172']);

        $this->get(route('intranet.gps-devices.index'))
            ->assertOk()
            ->assertSeeInOrder(['Bodega', 'Kits', 'Dispositivos GPS', 'Despachos', 'Certificación'])
            ->assertSee(route('intranet.gps-devices.index'))
            ->assertSeeText('HBR140')
            ->assertSeeText('Hunter Block')
            ->assertSeeText('Alquilado')
            ->assertSeeText('AUTOSAFE SAC')
            ->assertSeeText('Sucursal Paita')
            ->assertSeeText('282463')
            ->assertSeeText('Disponible')
            ->assertSeeText('HBR172')
            ->assertSeeText('Desactivado')
            ->assertSeeLivewire(CreateModal::class)
            ->assertSeeLivewire(EditModal::class)
            ->assertSeeLivewire(GpsDeviceModelCreateModal::class)
            ->assertSeeLivewire(ProviderCreateModal::class);
    }

    public function test_each_row_gets_the_id_of_its_device(): void
    {
        $gpsDevice = GpsDevice::factory()->create();

        Livewire::test(GpsDevicesTable::class)->assertSeeHtml("id: {$gpsDevice->id},");
    }

    public function test_the_list_is_ordered_by_code_and_filters_by_search_model_ownership_branch_and_status(): void
    {
        $truckLock = GpsDeviceModel::factory()->create(['name' => 'Truck Lock']);
        $otherBranch = Branch::factory()->create();
        GpsDevice::factory()->for($this->hunterBlock)->for($this->branch)->create(['code' => 'HBR200']);
        GpsDevice::factory()->for($truckLock)->for($otherBranch)->rented()->create(['code' => 'HBR100', 'monitoring_asset_id' => 282463]);
        GpsDevice::factory()->for($this->hunterBlock)->for($this->branch)->trashed()->create(['code' => 'HBR300']);

        Livewire::test(GpsDevicesTable::class)->assertSeeInOrder(['HBR100', 'HBR200', 'HBR300']);

        Livewire::test(GpsDevicesTable::class)
            ->set('search', 'HBR2')
            ->assertSeeText('HBR200')
            ->assertDontSeeText('HBR100');

        Livewire::test(GpsDevicesTable::class)
            ->set('search', '282463')
            ->assertSeeText('HBR100')
            ->assertDontSeeText('HBR200');

        Livewire::test(GpsDevicesTable::class)
            ->set('filterComponents.model', (string) $truckLock->id)
            ->assertSeeText('HBR100')
            ->assertDontSeeText('HBR200');

        Livewire::test(GpsDevicesTable::class)
            ->set('filterComponents.ownership', GpsDeviceOwnership::Own->value)
            ->assertSeeText('HBR200')
            ->assertDontSeeText('HBR100');

        Livewire::test(GpsDevicesTable::class)
            ->set('filterComponents.branch', (string) $otherBranch->id)
            ->assertSeeText('HBR100')
            ->assertDontSeeText('HBR200');

        Livewire::test(GpsDevicesTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('HBR300')
            ->assertDontSeeText('HBR200');

        Livewire::test(GpsDevicesTable::class)
            ->set('filterComponents.status', 'available')
            ->assertSeeText('HBR200')
            ->assertDontSeeText('HBR300');
    }

    public function test_an_own_device_is_registered_available_with_its_photo_and_without_a_rental_provider(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.code', ' hbr232 ')
            ->set('form.monitoringAssetId', ' 288872 ')
            ->set('form.gpsDeviceModelId', $this->hunterBlock->id)
            ->set('form.ownership', GpsDeviceOwnership::Own->value)
            ->set('form.rentalProviderId', Provider::query()->firstOrFail()->id)
            ->set('form.branchId', (string) $this->branch->id)
            ->set('form.notes', ' Llegó con el lote de noviembre. ')
            ->set('form.photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('form.thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Dispositivo GPS guardado')
            ->assertDispatched('refreshDatatable');

        $gpsDevice = GpsDevice::query()->sole();
        $this->assertSame('HBR232', $gpsDevice->code);
        $this->assertSame(288872, $gpsDevice->monitoring_asset_id);
        $this->assertSame($this->hunterBlock->id, $gpsDevice->gps_device_model_id);
        $this->assertSame(GpsDeviceOwnership::Own, $gpsDevice->ownership);
        $this->assertNull($gpsDevice->rental_provider_id);
        $this->assertSame($this->branch->id, $gpsDevice->branch_id);
        $this->assertSame('Disponible', $gpsDevice->status->label());
        $this->assertSame('Llegó con el lote de noviembre.', $gpsDevice->notes);
        Storage::disk('local')->assertExists([(string) $gpsDevice->photo_path, (string) $gpsDevice->thumbnail_path]);
    }

    public function test_the_id_of_24hm_the_notes_and_the_photo_are_optional(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.code', 'CND-0001')
            ->set('form.gpsDeviceModelId', $this->hunterBlock->id)
            ->set('form.branchId', (string) $this->branch->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('gps_devices', [
            'code' => 'CND-0001',
            'monitoring_asset_id' => null,
            'notes' => null,
            'photo_path' => null,
            'thumbnail_path' => null,
        ]);
    }

    public function test_a_rented_device_needs_an_active_rental_provider(): void
    {
        $provider = Provider::query()->firstOrFail();

        Livewire::test(CreateModal::class)
            ->set('form.code', 'HBR140')
            ->set('form.gpsDeviceModelId', $this->hunterBlock->id)
            ->set('form.ownership', GpsDeviceOwnership::Rented->value)
            ->set('form.branchId', (string) $this->branch->id)
            ->call('save')
            ->assertHasErrors(['form.rentalProviderId' => 'required'])
            ->assertSee('Elige el proveedor del alquiler.')
            ->set('form.rentalProviderId', Provider::factory()->trashed()->create()->id)
            ->call('save')
            ->assertHasErrors(['form.rentalProviderId' => 'exists'])
            ->set('form.rentalProviderId', $provider->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('gps_devices', ['code' => 'HBR140', 'ownership' => 'rented', 'rental_provider_id' => $provider->id]);
    }

    public function test_the_fields_of_a_device_are_validated(): void
    {
        $deactivatedModel = GpsDeviceModel::factory()->trashed()->create();
        $deactivatedBranch = Branch::factory()->trashed()->create();

        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.code' => 'required', 'form.gpsDeviceModelId' => 'required', 'form.branchId' => 'required'])
            ->assertSee('Escribe el código del dispositivo.')
            ->assertSee('Elige el modelo del dispositivo.')
            ->assertSee('Elige la sucursal del dispositivo.')
            ->set('form.code', 'HBR140')
            ->set('form.monitoringAssetId', '28A463')
            ->set('form.gpsDeviceModelId', $deactivatedModel->id)
            ->set('form.branchId', (string) $deactivatedBranch->id)
            ->set('form.ownership', 'borrowed')
            ->call('save')
            ->assertHasErrors([
                'form.monitoringAssetId' => 'digits_between',
                'form.gpsDeviceModelId' => 'exists',
                'form.branchId' => 'exists',
                'form.ownership',
            ])
            ->assertSee('El ID de 24HM solo lleva números.');

        $this->assertDatabaseCount('gps_devices', 0);
    }

    public function test_the_code_and_the_id_of_24hm_cannot_be_repeated_even_if_the_other_device_is_deactivated(): void
    {
        GpsDevice::factory()->trashed()->create(['code' => 'HBR140', 'monitoring_asset_id' => 282463]);

        Livewire::test(CreateModal::class)
            ->set('form.code', 'hbr140')
            ->set('form.monitoringAssetId', '282463')
            ->set('form.gpsDeviceModelId', $this->hunterBlock->id)
            ->set('form.branchId', (string) $this->branch->id)
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique', 'form.monitoringAssetId' => 'unique'])
            ->assertSee('Ya existe un dispositivo con este código, aunque esté desactivado.')
            ->assertSee('El dispositivo HBR140 ya tiene este ID de 24HM, aunque esté desactivado.');

        $this->assertDatabaseCount('gps_devices', 1);
    }

    public function test_every_field_of_a_device_can_be_edited_and_a_new_photo_replaces_the_old_one(): void
    {
        Storage::disk('local')->put('gps-devices/vieja.jpg', 'foto');
        Storage::disk('local')->put('gps-devices/thumbnails/vieja.jpg', 'miniatura');
        $gpsDevice = GpsDevice::factory()->for($this->hunterBlock)->for($this->branch)->trashed()->create([
            'code' => 'HBR140',
            'photo_path' => 'gps-devices/vieja.jpg',
            'thumbnail_path' => 'gps-devices/thumbnails/vieja.jpg',
        ]);
        $truckLock = GpsDeviceModel::factory()->create([
            'name' => 'Truck Lock',
            'category' => GpsDeviceCategory::DoorLock,
            'opening_type' => GpsOpeningType::Remote,
        ]);
        $otherBranch = Branch::factory()->create();
        $provider = Provider::query()->firstOrFail();

        Livewire::test(EditModal::class)
            ->dispatch('edit-gps-device', gpsDeviceId: $gpsDevice->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.code', 'HBR140')
            ->assertSet('form.savedThumbnailUrl', route('intranet.gps-devices.photo.thumbnail', ['gpsDevice' => $gpsDevice->id, 'v' => 'vieja']))
            ->set('form.code', 'HBR141')
            ->set('form.monitoringAssetId', '282464')
            ->set('form.gpsDeviceModelId', $truckLock->id)
            ->set('form.ownership', GpsDeviceOwnership::Rented->value)
            ->set('form.rentalProviderId', $provider->id)
            ->set('form.branchId', (string) $otherBranch->id)
            ->set('form.photo', UploadedFile::fake()->image('nueva.jpg'))
            ->set('form.thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Dispositivo GPS guardado')
            ->assertDispatched('refreshDatatable');

        $gpsDevice->refresh();
        $this->assertSame('HBR141', $gpsDevice->code);
        $this->assertSame(282464, $gpsDevice->monitoring_asset_id);
        $this->assertSame($truckLock->id, $gpsDevice->gps_device_model_id);
        $this->assertSame($provider->id, $gpsDevice->rental_provider_id);
        $this->assertSame($otherBranch->id, $gpsDevice->branch_id);
        $this->assertNotSame('gps-devices/vieja.jpg', $gpsDevice->photo_path);
        Storage::disk('local')->assertExists([(string) $gpsDevice->photo_path, (string) $gpsDevice->thumbnail_path]);
        Storage::disk('local')->assertMissing(['gps-devices/vieja.jpg', 'gps-devices/thumbnails/vieja.jpg']);
    }

    public function test_removing_the_photo_deletes_it_when_saving(): void
    {
        Storage::disk('local')->put('gps-devices/vieja.jpg', 'foto');
        Storage::disk('local')->put('gps-devices/thumbnails/vieja.jpg', 'miniatura');
        $gpsDevice = GpsDevice::factory()->for($this->hunterBlock)->for($this->branch)->create([
            'photo_path' => 'gps-devices/vieja.jpg',
            'thumbnail_path' => 'gps-devices/thumbnails/vieja.jpg',
        ]);

        Livewire::test(EditModal::class)
            ->dispatch('edit-gps-device', gpsDeviceId: $gpsDevice->id)
            ->set('form.removePhoto', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('gps_devices', ['id' => $gpsDevice->id, 'photo_path' => null, 'thumbnail_path' => null]);
        Storage::disk('local')->assertMissing(['gps-devices/vieja.jpg', 'gps-devices/thumbnails/vieja.jpg']);
    }

    public function test_editing_keeps_the_model_provider_and_branch_of_the_device_even_if_they_were_deactivated(): void
    {
        $provider = Provider::query()->firstOrFail();
        $gpsDevice = GpsDevice::factory()->for($this->hunterBlock)->for($this->branch)->rented($provider)->create();
        $this->hunterBlock->delete();
        $this->branch->delete();
        $provider->delete();

        Livewire::test(EditModal::class)
            ->dispatch('edit-gps-device', gpsDeviceId: $gpsDevice->id)
            ->assertSee('Sucursal Paita (desactivada)')
            ->set('form.notes', 'Revisado.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('gps_devices', [
            'id' => $gpsDevice->id,
            'notes' => 'Revisado.',
            'gps_device_model_id' => $this->hunterBlock->id,
            'rental_provider_id' => $provider->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_a_device_can_be_deactivated_and_reactivated(): void
    {
        $gpsDevice = GpsDevice::factory()->create();

        Livewire::test(GpsDevicesTable::class)
            ->call('deactivate', $gpsDevice->id)
            ->assertDispatched('toast', icon: 'success', title: 'Dispositivo GPS desactivado')
            ->call('deactivate', $gpsDevice->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Dispositivo GPS ya desactivado');

        $this->assertSoftDeleted($gpsDevice);

        Livewire::test(GpsDevicesTable::class)
            ->call('reactivate', $gpsDevice->id)
            ->assertDispatched('toast', icon: 'success', title: 'Dispositivo GPS reactivado')
            ->call('reactivate', $gpsDevice->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Dispositivo GPS ya activo');

        $this->assertNotSoftDeleted($gpsDevice);
    }

    public function test_the_photo_is_served_with_a_session_also_for_a_deactivated_device(): void
    {
        Storage::disk('local')->put('gps-devices/foto.jpg', 'foto');
        Storage::disk('local')->put('gps-devices/thumbnails/foto.jpg', 'miniatura');
        $withPhoto = GpsDevice::factory()->trashed()->create([
            'code' => 'HBR140',
            'photo_path' => 'gps-devices/foto.jpg',
            'thumbnail_path' => 'gps-devices/thumbnails/foto.jpg',
        ]);
        $withoutPhoto = GpsDevice::factory()->create();

        Livewire::test(GpsDevicesTable::class)
            ->assertSee(route('intranet.gps-devices.photo.thumbnail', ['gpsDevice' => $withPhoto->id, 'v' => 'foto']), false)
            ->assertSeeHtml('title="Ver la foto de HBR140"');

        $this->get(route('intranet.gps-devices.photo', $withPhoto))->assertOk();
        $this->get(route('intranet.gps-devices.photo.thumbnail', $withPhoto))->assertOk();
        $this->get(route('intranet.gps-devices.photo', $withoutPhoto))->assertNotFound();
    }

    public function test_the_model_select_searches_the_active_models_by_name_or_brand_and_shows_a_chosen_deactivated_one(): void
    {
        GpsDeviceModel::factory()->create(['name' => 'Rastreador Ajeno']);
        $retired = GpsDeviceModel::factory()->trashed()->create(['name' => 'Modelo Retirado']);

        $this->postJson(route('intranet.gps-device-models.options'), ['search' => 'autosafe'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Hunter Block')
            ->assertJsonPath('0.description', 'AUTOSAFE SAC · Candado para contenedor');

        $this->postJson(route('intranet.gps-device-models.options'), ['search' => 'Retirado'])->assertOk()->assertJsonCount(0);

        $this->postJson(route('intranet.gps-device-models.options'), ['selected' => [$retired->id]])
            ->assertOk()
            ->assertJsonPath('0.name', 'Modelo Retirado (desactivado)');
    }

    public function test_the_model_window_tells_the_device_form_which_model_it_created(): void
    {
        Livewire::test(GpsDeviceModelCreateModal::class)
            ->set('form.brandId', Provider::query()->firstOrFail()->id)
            ->set('form.name', 'Candado Inteligente')
            ->set('form.category', GpsDeviceCategory::LightVehicleLock->value)
            ->set('form.openingType', GpsOpeningType::Remote->value)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('gps-device-model-created', gpsDeviceModelId: GpsDeviceModel::query()->where('name', 'Candado Inteligente')->sole()->id);
    }
}
