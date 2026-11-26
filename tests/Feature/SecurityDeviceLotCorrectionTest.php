<?php

namespace Tests\Feature;

use App\Actions\SecurityDevices\ReceiveSecurityDeviceLots;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\SecurityDeviceAnnouncements\EditForm as AnnouncementEditForm;
use App\Livewire\SecurityDeviceLots\EditForm;
use App\Livewire\Tables\SecurityDeviceLotsTable;
use App\Models\Provider;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityDeviceLotCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private SecurityDeviceLot $lot;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->actingAs($user);
        $this->app->setLocale('es');

        $this->lot = $this->receiveLot('GSW', 200, 399, $user);
    }

    public function test_guests_cannot_open_the_correction(): void
    {
        auth()->logout();

        $this->get(route('intranet.security-device-lots.edit', $this->lot))->assertRedirect(route('login'));
    }

    public function test_the_detail_of_the_announcement_leads_to_the_corrections(): void
    {
        $announcement = $this->lot->announcement;

        Livewire::test(SecurityDeviceLotsTable::class, ['announcementId' => $announcement->id])
            ->assertSee(route('intranet.security-device-lots.show', $this->lot))
            ->assertSee(route('intranet.security-device-lots.edit', $this->lot));

        $this->get(route('intranet.security-device-announcements.show', $announcement))
            ->assertOk()
            ->assertSee(route('intranet.security-device-announcements.edit', $announcement))
            ->assertSee(route('intranet.security-device-announcements.lots.create', $announcement));

        $this->get(route('intranet.security-device-lots.edit', $this->lot))
            ->assertOk()
            ->assertSee('<title>Editar lote GSW 0000200 – 0000399 | ', false)
            ->assertSeeInOrder(['Anuncios de ingreso de lotes', $announcement->number(), 'GSW 0000200 – 0000399', 'Editar'])
            ->assertSeeText('Guardar cambios')
            ->assertDontSeeText('Número de guía')
            ->assertDontSeeText('La serie no se puede cambiar');

        $this->editForm()
            ->assertSet('form.seriesPrefix', 'GSW')
            ->assertSet('form.minDigits', '7')
            ->assertSet('form.seriesStart', '200')
            ->assertSet('form.seriesEnd', '399')
            ->assertSet('form.seriesLocked', false);
    }

    public function test_correcting_the_header_of_the_announcement_keeps_its_lots(): void
    {
        $announcement = $this->lot->announcement;
        $provider = Provider::factory()->create();
        $deviceIds = $this->lot->devices()->orderBy('id')->pluck('id');

        $this->get(route('intranet.security-device-announcements.edit', $announcement))
            ->assertOk()
            ->assertSee('<title>Editar anuncio de ingreso de lotes '.$announcement->number().' | ', false)
            ->assertSeeText('Lo que cambies aquí vale para el lote del anuncio.');

        Livewire::test(AnnouncementEditForm::class, ['announcement' => $announcement])
            ->assertSet('form.dispatchNumber', 'T001-000123')
            ->assertSet('form.receivedAt', '2026-11-09')
            ->set('form.providerId', (string) $provider->id)
            ->set('form.dispatchNumber', ' T001-000999 ')
            ->set('form.receivedAt', '2026-11-08')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.security-device-announcements.show', $announcement));

        $this->assertSame('Anuncio corregido', session('toast.title'));

        $announcement->refresh();
        $this->assertTrue($announcement->provider->is($provider));
        $this->assertSame('T001-000999', $announcement->dispatch_number);
        $this->assertSame('2026-11-08', $announcement->received_at->toDateString());
        $this->assertEquals($deviceIds, $this->lot->devices()->orderBy('id')->pluck('id'));
    }

    public function test_correcting_the_notes_of_a_lot_keeps_its_devices(): void
    {
        $deviceIds = $this->lot->devices()->orderBy('id')->pluck('id');

        $this->editForm()
            ->set('form.notes', 'Guía corregida.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.security-device-lots.show', $this->lot));

        $this->assertSame('Lote corregido', session('toast.title'));
        $this->assertSame('Se guardaron los cambios del lote.', session('toast.text'));
        $this->assertSame('Guía corregida.', $this->lot->refresh()->notes);
        $this->assertEquals($deviceIds, $this->lot->devices()->orderBy('id')->pluck('id'));
    }

    public function test_correcting_the_series_builds_every_device_again(): void
    {
        $this->editForm()
            ->set('form.seriesPrefix', 'gwf')
            ->set('form.minDigits', '5')
            ->set('form.seriesStart', '100')
            ->set('form.seriesEnd', '149')
            ->set('form.seriesSuffix', 'a')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.security-device-lots.show', $this->lot));

        $this->assertSame('Se rehicieron sus 50 dispositivos, todos inactivos.', session('toast.text'));

        $this->lot->refresh();
        $serialNumbers = $this->lot->devices()->orderBy('id')->pluck('serial_number');

        $this->assertSame(50, $this->lot->quantity);
        $this->assertSame('GWF 00100A – 00149A', $this->lot->name());
        $this->assertCount(50, $serialNumbers);
        $this->assertSame('GWF00100A', $serialNumbers->first());
        $this->assertSame('GWF00149A', $serialNumbers->last());
        $this->assertSame(50, $this->lot->devices()->where('status', SecurityDeviceStatus::Inactive)->count());
        $this->assertSame(50, SecurityDevice::query()->count());
    }

    public function test_the_series_is_locked_once_a_device_changed_status(): void
    {
        $this->lot->devices()->where('serial_number', 'GSW0000200')->update(['status' => SecurityDeviceStatus::InKit]);
        $this->lot->devices()->whereIn('serial_number', ['GSW0000201', 'GSW0000202'])->update(['status' => SecurityDeviceStatus::Used]);

        $this->get(route('intranet.security-device-lots.edit', $this->lot))
            ->assertOk()
            ->assertSeeText('La serie no se puede cambiar porque 3 dispositivos de este lote ya cambiaron de estado (1 en un kit y 2 usados).')
            ->assertSee('x-model="quantity"', false)
            ->assertDontSeeText('Calcula el correlativo final.');

        $this->editForm()
            ->assertSet('form.seriesLocked', true)
            ->set('form.seriesStart', '1')
            ->set('form.notes', 'Revisado.')
            ->call('save')
            ->assertHasNoErrors();

        $this->lot->refresh();
        $this->assertSame(200, $this->lot->series_start);
        $this->assertSame('Revisado.', $this->lot->notes);
        $this->assertSame(200, $this->lot->devices()->count());
    }

    public function test_nothing_is_saved_when_a_device_changes_status_while_the_series_is_being_corrected(): void
    {
        $form = $this->editForm()
            ->set('form.seriesStart', '300')
            ->set('form.seriesEnd', '499')
            ->set('form.notes', 'Revisado.');

        $this->lot->devices()->where('serial_number', 'GSW0000300')->update(['status' => SecurityDeviceStatus::InKit]);

        $form->call('save')
            ->assertHasErrors(['form.serialNumbers' => 'La serie ya no se puede cambiar porque algunos dispositivos del lote cambiaron de estado. Revisa los demás datos y guarda de nuevo.'])
            ->assertSee('No se guardaron los cambios.')
            ->assertSet('form.seriesLocked', true)
            ->assertSet('form.seriesStart', '200');

        $this->lot->refresh();
        $this->assertNull($this->lot->notes);
        $this->assertSame(200, $this->lot->series_start);
    }

    public function test_nothing_is_saved_when_a_new_serial_number_exists_in_another_lot(): void
    {
        $this->receiveLot('GSW', 450, 460);

        $this->editForm()
            ->set('form.seriesStart', '300')
            ->set('form.seriesEnd', '451')
            ->call('save')
            ->assertHasErrors('form.serialNumbers')
            ->assertSee('2 series ya existen en el sistema: GSW0000450 y GSW0000451. Revisa el rango.');

        $this->assertSame(200, $this->lot->refresh()->devices()->count());
        $this->assertSame('GSW0000200', $this->lot->devices()->orderBy('id')->value('serial_number'));

        $this->editForm()
            ->set('form.seriesStart', '300')
            ->set('form.seriesEnd', '449')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(150, $this->lot->refresh()->quantity);
    }

    public function test_the_range_follows_the_rules_of_the_reception(): void
    {
        $this->editForm()
            ->set('form.seriesStart', '399')
            ->set('form.seriesEnd', '200')
            ->call('save')
            ->assertHasErrors(['form.seriesRange' => 'El correlativo inicial no puede ser mayor que el final.']);

        $this->editForm()
            ->set('form.seriesStart', '1')
            ->set('form.seriesEnd', '12000')
            ->call('save')
            ->assertHasErrors(['form.seriesRange' => 'Un lote puede tener como máximo 10 000 dispositivos. Este rango tiene 12 000.']);

        $this->assertSame(200, $this->lot->refresh()->devices()->count());
    }

    public function test_the_current_origin_stays_valid_after_it_was_deactivated_but_no_other_inactive_one(): void
    {
        $announcement = $this->lot->announcement;
        $announcement->provider->delete();
        $otherInactive = Provider::factory()->trashed()->create(['business_name' => 'Proveedor Cerrado']);

        Livewire::test(AnnouncementEditForm::class, ['announcement' => $announcement->fresh()])
            ->assertSeeText($announcement->provider->business_name)
            ->assertDontSeeText('Proveedor Cerrado')
            ->set('form.dispatchNumber', 'T001-000999')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(AnnouncementEditForm::class, ['announcement' => $announcement->fresh()])
            ->set('form.providerId', (string) $otherInactive->id)
            ->call('save')
            ->assertHasErrors(['form.providerId' => 'exists']);
    }

    public function test_a_lot_is_removed_only_while_its_devices_are_unused_and_the_announcement_keeps_another_one(): void
    {
        $announcement = $this->lot->announcement;
        $table = fn (): Testable => Livewire::test(SecurityDeviceLotsTable::class, ['announcementId' => $announcement->id]);

        $table()->assertDontSeeHtml('title="Quitar lote"');
        $table()->call('remove', $this->lot->id)->assertDispatched('toast', icon: 'warning');
        $this->assertSame(200, SecurityDevice::query()->count());

        $second = $this->receiveLot('CTA', 1, 10, announcement: $announcement);
        $table()->assertSeeHtml('title="Quitar lote"');

        $second->devices()->where('serial_number', 'CTA0000001')->update(['status' => SecurityDeviceStatus::Discarded]);
        $table()->call('remove', $second->id)->assertDispatched('toast', icon: 'warning');

        $second->devices()->update(['status' => SecurityDeviceStatus::Inactive]);
        $table()->call('remove', $second->id)->assertRedirect(route('intranet.security-device-announcements.show', $announcement));

        $this->assertSame('Lote quitado', session('toast.title'));
        $this->assertModelMissing($second);
        $this->assertSame(200, SecurityDevice::query()->count());
    }

    /**
     * Receive a lot of inactive devices, as H37 does: in a new announcement, or in the given one.
     */
    private function receiveLot(string $prefix, int $start, int $end, ?User $user = null, ?SecurityDeviceAnnouncement $announcement = null): SecurityDeviceLot
    {
        $announcement ??= new SecurityDeviceAnnouncement([
            'shipping_line_id' => ShippingLine::factory()->create()->id,
            'provider_id' => Provider::factory()->create()->id,
            'received_at' => '2026-11-09',
            'dispatch_number' => 'T001-000123',
        ]);
        $type = SecurityDeviceType::factory()->create();
        $lot = (new SecurityDeviceLot([
            'security_device_type_id' => $type->id,
            'series_prefix' => $prefix,
            'min_digits' => 7,
            'series_start' => $start,
            'series_end' => $end,
        ]))->setRelation('securityDeviceType', $type);

        $this->assertTrue(app(ReceiveSecurityDeviceLots::class)->handle($announcement, [$lot], $user ?? User::factory()->create()));

        return $lot;
    }

    /**
     * Open the correction of the lot.
     */
    private function editForm(): Testable
    {
        return Livewire::test(EditForm::class, ['lot' => $this->lot->fresh()]);
    }
}
