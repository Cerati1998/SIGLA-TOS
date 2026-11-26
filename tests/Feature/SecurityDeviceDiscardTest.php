<?php

namespace Tests\Feature;

use App\Enums\SecurityDeviceStatus;
use App\Livewire\BulkDispatches\CreateModal as BulkDispatchCreateModal;
use App\Livewire\Kits\DeviceScanner;
use App\Livewire\SecurityDevices\DiscardDetailModal;
use App\Livewire\SecurityDevices\DiscardModal;
use App\Livewire\Tables\SecurityDevicesTable;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * T65: the kit of each device of a lot, and the discard of a device of a lot. T77: the photo of the discard.
 */
class SecurityDeviceDiscardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private SecurityDeviceType $seal;

    private SecurityDeviceLot $lot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Bodeguero Uno']);
        $this->actingAs($this->user);
        Storage::fake('local');
        $this->app->setLocale('es');

        $this->seal = SecurityDeviceType::factory()->create(['name' => 'Sello botella']);
        $this->lot = SecurityDeviceLot::factory()->for($this->seal)->create(['series_prefix' => 'SB', 'min_digits' => 6, 'series_start' => 1, 'series_end' => 3, 'quantity' => 3]);

        foreach (['SB000001' => SecurityDeviceStatus::Available, 'SB000002' => SecurityDeviceStatus::Inactive, 'SB000003' => SecurityDeviceStatus::InKit] as $serialNumber => $status) {
            SecurityDevice::factory()->for($this->lot, 'lot')->create(['serial_number' => $serialNumber, 'status' => $status]);
        }

        Kit::factory()->create(['pouch_code' => 'KIT-000046'])->devices()->attach($this->device('SB000003')->id);
    }

    public function test_the_devices_of_the_lot_show_their_kit_and_offer_the_discard_only_out_of_a_kit(): void
    {
        $table = Livewire::test(SecurityDevicesTable::class, ['lotId' => $this->lot->id])
            ->assertSeeText('KIT-000046');

        $this->assertSame(2, substr_count($table->html(), 'title="Descartar"'));
    }

    public function test_a_device_is_discarded_with_its_reason_its_photo_and_who_did_it(): void
    {
        Livewire::test(DiscardModal::class, ['lot' => $this->lot])
            ->assertSeeText('Tomar foto')
            ->call('open', $this->device('SB000002')->id)
            ->assertSet('form.serialNumber', 'SB000002')
            ->set('form.reason', ' Llegó roto en la recepción. ')
            ->set('form.photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('form.thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.security-device-lots.show', $this->lot));

        $device = $this->device('SB000002');

        $this->assertSame(SecurityDeviceStatus::Discarded, $device->status);
        $this->assertSame('Llegó roto en la recepción.', $device->discard_reason);
        $this->assertTrue($device->discarder?->is($this->user));
        $this->assertNotNull($device->discarded_at);
        $this->assertNotNull($device->discard_photo_path);
        Storage::disk('local')->assertExists([(string) $device->discard_photo_path, (string) $device->discard_thumbnail_path]);
        $this->assertSame('Dispositivo descartado', session('toast.title'));

        $this->get(route('intranet.security-device-lots.show', $this->lot))->assertSeeText('Descartados');
    }

    public function test_the_reason_and_the_photo_are_required(): void
    {
        Livewire::test(DiscardModal::class, ['lot' => $this->lot])
            ->call('open', $this->device('SB000001')->id)
            ->call('save')
            ->assertHasErrors(['form.reason' => 'required', 'form.photo' => 'required'])
            ->assertSeeText('Toma la foto del dispositivo.');

        $this->assertSame(SecurityDeviceStatus::Available, $this->device('SB000001')->status);
    }

    public function test_a_device_in_a_kit_is_not_discarded(): void
    {
        Livewire::test(DiscardModal::class, ['lot' => $this->lot])
            ->call('open', $this->device('SB000003')->id)
            ->set('form.reason', 'Roto.')
            ->set('form.photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('form.thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('save')
            ->assertDispatched('toast', icon: 'warning');

        $this->assertSame(SecurityDeviceStatus::InKit, $this->device('SB000003')->status);
        $this->assertSame([], Storage::disk('local')->allFiles('security-devices'));
    }

    public function test_only_a_discarded_device_shows_its_discard_with_the_reason_the_photo_and_who_did_it(): void
    {
        $this->device('SB000001')->update([
            'status' => SecurityDeviceStatus::Discarded,
            'discarded_at' => '2026-11-24 16:20:00',
            'discarded_by' => $this->user->id,
            'discard_reason' => 'Llegó roto en la recepción.',
            'discard_photo_path' => 'security-devices/discards/foto.jpg',
            'discard_thumbnail_path' => 'security-devices/discards/thumbnails/foto.jpg',
        ]);
        Storage::disk('local')->put('security-devices/discards/foto.jpg', (string) UploadedFile::fake()->image('foto.jpg')->get());
        Storage::disk('local')->put('security-devices/discards/thumbnails/foto.jpg', (string) UploadedFile::fake()->image('miniatura.jpg')->get());
        $discarded = $this->device('SB000001');

        $table = Livewire::test(SecurityDevicesTable::class, ['lotId' => $this->lot->id]);
        $this->assertSame(1, substr_count($table->html(), 'title="Ver descarte"'));

        Livewire::test(DiscardDetailModal::class, ['lot' => $this->lot])
            ->call('open', $discarded->id)
            ->assertSet('openModal', true)
            ->assertSeeText('Descarte de SB000001')
            ->assertSeeText('Llegó roto en la recepción.')
            ->assertSeeText('Bodeguero Uno')
            ->assertSeeText('el 24/11/2026 a las 11:20')
            ->assertSee(route('intranet.security-devices.discard-photo.thumbnail', $discarded), false);

        Livewire::test(DiscardDetailModal::class, ['lot' => $this->lot])
            ->call('open', $this->device('SB000002')->id)
            ->assertSet('openModal', false)
            ->assertDispatched('toast', icon: 'warning');

        $this->get(route('intranet.security-devices.discard-photo', $discarded))->assertOk();
        $this->get(route('intranet.security-devices.discard-photo.thumbnail', $discarded))->assertOk();
        $this->get(route('intranet.security-devices.discard-photo', $this->device('SB000002')))->assertNotFound();

        auth()->logout();
        $this->get(route('intranet.security-devices.discard-photo', $discarded))->assertRedirect(route('login'));
    }

    public function test_a_device_of_another_lot_cannot_be_opened(): void
    {
        $other = SecurityDevice::factory()->create();

        Livewire::test(DiscardModal::class, ['lot' => $this->lot])
            ->call('open', $other->id)
            ->assertNotFound();
    }

    public function test_a_discarded_device_goes_neither_in_a_kit_nor_in_a_bulk_dispatch(): void
    {
        $this->device('SB000001')->update(['status' => SecurityDeviceStatus::Discarded]);
        $kitType = KitType::factory()->carrying($this->seal, 1)->create();
        $kit = Kit::factory()->create(['kit_type_id' => $kitType->id, 'shipping_line_id' => $this->lot->announcement->shipping_line_id]);
        $kit->securityDeviceTypes()->attach($this->seal->id, ['quantity' => 1]);

        Livewire::test(DeviceScanner::class, ['kit' => $kit])
            ->call('add', Kit::slotKey($this->seal->id, null), 'SB000001')
            ->assertReturned(['ok' => false, 'message' => 'Está Descartado; solo se agregan dispositivos disponibles.']);

        Livewire::test(BulkDispatchCreateModal::class)
            ->call('scan', 'SB000001')
            ->assertReturned(['ok' => false, 'message' => 'Está Descartado; solo se despachan dispositivos disponibles.']);
    }

    private function device(string $serialNumber): SecurityDevice
    {
        return SecurityDevice::query()->where('serial_number', $serialNumber)->sole();
    }
}
