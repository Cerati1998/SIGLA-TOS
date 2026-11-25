<?php

namespace Tests\Feature;

use App\Actions\Certifications\FindKitToCertify;
use App\Actions\Certifications\StartCertification;
use App\Enums\CertificationSectionType;
use App\Enums\KitAssignment;
use App\Enums\KitStatus;
use App\Livewire\Inspections\StepForm;
use App\Models\Branch;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationSection;
use App\Models\CertificationStep;
use App\Models\Kit;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\YardZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class CertificationInspectionTest extends TestCase
{
    use RefreshDatabase;

    private User $inspector;

    private ShippingLine $shippingLine;

    private SecurityDeviceType $tape;

    private SecurityDeviceType $seal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('es');
        $this->inspector = User::factory()->create(['name' => 'Inspector Uno', 'branch_id' => Branch::factory()->create()->id]);
        $this->shippingLine = ShippingLine::factory()->create(['code' => 'NDE', 'name' => 'Naviera de Ejemplo']);
        $this->tape = SecurityDeviceType::factory()->splitsInternalExternal()->create(['name' => 'Cinta de inspección']);
        $this->seal = SecurityDeviceType::factory()->create(['name' => 'Sello botella']);

        CertificationSection::factory()->carrying($this->tape, 2)->create(['name' => 'Techo', 'type' => CertificationSectionType::Internal, 'sort_order' => 1]);
        CertificationSection::factory()->carrying($this->tape, 1)->carrying($this->seal, 1)->create(['name' => 'Panel frontal', 'type' => CertificationSectionType::External, 'sort_order' => 2]);
        CertificationSection::factory()->create(['name' => 'Fotografía del EIR', 'type' => CertificationSectionType::Internal, 'sort_order' => 3]);
        CertificationSection::factory()->carrying($this->seal, 5)->deactivated()->create(['name' => 'Sección desactivada', 'type' => CertificationSectionType::External]);
    }

    public function test_assigning_copies_the_active_sections_and_places_the_devices_of_the_kit_in_each_one(): void
    {
        $pouch = SecurityDeviceType::factory()->pouch()->create(['name' => 'Funda']);
        $kit = $this->kitWith(['KIT-000046' => [$pouch, null], 'CTA0000001' => [$this->tape, KitAssignment::Internal], 'CTA0000002' => [$this->tape, KitAssignment::Internal],
            'CTA0000003' => [$this->tape, KitAssignment::External], 'SLL0000001' => [$this->seal, null]]);

        $this->assertNull(app(FindKitToCertify::class)->rejectionReason($kit, $this->shippingLine));

        $certification = app(StartCertification::class)->handle($this->gateOutRequest(), $this->shippingLine, $kit, null, YardZone::factory()->create(), $this->inspector);

        $steps = $certification->steps()->with(['section', 'devices'])->get();

        $this->assertSame(
            [['Techo', 'internal', 1, ['CTA0000001', 'CTA0000002']], ['Panel frontal', 'external', 2, ['CTA0000003', 'SLL0000001']], ['Fotografía del EIR', 'internal', 3, []]],
            $steps->map(fn (CertificationStep $step): array => [
                $step->section->name, $step->type->value, $step->position, $step->devices->pluck('serial_number')->all(),
            ])->all(),
        );
        $this->assertSame(['CLIENTE DE EJEMPLO', 'PRODUCTO DE EJEMPLO'], [$certification->client, $certification->product]);
    }

    public function test_a_kit_that_lacks_devices_for_the_active_sections_is_rejected_saying_what_is_missing(): void
    {
        $kit = $this->kitWith(['CTA0000001' => [$this->tape, KitAssignment::Internal], 'CTA0000002' => [$this->tape, KitAssignment::Internal],
            'SLL0000001' => [$this->seal, null], 'SLL0000002' => [$this->seal, null]]);

        $reason = app(FindKitToCertify::class)->rejectionReason($kit, $this->shippingLine);

        $this->assertSame(
            'El kit KIT-000046 no calza con lo que piden las secciones activas: Falta 1 · Cinta de inspección · Externa. Escanea otra funda o pide otro kit en bodega.',
            $reason,
        );
    }

    public function test_the_devices_left_over_stay_in_the_kit_without_a_section_and_each_section_takes_the_lowest_correlatives(): void
    {
        $kit = $this->kitWith(['CTA10' => [$this->tape, KitAssignment::Internal], 'CTA9' => [$this->tape, KitAssignment::Internal],
            'CTA11' => [$this->tape, KitAssignment::Internal], 'CTA12' => [$this->tape, KitAssignment::External], 'SLL20' => [$this->seal, null], 'SLL3' => [$this->seal, null]]);

        $this->assertNull(app(FindKitToCertify::class)->rejectionReason($kit, $this->shippingLine));

        $certification = app(StartCertification::class)->handle($this->gateOutRequest(), $this->shippingLine, $kit, null, YardZone::factory()->create(), $this->inspector);

        $this->assertSame(
            ['Techo' => ['CTA9', 'CTA10'], 'Panel frontal' => ['CTA12', 'SLL3'], 'Fotografía del EIR' => []],
            $certification->steps()->with(['section', 'devices'])->get()
                ->mapWithKeys(fn (CertificationStep $step): array => [$step->section->name => $step->devices->pluck('serial_number')->sortBy(fn (string $serialNumber): string => $serialNumber, SORT_NATURAL)->values()->all()])
                ->all(),
        );
        $this->assertSame(6, $kit->devices()->count());
    }

    public function test_the_menu_opens_the_panel_with_its_cards_and_the_pending_units_of_each_inspection(): void
    {
        $this->certificationWithSteps();

        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.index'))
            ->assertOk()
            ->assertSeeInOrder(['Certificación', 'Solicitudes de retiro', 'Inspección', 'Certificaciones', 'Checkpoint'])
            ->assertSeeInOrder(['Asignación', 'Inspección interna', '1 pendiente', 'Inspección externa', '1 pendiente']);
    }

    public function test_each_inspection_lists_only_the_units_that_still_have_a_section_of_its_type_without_a_photo(): void
    {
        $certification = $this->certificationWithSteps('MSKU7654321');
        $certification->steps()->where('type', CertificationSectionType::Internal)->get()
            ->each(fn (CertificationStep $step) => CertificationPhoto::factory()->create(['certification_step_id' => $step->id]));
        $this->certificationWithSteps('TGHU5550123');

        $this->actingAs($this->inspector)
            ->get(route('intranet.inspections.index', 'internal'))
            ->assertOk()
            ->assertSeeText('TGHU5550123')
            ->assertDontSeeText('MSKU7654321');

        $this->actingAs($this->inspector)
            ->get(route('intranet.inspections.index', 'external'))
            ->assertOk()
            ->assertSeeText('MSKU7654321')
            ->assertSeeText('La interna ya está completa')
            ->assertSeeText('TGHU5550123');
    }

    public function test_the_inspection_shows_its_sections_with_their_devices(): void
    {
        $certification = $this->certificationWithSteps();

        $this->actingAs($this->inspector)
            ->get(route('intranet.inspections.show', ['internal', $certification]))
            ->assertOk()
            ->assertSeeText('Techo')
            ->assertSeeText('Fotografía del EIR')
            ->assertDontSeeText('Panel frontal')
            ->assertSeeText('Tomar foto');

        $this->actingAs($this->inspector)
            ->get(route('intranet.inspections.show', ['external', Certification::factory()->create()]))
            ->assertNotFound();
    }

    public function test_the_inspector_saves_a_photo_with_its_thumbnail_and_who_uploaded_it(): void
    {
        Storage::fake('local');
        $this->freezeSecond();
        $certification = $this->certificationWithSteps();
        $step = $certification->steps()->where('type', CertificationSectionType::Internal)->firstOrFail();

        $this->stepForm($certification)
            ->set('photo', UploadedFile::fake()->image('foto.jpg', 1920, 1440))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg', 320, 240))
            ->call('savePhoto', $step->id, null)
            ->assertHasNoErrors()
            ->assertReturned(true);

        $photo = CertificationPhoto::query()->sole();

        $this->assertSame([$step->id, $this->inspector->id], [$photo->certification_step_id, $photo->uploaded_by]);
        $this->assertTrue($photo->created_at?->equalTo(now()));
        Storage::disk('local')->assertExists([$photo->path, $photo->thumbnail_path]);
        $this->assertStringStartsWith("certifications/{$certification->id}/", $photo->path);
        $this->assertSame(['total' => 2, 'done' => 1], $certification->inspectionProgress(CertificationSectionType::Internal));
    }

    public function test_a_photo_is_replaced_by_a_new_one_and_can_be_deleted(): void
    {
        Storage::fake('local');
        $certification = $this->certificationWithSteps();
        $step = $certification->steps()->where('type', CertificationSectionType::Internal)->firstOrFail();
        $component = $this->stepForm($certification)
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $step->id, null);
        $photo = CertificationPhoto::query()->sole();
        $previousPath = $photo->path;

        $component->set('photo', UploadedFile::fake()->image('otra.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('otra-miniatura.jpg'))
            ->call('savePhoto', $step->id, $photo->id)
            ->assertHasNoErrors();

        $photo->refresh();
        $this->assertNotSame($previousPath, $photo->path);
        Storage::disk('local')->assertMissing($previousPath);
        Storage::disk('local')->assertExists($photo->path);

        $component->call('deletePhoto', $step->id, $photo->id)->assertDispatched('toast');

        $this->assertSame(0, CertificationPhoto::query()->count());
        Storage::disk('local')->assertMissing([$photo->path, $photo->thumbnail_path]);
    }

    public function test_only_jpeg_photos_are_saved(): void
    {
        Storage::fake('local');
        $certification = $this->certificationWithSteps();
        $step = $certification->steps()->firstOrFail();

        $this->stepForm($certification)
            ->set('photo', UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $step->id, null)
            ->assertHasErrors('photo');

        $this->assertSame(0, CertificationPhoto::query()->count());
    }

    public function test_a_section_of_the_other_inspection_cannot_get_photos_from_this_one(): void
    {
        Storage::fake('local');
        $certification = $this->certificationWithSteps();
        $externalStep = $certification->steps()->where('type', CertificationSectionType::External)->firstOrFail();

        $this->stepForm($certification)
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $externalStep->id, null)
            ->assertNotFound();

        $this->assertSame(0, CertificationPhoto::query()->count());
    }

    public function test_photos_are_served_only_with_a_session(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('certifications/1/foto.jpg', 'foto');
        Storage::disk('local')->put('certifications/1/thumbnails/foto.jpg', 'miniatura');
        $photo = CertificationPhoto::factory()->create(['path' => 'certifications/1/foto.jpg', 'thumbnail_path' => 'certifications/1/thumbnails/foto.jpg']);

        $this->get(route('intranet.certification-photos.show', $photo))->assertRedirect(route('login'));

        $this->actingAs($this->inspector)->get(route('intranet.certification-photos.show', $photo))->assertOk()->assertStreamedContent('foto');
        $this->actingAs($this->inspector)->get(route('intranet.certification-photos.thumbnail', $photo))->assertOk()->assertStreamedContent('miniatura');
    }

    /**
     * A certification already assigned with the active sections copied, as the assignment leaves it.
     */
    private function certificationWithSteps(string $container = 'ABCU1234567'): Certification
    {
        $certification = Certification::factory()->create(['container' => $container, 'shipping_line_id' => $this->shippingLine->id]);

        CertificationSection::query()->whereNotNull('sort_order')->orderBy('sort_order')->get()
            ->each(fn (CertificationSection $section, int $index) => $certification->steps()->create([
                'certification_section_id' => $section->id,
                'type' => $section->type,
                'position' => $index + 1,
            ]));

        return $certification;
    }

    private function stepForm(Certification $certification): Testable
    {
        return Livewire::actingAs($this->inspector)->test(StepForm::class, ['certification' => $certification, 'type' => CertificationSectionType::Internal]);
    }

    /**
     * A dispatched kit with the given devices, by serial number, with their type and side.
     *
     * @param  array<string, array{SecurityDeviceType, KitAssignment|null}>  $devices
     */
    private function kitWith(array $devices): Kit
    {
        $kit = Kit::factory()->create(['pouch_code' => 'KIT-000046', 'status' => KitStatus::Dispatched, 'shipping_line_id' => $this->shippingLine->id]);

        foreach ($devices as $serialNumber => [$type, $assignment]) {
            $lot = SecurityDeviceLot::factory()->create(['security_device_type_id' => $type->id, 'series_prefix' => preg_replace('/\d+$/', '', $serialNumber)]);
            $device = SecurityDevice::factory()->create(['security_device_lot_id' => $lot->id, 'serial_number' => $serialNumber]);

            $kit->devices()->attach($device->id, ['assignment' => $assignment?->value]);
        }

        return $kit;
    }

    /**
     * A request of SIGLA 1.0 with only what the assignment needs.
     *
     * @return array<string, mixed>
     */
    private function gateOutRequest(): array
    {
        $party = ['document_number' => null, 'name' => null];
        $vehicle = ['plate' => null, 'brand' => null, 'category' => null, 'body' => null, 'color' => null];

        return [
            'reference' => '10001',
            'booking' => 'BKG000000001',
            'container' => 'ABCU1234567',
            'shipping_line' => $this->shippingLine->name,
            'product' => 'PRODUCTO DE EJEMPLO',
            'vessel' => null,
            'voyage' => null,
            'destination' => null,
            'dispatched_at' => null,
            'client' => ['document_number' => null, 'name' => 'CLIENTE DE EJEMPLO'],
            'shipper' => $party,
            'carrier' => $party,
            'driver' => ['name' => null, 'license' => null, 'document_number' => null],
            'tractor' => $vehicle,
            'semi_trailer' => $vehicle,
        ];
    }
}
