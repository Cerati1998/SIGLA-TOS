<?php

namespace Tests\Feature;

use App\Actions\KitDispatches\BuildKitPdfPage;
use App\Actions\Kits\CreateKit;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\KitDispatches\CreateModal;
use App\Livewire\Kits\DeviceScanner;
use App\Livewire\Tables\KitDispatchesTable;
use App\Livewire\Tables\KitsTable;
use App\Models\Kit;
use App\Models\KitDispatch;
use App\Models\KitType;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KitDispatchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ShippingLine $maersk;

    private SecurityDeviceType $bottleSeal;

    private KitType $reeferKit;

    private Worker $receiver;

    private int $nextSerial = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Bodeguero Uno']);
        $this->actingAs($this->user);

        $this->maersk = ShippingLine::factory()->create(['code' => 'MSK', 'name' => 'Maersk']);
        $this->bottleSeal = SecurityDeviceType::factory()->create(['name' => 'Sello botella']);
        $this->reeferKit = KitType::factory()->carrying($this->bottleSeal, 2)->create(['name' => 'Kit de seguridad reefer']);
        $this->receiver = Worker::factory()->create(['full_name' => 'Trabajador De Ejemplo Dos', 'document_number' => '00000002']);
    }

    public function test_the_warehouse_dispatches_the_kits_marked_by_scanning_and_by_checkbox_to_a_worker_in_one_step(): void
    {
        $scanned = $this->builtKit('KIT-000046');
        $checked = $this->builtKit('KIT-000047');

        $window = Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.receiverId', $this->receiver->id);

        $window->call('mark', ' kit-000046 ')
            ->assertReturned(['ok' => true, 'message' => 'Kit de seguridad reefer · marcado', 'kitId' => $scanned->id, 'kitType' => 'Kit de seguridad reefer']);
        $window->set('form.kitIds', [$scanned->id, (string) $checked->id])
            ->assertSee('2 marcados')
            ->assertSee('2 Kit de seguridad reefer')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertSet('form.kitIds', [])
            ->assertDispatched('toast', icon: 'success', title: 'Despacho D-000001 registrado', text: '2 kits despachados a Trabajador De Ejemplo Dos.')
            ->assertDispatched('refreshDatatable');

        $dispatch = KitDispatch::query()->sole();

        $this->assertSame([$this->receiver->id, $this->user->id], [$dispatch->receiver_id, $dispatch->created_by]);
        $this->assertNotNull($dispatch->created_at);
        $this->assertSame(['KIT-000046', 'KIT-000047'], $dispatch->kits->pluck('pouch_code')->all());
        $this->assertSame([KitStatus::Dispatched, KitStatus::Dispatched], [$scanned->fresh()?->status, $checked->fresh()?->status]);
        $this->assertSame(
            [SecurityDeviceStatus::InKit],
            SecurityDevice::query()->pluck('status')->unique()->values()->all(),
        );
    }

    public function test_a_scanned_pouch_that_cannot_be_dispatched_is_rejected_with_the_reason_and_is_not_marked(): void
    {
        $incomplete = $this->kit('KIT-000048', devices: 1);
        $alreadyDispatched = $this->builtKit('KIT-000031');
        $dispatch = KitDispatch::factory()->create();
        $dispatch->kits()->attach($alreadyDispatched->id);
        $alreadyDispatched->update(['status' => KitStatus::Dispatched]);

        $window = Livewire::test(CreateModal::class);

        $window->call('mark', 'KIT-009999')
            ->assertReturned(['ok' => false, 'message' => 'No encontramos un kit con la funda KIT-009999.']);
        $window->call('mark', $incomplete->pouch_code)
            ->assertReturned(['ok' => false, 'message' => 'Está Armando: le faltan 1 Sello botella. Complétalo antes de despacharlo.']);
        $window->call('mark', $alreadyDispatched->pouch_code)
            ->assertReturned(['ok' => false, 'message' => sprintf('Ya está despachado en el despacho %s.', $dispatch->number())]);

        $window->assertSet('form.kitIds', []);
    }

    public function test_scanning_a_kit_that_is_already_marked_does_not_mark_it_twice(): void
    {
        $kit = $this->builtKit('KIT-000050');

        Livewire::test(CreateModal::class)
            ->call('mark', 'KIT-000050')
            ->call('mark', 'KIT-000050')
            ->assertReturned(['ok' => true, 'message' => 'Ya estaba marcado en este despacho.'])
            ->assertSet('form.kitIds', [$kit->id]);
    }

    public function test_the_receiver_and_at_least_one_kit_are_required_and_the_receiver_must_be_active(): void
    {
        $inactive = Worker::factory()->create(['deleted_at' => now()]);

        Livewire::test(CreateModal::class)
            ->call('confirmDispatch')
            ->assertHasErrors(['form.receiverId' => 'required', 'form.kitIds' => 'required'])
            ->assertSee('Elige quién recibe los kits.')
            ->assertSee('Marca al menos un kit para despachar.')
            ->assertNotDispatched('confirm-kit-dispatch');

        Livewire::test(CreateModal::class)
            ->set('form.receiverId', $inactive->id)
            ->set('form.kitIds', [$this->builtKit('KIT-000046')->id])
            ->call('save')
            ->assertHasErrors(['form.receiverId' => 'exists']);

        $this->assertSame(0, KitDispatch::query()->count());
    }

    public function test_the_dispatch_is_confirmed_with_how_many_kits_go_and_who_receives_them(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.receiverId', $this->receiver->id)
            ->set('form.kitIds', [$this->builtKit('KIT-000046')->id, $this->builtKit('KIT-000047')->id])
            ->call('confirmDispatch')
            ->assertHasNoErrors()
            ->assertDispatched('confirm-kit-dispatch', title: '¿Despachar 2 kits a Trabajador De Ejemplo Dos?', text: 'Después no podrás cambiar su contenido.');
    }

    public function test_a_kit_dispatched_by_someone_else_after_it_was_marked_stops_the_whole_dispatch(): void
    {
        $kit = $this->builtKit('KIT-000046');
        $other = $this->builtKit('KIT-000047');

        $window = Livewire::test(CreateModal::class)
            ->set('form.receiverId', $this->receiver->id)
            ->set('form.kitIds', [$kit->id, $other->id]);

        $earlier = KitDispatch::factory()->create();
        $earlier->kits()->attach($kit->id);
        $kit->update(['status' => KitStatus::Dispatched]);

        $window->call('save')
            ->assertHasErrors('form.kitIds')
            ->assertSee(sprintf('KIT-000046: Ya está despachado en el despacho %s.', $earlier->number()));

        $this->assertSame(1, KitDispatch::query()->count());
        $this->assertSame(KitStatus::Built, $other->fresh()?->status);
    }

    public function test_the_table_of_the_window_only_lists_kits_armados_not_dispatched_and_filters_them(): void
    {
        $sealsKit = KitType::factory()->carrying($this->bottleSeal, 1)->create(['name' => 'Kit de cambio de sellos']);
        $this->builtKit('KIT-000046');
        $this->builtKit('KIT-000051', $sealsKit);
        $this->kit('KIT-000048', devices: 0);
        $dispatched = $this->builtKit('KIT-000031');
        KitDispatch::factory()->create()->kits()->attach($dispatched->id);
        $dispatched->update(['status' => KitStatus::Dispatched]);

        Livewire::test(CreateModal::class)
            ->assertSee(['KIT-000046', 'KIT-000051', '2 armados disponibles'])
            ->assertDontSee(['KIT-000048', 'KIT-000031'])
            ->set('kitTypeFilter', (string) $sealsKit->id)
            ->assertSee('KIT-000051')
            ->assertDontSee('KIT-000046')
            ->set('kitTypeFilter', '')
            ->set('pouchSearch', '46')
            ->assertSee('KIT-000046')
            ->assertDontSee('KIT-000051')
            ->set('pouchSearch', '')
            ->call('mark', 'KIT-000051')
            ->set('onlyMarked', true)
            ->assertSee('KIT-000051')
            ->assertDontSee('KIT-000046');
    }

    public function test_closing_the_window_without_dispatching_forgets_what_was_chosen(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.receiverId', $this->receiver->id)
            ->call('mark', $this->builtKit('KIT-000046')->pouch_code)
            ->set('kitTypeFilter', (string) $this->reeferKit->id)
            ->call('discard')
            ->assertSet('form.receiverId', null)
            ->assertSet('form.kitIds', [])
            ->assertSet('kitTypeFilter', '');

        $this->assertSame(0, KitDispatch::query()->count());
    }

    public function test_the_list_shows_each_dispatch_with_its_number_receiver_kits_and_who_dispatched_it_and_filters_them(): void
    {
        $sealsKit = KitType::factory()->carrying($this->bottleSeal, 1)->create(['name' => 'Kit de cambio de sellos']);
        $other = Worker::factory()->create(['full_name' => 'Otro Trabajador']);
        $first = $this->dispatchOf($this->receiver, [$this->builtKit('KIT-000046'), $this->builtKit('KIT-000051', $sealsKit)]);
        $second = $this->dispatchOf($other, [$this->builtKit('KIT-000047')]);
        $second->forceFill(['created_at' => now()->subWeeks(2)])->save();

        Livewire::test(KitDispatchesTable::class)
            ->assertSee(['D-000001', 'Trabajador De Ejemplo Dos', '1 Kit de cambio de sellos · 1 Kit de seguridad reefer', 'Bodeguero Uno', $first->week()])
            ->assertSee(['D-000002', 'Otro Trabajador'])
            ->set('filterComponents.week', $first->week())
            ->assertSee('D-000001')
            ->assertDontSee('D-000002')
            ->set('filterComponents.week', '')
            ->set('filterComponents.receiver', (string) $other->id)
            ->assertSee('D-000002')
            ->assertDontSee('D-000001')
            ->set('filterComponents.receiver', '')
            ->set('filterComponents.kit_type', (string) $sealsKit->id)
            ->assertSee('D-000001')
            ->assertDontSee('D-000002')
            ->set('filterComponents.kit_type', '')
            ->set('search', 'd-000002')
            ->assertSee('Otro Trabajador')
            ->assertDontSee('D-000001')
            ->set('search', 'ejemplo dos')
            ->assertSee('D-000001')
            ->assertDontSee('D-000002');
    }

    public function test_the_page_of_a_dispatched_kit_shows_its_dispatch_and_its_pdf_and_no_longer_offers_to_scan_or_remove(): void
    {
        $kit = $this->builtKit('KIT-000046');
        $dispatch = $this->dispatchOf($this->receiver, [$kit]);

        Livewire::test(DeviceScanner::class, ['kit' => $kit->fresh()])
            ->assertSee(sprintf('Despachado en %s', $dispatch->number()))
            ->assertSee('Trabajador De Ejemplo Dos')
            ->assertSeeHtml(route('intranet.kits.pdf', $kit))
            ->assertDontSeeHtml('data-slot-input')
            ->assertDontSeeHtml('Quitar del kit');

        Livewire::test(KitsTable::class)
            ->assertSeeHtml(route('intranet.kits.pdf', $kit));
    }

    public function test_the_pdf_of_a_dispatched_kit_opens_in_the_browser_and_a_kit_not_dispatched_has_none(): void
    {
        $kit = $this->builtKit('KIT-000046');
        $notDispatched = $this->builtKit('KIT-000047');
        $dispatch = $this->dispatchOf($this->receiver, [$kit]);

        $this->get(route('intranet.kits.pdf', $kit))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', sprintf('inline; filename="Despacho %s - KIT-000046.pdf"', $dispatch->number()));

        $this->get(route('intranet.kits.pdf', $notDispatched))->assertNotFound();

        auth()->logout();
        $this->get(route('intranet.kits.pdf', $kit))->assertRedirect(route('login'));
    }

    public function test_the_pdf_shows_the_contents_of_the_kit_when_it_was_dispatched_who_delivered_it_and_to_whom(): void
    {
        $kit = $this->builtKit('KIT-000046');
        $dispatch = $this->dispatchOf($this->receiver, [$kit]);

        $html = view('pdf.kits.dispatch', [
            'dispatch' => $dispatch->load(['receiver', 'creator']),
            'pages' => [app(BuildKitPdfPage::class)->handle($kit)],
            'generatedBy' => 'Bodeguero Uno',
        ])->render();

        $this->assertStringContainsString('Despacho de kit de seguridad', $html);
        $this->assertStringContainsString($dispatch->number(), $html);
        $this->assertStringContainsString('KIT-000046', $html);
        $this->assertStringContainsString('SLL0000001', $html);
        $this->assertStringContainsString('SLL0000002', $html);
        $this->assertStringContainsString(($dispatch->created_at ?? now())->timezone('America/Lima')->format('d/m/Y H:i'), $html);
        $this->assertStringContainsString('Trabajador De Ejemplo Dos', $html);
        $this->assertStringContainsString('Bodeguero Uno', $html);
        $this->assertStringNotContainsString('00000002', $html);
    }

    public function test_the_list_of_dispatches_offers_the_pdf_of_each_dispatch_with_one_page_per_kit(): void
    {
        $dispatch = $this->dispatchOf($this->receiver, [$this->builtKit('KIT-000046'), $this->builtKit('KIT-000047')]);

        Livewire::test(KitDispatchesTable::class)
            ->assertSeeHtml(route('intranet.kit-dispatches.pdf', $dispatch));

        $this->get(route('intranet.kit-dispatches.pdf', $dispatch))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', sprintf('inline; filename="Despacho %s.pdf"', $dispatch->number()));

        $html = view('pdf.kits.dispatch', [
            'dispatch' => $dispatch->load(['receiver', 'creator']),
            'pages' => $dispatch->kits->map(fn (Kit $kit): array => app(BuildKitPdfPage::class)->handle($kit))->all(),
            'generatedBy' => 'Bodeguero Uno',
        ])->render();

        $this->assertSame(1, substr_count($html, 'class="salto"'));
        $this->assertStringContainsString('KIT-000046', $html);
        $this->assertStringContainsString('KIT-000047', $html);
    }

    public function test_the_worker_search_of_the_window_only_offers_active_workers_by_name_or_document(): void
    {
        Worker::factory()->create(['full_name' => 'Trabajador Desactivado', 'deleted_at' => now()]);

        $this->postJson(route('intranet.workers.options'), ['search' => 'trabajador'])
            ->assertOk()
            ->assertExactJson([['id' => $this->receiver->id, 'name' => 'Trabajador De Ejemplo Dos', 'description' => 'DNI 00000002']]);

        $this->postJson(route('intranet.workers.options'), ['search' => '00000002'])
            ->assertJsonPath('0.id', $this->receiver->id);
    }

    /**
     * Create a kit of the kit type with the given number of devices in it, in its status.
     */
    private function kit(string $pouchCode, ?KitType $kitType = null, int $devices = 2): Kit
    {
        $kit = app(CreateKit::class)->handle([
            'pouchCode' => $pouchCode,
            'kitTypeId' => ($kitType ?? $this->reeferKit)->id,
            'shippingLineId' => $this->maersk->id,
        ], $this->user);

        for ($added = 0; $added < $devices; $added++) {
            $kit->devices()->attach($this->device()->id);
        }

        $kit->syncStatus();

        return $kit->refresh();
    }

    /**
     * Create a complete kit, Armado.
     */
    private function builtKit(string $pouchCode, ?KitType $kitType = null): Kit
    {
        $kitType ??= $this->reeferKit;

        return $this->kit($pouchCode, $kitType, (int) $kitType->securityDeviceTypes()->sum('quantity'));
    }

    /**
     * Dispatch the kits to the worker, as the window does.
     *
     * @param  list<Kit>  $kits
     */
    private function dispatchOf(Worker $receiver, array $kits): KitDispatch
    {
        $dispatch = KitDispatch::factory()->create(['receiver_id' => $receiver->id, 'created_by' => $this->user->id]);
        $dispatch->kits()->attach(array_map(fn (Kit $kit): int => $kit->id, $kits));
        Kit::query()->whereKey(array_map(fn (Kit $kit): int => $kit->id, $kits))->update(['status' => KitStatus::Dispatched]);

        return $dispatch;
    }

    /**
     * Create a bottle seal of Maersk, already in a kit.
     */
    private function device(): SecurityDevice
    {
        $announcement = SecurityDeviceAnnouncement::factory()->for($this->maersk)->for($this->bottleSeal)->create();
        $lot = SecurityDeviceLot::factory()->for($announcement, 'announcement')->create();

        return SecurityDevice::factory()->for($lot, 'lot')->create([
            'serial_number' => sprintf('SLL%07d', $this->nextSerial++),
            'status' => SecurityDeviceStatus::InKit,
        ]);
    }
}
