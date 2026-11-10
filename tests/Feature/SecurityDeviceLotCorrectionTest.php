<?php

namespace Tests\Feature;

use App\Actions\SecurityDevices\ReceiveSecurityDeviceLot;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\SecurityDeviceLots\EditForm;
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

    public function test_the_detail_leads_to_the_form_filled_with_the_lot(): void
    {
        $this->get(route('intranet.security-device-lots.show', $this->lot))
            ->assertOk()
            ->assertSee(route('intranet.security-device-lots.edit', $this->lot))
            ->assertSeeText('Editar');

        $this->get(route('intranet.security-device-lots.edit', $this->lot))
            ->assertOk()
            ->assertSee('<title>Editar lote GSW 0000200 – 0000399 | ', false)
            ->assertSeeInOrder(['Lotes de dispositivos', 'GSW 0000200 – 0000399', 'Editar'])
            ->assertSeeText('Guardar cambios')
            ->assertDontSeeText('La serie no se puede cambiar');

        $this->editForm()
            ->assertSet('form.dispatchNumber', 'T001-000123')
            ->assertSet('form.receivedAt', '2026-11-09')
            ->assertSet('form.seriesPrefix', 'GSW')
            ->assertSet('form.minDigits', '7')
            ->assertSet('form.seriesStart', '200')
            ->assertSet('form.seriesEnd', '399')
            ->assertSet('form.seriesLocked', false);
    }

    public function test_correcting_the_origin_and_the_reception_keeps_the_devices(): void
    {
        $provider = Provider::factory()->create();
        $deviceIds = $this->lot->devices()->orderBy('id')->pluck('id');

        $this->editForm()
            ->set('form.providerId', (string) $provider->id)
            ->set('form.dispatchNumber', 'T001-000999')
            ->set('form.receivedAt', '2026-11-08')
            ->set('form.notes', 'Guía corregida.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.security-device-lots.show', $this->lot));

        $this->assertSame('Lote corregido', session('toast.title'));
        $this->assertSame('Se guardaron los cambios del lote.', session('toast.text'));

        $this->lot->refresh();
        $this->assertTrue($this->lot->announcement->provider->is($provider));
        $this->assertSame('T001-000999', $this->lot->dispatch_number);
        $this->assertSame('2026-11-08', $this->lot->received_at->toDateString());
        $this->assertSame('Guía corregida.', $this->lot->notes);
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

        $this->assertSame('Se rehicieron sus 50 dispositivos, todos disponibles.', session('toast.text'));

        $this->lot->refresh();
        $serialNumbers = $this->lot->devices()->orderBy('id')->pluck('serial_number');

        $this->assertSame(50, $this->lot->quantity);
        $this->assertSame('GWF 00100A – 00149A', $this->lot->announcement->lotName());
        $this->assertCount(50, $serialNumbers);
        $this->assertSame('GWF00100A', $serialNumbers->first());
        $this->assertSame('GWF00149A', $serialNumbers->last());
        $this->assertSame(50, $this->lot->devices()->where('status', SecurityDeviceStatus::Available)->count());
        $this->assertSame(50, SecurityDevice::query()->count());
    }

    public function test_the_series_is_locked_once_a_device_changed_status(): void
    {
        $this->lot->devices()->where('serial_number', 'GSW0000200')->update(['status' => SecurityDeviceStatus::InKit]);
        $this->lot->devices()->whereIn('serial_number', ['GSW0000201', 'GSW0000202'])->update(['status' => SecurityDeviceStatus::Used]);

        $this->get(route('intranet.security-device-lots.edit', $this->lot))
            ->assertOk()
            ->assertSeeText('La serie no se puede cambiar porque 3 dispositivos de este lote ya cambiaron de estado (1 en un kit y 2 usados).');

        $this->editForm()
            ->assertSet('form.seriesLocked', true)
            ->set('form.seriesStart', '1')
            ->set('form.dispatchNumber', 'T001-000999')
            ->call('save')
            ->assertHasNoErrors();

        $this->lot->refresh();
        $this->assertSame(200, $this->lot->announcement->series_start);
        $this->assertSame('T001-000999', $this->lot->dispatch_number);
        $this->assertSame(200, $this->lot->devices()->count());
    }

    public function test_nothing_is_saved_when_a_device_changes_status_while_the_series_is_being_corrected(): void
    {
        $form = $this->editForm()
            ->set('form.seriesStart', '300')
            ->set('form.seriesEnd', '499')
            ->set('form.dispatchNumber', 'T001-000999');

        $this->lot->devices()->where('serial_number', 'GSW0000300')->update(['status' => SecurityDeviceStatus::InKit]);

        $form->call('save')
            ->assertHasErrors(['form.serialNumbers' => 'La serie ya no se puede cambiar porque algunos dispositivos del lote cambiaron de estado. Revisa los demás datos y guarda de nuevo.'])
            ->assertSee('No se guardaron los cambios.')
            ->assertSet('form.seriesLocked', true)
            ->assertSet('form.seriesStart', '200');

        $this->lot->refresh();
        $this->assertSame('T001-000123', $this->lot->dispatch_number);
        $this->assertSame(200, $this->lot->announcement->series_start);
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
        $this->lot->announcement->provider->delete();
        $otherInactive = Provider::factory()->trashed()->create(['business_name' => 'Proveedor Cerrado']);

        $this->editForm()
            ->assertSeeText($this->lot->announcement->provider->business_name)
            ->assertDontSeeText('Proveedor Cerrado')
            ->set('form.dispatchNumber', 'T001-000999')
            ->call('save')
            ->assertHasNoErrors();

        $this->editForm()
            ->set('form.providerId', (string) $otherInactive->id)
            ->call('save')
            ->assertHasErrors(['form.providerId' => 'exists']);
    }

    /**
     * Receive a lot of available devices, as H07 does.
     */
    private function receiveLot(string $prefix, int $start, int $end, ?User $user = null): SecurityDeviceLot
    {
        $announcement = new SecurityDeviceAnnouncement([
            'shipping_line_id' => ShippingLine::factory()->create()->id,
            'provider_id' => Provider::factory()->create()->id,
            'security_device_type_id' => SecurityDeviceType::factory()->create()->id,
            'series_prefix' => $prefix,
            'min_digits' => 7,
            'series_start' => $start,
            'series_end' => $end,
            'series_suffix' => null,
        ]);

        $lot = app(ReceiveSecurityDeviceLot::class)->handle($announcement, [
            'received_at' => '2026-11-09',
            'dispatch_number' => 'T001-000123',
            'notes' => null,
        ], $user ?? User::factory()->create());

        $this->assertNotNull($lot);

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
