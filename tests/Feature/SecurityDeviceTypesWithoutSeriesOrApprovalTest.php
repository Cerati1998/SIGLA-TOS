<?php

namespace Tests\Feature;

use App\Enums\KitStatus;
use App\Enums\SecurityDeviceKind;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\BulkDispatches\CreateModal as BulkDispatchCreateModal;
use App\Livewire\Kits\DeviceScanner;
use App\Livewire\SecurityDeviceAnnouncements\CreateForm;
use App\Livewire\SecurityDeviceLots\EditForm;
use App\Livewire\SecurityDevices\SerialNumberSearch;
use App\Livewire\SecurityDeviceTypes\CreateModal;
use App\Livewire\SecurityDeviceTypes\EditModal;
use App\Livewire\Tables\SecurityDeviceLotsTable;
use App\Livewire\Tables\SecurityDeviceTypesTable;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\Provider;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * T62: the types of security device that do not use a series or do not require the approval of their lots.
 */
class SecurityDeviceTypesWithoutSeriesOrApprovalTest extends TestCase
{
    use RefreshDatabase;

    private ShippingLine $shippingLine;

    private Provider $provider;

    private SecurityDeviceType $padlock;

    private SecurityDeviceType $seal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');

        $this->shippingLine = ShippingLine::factory()->create(['code' => 'MSK']);
        $this->provider = Provider::factory()->create();
        $this->padlock = SecurityDeviceType::factory()->withoutSeries()->withoutLotApproval()->create(['name' => 'Candado']);
        $this->seal = SecurityDeviceType::factory()->withoutSeries()->create(['name' => 'Sello numerado']);
    }

    public function test_a_new_type_uses_a_series_and_requires_approval_unless_its_boxes_say_otherwise(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.name', 'Precinto ISO')
            ->set('form.kind', SecurityDeviceKind::ContainerSeal->value)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Candado de bodega')
            ->set('form.kind', SecurityDeviceKind::Other->value)
            ->set('form.withoutSeries', true)
            ->set('form.requiresLotApproval', false)
            ->call('save')
            ->assertHasNoErrors();

        $iso = SecurityDeviceType::query()->where('name', 'Precinto ISO')->sole();
        $padlock = SecurityDeviceType::query()->where('name', 'Candado de bodega')->sole();

        $this->assertTrue($iso->uses_series);
        $this->assertTrue($iso->requires_lot_approval);
        $this->assertFalse($padlock->uses_series);
        $this->assertFalse($padlock->requires_lot_approval);

        Livewire::test(SecurityDeviceTypesTable::class)
            ->set('search', 'Candado de bodega')
            ->assertSeeText('No utiliza')
            ->assertSeeText('No requiere');
    }

    public function test_the_boxes_of_a_type_change_only_while_it_has_no_lots(): void
    {
        Livewire::test(EditModal::class)
            ->call('edit', $this->seal->id)
            ->assertSet('form.hasLots', false)
            ->set('form.requiresLotApproval', false)
            ->call('save');

        $this->assertFalse($this->seal->refresh()->requires_lot_approval);

        $this->receiveLot($this->padlock, 1, 3);

        Livewire::test(EditModal::class)
            ->call('edit', $this->padlock->id)
            ->assertSet('form.hasLots', true)
            ->assertSeeText('Este tipo ya tiene lotes')
            ->set('form.name', 'Candado grande')
            ->set('form.withoutSeries', false)
            ->set('form.requiresLotApproval', true)
            ->call('save');

        $this->padlock->refresh();
        $this->assertSame('Candado grande', $this->padlock->name);
        $this->assertFalse($this->padlock->uses_series);
        $this->assertFalse($this->padlock->requires_lot_approval);
    }

    public function test_a_lot_of_a_type_without_series_is_its_padded_correlatives_and_needs_no_start_of_series(): void
    {
        $this->lotForm($this->seal, '6', '25', '30', prefix: 'GSW', suffix: 'A')
            ->call('save')
            ->assertHasNoErrors();

        $lot = SecurityDeviceLot::query()->sole();

        $this->assertSame('', $lot->series_prefix);
        $this->assertNull($lot->series_suffix);
        $this->assertSame('000025 – 000030', $lot->name());
        $this->assertSame(
            ['000025', '000026', '000027', '000028', '000029', '000030'],
            $lot->devices()->orderBy('serial_number')->pluck('serial_number')->all(),
        );
        $this->assertSame([SecurityDeviceStatus::Inactive->value], $lot->devices()->distinct()->pluck('status')->map->value->all());
    }

    public function test_a_type_with_series_still_requires_its_start_of_series(): void
    {
        $iso = SecurityDeviceType::factory()->create();

        $this->lotForm($iso, '6', '1', '3')
            ->call('save')
            ->assertHasErrors('form.lots')
            ->assertSeeText('Lote 1: El campo inicio de serie es obligatorio.');
    }

    public function test_the_devices_of_a_type_without_approval_are_born_available(): void
    {
        $this->lotForm($this->padlock, '6', '1', '4')->call('save')->assertHasNoErrors();

        $lot = SecurityDeviceLot::query()->sole();

        $this->assertSame(4, $lot->devices()->where('status', SecurityDeviceStatus::Available->value)->count());
    }

    public function test_a_serial_number_without_series_is_unique_only_within_its_type(): void
    {
        $this->receiveLot($this->padlock, 1, 50);

        $this->lotForm($this->seal, '6', '40', '60')->call('save')->assertHasNoErrors();

        $this->lotForm($this->padlock, '6', '49', '51')
            ->call('save')
            ->assertHasErrors('form.lots');

        $this->assertSame(2, SecurityDeviceLot::query()->count());
        $this->assertSame(2, SecurityDevice::query()->where('serial_number', '000050')->count());
    }

    public function test_the_error_names_the_type_where_the_serial_numbers_exist(): void
    {
        $this->receiveLot($this->padlock, 1, 5);

        $this->lotForm($this->padlock, '6', '5', '6')
            ->call('save')
            ->assertSeeText('1 serie ya existe en Candado: 000005. Revisa el rango.');
    }

    public function test_a_serial_number_without_series_cannot_repeat_one_of_a_type_with_series(): void
    {
        $iso = SecurityDeviceType::factory()->create();
        $this->receiveLot($iso, 1, 3, '0');

        $this->lotForm($this->padlock, '7', '1', '3')
            ->call('save')
            ->assertHasErrors('form.lots');

        $this->receiveLot($this->seal, 10, 12);

        $this->lotForm($iso, '2', '10', '12', prefix: '0000')
            ->call('save')
            ->assertHasErrors('form.lots');
    }

    public function test_a_lot_changes_its_type_only_to_one_with_the_same_boxes(): void
    {
        $lot = $this->receiveLot($this->padlock, 1, 3);
        $otherPadlock = SecurityDeviceType::factory()->withoutSeries()->withoutLotApproval()->create(['name' => 'Candado chico']);

        $this->editForm($lot)
            ->set('form.securityDeviceTypeId', (string) $this->seal->id)
            ->call('save')
            ->assertHasErrors('form.securityDeviceTypeId')
            ->assertSeeText('Solo puedes cambiarlo por un tipo que también no utilice serie y no requiera aprobación en lote, como Candado.');

        $this->editForm($lot)
            ->set('form.securityDeviceTypeId', (string) $otherPadlock->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($otherPadlock->id, $lot->refresh()->security_device_type_id);
        $this->assertSame([$otherPadlock->id], $lot->devices()->distinct()->pluck('serial_number_scope')->all());
    }

    public function test_a_lot_cannot_move_to_a_type_without_series_where_its_serial_numbers_exist(): void
    {
        $lot = $this->receiveLot($this->padlock, 1, 3);
        $otherPadlock = SecurityDeviceType::factory()->withoutSeries()->withoutLotApproval()->create(['name' => 'Candado chico']);
        $this->receiveLot($otherPadlock, 3, 4);

        $this->editForm($lot)
            ->set('form.securityDeviceTypeId', (string) $otherPadlock->id)
            ->call('save')
            ->assertHasErrors('form.serialNumbers');

        $this->assertSame($this->padlock->id, $lot->refresh()->security_device_type_id);
    }

    public function test_the_series_of_a_lot_without_approval_is_corrected_while_its_devices_are_still_available(): void
    {
        $lot = $this->receiveLot($this->padlock, 1, 3);

        $this->editForm($lot)
            ->set('form.seriesEnd', '4')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Se rehicieron sus 4 dispositivos, todos disponibles.', session('toast.text'));
        $this->assertSame(4, $lot->devices()->where('status', SecurityDeviceStatus::Available->value)->count());

        $lot->devices()->first()?->update(['status' => SecurityDeviceStatus::InKit]);

        $this->editForm($lot)->assertSet('form.seriesLocked', true);
    }

    public function test_the_lots_without_approval_say_so_in_the_list_and_their_detail_offers_no_approval(): void
    {
        $withoutApproval = $this->receiveLot($this->padlock, 1, 3);
        $withApproval = $this->receiveLot($this->seal, 1, 2);

        Livewire::test(SecurityDeviceLotsTable::class, ['announcementId' => $withoutApproval->security_device_announcement_id])
            ->assertSeeText('No requiere');

        Livewire::test(SecurityDeviceLotsTable::class, ['announcementId' => $withApproval->security_device_announcement_id])
            ->assertSeeText('0 de 2');

        $this->get(route('intranet.security-device-lots.show', $withoutApproval))
            ->assertOk()
            ->assertDontSeeText('Registrar aprobación')
            ->assertDontSeeText('Historial de aprobaciones');

        $this->get(route('intranet.security-device-lots.show', $withApproval))
            ->assertSeeText('Registrar aprobación');
    }

    public function test_searching_a_serial_number_shared_by_two_types_shows_both(): void
    {
        $this->receiveLot($this->padlock, 50, 50);
        $this->receiveLot($this->seal, 50, 50);

        Livewire::test(SerialNumberSearch::class)
            ->set('serialNumber', '000050')
            ->call('search')
            ->assertSeeText('La serie 000050 existe en 2 tipos de dispositivo sin serie.')
            ->assertSeeText('Candado')
            ->assertSeeText('Sello numerado');
    }

    public function test_the_bulk_dispatch_asks_for_the_type_when_a_serial_number_is_shared(): void
    {
        $this->receiveLot($this->padlock, 50, 52);
        $this->receiveLot($this->seal, 50, 52);

        $window = Livewire::test(BulkDispatchCreateModal::class);

        $window->call('scan', '000050')
            ->assertReturned(['ok' => false, 'message' => 'La serie 000050 existe en más de un tipo. Elige el tipo de dispositivo.']);

        $window->call('scan', '000050', (string) $this->padlock->id)
            ->assertReturned(['ok' => true, 'message' => 'Candado · agregado']);

        $window->call('byRange', '000051', '000052', false, (string) $this->padlock->id)
            ->assertReturned(['ok' => true, 'message' => 'Candado · 000051 a 000052 · 2 dispositivos', 'count' => 2]);

        $window->call('byRange', '000051', '000052')
            ->assertReturned(['ok' => false, 'message' => 'La serie 000051 existe en más de un tipo. Elige el tipo de dispositivo.', 'count' => 0]);
    }

    public function test_a_kit_takes_the_shared_serial_number_of_the_type_of_its_field(): void
    {
        $padlockForKit = SecurityDeviceType::factory()->withoutSeries()->withoutLotApproval()->create(['name' => 'Candado de kit']);
        $this->receiveLot($this->padlock, 50, 50);
        $this->receiveLot($padlockForKit, 50, 50);

        $kitType = KitType::factory()->carrying($padlockForKit, 1)->create();
        $kit = Kit::factory()->create(['kit_type_id' => $kitType->id, 'shipping_line_id' => $this->shippingLine->id, 'status' => KitStatus::Building]);
        $kit->securityDeviceTypes()->attach($padlockForKit->id, ['quantity' => 1]);

        Livewire::test(DeviceScanner::class, ['kit' => $kit])
            ->call('add', Kit::slotKey($padlockForKit->id, null), '000050')
            ->assertReturned(['ok' => true, 'message' => 'Candado de kit · agregado']);

        $this->assertSame($padlockForKit->id, $kit->devices()->sole()->lot->security_device_type_id);
    }

    /**
     * Fill a new announcement with one lot of the given type and range. The start and the end of series stay empty
     * unless they are given.
     */
    private function lotForm(SecurityDeviceType $type, string $minDigits, string $seriesStart, string $seriesEnd, string $prefix = '', string $suffix = ''): Testable
    {
        return Livewire::test(CreateForm::class)
            ->set('form.shippingLineId', (string) $this->shippingLine->id)
            ->set('form.providerId', (string) $this->provider->id)
            ->set('form.receivedAt', '2026-11-20')
            ->set('form.dispatchNumber', 'T001-000123')
            ->set('form.lots', [[
                'securityDeviceTypeId' => (string) $type->id,
                'seriesPrefix' => $prefix,
                'minDigits' => $minDigits,
                'seriesStart' => $seriesStart,
                'seriesEnd' => $seriesEnd,
                'seriesSuffix' => $suffix,
                'notes' => '',
            ]]);
    }

    /**
     * Receive a lot of the given type through the form, with six digits and, for a type with series, the given prefix.
     */
    private function receiveLot(SecurityDeviceType $type, int $start, int $end, string $prefix = 'GSW'): SecurityDeviceLot
    {
        $this->lotForm($type, '6', (string) $start, (string) $end, prefix: $prefix)
            ->call('save')
            ->assertHasNoErrors();

        return SecurityDeviceLot::query()->latest('id')->firstOrFail();
    }

    /**
     * Open the correction of the lot.
     */
    private function editForm(SecurityDeviceLot $lot): Testable
    {
        return Livewire::test(EditForm::class, ['lot' => $lot->fresh()]);
    }
}
