<?php

namespace Tests\Feature;

use App\Enums\BulkDispatchReason;
use App\Enums\CertificationSectionType;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\Checkpoints\ReinspectionModal;
use App\Livewire\Checkpoints\StepForm as CheckpointStepForm;
use App\Livewire\Checkpoints\UnitPicker;
use App\Livewire\Inspections\ReinspectionDeviceForm;
use App\Livewire\Inspections\StepForm as InspectionStepForm;
use App\Livewire\Tables\CertificationsTable;
use App\Livewire\Tables\CheckpointsTable;
use App\Models\BulkDispatch;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationSection;
use App\Models\CertificationStep;
use App\Models\Checkpoint;
use App\Models\CheckpointMark;
use App\Models\CheckpointPhoto;
use App\Models\Kit;
use App\Models\ReinspectionDevice;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * H36: the list of checkpoints, with its search and its filters, and the detail of a checkpoint, only to read, with
 * its steps, its marks and its reinspections. The checkpoint keeps who started it.
 */
class CheckpointConsultationTest extends TestCase
{
    use RefreshDatabase;

    private User $inspector;

    private SecurityDeviceType $tape;

    private Certification $certification;

    private CertificationStep $evaporators;

    private Checkpoint $checkpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('es');
        Storage::fake('local');
        $this->inspector = User::factory()->create(['name' => 'Inspector Checkpoint']);
        $this->tape = SecurityDeviceType::factory()->create(['name' => 'Cinta de inspección']);

        $evaporators = CertificationSection::factory()->create(['name' => 'Evaporadores cerrados', 'type' => CertificationSectionType::External, 'sort_order' => 1]);
        $fan = CertificationSection::factory()->create(['name' => 'Ventilador', 'type' => CertificationSectionType::External, 'sort_order' => 2]);
        CertificationSection::factory()->checkpoint()->create(['name' => 'Placa, cédula y transporte', 'sort_order' => 3]);
        CertificationSection::factory()->checkpoint([$evaporators, $fan])->create(['name' => 'Verificación de cintas de evaporadores', 'sort_order' => 4]);

        $kit = Kit::factory()->create(['pouch_code' => 'KIT-000001', 'status' => KitStatus::Used]);
        $this->certification = Certification::factory()->create([
            'container' => 'TEMU9795602',
            'booking' => 'BKG000000005',
            'kit_id' => $kit->id,
            'tractor_id' => Vehicle::factory()->create(['plate' => 'EEE555'])->id,
            'closed_at' => now()->subHour(),
            'closed_by' => $this->inspector->id,
        ]);
        $this->evaporators = $this->certification->steps()->create(['certification_section_id' => $evaporators->id, 'type' => CertificationSectionType::External, 'position' => 1]);
        $fanStep = $this->certification->steps()->create(['certification_section_id' => $fan->id, 'type' => CertificationSectionType::External, 'position' => 2]);

        foreach (['WXG001' => $this->evaporators, 'WXG002' => $this->evaporators, 'WXG003' => $fanStep] as $serialNumber => $step) {
            $device = $this->device($serialNumber, SecurityDeviceStatus::Used);
            $kit->devices()->attach($device->id);
            $step->devices()->attach($device->id);
            CertificationPhoto::factory()->create(['certification_step_id' => $step->id, 'uploaded_by' => $this->inspector->id]);
        }

        Livewire::actingAs($this->inspector)->test(UnitPicker::class)->call('open', $this->certification->id);
        $this->checkpoint = Checkpoint::query()->sole();

