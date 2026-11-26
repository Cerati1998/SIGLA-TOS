<?php

namespace Tests\Feature;

use App\Actions\Certifications\ListPendingInspections;
use App\Actions\Certifications\ReopenCertification;
use App\Actions\Reinspections\StartReinspection;
use App\Enums\BulkDispatchReason;
use App\Enums\CertificationSectionType;
use App\Enums\CertificationStatus;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\Checkpoints\ReinspectionModal;
use App\Livewire\Checkpoints\StepForm as CheckpointStepForm;
use App\Livewire\Checkpoints\UnitPicker;
use App\Livewire\Inspections\ReinspectionDeviceForm;
use App\Livewire\Inspections\StepForm as InspectionStepForm;
use App\Livewire\Tables\CertificationsTable;
use App\Models\BulkDispatch;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationSection;
use App\Models\CertificationStep;
use App\Models\Checkpoint;
use App\Models\CheckpointMark;
use App\Models\Kit;
use App\Models\Reinspection;
use App\Models\ReinspectionDevice;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * H35: the checkpoint starts a reinspection with the incorrect series, the certified inspector replaces them in the
 * reopened sections and takes new photos, closes the reinspection, and the checkpoint verifies the new series.
 */
class ReinspectionTest extends TestCase
{
    use RefreshDatabase;

    private User $inspector;

    private SecurityDeviceType $tape;

    private Certification $certification;

    private CertificationStep $evaporators;

