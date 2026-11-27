<?php

namespace Tests\Feature;

use App\Enums\CertificationSectionType;
use App\Livewire\Checkpoints\StepForm;
use App\Livewire\Checkpoints\UnitPicker;
use App\Models\Certification;
use App\Models\CertificationSection;
use App\Models\Checkpoint;
use App\Models\CheckpointMark;
use App\Models\CheckpointStep;
use App\Models\SecurityDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class CheckpointTest extends TestCase
{
    use RefreshDatabase;

    private User $inspector;

    private CertificationSection $evaporators;

    private CertificationSection $fan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('es');
        $this->inspector = User::factory()->create(['name' => 'Inspector Checkpoint']);

        $this->evaporators = CertificationSection::factory()->create(['name' => 'Evaporadores cerrados', 'type' => CertificationSectionType::External, 'sort_order' => 1]);
        $this->fan = CertificationSection::factory()->create(['name' => 'Ventilador', 'type' => CertificationSectionType::External, 'sort_order' => 2]);
        CertificationSection::factory()->checkpoint()->create(['name' => 'Placa, cédula y transporte', 'sort_order' => 3]);
        CertificationSection::factory()->checkpoint([$this->evaporators, $this->fan])->create(['name' => 'Verificación de cintas de evaporadores', 'sort_order' => 4]);
        CertificationSection::factory()->checkpoint()->deactivated()->create(['name' => 'Paso desactivado']);
    }

    public function test_the_menu_leads_to_the_units_with_the_checkpoint_pending(): void
    {
        $this->actingAs($this->inspector)
            ->get(route('intranet.checkpoints.pending'))
            ->assertOk()
            ->assertSeeInOrder(['Checkpoint', 'Registrar checkpoint', 'Checkpoints'])
            ->assertSeeText('Con checkpoint pendiente · 0');

        auth()->logout();

        $this->get(route('intranet.checkpoints.pending'))->assertRedirect(route('login'));
    }

    public function test_only_closed_certifications_without_a_closed_checkpoint_are_listed_the_half_done_first(): void
    {
        $this->certification('TEMU9795602');
        $halfDone = $this->certification('TGHU5550123');
        $this->certification('ABCU0000001', closed: false);
        $this->certification('ABCU0000002', annulled: true);
        Checkpoint::factory()->closed()->create(['certification_id' => $this->certification('ABCU0000003')->id]);
        $this->open($halfDone);

        Livewire::actingAs($this->inspector)->test(UnitPicker::class)
            ->assertSeeText('Con checkpoint pendiente · 2')
            ->assertSeeTextInOrder(['TGHU5550123', 'En proceso', '0 de 2 pasos', 'TEMU9795602', 'Sin empezar', '0 de 2 pasos'])
            ->assertDontSeeText('ABCU0000001')
            ->assertDontSeeText('ABCU0000002')
            ->assertDontSeeText('ABCU0000003');
    }

    public function test_the_closing_times_are_shown_in_lima_time(): void
    {
        $certification = $this->certification();
        $certification->forceFill(['closed_at' => '2026-11-27 15:49:00'])->save();

        Livewire::actingAs($this->inspector)->test(UnitPicker::class)
            ->assertSeeText('Certificación cerrada el 27/11/2026 a las 10:49')
            ->assertDontSeeText('a las 15:49');

        $checkpoint = $this->open($certification);
        $checkpoint->forceFill(['closed_at' => '2026-11-27 16:05:00', 'closed_by' => $this->inspector->id])->save();

        $this->stepForm($checkpoint)->assertSeeText('el 27/11/2026 a las 11:05: solo lectura.');
    }

    public function test_opening_a_unit_starts_its_checkpoint_with_the_active_checkpoint_sections_and_the_series_to_verify(): void
    {
        $certification = $this->certification();

        $checkpoint = $this->open($certification);

        $this->assertSame(['Placa, cédula y transporte', 'Verificación de cintas de evaporadores'], $checkpoint->steps->map(fn (CheckpointStep $step) => $step->section->name)->all());
        $this->assertSame(['photos', 'verification'], $checkpoint->steps->map(fn (CheckpointStep $step) => $step->kind->value)->all());
        $this->assertSame(['WXG001', 'WXG002', 'WXG003'], $checkpoint->steps[1]->marks->map(fn (CheckpointMark $mark) => $mark->device->serial_number)->all());
        $this->assertTrue($checkpoint->steps[1]->marks->every(fn (CheckpointMark $mark) => $mark->is_correct === null));
        $this->actingAs($this->inspector)->get(route('intranet.checkpoints.show', $checkpoint))
            ->assertOk()
            ->assertSeeTextInOrder(['Placa, cédula y transporte', 'Verificación de cintas de evaporadores', 'Observaciones y finalizar']);

        CertificationSection::factory()->checkpoint()->create(['name' => 'Paso nuevo', 'sort_order' => 5]);

        $this->assertSame($checkpoint->id, $this->open($certification)->id);
        $this->assertSame(1, Checkpoint::query()->count());
        $this->assertSame(2, $checkpoint->steps()->count());
    }

    public function test_a_unit_whose_certification_is_not_closed_cannot_be_opened(): void
    {
        $certification = $this->certification(closed: false);

        Livewire::actingAs($this->inspector)->test(UnitPicker::class)
            ->call('open', $certification->id)
            ->assertNotFound();

        $this->assertSame(0, Checkpoint::query()->count());
    }

    public function test_the_optional_photo_of_the_container_is_kept_in_the_checkpoint_and_a_new_one_replaces_it(): void
    {
        Storage::fake('local');
        $certification = $this->certification();

        $this->open($certification, withPhoto: true);
        $checkpoint = Checkpoint::query()->sole();
        $firstPhoto = $checkpoint->container_photo_path;

        $this->assertNotNull($firstPhoto);
        $this->assertSame($this->inspector->id, $checkpoint->container_photo_by);
        $this->assertNotNull($checkpoint->container_photo_at);
        Storage::disk('local')->assertExists($firstPhoto);

        $this->open($certification, withPhoto: true);

        $this->assertNotSame($firstPhoto, $checkpoint->refresh()->container_photo_path);
        Storage::disk('local')->assertMissing($firstPhoto);
        $this->actingAs($this->inspector)->get(route('intranet.checkpoints.container-photo', $checkpoint))->assertOk();
    }

    public function test_a_photo_step_and_the_extra_photos_keep_their_photos_with_who_uploaded_them(): void
    {
        Storage::fake('local');
        $checkpoint = $this->open($this->certification());
        $photoStep = $checkpoint->steps[0];

        $this->stepForm($checkpoint)
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $photoStep->id, null)
            ->assertReturned(true)
            ->set('photo', UploadedFile::fake()->image('extra.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('extra-miniatura.jpg'))
            ->call('savePhoto', null, null)
            ->assertReturned(true);

        $this->assertSame(1, $photoStep->photos()->count());
        $this->assertSame(1, $checkpoint->extraPhotos()->count());
        $this->assertSame($this->inspector->id, $photoStep->photos()->sole()->uploaded_by);
    }

    public function test_a_verification_step_does_not_take_photos(): void
    {
        Storage::fake('local');
        $checkpoint = $this->open($this->certification());

        $this->stepForm($checkpoint)
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $checkpoint->steps[1]->id, null)
            ->assertNotFound();
    }

    public function test_each_series_is_marked_correct_or_incorrect_with_who_marked_it_and_when(): void
    {
        $checkpoint = $this->open($this->certification());
        $mark = $checkpoint->steps[1]->marks[0];

        $this->stepForm($checkpoint)
            ->assertSeeText('WXG001')
            ->assertSeeText('Evaporadores cerrados (externa)')
            ->call('mark', $mark->id, false)
            ->assertSeeText('Hay 1 serie incorrecta.');

        $mark->refresh();
        $this->assertFalse($mark->is_correct);
        $this->assertSame($this->inspector->id, $mark->marked_by);
        $this->assertNotNull($mark->marked_at);

        $this->stepForm($checkpoint)->call('mark', $mark->id, true);
        $this->assertTrue($mark->refresh()->is_correct);

        $otherMark = $this->open($this->certification('TGHU5550123'))->steps[1]->marks[0];
        $this->stepForm($checkpoint)->call('mark', $otherMark->id, true)->assertNotFound();
    }

    public function test_a_verification_step_without_devices_says_so_and_is_complete(): void
    {
        $checkpoint = $this->open($this->certification(withDevices: false));

        $this->assertTrue($checkpoint->steps[1]->isComplete());
        $this->stepForm($checkpoint)->assertSeeText('Sin dispositivos que verificar.');
    }

    public function test_finishing_needs_every_step_complete_and_every_series_correct(): void
    {
        Storage::fake('local');
        $checkpoint = $this->open($this->certification());
        [$first, $second, $third] = $checkpoint->steps[1]->marks->all();

        $this->stepForm($checkpoint)
            ->call('finish')
            ->assertHasErrors('checkpoint');

        $this->withPhoto($checkpoint)
            ->call('mark', $first->id, true)
            ->call('mark', $second->id, false)
            ->call('mark', $third->id, true)
            ->call('finish')
            ->assertHasErrors('checkpoint')
            ->assertSeeText('Hay series incorrectas: inicia la reinspección.');

        $this->assertFalse($checkpoint->refresh()->isClosed());
    }

    public function test_a_complete_checkpoint_with_every_series_correct_is_closed_with_who_closed_it(): void
    {
        Storage::fake('local');
        $checkpoint = $this->open($this->certification());

        $component = $this->withPhoto($checkpoint)->set('observations', '  Todo en orden.  ');

        $this->assertSame('Todo en orden.', $checkpoint->refresh()->observations);

        foreach ($checkpoint->steps[1]->marks as $mark) {
            $component->call('mark', $mark->id, true);
        }

        $component->call('finish')
            ->assertHasNoErrors()
            ->assertDispatched('toast', icon: 'success', title: 'Checkpoint cerrado')
            ->assertRedirect(route('intranet.checkpoints.pending'));

        $checkpoint->refresh();
        $this->assertTrue($checkpoint->isClosed());
        $this->assertSame($this->inspector->id, $checkpoint->closed_by);

        Livewire::actingAs($this->inspector)->test(UnitPicker::class)->assertSeeText('Con checkpoint pendiente · 0');
    }

    public function test_a_closed_checkpoint_is_not_edited(): void
    {
        $checkpoint = $this->open($this->certification());
        $mark = $checkpoint->steps[1]->marks[0];
        $checkpoint->forceFill(['closed_at' => now(), 'closed_by' => $this->inspector->id])->save();

        $this->stepForm($checkpoint)
            ->assertSeeText('solo lectura')
            ->call('mark', $mark->id, true)
            ->assertDispatched('toast', icon: 'warning', title: 'Checkpoint cerrado');

        $this->assertNull($mark->refresh()->is_correct);
    }

    /**
     * A certification with the two external sections of the setup, closed unless asked, and with three tapes placed
     * in them when asked.
     */
    private function certification(string $container = 'TEMU9795602', bool $closed = true, bool $annulled = false, bool $withDevices = true): Certification
    {
        $certification = Certification::factory()->create([
            'container' => $container,
            'closed_at' => $closed ? now()->subHour() : null,
            'closed_by' => $closed ? $this->inspector->id : null,
            'annulled_at' => $annulled ? now() : null,
        ]);

        $evaporators = $certification->steps()->create(['certification_section_id' => $this->evaporators->id, 'type' => CertificationSectionType::External, 'position' => 1]);
        $fan = $certification->steps()->create(['certification_section_id' => $this->fan->id, 'type' => CertificationSectionType::External, 'position' => 2]);

        if ($withDevices) {
            $prefix = $container === 'TEMU9795602' ? 'WXG' : $container.'-';
            $evaporators->devices()->attach(SecurityDevice::factory()->create(['serial_number' => $prefix.'002'])->id);
            $evaporators->devices()->attach(SecurityDevice::factory()->create(['serial_number' => $prefix.'001'])->id);
            $fan->devices()->attach(SecurityDevice::factory()->create(['serial_number' => $prefix.'003'])->id);
        }

        return $certification;
    }

    /**
     * Open a unit from the list, with a photo of the container when asked, and get its checkpoint.
     */
    private function open(Certification $certification, bool $withPhoto = false): Checkpoint
    {
        $component = Livewire::actingAs($this->inspector)->test(UnitPicker::class);

        if ($withPhoto) {
            $component->set('photo', UploadedFile::fake()->image('contenedor.jpg'))
                ->set('thumbnail', UploadedFile::fake()->image('contenedor-miniatura.jpg'));
        }

        $component->call('open', $certification->id)->assertHasNoErrors();

        $checkpoint = Checkpoint::query()->where('certification_id', $certification->id)->sole();
        $component->assertRedirect(route('intranet.checkpoints.show', $checkpoint));

        return $checkpoint->load('steps.section', 'steps.marks.device');
    }

    private function stepForm(Checkpoint $checkpoint): Testable
    {
        return Livewire::actingAs($this->inspector)->test(StepForm::class, ['checkpoint' => $checkpoint]);
    }

    /**
     * The step form after taking the photo of the photo step.
     */
    private function withPhoto(Checkpoint $checkpoint): Testable
    {
        return $this->stepForm($checkpoint)
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $checkpoint->steps[0]->id, null);
    }
}
