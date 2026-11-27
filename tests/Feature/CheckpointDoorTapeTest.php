<?php

namespace Tests\Feature;

use App\Enums\CertificationSectionType;
use App\Enums\CheckpointStatus;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\CertificationSections\CreateModal;
use App\Livewire\Checkpoints\Record;
use App\Livewire\Checkpoints\ReinspectionModal;
use App\Livewire\Checkpoints\StepForm;
use App\Livewire\Checkpoints\UnitPicker;
use App\Livewire\Tables\CheckpointsTable;
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
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * T91: an incorrect series in a verification step that does not ask for a reinspection, like the door tape, lets the
 * checkpoint close with a required observation as «Cerrado con observación»; the other incorrect series still ask for
 * a reinspection.
 */
class CheckpointDoorTapeTest extends TestCase
{
    use RefreshDatabase;

    private User $inspector;

    private Certification $certification;

    private CertificationStep $evaporators;

    private Checkpoint $checkpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('es');
        Storage::fake('local');
        $this->inspector = User::factory()->create(['name' => 'Inspector Checkpoint']);

        $evaporators = CertificationSection::factory()->create(['name' => 'Evaporadores cerrados', 'type' => CertificationSectionType::External, 'sort_order' => 1]);
        $floorKit = CertificationSection::factory()->create(['name' => 'Kit de precintos en piso', 'type' => CertificationSectionType::Internal, 'sort_order' => 2]);
        CertificationSection::factory()->checkpoint()->create(['name' => 'Placa, cédula y transporte', 'sort_order' => 3]);
        CertificationSection::factory()->checkpoint([$evaporators])->create(['name' => 'Verificación de cintas de evaporadores', 'sort_order' => 4]);
        CertificationSection::factory()->checkpoint([$floorKit])->create(['name' => 'Verificación de cinta de puerta', 'skips_reinspection' => true, 'sort_order' => 5]);

        $kit = Kit::factory()->create(['status' => KitStatus::Used]);
        $this->certification = Certification::factory()->create([
            'container' => 'CAIU5512340',
            'kit_id' => $kit->id,
            'closed_at' => now()->subHour(),
            'closed_by' => $this->inspector->id,
        ]);
        $this->evaporators = $this->certification->steps()->create(['certification_section_id' => $evaporators->id, 'type' => CertificationSectionType::External, 'position' => 1]);
        $door = $this->certification->steps()->create(['certification_section_id' => $floorKit->id, 'type' => CertificationSectionType::Internal, 'position' => 2]);

        $tape = SecurityDeviceType::factory()->create(['name' => 'Cinta de inspección']);
        foreach (['WXG001' => $this->evaporators, 'WXG010' => $door] as $serialNumber => $step) {
            $device = SecurityDevice::factory()->for(SecurityDeviceLot::factory()->for($tape), 'lot')->create(['serial_number' => $serialNumber, 'status' => SecurityDeviceStatus::Used]);
            $kit->devices()->attach($device->id);
            $step->devices()->attach($device->id);
            CertificationPhoto::factory()->create(['certification_step_id' => $step->id, 'uploaded_by' => $this->inspector->id]);
        }

