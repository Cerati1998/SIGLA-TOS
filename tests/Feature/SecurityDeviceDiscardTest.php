<?php

namespace Tests\Feature;

use App\Enums\SecurityDeviceStatus;
use App\Livewire\BulkDispatches\CreateModal as BulkDispatchCreateModal;
use App\Livewire\Kits\DeviceScanner;
use App\Livewire\SecurityDevices\DiscardModal;
use App\Livewire\Tables\SecurityDevicesTable;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * T65: the kit of each device of a lot, and the discard of a device of a lot.
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

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->app->setLocale('es');

        $this->seal = SecurityDeviceType::factory()->create(['name' => 'Sello botella']);
        $announcement = SecurityDeviceAnnouncement::factory()->for($this->seal)->create(['series_prefix' => 'SB', 'min_digits' => 6, 'series_start' => 1, 'series_end' => 3]);
        $this->lot = SecurityDeviceLot::factory()->for($announcement, 'announcement')->create(['quantity' => 3]);

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

    public function test_a_device_is_discarded_with_its_reason_and_who_did_it(): void
    {
        Livewire::test(DiscardModal::class, ['lot' => $this->lot])
            ->call('open', $this->device('SB000002')->id)
            ->assertSet('form.serialNumber', 'SB000002')
            ->set('form.reason', ' Llegó roto en la recepción. ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.security-device-lots.show', $this->lot));

        $device = $this->device('SB000002');

        $this->assertSame(SecurityDeviceStatus::Discarded, $device->status);
        $this->assertSame('Llegó roto en la recepción.', $device->discard_reason);
        $this->assertTrue($device->discarder?->is($this->user));
        $this->assertNotNull($device->discarded_at);
        $this->assertSame('Dispositivo descartado', session('toast.title'));

        $this->get(route('intranet.security-device-lots.show', $this->lot))->assertSeeText('Descartados');
    }

    public function test_the_reason_is_required(): void
    {
        Livewire::test(DiscardModal::class, ['lot' => $this->lot])
            ->call('open', $this->device('SB000001')->id)
            ->call('save')
            ->assertHasErrors(['form.reason' => 'required']);

        $this->assertSame(SecurityDeviceStatus::Available, $this->device('SB000001')->status);
    }

    public function test_a_device_in_a_kit_is_not_discarded(): void
    {
        Livewire::test(DiscardModal::class, ['lot' => $this->lot])
            ->call('open', $this->device('SB000003')->id)
            ->set('form.reason', 'Roto.')
            ->call('save')
            ->assertDispatched('toast', icon: 'warning');

        $this->assertSame(SecurityDeviceStatus::InKit, $this->device('SB000003')->status);
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