        $form = $this->checkpointForm()
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $this->checkpoint->steps()->firstOrFail()->id, null);

        foreach (['WXG001' => true, 'WXG002' => false, 'WXG003' => true] as $serialNumber => $isCorrect) {
            $form->call('mark', $this->mark($serialNumber)->id, $isCorrect);
        }
    }

    public function test_opening_a_unit_keeps_who_started_its_checkpoint(): void
    {
        $this->assertSame($this->inspector->id, $this->checkpoint->started_by);

        $this->actingAs($this->inspector)
            ->get(route('intranet.checkpoints.details', $this->checkpoint))
            ->assertOk()
            ->assertSeeText('Pendiente: 2 de 2 pasos completos.')
            ->assertSeeText('por Inspector Checkpoint');
    }

    public function test_the_list_shows_each_checkpoint_with_its_status_its_progress_and_who_works_on_it(): void
    {
        $this->checkpoints()
            ->assertSeeTextInOrder(['TEMU9795602', 'Pendiente', 'Pasos 2/2', 'Series 2/3 · 1 incorrecta', 'Inspector Checkpoint'])
            ->assertSeeHtml('aria-label="Ver el checkpoint de TEMU9795602"');

        $this->startReinspection();
        $this->checkpoints()->assertSeeTextInOrder(['TEMU9795602', 'En reinspección']);

        $this->closeEverything();
        $this->checkpoints()->assertSeeTextInOrder(['TEMU9795602', 'Cerrado', 'Pasos 2/2', 'Series 3/3 correctas · 1 reinspección']);
    }

    public function test_the_list_is_searched_by_container_booking_or_plate_and_filtered_by_date_shipping_line_inspector_and_status(): void
    {
        $other = User::factory()->create(['name' => 'Otro Inspector']);
        $seatrade = ShippingLine::factory()->create(['code' => 'SEAT']);
        $unit = Certification::factory()->create(['container' => 'ABCU1234567', 'booking' => 'BKG000000001', 'shipping_line_id' => $seatrade->id, 'closed_at' => now()->subDays(3)]);
        $this->checkpoint->forceFill(['created_at' => '2026-11-20 15:00:00'])->save();
        Checkpoint::factory()->closed()->create(['certification_id' => $unit->id, 'started_by' => $other->id, 'created_at' => '2026-11-19 15:00:00']);

        $table = $this->checkpoints();

        $table->set('search', 'temu 979')->assertSeeText('TEMU9795602')->assertDontSeeText('ABCU1234567');
        $table->set('search', 'BKG000000001')->assertSeeText('ABCU1234567')->assertDontSeeText('TEMU9795602');
        $table->set('search', 'eee555')->assertSeeText('TEMU9795602')->assertDontSeeText('ABCU1234567');
        $table->set('search', '')->set('filterComponents.started_on', '2026-11-20')->assertSeeText('TEMU9795602')->assertDontSeeText('ABCU1234567');
        $table->set('filterComponents.started_on', '')->set('filterComponents.shipping_line', (string) $seatrade->id)
            ->assertSeeText('ABCU1234567')->assertDontSeeText('TEMU9795602');
        $table->set('filterComponents.shipping_line', '')->set('filterComponents.inspector', (string) $other->id)
            ->assertSeeText('ABCU1234567')->assertDontSeeText('TEMU9795602');
        $table->set('filterComponents.inspector', (string) $this->inspector->id)
            ->assertSeeText('TEMU9795602')->assertDontSeeText('ABCU1234567');
        $table->set('filterComponents.inspector', '')->set('filterComponents.status', 'closed')
            ->assertSeeText('ABCU1234567')->assertDontSeeText('TEMU9795602');
        $table->set('filterComponents.status', 'pending')
            ->assertSeeText('TEMU9795602')->assertDontSeeText('ABCU1234567');
    }

    public function test_the_detail_shows_each_step_each_mark_and_each_reinspection_only_to_read(): void
    {
        $this->closeEverything();

        $this->actingAs($this->inspector)
            ->get(route('intranet.checkpoints.details', $this->checkpoint))
            ->assertOk()
            ->assertSeeText('Cerrado')
            ->assertSeeText('Tuvo 1 reinspección.')
            ->assertSeeText('Volver a Checkpoints')
            ->assertSeeText('Ver la certificación')
            ->assertSeeInOrder(['Reinspección 1', 'Cerrada', '«Cinta rota.».', 'Inspector Checkpoint, en el checkpoint', 'Evaporadores cerrados', 'WXG002', 'WXG900', 'la anterior quedó Descartada', 'Segunda verificación', 'WXG900', 'Correcta'])
            ->assertSeeInOrder(['1 · Placa, cédula y transporte', '2 · Verificación de cintas de evaporadores', 'De Evaporadores cerrados y Ventilador'])
            ->assertSeeInOrder(['WXG001', 'WXG002', 'Incorrecta', 'reemplazada', 'WXG900', 'Correcta', 'reinspección 1', 'WXG003'])
            ->assertSeeInOrder(['Observaciones y fotos extra', '«Semirremolque recién pintado»'])
            ->assertSee(route('intranet.checkpoint-photos.thumbnail', CheckpointPhoto::query()->firstOrFail()))
            ->assertDontSeeText('Finalizar')
            ->assertDontSeeText('Descargar PDF');
    }

    public function test_the_detail_of_the_certification_leads_to_its_checkpoint_when_it_has_one(): void
    {
        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.show', $this->certification))
            ->assertOk()
            ->assertSeeText('Ver checkpoint')
            ->assertSee(route('intranet.checkpoints.details', $this->checkpoint));

        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.show', Certification::factory()->create(['closed_at' => now()])))
            ->assertOk()
            ->assertDontSeeText('Ver checkpoint');
    }

    public function test_the_menu_leads_to_the_list_and_guests_cannot_open_the_list_or_the_detail(): void
    {
        $this->actingAs($this->inspector)
            ->get(route('intranet.checkpoints.index'))
            ->assertOk()
            ->assertSee(route('intranet.checkpoints.index'))
            ->assertSeeLivewire(CheckpointsTable::class);

        auth()->logout();

        $this->get(route('intranet.checkpoints.index'))->assertRedirect(route('login'));
        $this->get(route('intranet.checkpoints.details', $this->checkpoint))->assertRedirect(route('login'));
    }

    private function checkpoints(): Testable
    {
        return Livewire::actingAs($this->inspector)->test(CheckpointsTable::class);
    }

    private function checkpointForm(): Testable
    {
        return Livewire::actingAs($this->inspector)->test(CheckpointStepForm::class, ['checkpoint' => $this->checkpoint->refresh()]);
    }

    private function startReinspection(): void
    {
        Livewire::actingAs($this->inspector)
            ->test(ReinspectionModal::class, ['checkpoint' => $this->checkpoint->refresh(), 'sections' => []])
            ->set('form.openModal', true)
            ->set('form.reason', 'Cinta rota.')
            ->call('start')
            ->assertHasNoErrors();
    }

    /**
     * Start a reinspection, replace the incorrect series by an extra, take the new photo, close it, mark the new
     * series and close the checkpoint with its observations.
     */
    private function closeEverything(): void
    {
        $this->startReinspection();

        $dispatch = BulkDispatch::factory()->create(['reason' => BulkDispatchReason::Operation, 'client_id' => null, 'kit_id' => $this->certification->kit_id]);
        $dispatch->devices()->attach($this->device('WXG900', SecurityDeviceStatus::Dispatched)->id);

        Livewire::actingAs($this->inspector)
            ->test(ReinspectionDeviceForm::class, ['reinspectionDevice' => ReinspectionDevice::query()->sole(), 'dispatchCodes' => []])
            ->set('serialNumber', 'WXG900')
            ->set('photo', UploadedFile::fake()->image('descarte.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('descarte-miniatura.jpg'))
            ->call('replace')
            ->assertHasNoErrors();

        Livewire::actingAs($this->inspector)
            ->test(InspectionStepForm::class, ['certification' => $this->certification->refresh(), 'type' => CertificationSectionType::External])
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $this->evaporators->id, null)
            ->assertReturned(true);

        Livewire::actingAs($this->inspector)->test(CertificationsTable::class)
            ->call('closeReinspection', $this->certification->id)
            ->assertDispatched('toast', icon: 'success');

        $this->checkpointForm()
            ->call('mark', $this->mark('WXG900')->id, true)
            ->set('observations', 'Semirremolque recién pintado')
            ->call('finish')
            ->assertHasNoErrors();
    }

    private function device(string $serialNumber, SecurityDeviceStatus $status): SecurityDevice
    {
        $lot = SecurityDeviceLot::factory()->for($this->tape)->create();

        return SecurityDevice::factory()->for($lot, 'lot')->create(['serial_number' => $serialNumber, 'status' => $status]);
    }

    private function mark(string $serialNumber): CheckpointMark
    {
        $device = SecurityDevice::query()->where('serial_number', $serialNumber)->sole();

        return CheckpointMark::query()->whereNull('superseded_by_reinspection_id')->where('security_device_id', $device->id)->sole();
    }
}