    private CertificationStep $fan;

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
            'kit_id' => $kit->id,
            'closed_at' => now()->subHour(),
            'closed_by' => $this->inspector->id,
        ]);
        $this->evaporators = $this->certification->steps()->create(['certification_section_id' => $evaporators->id, 'type' => CertificationSectionType::External, 'position' => 1]);
        $this->fan = $this->certification->steps()->create(['certification_section_id' => $fan->id, 'type' => CertificationSectionType::External, 'position' => 2]);

        foreach (['WXG001' => $this->evaporators, 'WXG002' => $this->evaporators, 'WXG003' => $this->fan] as $serialNumber => $step) {
            $device = $this->device($serialNumber, SecurityDeviceStatus::Used);
            $kit->devices()->attach($device->id);
            $step->devices()->attach($device->id);
            CertificationPhoto::factory()->create(['certification_step_id' => $step->id, 'uploaded_by' => $this->inspector->id]);
        }

        $this->checkpoint = $this->checkpointWithMarks(['WXG001' => true, 'WXG002' => false, 'WXG003' => true]);
    }

    public function test_starting_a_reinspection_asks_for_a_reason_and_reopens_only_the_sections_with_incorrect_series(): void
    {
        $this->checkpointForm()
            ->assertSeeText('Iniciar reinspección')
            ->assertDontSeeText('Finalizar y cerrar');

        $this->startReinspection('')->assertHasErrors(['form.reason' => 'required']);
        $this->assertFalse($this->checkpoint->refresh()->isInReinspection());

        $this->startReinspection('La cinta del evaporador 3 está rota.')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.checkpoints.pending'));

        $reinspection = Reinspection::query()->sole();
        $this->assertSame('La cinta del evaporador 3 está rota.', $reinspection->reason);
        $this->assertSame($this->inspector->id, $reinspection->started_by);
        $this->assertSame([$this->evaporators->id], $reinspection->reopenedStepIds());
        $this->assertSame(['WXG002'], $reinspection->devices->map(fn (ReinspectionDevice $device): string => $device->device->serial_number)->all());
        $this->assertFalse($this->checkpoint->refresh()->isClosed());
        $this->assertSame(CertificationStatus::InReinspection, $this->certification->refresh()->status());

        Livewire::actingAs($this->inspector)->test(UnitPicker::class)->assertDontSeeText('TEMU9795602');
        $this->checkpointForm()->call('mark', $this->mark('WXG002')->id, true);
        $this->assertFalse($this->mark('WXG002')->is_correct);
    }

    public function test_a_reinspection_needs_every_step_complete_and_an_incorrect_series(): void
    {
        $this->checkpoint->photos()->delete();

        $this->expectRefusal('Falta completar pasos', fn () => app(StartReinspection::class)->handle($this->checkpoint, 'Rota.', $this->inspector));
    }

    public function test_in_reinspection_the_certification_is_listed_as_such_and_has_no_pdf_nor_reopening(): void
    {
        $this->startReinspection('Cinta rota.');

        Livewire::actingAs($this->inspector)->test(CertificationsTable::class)
            ->set('filterComponents.status', CertificationStatus::InReinspection->value)
            ->assertSeeText('TEMU9795602')
            ->assertSeeText('En reinspección')
            ->assertSeeText('Reinspección 0/1')
            ->assertDontSeeHtml('title="Descargar PDF"')
            ->assertDontSeeHtml('title="Reaperturar"');

        $this->actingAs($this->inspector)->get(route('intranet.certifications.pdf', $this->certification))->assertNotFound();
        $this->expectRefusal('Está en reinspección', fn () => app(ReopenCertification::class)->handle($this->certification->refresh(), 'Foto borrosa.', $this->inspector));
    }

    public function test_the_unit_shows_up_in_the_inspection_of_its_reopened_sections_with_only_those_sections(): void
    {
        $this->startReinspection('Cinta rota.');

        $units = app(ListPendingInspections::class)->handle(CertificationSectionType::External);
        $this->assertSame([['TEMU9795602', true, 0, 1]], array_map(fn (array $unit): array => [$unit['container'], $unit['reinspection'], $unit['done'], $unit['total']], $units));
        $this->assertSame([], app(ListPendingInspections::class)->handle(CertificationSectionType::Internal));

        $this->inspectionForm()
            ->assertSeeText('Reinspección iniciada en el checkpoint')
            ->assertSeeText('Motivo: «Cinta rota.»')
            ->assertSeeText('Evaporadores cerrados')
            ->assertDontSeeText('Ventilador')
            ->assertSeeText('Dispositivo a reemplazar')
            ->assertSeeText('Falta cambiar 1');
    }

    public function test_the_incorrect_series_is_replaced_by_a_scanned_extra_with_the_reason_of_the_reinspection_and_the_photo_of_the_discard(): void
    {
        $this->startReinspection('Cinta rota.');
        $this->extras(['WXG900']);

        $this->replaceDevice('XYZ999')->assertHasErrors('serialNumber');
        $this->replaceDevice('wxg900', withPhoto: false)->assertHasErrors(['photo' => 'required']);

        $this->replaceDevice(' wxg900 ')->assertHasNoErrors()->assertDispatched('security-device-replaced');

        $old = $this->serial('WXG002');
        $new = $this->serial('WXG900');
        $this->assertSame(SecurityDeviceStatus::Discarded, $old->status);
        $this->assertSame('Cinta rota.', $old->discard_reason);
        $this->assertNotNull($old->discard_photo_path);
        $this->assertSame(SecurityDeviceStatus::Used, $new->status);
        $this->assertTrue($this->evaporators->devices()->whereKey($new->id)->exists());

        $device = ReinspectionDevice::query()->sole();
        $this->assertTrue($device->isReplaced());
        $this->assertSame($device->reinspection_id, $device->replacement?->reinspection_id);
        $this->assertSame('Cinta rota.', $device->replacement?->reason);
    }

    public function test_the_new_photos_come_after_the_replacement_and_the_old_ones_are_kept_as_before_the_reinspection(): void
    {
        $this->startReinspection('Cinta rota.');
        $this->extras(['WXG900']);

        $this->inspectionForm()->set('photo', UploadedFile::fake()->image('foto.jpg'))->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $this->evaporators->id, null)
            ->assertReturned(false);

        $this->replaceDevice('WXG900');

        $this->inspectionForm()
            ->assertSeeText('Antes de la reinspección · 2')
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $this->evaporators->id, null)
            ->assertReturned(true);

        $this->inspectionForm()->set('photo', UploadedFile::fake()->image('foto.jpg'))->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $this->fan->id, null)
            ->assertReturned(false);

        $this->assertSame(['total' => 1, 'done' => 1], Reinspection::query()->sole()->progress());
    }

    public function test_closing_the_reinspection_needs_every_reopened_section_done_and_sends_the_unit_back_to_the_checkpoint(): void
    {
        $this->startReinspection('Cinta rota.');
        $this->extras(['WXG900']);

        $this->assignments()->call('closeReinspection', $this->certification->id)->assertDispatched('toast', icon: 'warning');
        $this->assertNull(Reinspection::query()->sole()->closed_at);

        $this->replaceDevice('WXG900');
        $this->newPhoto($this->evaporators);

        $this->assignments()->call('closeReinspection', $this->certification->id)->assertDispatched('toast', icon: 'success');

        $reinspection = Reinspection::query()->sole();
        $this->assertNotNull($reinspection->closed_at);
        $this->assertSame($this->inspector->id, $reinspection->closed_by);
        $this->assertSame(CertificationStatus::Closed, $this->certification->refresh()->status());
        $this->assertNotNull(CheckpointMark::query()->where('security_device_id', $this->serial('WXG002')->id)->sole()->superseded_by_reinspection_id);
        $this->assertSame($reinspection->id, CheckpointMark::query()->where('security_device_id', $this->serial('WXG900')->id)->sole()->reinspection_id);

        Livewire::actingAs($this->inspector)->test(UnitPicker::class)
            ->assertSeeTextInOrder(['TEMU9795602', 'Vuelve de reinspección']);
    }

    public function test_the_second_round_verifies_only_the_new_series_and_closes_the_checkpoint(): void
    {
        $this->reinspectAndClose('WXG900');

        $this->checkpointForm()
            ->assertSeeText('Segunda vuelta')
            ->assertSeeText('Serie reemplazada')
            ->assertSeeText('WXG900')
            ->assertSeeText('Reemplaza a WXG002, descartada en la reinspección.')
            ->assertSeeText('Ya verificadas en la primera vuelta')
            ->assertDontSeeText('Placa, cédula y transporte')
            ->call('mark', $this->mark('WXG900')->id, true)
            ->call('finish')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.checkpoints.pending'));

        $this->assertTrue($this->checkpoint->refresh()->isClosed());
    }

    public function test_a_new_series_marked_incorrect_starts_another_reinspection(): void
    {
        $this->reinspectAndClose('WXG900');
        $this->checkpointForm()->call('mark', $this->mark('WXG900')->id, false)->assertSeeText('Iniciar reinspección');

        $this->startReinspection('La nueva también está rota.')->assertHasNoErrors();

        $this->assertSame(2, Reinspection::query()->count());
        $this->assertSame(['WXG900'], Reinspection::query()->latest('id')->firstOrFail()->devices->map(fn (ReinspectionDevice $device): string => $device->device->serial_number)->all());
    }

    /**
     * Give the unit its checkpoint, with the photo of its photo step and the series marked as given.
     *
     * @param  array<string, bool>  $marks
     */
    private function checkpointWithMarks(array $marks): Checkpoint
    {
        Livewire::actingAs($this->inspector)->test(UnitPicker::class)->call('open', $this->certification->id);
        $checkpoint = Checkpoint::query()->sole()->load('steps');

        $form = $this->checkpointFormOf($checkpoint)
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $checkpoint->steps[0]->id, null);

        foreach ($marks as $serialNumber => $isCorrect) {
            $form->call('mark', CheckpointMark::query()->where('security_device_id', $this->serial($serialNumber)->id)->sole()->id, $isCorrect);
        }

        return $checkpoint;
    }

    private function checkpointForm(): Testable
    {
        return $this->checkpointFormOf($this->checkpoint->refresh());
    }

    private function checkpointFormOf(Checkpoint $checkpoint): Testable
    {
        return Livewire::actingAs($this->inspector)->test(CheckpointStepForm::class, ['checkpoint' => $checkpoint]);
    }

    private function startReinspection(string $reason): Testable
    {
        return Livewire::actingAs($this->inspector)
            ->test(ReinspectionModal::class, ['checkpoint' => $this->checkpoint->refresh(), 'sections' => []])
            ->set('form.openModal', true)
            ->set('form.reason', $reason)
            ->call('start');
    }

    private function inspectionForm(): Testable
    {
        return Livewire::actingAs($this->inspector)->test(InspectionStepForm::class, ['certification' => $this->certification->refresh(), 'type' => CertificationSectionType::External]);
    }

    private function replaceDevice(string $serialNumber, bool $withPhoto = true): Testable
    {
        $component = Livewire::actingAs($this->inspector)
            ->test(ReinspectionDeviceForm::class, ['reinspectionDevice' => ReinspectionDevice::query()->latest('id')->firstOrFail(), 'dispatchCodes' => []])
            ->set('serialNumber', $serialNumber);

        if ($withPhoto) {
            $component->set('photo', UploadedFile::fake()->image('descarte.jpg'))->set('thumbnail', UploadedFile::fake()->image('descarte-miniatura.jpg'));
        }

        return $component->call('replace');
    }

    private function newPhoto(CertificationStep $step): void
    {
        $this->inspectionForm()->set('photo', UploadedFile::fake()->image('foto.jpg'))->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $step->id, null)
            ->assertReturned(true);
    }

    private function assignments(): Testable
    {
        return Livewire::actingAs($this->inspector)->test(CertificationsTable::class);
    }

    /**
     * Start a reinspection, replace the incorrect series by the given extra, take the new photo and close it.
     */
    private function reinspectAndClose(string $serialNumber): void
    {
        $this->startReinspection('Cinta rota.');
        $this->extras([$serialNumber]);
        $this->replaceDevice($serialNumber);
        $this->newPhoto($this->evaporators);
        $this->assignments()->call('closeReinspection', $this->certification->id)->assertDispatched('toast', icon: 'success');
    }

    /**
     * Send the given tapes to the unit in a bulk dispatch for the operation: its extra devices.
     *
     * @param  list<string>  $serialNumbers
     */
    private function extras(array $serialNumbers): void
    {
        $dispatch = BulkDispatch::factory()->create(['reason' => BulkDispatchReason::Operation, 'client_id' => null, 'kit_id' => $this->certification->kit_id]);

        foreach ($serialNumbers as $serialNumber) {
            $dispatch->devices()->attach($this->device($serialNumber, SecurityDeviceStatus::Dispatched)->id);
        }
    }

    private function device(string $serialNumber, SecurityDeviceStatus $status): SecurityDevice
    {
        $lot = SecurityDeviceLot::factory()->for($this->tape)->create();

        return SecurityDevice::factory()->for($lot, 'lot')->create(['serial_number' => $serialNumber, 'status' => $status]);
    }

    private function serial(string $serialNumber): SecurityDevice
    {
        return SecurityDevice::query()->where('serial_number', $serialNumber)->sole();
    }

    private function mark(string $serialNumber): CheckpointMark
    {
        return CheckpointMark::query()->whereNull('superseded_by_reinspection_id')->where('security_device_id', $this->serial($serialNumber)->id)->sole();
    }

    private function expectRefusal(string $message, callable $action): void
    {
        try {
            $action();
        } catch (ValidationException $exception) {
            $this->assertStringContainsString($message, implode(' ', $exception->validator->errors()->all()));

            return;
        }

        $this->fail('It was not refused.');
    }
}
