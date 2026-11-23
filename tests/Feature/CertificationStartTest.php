<?php

namespace Tests\Feature;

use App\Enums\KitStatus;
use App\Livewire\Certifications\StartForm;
use App\Models\Branch;
use App\Models\Carrier;
use App\Models\CarrierDriver;
use App\Models\Certification;
use App\Models\Driver;
use App\Models\Kit;
use App\Models\KitDispatch;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Worker;
use App\Models\YardZone;
use App\Services\ShippingLineRegistry;
use App\Services\Sigla1LookupStatus;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class CertificationStartTest extends TestCase
{
    use RefreshDatabase;

    private const string API_URL = 'https://sigla1.test/api.php';

    private User $inspector;

    private ShippingLine $requestLine;

    private YardZone $yardZone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentitySeeder::class);
        config([
            'services.sigla1.url' => self::API_URL,
            'services.sigla1.token' => 'test-token',
        ]);
        $this->app->setLocale('es');

        $branch = Branch::factory()->create();
        $this->inspector = User::factory()->create(['name' => 'Inspector Uno', 'branch_id' => $branch->id]);
        $this->requestLine = ShippingLine::factory()->create(['code' => 'NDE', 'name' => 'Naviera de Ejemplo']);
        $this->yardZone = YardZone::factory()->create(['branch_id' => $branch->id, 'name' => 'Zona A · Lavado']);
    }

    public function test_guests_cannot_open_the_inspection(): void
    {
        $this->get(route('intranet.certifications.create'))->assertRedirect(route('login'));
    }

    public function test_the_certification_menu_has_the_inspection_and_a_disabled_checkpoint(): void
    {
        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.create'))
            ->assertOk()
            ->assertSeeInOrder(['Certificación', 'Solicitudes de retiro', 'Inspección', 'Checkpoint', 'Administración'])
            ->assertSee('aria-disabled="true"', false)
            ->assertSeeText('Iniciar certificación');
    }

    public function test_the_inspector_starts_a_certification_that_keeps_only_the_request_reference_and_the_links_to_the_catalogs(): void
    {
        $this->freezeSecond();
        $this->fakeSigla1([$this->gateOutRequest()]);
        $kit = $this->dispatchedKit('KIT-000046');

        $this->lookedUp()
            ->assertSet('gateOutRequest.reference', '10001')
            ->assertSeeText('Solicitud 10001')
            ->set('form.pouchCode', ' kit-000046 ')
            ->call('findKit')
            ->assertHasNoErrors()
            ->assertSet('kitId', $kit->id)
            ->assertSeeText('Despachado a')
            ->assertSeeText('Trabajador De Ejemplo')
            ->set('form.inspectionSheetCode', ' fmt-0001234 ')
            ->set('form.yardZoneId', (string) $this->yardZone->id)
            ->call('start')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.certifications.list'));

        Http::assertSent(fn (Request $request): bool => $request['container'] === 'ABCU1234567' && $request['booking'] === 'BKG000000001');

        $certification = Certification::query()->sole();
        $carrier = Carrier::query()->sole();
        $driver = Driver::query()->sole();

        $this->assertSame(
            ['10001', 'BKG000000001', 'ABCU1234567', $this->requestLine->id, $kit->id, 'FMT-0001234', $this->yardZone->id, $this->inspector->id],
            [$certification->gate_out_request_reference, $certification->booking, $certification->container, $certification->shipping_line_id,
                $certification->kit_id, $certification->inspection_sheet_code, $certification->yard_zone_id, $certification->certified_by],
        );
        $this->assertTrue($certification->started_at->equalTo(now()));
        $this->assertSame(['OPERADOR DE EJEMPLO S.A.C.', 'PRODUCTO DE EJEMPLO'], [$certification->client, $certification->product]);
        $this->assertSame(['20000000003', 'TRANSPORTES DE EJEMPLO S.R.L.'], [$carrier->document_number, $carrier->business_name]);
        $this->assertSame(['00000001', 'NOMBRE APELLIDO DE EJEMPLO', 'B00000001'], [$driver->document_number, $driver->full_name, $driver->license]);
        $this->assertSame([$carrier->id, $driver->id], [$certification->carrier_id, $certification->driver_id]);
        $this->assertTrue(CarrierDriver::query()->where(['carrier_id' => $carrier->id, 'driver_id' => $driver->id])->exists());
        $this->assertSame(['AAA111', 'BBB222'], [$certification->tractor?->plate, $certification->semiTrailer?->plate]);
        $this->assertSame(KitStatus::Dispatched, $kit->fresh()?->status);
        $this->assertSame(1, ShippingLine::query()->count());
        $this->assertSame(2, Vehicle::query()->count());
    }

    public function test_when_sigla1_sends_more_than_one_request_the_one_dispatched_last_is_taken(): void
    {
        $this->fakeSigla1([
            $this->gateOutRequest(),
            $this->gateOutRequest(['id_solicitud' => '10002', 'fecha_despacho' => '2026-11-12 08:00:00']),
        ]);

        $this->lookedUp()->assertSet('gateOutRequest.reference', '10002');
    }

    public function test_a_request_that_already_has_a_certification_offers_to_open_it_and_cannot_start_another(): void
    {
        $certification = Certification::factory()->create([
            'gate_out_request_reference' => '10001',
            'container' => 'ABCU1234567',
            'certified_by' => $this->inspector->id,
        ]);
        $this->fakeSigla1([$this->gateOutRequest()]);
        $kit = $this->dispatchedKit('KIT-000046');

        $this->lookedUp()
            ->assertSet('existingCertificationId', $certification->id)
            ->assertSeeText('ya tiene una certificación')
            ->assertSee(route('intranet.certifications.show', $certification))
            ->set('form.pouchCode', $kit->pouch_code)
            ->call('findKit')
            ->assertSet('kitId', null)
            ->call('start');

        $this->assertSame(1, Certification::query()->count());
    }

    public function test_the_container_needs_its_four_letters_and_its_seven_numbers_and_the_booking_is_required(): void
    {
        Http::preventStrayRequests();

        Livewire::actingAs($this->inspector)
            ->test(StartForm::class)
            ->set('form.containerOwner', 'AB1')
            ->set('form.containerNumber', '12345')
            ->call('search')
            ->assertHasErrors(['form.containerOwner' => 'regex', 'form.containerNumber' => 'regex', 'form.booking' => 'required']);

        Http::assertNothingSent();
    }

    public function test_when_sigla1_does_not_answer_the_inspector_can_retry(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response(null, 500)]);

        $this->lookedUp()
            ->assertSet('status', Sigla1LookupStatus::Unavailable)
            ->assertSeeText('SIGLA 1.0 no responde en este momento.')
            ->assertSeeText('Reintentar');
    }

    public function test_a_pouch_that_cannot_be_used_is_rejected_with_the_reason(): void
    {
        $this->fakeSigla1([$this->gateOutRequest()]);
        $built = Kit::factory()->create(['pouch_code' => 'KIT-000051', 'status' => KitStatus::Built, 'shipping_line_id' => $this->requestLine->id]);
        $otherLine = $this->dispatchedKit('KIT-000052', ShippingLine::factory()->create(['code' => 'MSK', 'name' => 'Maersk']));
        $used = $this->dispatchedKit('KIT-000040');
        Certification::factory()->create(['kit_id' => $used->id, 'container' => 'MSKU7654321']);

        $form = $this->lookedUp();

        foreach ([
            'KIT-999999' => 'No hay ningún kit con la funda KIT-999999.',
            $built->pouch_code => 'El kit KIT-000051 está Armado y todavía no se despachó.',
            $otherLine->pouch_code => 'El kit KIT-000052 es de MSK · Maersk y la solicitud es de NDE · Naviera de Ejemplo.',
            $used->pouch_code => 'El kit KIT-000040 ya está en la certificación del contenedor MSKU7654321.',
        ] as $pouchCode => $reason) {
            $form->set('form.pouchCode', $pouchCode)
                ->call('findKit')
                ->assertHasErrors(['form.pouchCode'])
                ->assertSeeText($reason)
                ->assertSet('kitId', null);
        }
    }

    public function test_the_yard_zone_must_be_an_active_one_of_the_branch_and_the_inspection_sheet_cannot_repeat(): void
    {
        $this->fakeSigla1([$this->gateOutRequest()]);
        $kit = $this->dispatchedKit('KIT-000046');
        Certification::factory()->create(['inspection_sheet_code' => 'FMT-0001234', 'container' => 'MSKU7654321']);
        $otherBranchZone = YardZone::factory()->create();

        $this->lookedUp()
            ->set('form.pouchCode', $kit->pouch_code)
            ->call('findKit')
            ->call('start')
            ->assertHasErrors(['form.yardZoneId' => 'required'])
            ->set('form.yardZoneId', (string) $otherBranchZone->id)
            ->set('form.inspectionSheetCode', 'fmt-0001234')
            ->call('start')
            ->assertHasErrors(['form.yardZoneId' => 'exists', 'form.inspectionSheetCode'])
            ->assertSeeText('El formato FMT-0001234 ya está en la certificación del contenedor MSKU7654321.')
            ->assertDispatched('certification-step', step: 2);

        $this->assertSame(1, Certification::query()->count());
    }

    public function test_a_kit_is_rejected_when_the_shipping_line_of_the_request_is_not_in_the_catalog(): void
    {
        $this->fakeSigla1([$this->gateOutRequest()]);
        $kit = $this->dispatchedKit('KIT-000046');
        $this->requestLine->update(['name' => 'Otra naviera']);

        $this->lookedUp()
            ->set('form.pouchCode', $kit->pouch_code)
            ->call('findKit')
            ->assertSeeText('El kit KIT-000046 es de NDE · Otra naviera y la solicitud es de NAVIERA DE EJEMPLO.');
    }

    public function test_when_saving_fails_nothing_is_saved_and_the_inspector_is_told(): void
    {
        $this->fakeSigla1([$this->gateOutRequest()]);
        $kit = $this->dispatchedKit('KIT-000046');
        $form = $this->lookedUp()->set('form.pouchCode', $kit->pouch_code)->call('findKit');

        $this->mock(ShippingLineRegistry::class, function ($mock): void {
            $mock->shouldReceive('find')->andReturn($this->requestLine);
            $mock->shouldReceive('findOrRegister')->andThrow(new RuntimeException('The database went away.'));
        });

        $form->set('form.yardZoneId', (string) $this->yardZone->id)
            ->call('start')
            ->assertSet('startFailed', true)
            ->assertSeeText('No se pudo iniciar la certificación y no se guardó nada.')
            ->assertNoRedirect();

        $this->assertSame([0, 0, 0], [Certification::query()->count(), Carrier::query()->count(), Driver::query()->count()]);
    }

    public function test_a_started_certification_shows_what_it_keeps_without_asking_sigla1(): void
    {
        $certification = Certification::factory()->create([
            'gate_out_request_reference' => '10001',
            'booking' => 'BKG000000001',
            'container' => 'ABCU1234567',
            'client' => 'OPERADOR DE EJEMPLO S.A.C.',
            'inspection_sheet_code' => 'FMT-0001234',
            'yard_zone_id' => $this->yardZone->id,
            'certified_by' => $this->inspector->id,
        ]);
        Http::preventStrayRequests();

        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.show', $certification))
            ->assertOk()
            ->assertSeeText('Certificación ABCU1234567')
            ->assertSeeText('por Inspector Uno, en la zona Zona A · Lavado.')
            ->assertSeeText('FMT-0001234')
            ->assertSeeText('OPERADOR DE EJEMPLO S.A.C.');

        Http::assertNothingSent();
    }

    /**
     * Look up the request of the fixture, writing the container in its two parts.
     */
    private function lookedUp(): Testable
    {
        return Livewire::actingAs($this->inspector)
            ->test(StartForm::class)
            ->set('form.containerOwner', 'abcu')
            ->set('form.containerNumber', '1234567')
            ->set('form.booking', ' bkg000000001 ')
            ->call('search')
            ->assertHasNoErrors();
    }

    /**
     * @param  list<array<string, string>>  $gateOutRequests
     */
    private function fakeSigla1(array $gateOutRequests): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response($gateOutRequests)]);
    }

    private function dispatchedKit(string $pouchCode, ?ShippingLine $shippingLine = null): Kit
    {
        $kit = Kit::factory()->create([
            'pouch_code' => $pouchCode,
            'status' => KitStatus::Dispatched,
            'shipping_line_id' => ($shippingLine ?? $this->requestLine)->id,
        ]);
        $dispatch = KitDispatch::factory()->create(['receiver_id' => Worker::factory()->create(['full_name' => 'Trabajador De Ejemplo'])->id]);
        $dispatch->kits()->attach($kit->id);

        return $kit;
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function gateOutRequest(array $overrides = []): array
    {
        /** @var array<string, string> $item */
        $item = json_decode((string) file_get_contents(base_path('tests/Fixtures/sigla1/gate-out-request.json')), true);

        return [...$item, ...$overrides];
    }
}