        Livewire::actingAs($this->inspector)->test(UnitPicker::class)->call('open', $this->certification->id);
        $this->checkpoint = Checkpoint::query()->sole();
        $this->form()
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $this->checkpoint->steps()->firstOrFail()->id, null);
    }

    public function test_an_incorrect_door_tape_closes_the_checkpoint_only_with_an_observation(): void
    {
        $this->assertTrue($this->checkpoint->steps()->where('skips_reinspection', true)->exists());
        $this->mark('WXG001', true);
        $this->mark('WXG010', false);

        $this->form()
            ->assertSeeText('Observación obligatoria')
            ->assertSeeText('Sin reinspección')
            ->assertSeeText('Finalizar y cerrar')
            ->assertDontSeeText('Iniciar reinspección')
            ->call('finish')
            ->assertHasErrors('observations')
            ->assertSeeText('Escribe la observación: hay una serie incorrecta que no pide reinspección.');
        $this->assertFalse($this->checkpoint->refresh()->isClosed());

        $this->form()
            ->set('observations', 'La cinta de la puerta llegó rota.')
            ->call('finish')
            ->assertHasNoErrors()
            ->assertRedirect(route('intranet.checkpoints.pending'));

        $checkpoint = $this->checkpoint->refresh();
        $this->assertTrue($checkpoint->isClosed());
        $this->assertSame(CheckpointStatus::ClosedWithObservation, $checkpoint->status());
        $this->assertSame(0, Reinspection::query()->count());

        Livewire::actingAs($this->inspector)->test(Record::class, ['checkpoint' => $checkpoint])
            ->assertSeeText('Cerrado con observación')
            ->assertSeeText('Observación: «La cinta de la puerta llegó rota.»');
    }

    public function test_the_list_shows_and_filters_the_checkpoints_closed_with_an_observation(): void
    {
        $this->mark('WXG001', true);
        $this->mark('WXG010', false);
        $this->form()->set('observations', 'Cinta de puerta rota.')->call('finish')->assertHasNoErrors();

        $table = Livewire::actingAs($this->inspector)->test(CheckpointsTable::class)
            ->assertSeeText('Cerrado con observación')
            ->set('filterComponents.status', 'closed_with_observation')
            ->assertSeeText('CAIU5512340');
        $table->set('filterComponents.status', 'closed')->assertDontSeeText('CAIU5512340');
    }

    public function test_another_incorrect_series_still_asks_for_a_reinspection_of_only_its_section_after_the_observation(): void
    {
        $this->mark('WXG001', false);
        $this->mark('WXG010', false);

        $this->form()
            ->assertSeeText('Iniciar reinspección')
            ->assertDontSeeText('Finalizar y cerrar')
            ->call('finish')
            ->assertHasErrors(['checkpoint']);

        $this->startReinspection()->assertDispatched('toast', icon: 'warning', text: 'Escribe la observación: hay una serie incorrecta que no pide reinspección.');
        $this->assertSame(0, Reinspection::query()->count());

        $this->form()->set('observations', 'Cinta de puerta rota.');
        $this->startReinspection()->assertHasNoErrors();

        $reinspection = Reinspection::query()->sole();
        $this->assertSame([$this->evaporators->id], $reinspection->reopenedStepIds());
        $this->assertSame(['WXG001'], $reinspection->devices->map(fn (ReinspectionDevice $device): string => $device->device->serial_number)->all());
    }

    public function test_the_admin_marks_a_verification_section_that_does_not_ask_for_a_reinspection(): void
    {
        $this->actingAs($this->inspector);
        $floorKit = CertificationSection::query()->where('name', 'Kit de precintos en piso')->sole();

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Verificación de sello cable')
            ->set('form.type', 'checkpoint')
            ->set('form.checkpointKind', 'verification')
            ->set('form.verifiedSectionIds', [$floorKit->id])
            ->set('form.skipsReinspection', true)
            ->call('save')
            ->assertHasNoErrors();
        Livewire::test(CreateModal::class)
            ->set('form.name', 'Panorámica de puertas')
            ->set('form.type', 'checkpoint')
            ->set('form.checkpointKind', 'photos')
            ->set('form.skipsReinspection', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(CertificationSection::query()->where('name', 'Verificación de sello cable')->sole()->skips_reinspection);
        $this->assertFalse(CertificationSection::query()->where('name', 'Panorámica de puertas')->sole()->skips_reinspection);
    }

    private function form(): Testable
    {
        return Livewire::actingAs($this->inspector)->test(StepForm::class, ['checkpoint' => $this->checkpoint->refresh()]);
    }

    private function mark(string $serialNumber, bool $isCorrect): void
    {
        $device = SecurityDevice::query()->where('serial_number', $serialNumber)->sole();

        $this->form()->call('mark', CheckpointMark::query()->where('security_device_id', $device->id)->sole()->id, $isCorrect);
    }

    private function startReinspection(): Testable
    {
        return Livewire::actingAs($this->inspector)
            ->test(ReinspectionModal::class, ['checkpoint' => $this->checkpoint->refresh(), 'sections' => []])
            ->set('form.openModal', true)
            ->set('form.reason', 'La cinta del evaporador está rota.')
            ->call('start');
    }
}
