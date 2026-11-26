<?php

namespace Tests\Feature;

use App\Enums\BulkDispatchReason;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\BulkDispatches\CreateModal;
use App\Livewire\Kits\CreateForm;
use App\Livewire\Kits\DeviceScanner;
use App\Models\Client;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\Worker;
use App\Models\YardZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The shipping lines in linear mode dispatch their devices in the order of their correlatives, in a kit and in bulk
 * (T82).
 */
class LinearDispatchModeTest extends TestCase
{
    use RefreshDatabase;

    private const string SKIPS_GWF501 = 'Falta despachar antes el GWF501. Chiquita Brands International despacha en orden.';

    private User $user;

    private ShippingLine $chiquita;

    private SecurityDeviceType $containerSeal;

    private SecurityDeviceType $pouch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->chiquita = ShippingLine::factory()->linear()->create(['code' => 'CHQT', 'name' => 'Chiquita Brands International']);
        $this->containerSeal = SecurityDeviceType::factory()->create(['name' => 'Precinto de naviera']);
        $this->pouch = SecurityDeviceType::factory()->pouch()->create(['name' => 'Funda']);
    }

    public function test_a_kit_rejects_a_device_that_skips_the_next_one_and_takes_them_in_order(): void
    {
        $lot = $this->lot($this->chiquita, $this->containerSeal, 'GWF', 500, 503);
        $this->setStatus($lot, 500, SecurityDeviceStatus::Used);
        $scanner = Livewire::test(DeviceScanner::class, ['kit' => $this->kit($this->chiquita)]);

        $scanner->call('add', $this->sealSlot(), 'GWF502')->assertReturned(['ok' => false, 'message' => self::SKIPS_GWF501]);
        $scanner->call('add', $this->sealSlot(), 'GWF501')->assertReturned(['ok' => true, 'message' => 'Precinto de naviera · agregado']);
        $scanner->call('add', $this->sealSlot(), 'GWF502')->assertReturned(['ok' => true, 'message' => 'Precinto de naviera · agregado']);

        $this->assertSame(SecurityDeviceStatus::InKit, $this->deviceOf($lot, 502)->status);
    }

    public function test_discarded_devices_and_devices_waiting_for_their_approval_do_not_hold_the_order_back(): void
    {
        $lot = $this->lot($this->chiquita, $this->containerSeal, 'GWF', 500, 503);
        $this->setStatus($lot, 500, SecurityDeviceStatus::Discarded);
        $this->setStatus($lot, 501, SecurityDeviceStatus::Inactive);

        Livewire::test(DeviceScanner::class, ['kit' => $this->kit($this->chiquita)])
            ->call('add', $this->sealSlot(), 'GWF502')
            ->assertReturned(['ok' => true, 'message' => 'Precinto de naviera · agregado']);
    }

    public function test_a_device_taken_out_of_a_kit_becomes_the_next_one_again(): void
    {
        $lot = $this->lot($this->chiquita, $this->containerSeal, 'GWF', 501, 502);
        $scanner = Livewire::test(DeviceScanner::class, ['kit' => $this->kit($this->chiquita)]);

        $scanner->call('add', $this->sealSlot(), 'GWF501')->call('remove', $this->deviceOf($lot, 501)->id);

        $scanner->call('add', $this->sealSlot(), 'GWF502')->assertReturned(['ok' => false, 'message' => self::SKIPS_GWF501]);
    }

    public function test_each_type_and_series_keeps_its_own_order_and_a_line_in_random_mode_takes_any_order(): void
    {
        $tape = SecurityDeviceType::factory()->create(['name' => 'Cinta de inspección']);
        $maersk = ShippingLine::factory()->create(['code' => 'MAEU', 'name' => 'Maersk Line']);
        $this->lot($this->chiquita, $this->containerSeal, 'GWF', 501, 502);
        $this->lot($this->chiquita, $this->containerSeal, 'GWX', 100, 101);
        $this->lot($this->chiquita, $tape, 'CHT', 120, 121);
        $this->lot($maersk, $this->containerSeal, 'MSK', 1, 3);

        $window = Livewire::test(CreateModal::class);

        $window->call('scan', 'GWX100')->assertReturned(['ok' => true, 'message' => 'Precinto de naviera · agregado']);
        $window->call('scan', 'CHT120')->assertReturned(['ok' => true, 'message' => 'Cinta de inspección · agregado']);
        $window->call('scan', 'MSK003')->assertReturned(['ok' => true, 'message' => 'Precinto de naviera · agregado']);
    }

    public function test_the_pouch_of_a_new_kit_follows_the_order_too(): void
    {
        $this->lot($this->chiquita, $this->pouch, 'FCH', 1, 2);

        Livewire::test(CreateForm::class)
            ->set('form.pouchCode', 'FCH002')
            ->set('form.kitTypeId', (string) $this->kitType()->id)
            ->set('form.shippingLineId', (string) $this->chiquita->id)
            ->call('save')
            ->assertHasErrors('form.pouchCode')
            ->assertSee('Falta despachar antes el FCH001. Chiquita Brands International despacha en orden.');

        $this->assertDatabaseCount('kits', 0);
    }

    public function test_a_bulk_scan_shares_the_order_with_the_kits_and_counts_the_devices_already_chosen(): void
    {
        $lot = $this->lot($this->chiquita, $this->containerSeal, 'GWF', 500, 502);
        Livewire::test(DeviceScanner::class, ['kit' => $this->kit($this->chiquita)])->call('add', $this->sealSlot(), 'GWF500');

        $window = Livewire::test(CreateModal::class);

        $window->call('scan', 'GWF502')->assertReturned(['ok' => false, 'message' => self::SKIPS_GWF501]);
        $window->call('scan', 'GWF501')->assertReturned(['ok' => true, 'message' => 'Precinto de naviera · agregado']);
        $window->call('scan', 'GWF502')->assertReturned(['ok' => true, 'message' => 'Precinto de naviera · agregado']);

        $window->assertSet('form.deviceIds', [$this->deviceOf($lot, 501)->id, $this->deviceOf($lot, 502)->id]);
    }

    public function test_a_bulk_range_or_quantity_must_start_at_the_next_device(): void
    {
        $this->lot($this->chiquita, $this->containerSeal, 'GWF', 501, 505);

        $window = Livewire::test(CreateModal::class);

        $window->call('byRange', 'GWF502', 'GWF503', true)
            ->assertReturned(['ok' => false, 'message' => 'El rango tiene que empezar en el GWF501. Chiquita Brands International despacha en orden.', 'count' => 0]);
        $window->call('byRange', 'GWF501', 'GWF502', true)
            ->assertReturned(['ok' => true, 'message' => 'Agregados: Precinto de naviera · GWF501 a GWF502 · 2 dispositivos.', 'count' => 2]);
        $window->call('byQuantity', (string) $this->containerSeal->id, 'GWF504', '1', true)
            ->assertReturned(['ok' => false, 'message' => 'El rango tiene que empezar en el GWF503. Chiquita Brands International despacha en orden.', 'count' => 0]);
        $window->call('byQuantity', (string) $this->containerSeal->id, 'GWF', '2', true)
            ->assertReturned(['ok' => true, 'message' => 'Agregados: Precinto de naviera · GWF503 a GWF504 · 2 dispositivos.', 'count' => 2]);
    }

    public function test_a_bulk_dispatch_is_not_saved_when_a_device_it_skips_was_taken_out_of_a_kit_meanwhile(): void
    {
        $lot = $this->lot($this->chiquita, $this->containerSeal, 'GWF', 501, 502);
        $scanner = Livewire::test(DeviceScanner::class, ['kit' => $this->kit($this->chiquita)])->call('add', $this->sealSlot(), 'GWF501');
        $window = Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.receiverId', Worker::factory()->create()->id)
            ->set('form.reason', BulkDispatchReason::Sale->value)
            ->set('form.clientId', Client::factory()->create()->id)
            ->set('form.yardZoneId', YardZone::factory()->create(['branch_id' => $this->user->branch_id])->id)
            ->call('scan', 'GWF502');

        $scanner->call('remove', $this->deviceOf($lot, 501)->id);

        $window->call('save')
            ->assertHasErrors('form.deviceIds')
            ->assertSee(self::SKIPS_GWF501)
            ->assertNotDispatched('toast');

        $this->assertDatabaseCount('bulk_dispatches', 0);
        $this->assertSame(SecurityDeviceStatus::Available, $this->deviceOf($lot, 502)->status);
    }

    /**
     * Create a lot of the shipping line and the type with the given correlatives of three digits, all Available.
     */
    private function lot(ShippingLine $shippingLine, SecurityDeviceType $type, string $prefix, int $start, int $end): SecurityDeviceLot
    {
        $lot = SecurityDeviceLot::factory()
            ->for(SecurityDeviceAnnouncement::factory()->for($shippingLine), 'announcement')
            ->for($type)
            ->create(['series_prefix' => $prefix, 'min_digits' => 3, 'series_start' => $start, 'series_end' => $end]);

        foreach (range($start, $end) as $correlative) {
            SecurityDevice::factory()->for($lot, 'lot')->create([
                'serial_number' => $lot->serialNumber($correlative),
                'status' => SecurityDeviceStatus::Available,
            ]);
        }

        return $lot;
    }

    private function deviceOf(SecurityDeviceLot $lot, int $correlative): SecurityDevice
    {
        return SecurityDevice::query()->where('serial_number', $lot->serialNumber($correlative))->sole();
    }

    private function setStatus(SecurityDeviceLot $lot, int $correlative, SecurityDeviceStatus $status): void
    {
        $this->deviceOf($lot, $correlative)->update(['status' => $status]);
    }

    /**
     * Get a kit type that carries a pouch and three container seals.
     */
    private function kitType(): KitType
    {
        return KitType::factory()->carrying($this->pouch, 1)->carrying($this->containerSeal, 3)->create();
    }

    /**
     * Create a kit of the shipping line, with a pouch of a series of its own so that it skips nothing.
     */
    private function kit(ShippingLine $shippingLine): Kit
    {
        $this->lot($shippingLine, $this->pouch, 'KIT', 46, 46);

        Livewire::test(CreateForm::class)
            ->set('form.pouchCode', 'KIT046')
            ->set('form.kitTypeId', (string) $this->kitType()->id)
            ->set('form.shippingLineId', (string) $shippingLine->id)
            ->call('save')
            ->assertHasNoErrors();

        return Kit::query()->where('pouch_code', 'KIT046')->sole();
    }

    private function sealSlot(): string
    {
        return Kit::slotKey($this->containerSeal->id, null);
    }
}
