<?php

namespace Tests\Feature;

use App\Actions\Certifications\DescribeCertification;
use App\Enums\CertificationSectionType;
use App\Enums\KitStatus;
use App\Livewire\Tables\CertificationsTable;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationSection;
use App\Models\CertificationStep;
use App\Models\Driver;
use App\Models\Kit;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\PhotoStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Frame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CertificationConsultationTest extends TestCase
{
    use RefreshDatabase;

    private User $inspector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('es');
        $this->inspector = User::factory()->create(['name' => 'Inspector Uno']);
    }

    public function test_the_list_is_searched_by_container_booking_or_plate(): void
    {
        $this->certification('ABCU1234567', ['booking' => 'BKG000000001', 'tractor_id' => Vehicle::factory()->create(['plate' => 'AAA111'])->id]);
        $this->certification('TGHU5550123', ['booking' => 'BKG000000003']);

        $table = Livewire::actingAs($this->inspector)->test(CertificationsTable::class);

        $table->set('search', 'abcu 123')->assertSeeText('ABCU1234567')->assertDontSeeText('TGHU5550123');
        $table->set('search', 'BKG000000003')->assertSeeText('TGHU5550123')->assertDontSeeText('ABCU1234567');
        $table->set('search', 'aaa111')->assertSeeText('ABCU1234567')->assertDontSeeText('TGHU5550123');
    }

    public function test_the_list_is_filtered_by_date_shipping_line_inspector_and_status(): void
    {
        $other = User::factory()->create(['name' => 'Otro Inspector']);
        $seatrade = ShippingLine::factory()->create(['code' => 'SEAT']);
        $this->certification('ABCU1234567', ['started_at' => '2026-11-20 13:40:00'], closed: true);
        $this->certification('TGHU5550123', ['started_at' => '2026-11-19 15:00:00', 'shipping_line_id' => $seatrade->id, 'certified_by' => $other->id]);

        $table = Livewire::actingAs($this->inspector)->test(CertificationsTable::class);

        $table->set('filterComponents.started_on', '2026-11-20')->assertSeeText('ABCU1234567')->assertDontSeeText('TGHU5550123');
        $table->set('filterComponents.started_on', '')->set('filterComponents.shipping_line', (string) $seatrade->id)
            ->assertSeeText('TGHU5550123')->assertDontSeeText('ABCU1234567');
        $table->set('filterComponents.shipping_line', '')->set('filterComponents.inspector', (string) $other->id)
            ->assertSeeText('TGHU5550123')->assertDontSeeText('ABCU1234567');
        $table->set('filterComponents.inspector', '')->set('filterComponents.status', 'closed')
            ->assertSeeText('ABCU1234567')->assertDontSeeText('TGHU5550123')
            ->assertSeeHtml('title="Descargar PDF"');
    }

    public function test_the_detail_shows_each_inspection_with_its_inspector_and_each_section_with_its_devices_and_photos(): void
    {
        $certification = $this->certification('ABCU1234567', closed: true);

        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.show', $certification))
            ->assertOk()
            ->assertSeeText('Cerrada')
            ->assertSeeText('Descargar PDF')
            ->assertSeeText('Volver a Asignaciones')
            ->assertSeeInOrder(['Interna · 1 sección', 'Inspector Uno', 'Externa · 1 sección', 'Inspector Uno'])
            ->assertSeeInOrder(['1 · Techo', 'CTA1234567', '2 · Panel frontal', 'SLL1234567'])
            ->assertSee(route('intranet.certification-photos.thumbnail', CertificationPhoto::query()->first()));
    }

    public function test_the_pdf_is_downloaded_only_from_a_closed_certification_and_carries_the_dni_of_the_driver(): void
    {
        Storage::fake('local');
        $open = $this->certification('TGHU5550123');
        $closed = $this->certification('ABCU1234567', ['driver_id' => Driver::factory()->create(['full_name' => 'CONDUCTOR DE EJEMPLO', 'document_number' => '12345678'])->id], closed: true);
        CertificationPhoto::query()->each(function (CertificationPhoto $photo): void {
            Storage::disk('local')->put($photo->path, (string) UploadedFile::fake()->image('foto.jpg')->get());
        });

        $this->actingAs($this->inspector)->get(route('intranet.certifications.pdf', $open))->assertNotFound();

        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.pdf', $closed))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $html = view('pdf.certifications.certification', [
            ...app(DescribeCertification::class)->handle($closed),
            'certification' => $closed,
            'photoData' => fn (): string => '',
            'generatedBy' => 'Inspector Uno',
        ])->render();

        $this->assertStringContainsString('12345678', $html);
        $this->assertStringContainsString('CTA1234567', $html);
        $this->assertStringContainsString('Interior · puertas abiertas', $html);
        $this->assertStringContainsString('Inspección externa', $html);
    }

    public function test_in_the_pdf_each_device_is_marked_with_its_number_over_the_spot_of_its_section(): void
    {
        $certification = $this->certification('ABCU1234567', closed: true);

        $pdf = Pdf::loadView('pdf.certifications.certification', [
            ...app(DescribeCertification::class)->handle($certification),
            'certification' => $certification,
            'photoData' => fn (): string => '',
            'generatedBy' => 'Inspector Uno',
        ])->setPaper('a4');

        /** @var list<array{x: float, y: float, w: float, h: float}> $drawings */
        $drawings = [];
        /** @var array<int, array{x: float, y: float, w: float, h: float}> $marks */
        $marks = [];
        /** @var list<string> $paintOrder */
        $paintOrder = [];
        $pdf->getDomPDF()->setCallbacks([[
            'event' => 'end_frame',
            'f' => function (Frame $frame) use (&$drawings, &$marks, &$paintOrder): void {
                $node = $frame->get_node();
                $box = $frame->get_border_box();
                $box = ['x' => (float) $box['x'], 'y' => (float) $box['y'], 'w' => (float) $box['w'], 'h' => (float) $box['h']];

                if ($node->nodeName === 'img' && abs($box['w'] - 165) < 1) {
                    $drawings[] = $box;
                    $paintOrder[] = 'drawing';
                } elseif ($node->nodeName === 'div' && str_contains((string) $node->attributes?->getNamedItem('style')?->nodeValue, 'position: absolute')) {
                    $marks[(int) trim((string) $node->textContent)] = $box;
                    $paintOrder[] = 'mark';
                }
            },
        ]]);
        $pdf->output();

        $this->assertCount(3, $drawings);
        $this->assertSame([1], array_keys($marks));
        $this->assertSame(['drawing', 'mark', 'drawing', 'drawing'], $paintOrder);
        [$inside] = $drawings;
        $this->assertEqualsWithDelta($inside['x'] + 0.50 * $inside['w'], $marks[1]['x'] + $marks[1]['w'] / 2, 2);
        $this->assertEqualsWithDelta($inside['y'] + 0.18 * $inside['h'], $marks[1]['y'] + $marks[1]['h'] / 2, 2);
    }

    public function test_the_pdf_takes_each_photo_from_the_whole_photo_shrunk_to_at_most_1024_px(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('certifications/1/grande.jpg', (string) UploadedFile::fake()->image('grande.jpg', 1920, 1440)->get());
        Storage::disk('local')->put('certifications/1/chica.jpg', (string) UploadedFile::fake()->image('chica.jpg', 800, 600)->get());
        $photoStorage = app(PhotoStorage::class);

        $this->assertSame([1024, 768], $this->sizeOf($photoStorage->resizedDataUri('certifications/1/grande.jpg', 1024)));
        $this->assertSame([800, 600], $this->sizeOf($photoStorage->resizedDataUri('certifications/1/chica.jpg', 1024)));
        $this->assertSame('', $photoStorage->resizedDataUri('certifications/1/no-existe.jpg', 1024));
    }

    public function test_guests_cannot_open_the_detail_or_the_pdf(): void
    {
        $certification = $this->certification('ABCU1234567', closed: true);

        $this->get(route('intranet.certifications.show', $certification))->assertRedirect(route('login'));
        $this->get(route('intranet.certifications.pdf', $certification))->assertRedirect(route('login'));
    }

    /**
     * A certification with an internal section carrying a tape and an external one carrying a seal, each with a
     * photo, closed or still open.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function certification(string $container, array $attributes = [], bool $closed = false): Certification
    {
        $kit = Kit::factory()->create(['status' => $closed ? KitStatus::Used : KitStatus::Dispatched]);
        $certification = Certification::factory()->create([
            'container' => $container,
            'kit_id' => $kit->id,
            'certified_by' => $this->inspector->id,
            'closed_at' => $closed ? now() : null,
            'closed_by' => $closed ? $this->inspector->id : null,
            ...$attributes,
        ]);

        foreach ([[CertificationSectionType::Internal, 'Techo', 'Cinta de inspección', 'CTA'], [CertificationSectionType::External, 'Panel frontal', 'Sello botella', 'SLL']] as $index => [$type, $name, $deviceType, $prefix]) {
            $step = CertificationStep::factory()->create([
                'certification_id' => $certification->id,
                'certification_section_id' => (CertificationSection::query()->firstWhere('name', $name) ?? CertificationSection::factory()->create(['name' => $name, 'type' => $type]))->id,
                'type' => $type,
                'position' => $index + 1,
            ]);
            $lot = SecurityDeviceLot::factory()->create([
                'security_device_type_id' => SecurityDeviceType::query()->firstOrCreate(['name' => $deviceType], SecurityDeviceType::factory()->make(['name' => $deviceType])->toArray())->id,
            ]);
            $device = SecurityDevice::factory()->create(['security_device_lot_id' => $lot->id, 'serial_number' => $prefix.substr($container, -7)]);
            $step->devices()->attach($device->id);
            CertificationPhoto::factory()->create(['certification_step_id' => $step->id, 'uploaded_by' => $this->inspector->id]);
        }

        return $certification;
    }

    /**
     * The width and the height of the JPEG inside a data URI.
     *
     * @return array{int, int}
     */
    private function sizeOf(string $dataUri): array
    {
        $size = getimagesizefromstring((string) base64_decode(substr($dataUri, strlen('data:image/jpeg;base64,'))));
        $this->assertNotFalse($size);

        return [$size[0], $size[1]];
    }
}
