<?php

namespace Tests\Feature;

use App\Enums\BulkDispatchReason;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\BulkDispatches\CreateModal;
use App\Livewire\Tables\BulkDispatchesTable;
use App\Models\BulkDispatch;
use App\Models\Certification;
use App\Models\Client;
use App\Models\Kit;
use App\Models\KitDispatch;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\User;
use App\Models\Worker;
use App\Models\YardZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class BulkDispatchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Worker $receiver;

    private YardZone $yardZone;

    private Client $client;

    private SecurityDeviceType $bottleSeal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Bodeguero Uno']);
        $this->actingAs($this->user);

        $this->receiver = Worker::factory()->create(['full_name' => 'Trabajador De Ejemplo Dos']);
        $this->yardZone = YardZone::factory()->create(['branch_id' => $this->user->branch_id, 'name' => 'Zona certificación']);
        $this->client = Client::factory()->create(['business_name' => 'Agroexportadora del Norte S.A.C.']);
        $this->bottleSeal = SecurityDeviceType::factory()->create(['name' => 'Sello botella']);
    }

    public function test_guests_cannot_open_the_bulk_dispatches(): void
    {
        auth()->logout();

        $this->get(route('intranet.bulk-dispatches.index'))->assertRedirect(route('login'));
    }

    public function test_the_bulk_dispatches_have_their_own_list_apart_from_the_dispatches_of_kits(): void
    {
        $kitDispatch = KitDispatch::factory()->create();
        $bulkDispatch = $this->bulkDispatch(['reason' => BulkDispatchReason::Sale], 2);

        $this->get(route('intranet.bulk-dispatches.index'))
            ->assertOk()
            ->assertSeeInOrder(['Despachos', 'Kits', 'A granel'])
            ->assertSee(route('intranet.bulk-dispatches.index'))
            ->assertSeeText('Despachar a granel');

        Livewire::test(BulkDispatchesTable::class)
            ->assertSeeText($bulkDispatch->number())
            ->assertDontSeeText($kitDispatch->number().' ')
            ->assertSeeText('Venta')
            ->assertSeeText('Agroexportadora del Norte S.A.C.')
            ->assertSeeText('Zona certificación')
            ->assertSeeText('2 Sello botella')
            ->assertSeeText('Bodeguero Uno');
    }

    public function test_the_list_searches_by_number_or_worker_and_filters_by_reason(): void
    {
        $sale = $this->bulkDispatch(['reason' => BulkDispatchReason::Sale], 1);
        $operation = $this->bulkDispatch(['reason' => BulkDispatchReason::Operation, 'client_id' => null, 'kit_id' => $this->dispatchedKit('KIT-000046')->id, 'receiver_id' => Worker::factory()->create(['full_name' => 'Otro Trabajador'])->id], 1);

        Livewire::test(BulkDispatchesTable::class)
            ->set('search', $operation->number())
            ->assertSeeText('Kit KIT-000046')
            ->assertDontSeeText($sale->number());

        Livewire::test(BulkDispatchesTable::class)
            ->set('search', 'Otro Trab')
            ->assertSeeText($operation->number())
            ->assertDontSeeText($sale->number());

        Livewire::test(BulkDispatchesTable::class)
            ->set('filterComponents.reason', BulkDispatchReason::Sale->value)
            ->assertSeeText($sale->number())
            ->assertDontSeeText($operation->number());
    }

    public function test_a_scan_adds_an_available_device_and_rejects_the_others_with_their_reason(): void
    {
        $lot = $this->lot('SB', 1001, 1006);
        $this->setStatus($lot, 1002, SecurityDeviceStatus::Inactive);
        $kit = Kit::factory()->create(['pouch_code' => 'KIT-000046']);
        $kit->devices()->attach($this->deviceOf($lot, 1003)->id);
        $this->setStatus($lot, 1003, SecurityDeviceStatus::InKit);
        $previous = $this->bulkDispatch([], 0);
        $previous->devices()->attach($this->deviceOf($lot, 1004)->id);
        $this->setStatus($lot, 1004, SecurityDeviceStatus::Dispatched);

        $window = Livewire::test(CreateModal::class);

        $window->call('scan', ' sb001001 ')->assertReturned(['ok' => true, 'message' => 'Sello botella · agregado']);
        $window->call('scan', 'SB001001')->assertReturned(['ok' => true, 'message' => 'Ya estaba en este despacho.']);
        $window->call('scan', 'SB001002')->assertReturned(['ok' => false, 'message' => 'No está activado para despacho. Actívalo desde su lote antes de despacharlo.']);
        $window->call('scan', 'SB001003')->assertReturned(['ok' => false, 'message' => 'Está en el kit KIT-000046. Un dispositivo en un kit no se despacha a granel.']);
        $window->call('scan', 'SB001004')->assertReturned(['ok' => false, 'message' => "Ya está despachado en el despacho {$previous->number()}."]);
        $window->call('scan', 'XX999')->assertReturned(['ok' => false, 'message' => 'No encontramos un dispositivo con la serie XX999.']);

        $window->assertSet('form.deviceIds', [$this->deviceOf($lot, 1001)->id])
            ->assertSee('SB001001')
            ->assertSee('Despachar 1 dispositivo');
    }

    public function test_a_range_is_previewed_and_added_all_or_nothing_skipping_the_gaps(): void
    {
        $lot = $this->lot('SB', 1001, 1010);
        $this->deviceOf($lot, 1005)->delete();
        $this->setStatus($lot, 1009, SecurityDeviceStatus::InKit);

        $window = Livewire::test(CreateModal::class);

        $window->call('byRange', 'SB001001', 'SB001006')
            ->assertReturned(['ok' => true, 'message' => 'Sello botella · SB001001 a SB001006 · 5 dispositivos', 'count' => 5])
            ->assertSet('form.deviceIds', []);

        $window->call('byRange', 'SB001001', 'SB001006', true)
            ->assertReturned(['ok' => true, 'message' => 'Agregados: Sello botella · SB001001 a SB001006 · 5 dispositivos.', 'count' => 5])
            ->assertSee('SB001001 – SB001004')
            ->assertSee('SB001006');

        $window->call('byRange', 'SB001007', 'SB001010', true)
            ->assertReturned(['ok' => false, 'message' => 'No se agregó ninguno: 1 de los 4 dispositivos no se pueden despachar. SB001009: Está En un kit; solo se despachan dispositivos disponibles.', 'count' => 0]);

        $this->assertCount(5, $window->get('form.deviceIds'));

        $window->call('byRange', 'SB001006', 'SB001001')
            ->assertReturned(['ok' => false, 'message' => 'La serie inicial no puede ser mayor que la final.', 'count' => 0]);

        $other = $this->lot('HL', 1, 2);

        $window->call('byRange', 'SB001001', $other->devices()->value('serial_number'))
            ->assertReturned(['ok' => false, 'message' => 'La serie inicial y la final deben ser del mismo lote.', 'count' => 0]);
    }

    public function test_a_range_cannot_bring_more_than_ten_thousand_devices(): void
    {
        $lot = $this->lot('CIN', 1, 1, 10001);

        Livewire::test(CreateModal::class)
            ->call('byRange', 'CIN000001', 'CIN010001')
            ->assertReturned(['ok' => false, 'message' => 'Un rango puede tener como máximo 10 000 dispositivos. Este tiene 10 001.', 'count' => 0]);

        $this->assertSame(2, $lot->devices()->count());
    }

    public function test_a_quantity_takes_the_next_available_devices_of_the_type_from_the_first_serial_number(): void
    {
        $lot = $this->lot('SB', 1001, 1008);
        $this->setStatus($lot, 1002, SecurityDeviceStatus::InKit);
        $otherType = SecurityDeviceType::factory()->create(['name' => 'Precinto de naviera']);

        $window = Livewire::test(CreateModal::class);

        $window->call('byQuantity', (string) $this->bottleSeal->id, 'SB001001', '3', true)
            ->assertReturned(['ok' => true, 'message' => 'Agregados: Sello botella · SB001001 a SB001004 · 3 dispositivos.', 'count' => 3]);

        $window->call('byQuantity', (string) $this->bottleSeal->id, 'SB001001', '2', true)
            ->assertReturned(['ok' => true, 'message' => 'Agregados: Sello botella · SB001005 a SB001006 · 2 dispositivos.', 'count' => 2]);

        $window->call('byQuantity', (string) $this->bottleSeal->id, 'SB001001', '5')
            ->assertReturned(['ok' => false, 'message' => 'Desde SB001001 solo quedan 2 Sello botella disponibles: no alcanza para 5. Baja la cantidad o elige otra serie inicial.', 'count' => 0]);

        $window->call('byQuantity', (string) $otherType->id, 'SB001001', '1')
            ->assertReturned(['ok' => false, 'message' => 'La serie SB001001 es de Sello botella, no de Precinto de naviera.', 'count' => 0]);

        $this->assertCount(5, $window->get('form.deviceIds'));
    }

    public function test_a_quantity_can_start_from_the_prefix_of_a_series_alone(): void
    {
        $lot = $this->lot('HLS', 1, 6);
        $this->setStatus($lot, 1, SecurityDeviceStatus::InKit);

        $window = Livewire::test(CreateModal::class);

        $window->call('byQuantity', (string) $this->bottleSeal->id, ' hls ', '2', true)
            ->assertReturned(['ok' => true, 'message' => 'Agregados: Sello botella · HLS000002 a HLS000003 · 2 dispositivos.', 'count' => 2]);

        $window->call('byQuantity', (string) $this->bottleSeal->id, 'HLS', '9')
            ->assertReturned(['ok' => false, 'message' => 'De la serie HLS solo quedan 3 Sello botella disponibles: no alcanza para 9. Baja la cantidad o elige otra serie inicial.', 'count' => 0]);

        $window->call('byQuantity', (string) $this->bottleSeal->id, 'XYZ', '1')
            ->assertReturned(['ok' => false, 'message' => 'No encontramos un dispositivo ni una serie XYZ. Escribe la serie completa (por ejemplo HLS0000009) o solo su prefijo (HLS).', 'count' => 0]);

        $window->call('byQuantity', (string) SecurityDeviceType::factory()->create(['name' => 'Sello cable'])->id, 'HLS', '1')
            ->assertReturned(['ok' => false, 'message' => 'La serie HLS es de Sello botella, no de Sello cable.', 'count' => 0]);
    }

    public function test_a_stretch_can_be_taken_out_of_the_dispatch(): void
    {
        $lot = $this->lot('SB', 1001, 1004);

        $window = Livewire::test(CreateModal::class)->call('byRange', 'SB001001', 'SB001002', true);
        $window->call('scan', 'SB001004')
            ->call('removeStretch', "{$lot->id}:1001:1002")
            ->assertSet('form.deviceIds', [$this->deviceOf($lot, 1004)->id]);
    }

    public function test_a_sale_dispatches_the_devices_to_the_worker_and_they_become_dispatched(): void
    {
        $lot = $this->lot('SB', 1001, 1003);

        $window = $this->filledWindow(BulkDispatchReason::Sale)
            ->set('form.clientId', $this->client->id)
            ->set('form.notes', ' Venta de sellos ')
            ->call('byRange', 'SB001001', 'SB001003', true)
            ->call('confirmDispatch')
            ->assertHasNoErrors()
            ->assertDispatched('confirm-bulk-dispatch', title: '¿Despachar 3 dispositivos?')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertSet('form.deviceIds', [])
            ->assertDispatched('refreshDatatable');

        $dispatch = BulkDispatch::query()->sole();

        $window->assertDispatched('toast', icon: 'success', title: "Despacho {$dispatch->number()} registrado", text: '3 dispositivos despachados a Trabajador De Ejemplo Dos.');

        $this->assertSame([$this->receiver->id, $this->user->id, BulkDispatchReason::Sale, $this->client->id, null, $this->yardZone->id, 'Venta de sellos'], [
            $dispatch->receiver_id, $dispatch->created_by, $dispatch->reason, $dispatch->client_id, $dispatch->kit_id, $dispatch->yard_zone_id, $dispatch->notes,
        ]);
        $this->assertSame(3, $dispatch->devices()->count());
        $this->assertSame(3, $lot->devices()->where('status', SecurityDeviceStatus::Dispatched)->count());
    }

    public function test_the_operation_asks_for_a_dispatched_kit(): void
    {
        $lot = $this->lot('SB', 1001, 1001);
        $builtKit = Kit::factory()->create(['pouch_code' => 'KIT-000047', 'status' => KitStatus::Built]);
        $dispatchedKit = $this->dispatchedKit('KIT-000046');

        $window = $this->filledWindow(BulkDispatchReason::Operation)
            ->set('form.clientId', $this->client->id)
            ->call('scan', 'SB001001')
            ->call('confirmDispatch')
            ->assertHasErrors(['form.kitId' => 'required'])
            ->set('form.kitId', $builtKit->id)
            ->call('confirmDispatch')
            ->assertHasErrors('form.kitId')
            ->assertSee('Elige el contenedor de una certificación en curso, reaperturada o cerrada.')
            ->set('form.kitId', $dispatchedKit->id)
            ->call('save')
            ->assertHasNoErrors();

        $dispatch = BulkDispatch::query()->sole();

        $this->assertSame([BulkDispatchReason::Operation, $dispatchedKit->id, null], [$dispatch->reason, $dispatch->kit_id, $dispatch->client_id]);
        $this->assertSame('Kit KIT-000046', $dispatch->destination());
        $this->assertSame(SecurityDeviceStatus::Dispatched, $this->deviceOf($lot, 1001)->status);
    }

    public function test_the_window_is_validated_and_the_yard_zone_must_be_of_the_branch_of_the_user(): void
    {
        $this->lot('SB', 1001, 1001);
        $otherBranchZone = YardZone::factory()->create(['name' => 'Zona de otra sede']);

        Livewire::test(CreateModal::class)
            ->call('confirmDispatch')
            ->assertHasErrors(['form.receiverId' => 'required', 'form.reason' => 'required', 'form.yardZoneId' => 'required', 'form.deviceIds' => 'required'])
            ->assertSee('Agrega al menos un dispositivo para despachar.')
            ->set('form.reason', BulkDispatchReason::Sale->value)
            ->set('form.yardZoneId', $otherBranchZone->id)
            ->call('confirmDispatch')
            ->assertHasErrors(['form.clientId' => 'required', 'form.yardZoneId' => 'exists'])
            ->assertSee('Elige una zona de patio activa de tu sede.')
            ->assertDontSee('Zona de otra sede')
            ->assertNotDispatched('confirm-bulk-dispatch');

        $this->assertDatabaseCount('bulk_dispatches', 0);
    }

    public function test_a_device_put_in_a_kit_after_it_was_chosen_stops_the_whole_dispatch(): void
    {
        $lot = $this->lot('SB', 1001, 1002);

        $window = $this->filledWindow(BulkDispatchReason::Sale)
            ->set('form.clientId', $this->client->id)
            ->call('byRange', 'SB001001', 'SB001002', true);

        Kit::factory()->create(['pouch_code' => 'KIT-000050'])->devices()->attach($this->deviceOf($lot, 1002)->id);
        $this->setStatus($lot, 1002, SecurityDeviceStatus::InKit);

        $window->call('save')
            ->assertHasErrors('form.deviceIds')
            ->assertSee('SB001002: Está en el kit KIT-000050. Un dispositivo en un kit no se despacha a granel.')
            ->assertNotDispatched('toast');

        $this->assertDatabaseCount('bulk_dispatches', 0);
        $this->assertSame(SecurityDeviceStatus::Available, $this->deviceOf($lot, 1001)->status);
    }

    public function test_the_detail_shows_the_data_and_each_type_with_its_ranges_quantity_and_serial_numbers(): void
    {
        $lot = $this->lot('SB', 1001, 1005);
        $dispatch = $this->bulkDispatch(['notes' => 'Reposición por precinto roto.'], 0);
        $dispatch->devices()->attach([$this->deviceOf($lot, 1001)->id, $this->deviceOf($lot, 1002)->id, $this->deviceOf($lot, 1005)->id]);

        $this->get(route('intranet.bulk-dispatches.show', $dispatch))
            ->assertOk()
            ->assertSeeText($dispatch->number())
            ->assertSeeText('Agroexportadora del Norte S.A.C.')
            ->assertSeeText('Reposición por precinto roto.')
            ->assertSeeText('Rangos SB001001 – SB001002, SB001005 · Cantidad 3')
            ->assertSeeInOrder(['SB001001', 'SB001002', 'SB001005'])
            ->assertDontSee('PDF');
    }

    public function test_the_kit_options_offer_only_dispatched_kits_and_the_client_options_only_active_clients(): void
    {
        $this->dispatchedKit('KIT-000046');
        Kit::factory()->create(['pouch_code' => 'KIT-000047', 'status' => KitStatus::Built]);
        Client::factory()->trashed()->create(['business_name' => 'Agroexportadora Cerrada']);

        $this->postJson(route('intranet.kits.dispatched-options'), ['search' => 'kit-0000'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'MSKU0000046 · KIT-000046');

        $this->postJson(route('intranet.clients.options'), ['search' => 'Agroexportadora'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Agroexportadora del Norte S.A.C.');
    }

    public function test_the_kit_options_are_the_containers_of_the_certifications_with_their_original_kit(): void
    {
        $certified = $this->dispatchedKit('KIT-000046', 'MSKU1234567');
        $this->dispatchedKit('KIT-000047', null);

        $this->postJson(route('intranet.kits.dispatched-options'), ['search' => 'msku123'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $certified->id)
            ->assertJsonPath('0.name', 'MSKU1234567 · KIT-000046');

        $this->postJson(route('intranet.kits.dispatched-options'), ['search' => 'KIT-00004'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'MSKU1234567 · KIT-000046');
    }

    public function test_the_operation_takes_the_kits_of_certifications_in_progress_reopened_or_closed_but_never_annulled(): void
    {
        $this->lot('SB', 1001, 1001);
        $uncertified = $this->dispatchedKit('KIT-000047', null);
        $inProgress = $this->dispatchedKit('KIT-000048', 'TCLU7654321');
        $inProgress->update(['status' => KitStatus::Dispatched]);
        $closed = $this->dispatchedKit('KIT-000049', 'TCLU7654322');
        $closed->certification?->update(['closed_at' => now()]);
        $annulled = $this->dispatchedKit('KIT-000045', 'TCLU7654320');
        $annulled->certification?->update(['annulled_at' => now()]);

        $this->postJson(route('intranet.kits.dispatched-options'), ['search' => 'KIT-00004'])
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.name', 'TCLU7654322 · KIT-000049')
            ->assertJsonPath('1.name', 'TCLU7654321 · KIT-000048');
        $this->assertStringContainsString('· Cerrada ·', $this->postJson(route('intranet.kits.dispatched-options'), ['search' => 'KIT-000049'])->json('0.description'));
        $this->assertStringContainsString('· En curso ·', $this->postJson(route('intranet.kits.dispatched-options'), ['search' => 'KIT-000048'])->json('0.description'));

        foreach ([$uncertified, $annulled] as $kit) {
            $this->filledWindow(BulkDispatchReason::Operation)
                ->call('scan', 'SB001001')
                ->set('form.kitId', $kit->id)
                ->call('confirmDispatch')
                ->assertHasErrors('form.kitId');
        }

        $this->filledWindow(BulkDispatchReason::Operation)
            ->call('scan', 'SB001001')
            ->set('form.kitId', $inProgress->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($inProgress->id, BulkDispatch::query()->sole()->kit_id);
    }

    /**
     * Open the window with the worker, the reason and the yard zone filled.
     */
    private function filledWindow(BulkDispatchReason $reason): Testable
    {
        return Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.receiverId', $this->receiver->id)
            ->set('form.reason', $reason->value)
            ->set('form.yardZoneId', $this->yardZone->id);
    }

    /**
     * Create a lot of bottle seals with the given correlatives, all Available. With $last, only the first and the last
     * correlatives of the range have a device.
     */
    private function lot(string $prefix, int $start, int $end, ?int $last = null): SecurityDeviceLot
    {
        $lot = SecurityDeviceLot::factory()->for($this->bottleSeal)->create([
            'series_prefix' => $prefix,
            'min_digits' => 6,
            'series_start' => $start,
            'series_end' => $last ?? $end,
        ]);

        foreach ($last === null ? range($start, $end) : [$start, $last] as $correlative) {
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
     * Create a dispatched kit, Used by default in a certification of a container named after its pouch, as MSKU0000046
     * for KIT-000046. With a null container, the kit stays Despachado, without certification.
     */
    private function dispatchedKit(string $pouchCode, ?string $container = ''): Kit
    {
        $kit = Kit::factory()->create(['pouch_code' => $pouchCode, 'status' => $container === null ? KitStatus::Dispatched : KitStatus::Used]);
        KitDispatch::factory()->create(['receiver_id' => $this->receiver->id])->kits()->attach($kit->id);

        if ($container !== null) {
            Certification::factory()->create(['kit_id' => $kit->id, 'container' => $container !== '' ? $container : 'MSKU0'.substr($pouchCode, -6)]);
        }

        return $kit;
    }

    /**
     * Create a bulk dispatch of the user with the given number of new bottle seals.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function bulkDispatch(array $attributes, int $devices): BulkDispatch
    {
        $dispatch = BulkDispatch::factory()->create([
            'receiver_id' => $this->receiver->id,
            'created_by' => $this->user->id,
            'client_id' => $this->client->id,
            'yard_zone_id' => $this->yardZone->id,
            ...$attributes,
        ]);

        if ($devices > 0) {
            $lot = $this->lot('BD'.$dispatch->id.'X', 1, $devices);
            $dispatch->devices()->attach($lot->devices()->pluck('id')->all());
        }

        return $dispatch;
    }
}
