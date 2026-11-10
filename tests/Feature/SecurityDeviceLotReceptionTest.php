<?php

namespace Tests\Feature;

use App\Enums\SecurityDeviceStatus;
use App\Livewire\SecurityDeviceLots\CreateForm;
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

class SecurityDeviceLotReceptionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ShippingLine $shippingLine;

    private Provider $provider;

    private SecurityDeviceType $securityDeviceType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->shippingLine = ShippingLine::factory()->create(['code' => 'MSK', 'name' => 'Maersk']);
        $this->provider = Provider::factory()->create(['business_name' => 'Proveedor de Precintos S.A.C.']);
        $this->securityDeviceType = SecurityDeviceType::factory()->create(['name' => 'Precinto ISO']);

        $this->actingAs($this->user);
        $this->app->setLocale('es');
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.security-device-lots.create'))->assertRedirect(route('login'));
    }

    public function test_the_warehouse_menu_leads_to_the_form_that_offers_only_active_options(): void
    {
        SecurityDeviceType::factory()->trashed()->create(['name' => 'Cinta Retirada']);
        ShippingLine::factory()->trashed()->create(['name' => 'Naviera Cerrada']);

        $this->get(route('intranet.security-device-lots.create'))
            ->assertOk()
            ->assertSeeInOrder(['Inicio', 'Bodega', 'Lotes de dispositivos', 'Administración'])
            ->assertSee('<title>Ingresar lote | ', false)
            ->assertSee('securityDeviceLotPreview')
            ->assertSeeText('MSK · Maersk')
            ->assertSeeText('Proveedor de Precintos S.A.C.')
            ->assertSeeText('Precinto ISO')
            ->assertDontSeeText('Cinta Retirada')
            ->assertDontSeeText('Naviera Cerrada');
    }

    public function test_saving_a_lot_generates_every_serial_number_as_an_available_device(): void
    {
        $this->fillForm()
            ->set('form.seriesPrefix', ' gsw ')
            ->set('form.notes', 'Llegaron en dos cajas.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', title: 'Lote guardado', text: 'Se registraron 200 dispositivos disponibles.')
            ->assertSet('form.seriesStart', '')
            ->assertSet('form.receivedAt', now('America/Lima')->toDateString());

        $lot = SecurityDeviceLot::query()->sole();

        $this->assertSame(200, $lot->quantity);
        $this->assertSame('2026-11-09', $lot->received_at->toDateString());
        $this->assertSame('T001-000123', $lot->dispatch_number);
        $this->assertSame('Llegaron en dos cajas.', $lot->notes);
        $this->assertTrue($lot->creator->is($this->user));
        $this->assertNotNull($lot->created_at);

        $this->assertSame('GSW', $lot->announcement->series_prefix);
        $this->assertTrue($lot->announcement->provider->is($this->provider));
        $this->assertTrue($lot->announcement->shippingLine->is($this->shippingLine));
        $this->assertTrue($lot->announcement->securityDeviceType->is($this->securityDeviceType));

        $serialNumbers = $lot->devices()->orderBy('id')->pluck('serial_number');
        $this->assertCount(200, $serialNumbers);
        $this->assertSame('GSW0000200', $serialNumbers->first());
        $this->assertSame('GSW0000399', $serialNumbers->last());
        $this->assertSame(200, $lot->devices()->where('status', SecurityDeviceStatus::Available)->count());
    }

    public function test_a_correlative_longer_than_the_minimum_of_digits_stays_whole(): void
    {
        $this->fillForm(minDigits: '3', seriesStart: '998', seriesEnd: '1001')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(
            ['GSW998', 'GSW999', 'GSW1000', 'GSW1001'],
            SecurityDevice::query()->orderBy('id')->pluck('serial_number')->all(),
        );
    }

    public function test_the_optional_series_suffix_closes_every_serial_number(): void
    {
        $this->fillForm(minDigits: '5', seriesStart: '100', seriesEnd: '200')
            ->set('form.seriesPrefix', 'GWF')
            ->set('form.seriesSuffix', ' a ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', text: 'Se registraron 101 dispositivos disponibles.');

        $serialNumbers = SecurityDevice::query()->orderBy('id')->pluck('serial_number');

        $this->assertSame('GWF00100A', $serialNumbers->first());
        $this->assertSame('GWF00200A', $serialNumbers->last());
        $this->assertSame('A', SecurityDeviceLot::query()->sole()->announcement->series_suffix);
    }

    public function test_the_series_suffix_only_takes_letters(): void
    {
        $this->fillForm()
            ->set('form.seriesSuffix', '7')
            ->call('save')
            ->assertHasErrors(['form.seriesSuffix' => 'El final de serie solo lleva letras.']);

        $this->assertDatabaseEmpty('security_device_lots');
    }

    public function test_a_deactivated_device_type_cannot_be_chosen(): void
    {
        $this->securityDeviceType->delete();

        $this->fillForm()
            ->call('save')
            ->assertHasErrors(['form.securityDeviceTypeId' => 'exists']);

        $this->assertDatabaseEmpty('security_device_lots');
    }

    public function test_the_first_correlative_cannot_be_after_the_last_one(): void
    {
        $this->fillForm(seriesStart: '399', seriesEnd: '200')
            ->call('save')
            ->assertHasErrors(['form.seriesRange' => 'El correlativo inicial no puede ser mayor que el final.']);

        $this->assertDatabaseEmpty('security_device_announcements');
    }

    public function test_a_lot_brings_at_most_ten_thousand_devices(): void
    {
        $this->fillForm(seriesStart: '1', seriesEnd: '12000')
            ->call('save')
            ->assertHasErrors(['form.seriesRange' => 'Un lote puede tener como máximo 10 000 dispositivos. Este rango tiene 12 000.']);

        $this->assertDatabaseEmpty('security_devices');

        $this->fillForm(seriesStart: '1', seriesEnd: '10000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', text: 'Se registraron 10 000 dispositivos disponibles.');

        $this->assertSame(10000, SecurityDevice::query()->count());
    }

    public function test_nothing_is_saved_when_some_serial_numbers_already_exist(): void
    {
        $earlierLot = SecurityDeviceLot::factory()->create();
        SecurityDevice::factory()->for($earlierLot, 'lot')->create(['serial_number' => 'GSW0000201']);
        SecurityDevice::factory()->for($earlierLot, 'lot')->create(['serial_number' => 'GSW0000250']);

        $this->fillForm()
            ->call('save')
            ->assertHasErrors('form.serialNumbers')
            ->assertSee('No se guardó el lote.')
            ->assertSee('2 series ya existen en el sistema: GSW0000201 y GSW0000250. Revisa el rango.')
            ->assertSet('form.seriesStart', '200');

        $this->assertSame(1, SecurityDeviceLot::query()->count());
        $this->assertSame(2, SecurityDevice::query()->count());
        $this->assertDatabaseCount('security_device_announcements', 1);
    }

    public function test_many_taken_serial_numbers_show_the_first_twenty_and_the_total(): void
    {
        $this->fillForm(seriesStart: '1', seriesEnd: '30')->call('save')->assertHasNoErrors();

        $this->fillForm(seriesStart: '1', seriesEnd: '40')
            ->call('save')
            ->assertSee('30 series ya existen en el sistema. Las 20 primeras: GSW0000001, ')
            ->assertSee('GSW0000019 y GSW0000020. Revisa el rango.')
            ->assertDontSee('GSW0000021');

        $this->assertSame(30, SecurityDevice::query()->count());
    }

    public function test_the_origin_and_the_range_are_required(): void
    {
        Livewire::test(CreateForm::class)
            ->set('form.receivedAt', '')
            ->call('save')
            ->assertHasErrors([
                'form.shippingLineId' => 'required',
                'form.providerId' => 'required',
                'form.securityDeviceTypeId' => 'required',
                'form.receivedAt' => 'required',
                'form.dispatchNumber' => 'required',
                'form.seriesPrefix' => 'required',
                'form.minDigits' => 'required',
                'form.seriesStart' => 'required',
                'form.seriesEnd' => 'required',
            ])
            ->assertHasNoErrors('form.notes');
    }

    /**
     * Fill the form with a valid lot of the serial numbers GSW0000200 to GSW0000399.
     */
    private function fillForm(string $minDigits = '7', string $seriesStart = '200', string $seriesEnd = '399'): Testable
    {
        return Livewire::test(CreateForm::class)
            ->set('form.shippingLineId', (string) $this->shippingLine->id)
            ->set('form.providerId', (string) $this->provider->id)
            ->set('form.securityDeviceTypeId', (string) $this->securityDeviceType->id)
            ->set('form.receivedAt', '2026-11-09')
            ->set('form.dispatchNumber', 'T001-000123')
            ->set('form.seriesPrefix', 'GSW')
            ->set('form.minDigits', $minDigits)
            ->set('form.seriesStart', $seriesStart)
            ->set('form.seriesEnd', $seriesEnd);
    }
}
