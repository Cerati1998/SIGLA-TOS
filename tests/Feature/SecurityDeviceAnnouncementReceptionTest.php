<?php

namespace Tests\Feature;

use App\Enums\SecurityDeviceStatus;
use App\Livewire\SecurityDeviceAnnouncements\CreateForm;
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

class SecurityDeviceAnnouncementReceptionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ShippingLine $shippingLine;

    private Provider $provider;

    private SecurityDeviceType $seal;

    private SecurityDeviceType $tape;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->shippingLine = ShippingLine::factory()->create(['code' => 'MSK', 'name' => 'Maersk']);
        $this->provider = Provider::factory()->create(['business_name' => 'Proveedor de Precintos S.A.C.']);
        $this->seal = SecurityDeviceType::factory()->create(['name' => 'Precinto ISO']);
        $this->tape = SecurityDeviceType::factory()->create(['name' => 'Cinta de inspección']);

        $this->actingAs($this->user);
        $this->app->setLocale('es');
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.security-device-announcements.create'))->assertRedirect(route('login'));
    }

    public function test_the_warehouse_menu_leads_to_the_form_that_offers_only_active_options(): void
    {
        SecurityDeviceType::factory()->trashed()->create(['name' => 'Cinta Retirada']);
        ShippingLine::factory()->trashed()->create(['name' => 'Naviera Cerrada']);

        $this->get(route('intranet.security-device-announcements.create'))
            ->assertOk()
            ->assertSeeInOrder(['Inicio', 'Bodega', 'Ingreso de lotes', 'Administración'])
            ->assertSee('<title>Nuevo anuncio de ingreso de lotes | ', false)
            ->assertSeeInOrder(['Anuncio', 'Agregar lote', 'Lotes del anuncio'])
            ->assertSee('securityDeviceLotPreview')
            ->assertSeeText('MSK · Maersk')
            ->assertSeeText('Proveedor de Precintos S.A.C.')
            ->assertSeeText('Precinto ISO')
            ->assertSeeInOrder(['Correlativo inicial', 'Cantidad', 'Calcula el correlativo final.', 'Correlativo final'])
            ->assertSeeText('Aún no agregas lotes.')
            ->assertDontSeeText('Cinta Retirada')
            ->assertDontSeeText('Naviera Cerrada');
    }

    public function test_saving_an_announcement_saves_its_lots_with_every_serial_number_as_an_inactive_device(): void
    {
        $this->fillHeader()
            ->set('form.lots', [
                $this->lot($this->seal, ['seriesPrefix' => ' gsw ', 'notes' => 'Llegaron en dos cajas.']),
                $this->lot($this->tape, ['seriesPrefix' => 'CTA', 'seriesStart' => '1000', 'seriesEnd' => '1499']),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $announcement = SecurityDeviceAnnouncement::query()->sole();
        $this->assertSame('AN-'.str_pad((string) $announcement->id, 6, '0', STR_PAD_LEFT), $announcement->number());
        $this->assertSame('Anuncio guardado', session('toast.title'));
        $this->assertSame("Anuncio de ingreso de lotes {$announcement->number()}: 2 lotes nuevos con 700 dispositivos.", session('toast.text'));
        $this->assertTrue($announcement->shippingLine->is($this->shippingLine));
        $this->assertTrue($announcement->provider->is($this->provider));
        $this->assertTrue($announcement->creator->is($this->user));
        $this->assertSame('2026-11-09', $announcement->received_at->toDateString());
        $this->assertSame('T001-000123', $announcement->dispatch_number);

        [$sealLot, $tapeLot] = $announcement->lots->all();
        $this->assertTrue($sealLot->securityDeviceType->is($this->seal));
        $this->assertSame(['GSW', 200, 'Llegaron en dos cajas.'], [$sealLot->series_prefix, $sealLot->quantity, $sealLot->notes]);
        $this->assertSame(['GSW0000200', 'GSW0000399'], [$sealLot->devices()->orderBy('id')->value('serial_number'), $sealLot->devices()->orderByDesc('id')->value('serial_number')]);
        $this->assertTrue($tapeLot->securityDeviceType->is($this->tape));
        $this->assertSame(500, $tapeLot->devices()->count());
        $this->assertSame(700, SecurityDevice::query()->where('status', SecurityDeviceStatus::Inactive)->count());
    }

    public function test_a_correlative_longer_than_the_minimum_of_digits_stays_whole_and_the_suffix_closes_it(): void
    {
        $this->fillHeader()
            ->set('form.lots', [$this->lot($this->seal, ['seriesPrefix' => 'GWF', 'minDigits' => '3', 'seriesStart' => '998', 'seriesEnd' => '1001', 'seriesSuffix' => ' a '])])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['GWF998A', 'GWF999A', 'GWF1000A', 'GWF1001A'], SecurityDevice::query()->orderBy('id')->pluck('serial_number')->all());
    }

    public function test_an_announcement_needs_its_header_and_at_least_one_lot(): void
    {
        Livewire::test(CreateForm::class)
            ->set('form.receivedAt', '')
            ->call('save')
            ->assertHasErrors([
                'form.shippingLineId' => 'required',
                'form.providerId' => 'required',
                'form.receivedAt' => 'required',
                'form.dispatchNumber' => 'required',
            ]);

        $this->fillHeader()
            ->call('save')
            ->assertHasErrors(['form.lots'])
            ->assertSee('Agrega al menos un lote para guardar el anuncio.');

        $this->assertDatabaseEmpty('security_device_announcements');
    }

    public function test_each_lot_is_checked_again_on_save(): void
    {
        $this->tape->delete();

        $this->fillHeader()
            ->set('form.lots', [$this->lot($this->seal), $this->lot($this->tape, ['seriesPrefix' => 'CTA'])])
            ->call('save')
            ->assertSee('Lote 2: Elige un tipo de dispositivo activo.');

        $this->fillHeader()
            ->set('form.lots', [$this->lot($this->seal, ['seriesSuffix' => '7'])])
            ->call('save')
            ->assertSee('Lote 1: El final de serie solo lleva letras.');

        $this->fillHeader()
            ->set('form.lots', [$this->lot($this->seal, ['seriesStart' => '399', 'seriesEnd' => '200'])])
            ->call('save')
            ->assertSee('Lote 1: El correlativo inicial no puede ser mayor que el final.');

        $this->assertDatabaseEmpty('security_device_announcements');
    }

    public function test_two_lots_of_the_announcement_cannot_bring_the_same_serial_number(): void
    {
        $this->fillHeader()
            ->set('form.lots', [$this->lot($this->seal), $this->lot($this->tape, ['seriesStart' => '350', 'seriesEnd' => '449'])])
            ->call('save')
            ->assertSee('El lote 2 choca con el lote 1 del anuncio: los dos traen la serie GSW0000350.')
            ->assertSet('form.collidingLots', [0, 1]);

        $this->assertDatabaseEmpty('security_device_lots');
    }

    public function test_an_announcement_brings_at_most_ten_thousand_devices_adding_up_its_lots(): void
    {
        $this->fillHeader()
            ->set('form.lots', [
                $this->lot($this->seal, ['seriesStart' => '1', 'seriesEnd' => '6000']),
                $this->lot($this->tape, ['seriesPrefix' => 'CTA', 'seriesStart' => '1', 'seriesEnd' => '4001']),
            ])
            ->call('save')
            ->assertSee('Un anuncio trae como máximo 10 000 series y estos lotes suman 10 001.');

        $this->assertDatabaseEmpty('security_devices');

        $this->fillHeader()
            ->set('form.lots', [
                $this->lot($this->seal, ['seriesStart' => '1', 'seriesEnd' => '6000']),
                $this->lot($this->tape, ['seriesPrefix' => 'CTA', 'seriesStart' => '1', 'seriesEnd' => '4000']),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(10000, SecurityDevice::query()->count());
    }

    public function test_nothing_is_saved_when_some_serial_numbers_already_exist(): void
    {
        $earlierLot = SecurityDeviceLot::factory()->create();
        SecurityDevice::factory()->for($earlierLot, 'lot')->create(['serial_number' => 'CTA0000201']);
        SecurityDevice::factory()->for($earlierLot, 'lot')->create(['serial_number' => 'CTA0000250']);

        $this->fillHeader()
            ->set('form.lots', [$this->lot($this->seal), $this->lot($this->tape, ['seriesPrefix' => 'CTA'])])
            ->call('save')
            ->assertHasErrors('form.lots')
            ->assertSee('No se guardó el anuncio.')
            ->assertSee('Lote 2 · Cinta de inspección: 2 series ya existen en el sistema: CTA0000201 y CTA0000250. Revisa el rango.')
            ->assertSet('form.collidingLots', [1]);

        $this->assertSame(1, SecurityDeviceLot::query()->count());
        $this->assertSame(2, SecurityDevice::query()->count());
        $this->assertDatabaseCount('security_device_announcements', 1);
    }

    public function test_lots_are_added_later_to_an_announcement_already_saved(): void
    {
        $announcement = SecurityDeviceAnnouncement::factory()->for($this->shippingLine)->create();
        SecurityDeviceLot::factory()->for($announcement, 'announcement')->for($this->seal)->create(['series_start' => 1, 'series_end' => 9000, 'quantity' => 9000]);

        $this->get(route('intranet.security-device-announcements.lots.create', $announcement))
            ->assertOk()
            ->assertSee('<title>Agregar lotes a '.$announcement->number().' | ', false)
            ->assertSeeText('Guardado')
            ->assertSeeText('Guardar lotes nuevos');

        Livewire::test(CreateForm::class, ['announcement' => $announcement])
            ->set('form.lots', [$this->lot($this->tape, ['seriesPrefix' => 'CTA', 'seriesStart' => '1', 'seriesEnd' => '1001'])])
            ->call('save')
            ->assertSee('Un anuncio trae como máximo 10 000 series y estos lotes suman 10 001.')
            ->set('form.lots', [$this->lot($this->tape, ['seriesPrefix' => 'CTA', 'seriesStart' => '1', 'seriesEnd' => '1000'])])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.security-device-announcements.show', $announcement));

        $this->assertSame('Lotes agregados', session('toast.title'));
        $this->assertSame(2, $announcement->lots()->count());
        $this->assertSame(1000, $announcement->lots()->reorder('id', 'desc')->firstOrFail()->devices()->count());
    }

    /**
     * Open the form with a valid header.
     */
    private function fillHeader(): Testable
    {
        return Livewire::test(CreateForm::class)
            ->set('form.shippingLineId', (string) $this->shippingLine->id)
            ->set('form.providerId', (string) $this->provider->id)
            ->set('form.receivedAt', '2026-11-09')
            ->set('form.dispatchNumber', 'T001-000123');
    }

    /**
     * Build a lot as the browser lists it: by default, the serial numbers GSW0000200 to GSW0000399.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function lot(SecurityDeviceType $type, array $overrides = []): array
    {
        return [
            'securityDeviceTypeId' => (string) $type->id,
            'seriesPrefix' => 'GSW',
            'minDigits' => '7',
            'seriesStart' => '200',
            'seriesEnd' => '399',
            'seriesSuffix' => '',
            'notes' => '',
            ...$overrides,
        ];
    }
}
