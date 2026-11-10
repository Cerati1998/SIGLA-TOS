<?php

namespace Tests\Feature;

use App\Enums\SecurityDeviceStatus;
use App\Livewire\SecurityDevices\SerialNumberSearch;
use App\Livewire\Tables\SecurityDeviceLotsTable;
use App\Livewire\Tables\SecurityDevicesTable;
use App\Models\Provider;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityDeviceLotsQueryTest extends TestCase
{
    use RefreshDatabase;

    private SecurityDeviceLot $lot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');

        $announcement = SecurityDeviceAnnouncement::factory()->create([
            'shipping_line_id' => ShippingLine::factory()->create(['code' => 'MSK', 'name' => 'Maersk']),
            'provider_id' => Provider::factory()->create(['business_name' => 'Proveedor de Precintos S.A.C.']),
            'security_device_type_id' => SecurityDeviceType::factory()->create(['name' => 'Precinto ISO']),
            'series_prefix' => 'GSW',
            'min_digits' => 7,
            'series_start' => 200,
            'series_end' => 203,
        ]);

        $this->lot = SecurityDeviceLot::factory()->for($announcement, 'announcement')->create([
            'quantity' => 4,
            'received_at' => '2026-11-09',
            'dispatch_number' => 'T001-000123',
            'created_by' => User::factory()->create(['name' => 'Bodeguero Uno']),
        ]);

        foreach ([
            'GSW0000200' => SecurityDeviceStatus::Available,
            'GSW0000201' => SecurityDeviceStatus::Available,
            'GSW0000202' => SecurityDeviceStatus::InKit,
            'GSW0000203' => SecurityDeviceStatus::Used,
        ] as $serialNumber => $status) {
            SecurityDevice::factory()->for($this->lot, 'lot')->create(['serial_number' => $serialNumber, 'status' => $status]);
        }
    }

    public function test_guests_cannot_open_the_lots_nor_their_detail(): void
    {
        auth()->logout();

        $this->get(route('intranet.security-device-lots.index'))->assertRedirect(route('login'));
        $this->get(route('intranet.security-device-lots.show', $this->lot))->assertRedirect(route('login'));
    }

    public function test_the_warehouse_menu_leads_to_the_list_of_lots(): void
    {
        $this->get(route('intranet.security-device-lots.index'))
            ->assertOk()
            ->assertSeeInOrder(['Bodega', 'Lotes de dispositivos', 'Administración'])
            ->assertSee(route('intranet.security-device-lots.index'))
            ->assertSeeText('Buscar un dispositivo');
    }

    public function test_the_list_shows_each_lot_with_its_origin_range_quantity_and_reception(): void
    {
        Livewire::test(SecurityDeviceLotsTable::class)
            ->assertSeeText('Precinto ISO')
            ->assertSeeText('MSK')
            ->assertSeeText('Proveedor de Precintos S.A.C.')
            ->assertSeeText('GSW')
            ->assertSeeText('0000200 – 0000203')
            ->assertSeeText('09/11/2026')
            ->assertSee(route('intranet.security-device-lots.show', $this->lot))
            ->assertSee(route('intranet.security-device-lots.create'));
    }

    public function test_the_list_filters_by_shipping_line_provider_type_and_reception_date(): void
    {
        $otherLot = SecurityDeviceLot::factory()
            ->for(SecurityDeviceAnnouncement::factory()->state(['series_prefix' => 'ZZZ']), 'announcement')
            ->create(['received_at' => '2026-10-01']);
        $otherAnnouncement = $otherLot->announcement;

        $filters = [
            'shipping_line' => $otherAnnouncement->shipping_line_id,
            'provider' => $otherAnnouncement->provider_id,
            'security_device_type' => $otherAnnouncement->security_device_type_id,
            'received_until' => '2026-10-31',
        ];

        foreach ($filters as $filter => $value) {
            Livewire::test(SecurityDeviceLotsTable::class)
                ->set("filterComponents.{$filter}", (string) $value)
                ->assertSeeText('ZZZ')
                ->assertDontSeeText('0000200 – 0000203');
        }

        Livewire::test(SecurityDeviceLotsTable::class)
            ->set('filterComponents.received_from', '2026-11-01')
            ->assertSeeText('0000200 – 0000203')
            ->assertDontSeeText('ZZZ');
    }

    public function test_the_detail_shows_the_lot_who_entered_it_and_how_many_devices_are_in_each_status(): void
    {
        $this->get(route('intranet.security-device-lots.show', $this->lot))
            ->assertOk()
            ->assertSee('<title>Lote GSW 0000200 – 0000203 | ', false)
            ->assertSeeText('MSK · Maersk')
            ->assertSeeText('T001-000123')
            ->assertSeeText('GSW0000200 – GSW0000203')
            ->assertSeeText('Bodeguero Uno')
            ->assertSeeText('Sin observaciones.')
            ->assertSeeInOrder(['2', 'Disponibles', '1', 'En un kit', '1', 'Usados', '0', 'Dañados'])
            ->assertSeeText('Dispositivos del lote');
    }

    public function test_the_devices_of_the_lot_can_be_searched_and_filtered_by_status(): void
    {
        SecurityDevice::factory()->create(['serial_number' => 'OTRO0000001']);

        Livewire::test(SecurityDevicesTable::class, ['lotId' => $this->lot->id])
            ->assertSeeText('GSW0000200')
            ->assertSeeText('GSW0000203')
            ->assertDontSeeText('OTRO0000001')
            ->set('search', '0202')
            ->assertSeeText('GSW0000202')
            ->assertDontSeeText('GSW0000200');

        Livewire::test(SecurityDevicesTable::class, ['lotId' => $this->lot->id])
            ->set('filterComponents.status', SecurityDeviceStatus::Used->value)
            ->assertSeeText('GSW0000203')
            ->assertDontSeeText('GSW0000201');
    }

    public function test_searching_a_serial_number_shows_its_status_and_lot(): void
    {
        Livewire::test(SerialNumberSearch::class)
            ->set('serialNumber', ' gsw0000202 ')
            ->call('search')
            ->assertSet('serialNumber', 'GSW0000202')
            ->assertSeeText('En un kit')
            ->assertSeeText('GSW 0000200 – 0000203')
            ->assertSee(route('intranet.security-device-lots.show', $this->lot));
    }

    public function test_searching_a_missing_serial_number_says_it_was_not_found(): void
    {
        Livewire::test(SerialNumberSearch::class)
            ->set('serialNumber', 'GSW0009999')
            ->call('search')
            ->assertSeeText('No encontramos un dispositivo con la serie GSW0009999.');

        Livewire::test(SerialNumberSearch::class)
            ->call('search')
            ->assertHasErrors(['serialNumber' => 'required']);
    }
}
