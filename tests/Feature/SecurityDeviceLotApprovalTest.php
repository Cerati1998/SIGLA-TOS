<?php

namespace Tests\Feature;

use App\Enums\SecurityDeviceStatus;
use App\Livewire\SecurityDeviceLots\ApproveModal;
use App\Livewire\SecurityDevices\SerialNumberSearch;
use App\Livewire\Tables\SecurityDeviceLotsTable;
use App\Livewire\Tables\SecurityDevicesTable;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceLotApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityDeviceLotApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private SecurityDeviceLot $lot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Bodeguero Uno']);
        $this->actingAs($this->user);

        $announcement = SecurityDeviceAnnouncement::factory()->create([
            'series_prefix' => 'GSW',
            'min_digits' => 7,
            'series_start' => 200,
            'series_end' => 209,
            'series_suffix' => null,
        ]);
        $this->lot = SecurityDeviceLot::factory()->for($announcement, 'announcement')->create(['quantity' => 10]);

        foreach ($announcement->serialNumbers() as $serialNumber) {
            SecurityDevice::factory()->for($this->lot, 'lot')->create(['serial_number' => $serialNumber]);
        }
    }

    public function test_a_new_lot_has_every_device_inactive_and_an_empty_approvals_log(): void
    {
        $this->get(route('intranet.security-device-lots.show', $this->lot))
            ->assertOk()
            ->assertSeeText('Inactivos')
            ->assertSeeText('Registrar aprobación')
            ->assertSeeText('Historial de aprobaciones')
            ->assertSeeText('0 de 10 aprobados')
            ->assertSeeText('Todavía no se registró ninguna aprobación de este lote.');

        $this->assertSame(10, $this->lot->devices()->where('status', SecurityDeviceStatus::Inactive)->count());
    }

    public function test_the_preview_counts_the_inactive_devices_of_the_stretch_and_the_ones_approved_already(): void
    {
        $this->approve(200, 202);

        Livewire::test(ApproveModal::class, ['lot' => $this->lot])
            ->set('form.correlativeStart', '201')
            ->set('form.correlativeEnd', '205')
            ->assertHasNoErrors()
            ->assertViewHas('preview', [
                'toApprove' => 3,
                'alreadyApproved' => 2,
                'firstSerialNumber' => 'GSW0000203',
                'lastSerialNumber' => 'GSW0000205',
            ])
            ->assertSeeText('3 dispositivos pasan a Disponible')
            ->assertSeeText('2 del tramo ya estaban aprobados')
            ->assertSeeText('Aprobar 3 dispositivos');
    }

    public function test_registering_an_approval_makes_the_inactive_devices_of_the_stretch_available_and_logs_it(): void
    {
        $this->approve(200, 201);

        Livewire::test(ApproveModal::class, ['lot' => $this->lot])
            ->set('form.correlativeStart', ' 200 ')
            ->set('form.correlativeEnd', '204')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.security-device-lots.show', $this->lot));

        $this->assertSame(['icon' => 'success', 'title' => 'Aprobación registrada', 'text' => '3 dispositivos pasaron a Disponible.'], session('toast'));

        $approval = $this->lot->approvals()->first();

        $this->assertNotNull($approval);
        $this->assertSame([200, 204, 3, $this->user->id], [$approval->correlative_start, $approval->correlative_end, $approval->approved_count, $approval->approved_by]);
        $this->assertEqualsCanonicalizing(['GSW0000202', 'GSW0000203', 'GSW0000204'], $approval->devices()->pluck('serial_number')->all());
        $this->assertSame(5, $this->lot->devices()->where('status', SecurityDeviceStatus::Available)->count());
        $this->assertSame(5, $this->lot->devices()->where('status', SecurityDeviceStatus::Inactive)->count());
        $this->assertFalse($this->lot->seriesCanBeCorrected());
    }

    public function test_the_stretch_must_be_inside_the_lot_and_go_from_the_lower_correlative_to_the_higher(): void
    {
        Livewire::test(ApproveModal::class, ['lot' => $this->lot])
            ->set('form.correlativeStart', '205')
            ->set('form.correlativeEnd', '215')
            ->assertHasErrors(['form.correlativeEnd' => 'between'])
            ->assertSeeText('El tramo debe estar dentro del lote: del 200 al 209.')
            ->assertViewHas('preview', null);

        Livewire::test(ApproveModal::class, ['lot' => $this->lot])
            ->set('form.correlativeStart', '206')
            ->set('form.correlativeEnd', '203')
            ->assertHasErrors(['form.correlativeStart' => 'lte'])
            ->assertSeeText('El correlativo inicial no puede ser mayor que el final.');

        Livewire::test(ApproveModal::class, ['lot' => $this->lot])
            ->call('save')
            ->assertHasErrors(['form.correlativeStart' => 'required', 'form.correlativeEnd' => 'required']);

        $this->assertDatabaseCount('security_device_lot_approvals', 0);
        $this->assertSame(10, $this->lot->devices()->where('status', SecurityDeviceStatus::Inactive)->count());
    }

    public function test_a_stretch_with_every_device_approved_already_registers_nothing(): void
    {
        $this->approve(200, 204);

        Livewire::test(ApproveModal::class, ['lot' => $this->lot])
            ->set('form.correlativeStart', '201')
            ->set('form.correlativeEnd', '203')
            ->assertSeeText('Los 3 dispositivos del tramo ya estaban aprobados.')
            ->call('save')
            ->assertDispatched('toast', icon: 'warning', title: 'Nada que aprobar')
            ->assertNoRedirect();

        $this->assertDatabaseCount('security_device_lot_approvals', 1);
    }

    public function test_the_approvals_log_shows_each_stretch_with_its_count_user_and_date_the_latest_first(): void
    {
        $this->travelTo('2026-11-12 14:15:00');
        $this->approve(200, 204);
        $this->travelTo('2026-11-14 21:20:00');
        $this->approve(205, 206, User::factory()->create(['name' => 'Bodeguero Dos']));

        $this->get(route('intranet.security-device-lots.show', $this->lot))
            ->assertSeeText('7 de 10 aprobados')
            ->assertSeeInOrder([
                'Del 205 al 206', '2 aprobados', 'GSW0000205 – GSW0000206', '14/11/2026 16:20 · Bodeguero Dos',
                'Del 200 al 204', '5 aprobados', 'GSW0000200 – GSW0000204', '12/11/2026 09:15 · Bodeguero Uno',
            ]);
    }

    public function test_the_devices_and_the_serial_search_show_when_a_device_was_approved(): void
    {
        $this->travelTo('2026-11-12 14:15:00');
        $this->approve(200, 200);

        Livewire::test(SecurityDevicesTable::class, ['lotId' => $this->lot->id])
            ->assertSeeInOrder(['GSW0000200', 'Disponible', '12/11/2026 09:15', 'GSW0000201', 'Inactivo']);

        Livewire::test(SerialNumberSearch::class)
            ->set('serialNumber', 'gsw0000200')
            ->call('search')
            ->assertSeeText('12/11/2026 09:15 · Bodeguero Uno');
    }

    public function test_the_list_of_lots_shows_and_filters_by_how_many_devices_were_approved(): void
    {
        $this->approve(200, 203);
        $untouched = SecurityDeviceLot::factory()->for(SecurityDeviceAnnouncement::factory()->state(['series_prefix' => 'AAA']), 'announcement')->create(['quantity' => 1]);
        SecurityDevice::factory()->for($untouched, 'lot')->create();
        $finished = SecurityDeviceLot::factory()->for(SecurityDeviceAnnouncement::factory()->state(['series_prefix' => 'ZZZ']), 'announcement')->create(['quantity' => 1]);
        SecurityDevice::factory()->for($finished, 'lot')->create(['status' => SecurityDeviceStatus::Available]);

        Livewire::test(SecurityDeviceLotsTable::class)
            ->assertSeeText('4 de 10')
            ->assertSeeText('0 de 1')
            ->assertSeeText('1 de 1');

        $prefixes = ['none' => 'AAA', 'partial' => 'GSW', 'full' => 'ZZZ'];

        foreach ($prefixes as $approval => $prefix) {
            $table = Livewire::test(SecurityDeviceLotsTable::class)
                ->set('filterComponents.approval', $approval)
                ->assertSeeText($prefix);

            foreach (array_diff($prefixes, [$prefix]) as $otherPrefix) {
                $table->assertDontSeeText($otherPrefix);
            }
        }
    }

    /**
     * Register the approval of a stretch of the lot, as bodega does it from its detail.
     */
    private function approve(int $correlativeStart, int $correlativeEnd, ?User $user = null): void
    {
        $user ??= $this->user;

        $approval = SecurityDeviceLotApproval::factory()->for($this->lot, 'lot')->create([
            'correlative_start' => $correlativeStart,
            'correlative_end' => $correlativeEnd,
            'approved_count' => $correlativeEnd - $correlativeStart + 1,
            'approved_by' => $user->id,
        ]);

        $this->lot->devices()
            ->whereIn('serial_number', array_map($this->lot->announcement->serialNumber(...), range($correlativeStart, $correlativeEnd)))
            ->update(['status' => SecurityDeviceStatus::Available->value, 'security_device_lot_approval_id' => $approval->id]);
    }
}
