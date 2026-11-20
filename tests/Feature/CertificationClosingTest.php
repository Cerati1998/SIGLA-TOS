<?php

namespace Tests\Feature;

use App\Enums\CertificationStatus;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\Certifications\ReopenModal;
use App\Livewire\Inspections\StepForm;
use App\Livewire\Tables\CertificationsTable;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationReopening;
use App\Models\CertificationStep;
use App\Models\Kit;
use App\Models\SecurityDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CertificationClosingTest extends TestCase
{
    use RefreshDatabase;

    private User $inspector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('es');
        $this->inspector = User::factory()->create(['name' => 'Inspector Uno']);
    }

    public function test_the_panel_has_the_card_of_the_list_of_assignments_with_how_many_are_ready_to_close(): void
    {
        $this->certification(internalDone: 1, externalDone: 1);
        $this->certification(internalDone: 0, externalDone: 1, container: 'TGHU5550123');

        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.index'))
            ->assertOk()
            ->assertSeeInOrder(['Asignación', 'Inspección interna', 'Inspección externa', 'Asignaciones', '1 lista para cerrar'])
            ->assertSee(route('intranet.certifications.list'));
    }

    public function test_the_list_shows_each_unit_with_its_status_its_progress_and_the_close_button_only_when_both_inspections_are_complete(): void
    {
        $ready = $this->certification(internalDone: 1, externalDone: 1);
        $inProgress = $this->certification(internalDone: 0, externalDone: 1, container: 'TGHU5550123');

        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.list'))
            ->assertOk()
            ->assertSeeLivewire(CertificationsTable::class)
            ->assertSeeText('Nueva asignación');

        Livewire::actingAs($this->inspector)->test(CertificationsTable::class)
            ->assertSeeText('ABCU1234567')
            ->assertSeeText('Lista para cerrar')
            ->assertSeeText('A medias')
            ->assertSeeText('Interna 0/1')
            ->assertSeeText('Externa 1/1')
            ->assertSeeHtml('id: '.$ready->id.',')
            ->assertSeeHtml('aria-label="Cerrar la certificación de ABCU1234567"')
            ->assertSeeHtml('aria-label="Cerrar, no disponible: faltan fotos en la interna"')
            ->assertSeeHtml(route('intranet.certifications.show', $inProgress));
    }

    public function test_closing_keeps_who_closed_it_and_turns_the_kit_and_its_devices_into_used(): void
    {
        $certification = $this->certification(internalDone: 1, externalDone: 1);

        Livewire::actingAs($this->inspector)->test(CertificationsTable::class)
            ->call('close', $certification->id)
            ->assertDispatched('toast', icon: 'success', title: 'Certificación cerrada')
            ->assertSeeText('Cerrada')
            ->assertSeeHtml('title="Reaperturar"');

        $certification->refresh();
        $this->assertNotNull($certification->closed_at);
        $this->assertTrue($this->inspector->is($certification->closer));
        $this->assertSame(KitStatus::Used, $certification->kit->status);
        $this->assertSame([SecurityDeviceStatus::Used], $certification->kit->devices->pluck('status')->unique()->values()->all());
    }

    public function test_a_certification_with_a_section_without_a_photo_is_not_closed(): void
    {
        $certification = $this->certification(internalDone: 0, externalDone: 1);

        Livewire::actingAs($this->inspector)->test(CertificationsTable::class)
            ->call('close', $certification->id)
            ->assertDispatched('toast', icon: 'warning', title: 'No se puede cerrar');

        $certification->refresh();
        $this->assertNull($certification->closed_at);
        $this->assertSame(KitStatus::Dispatched, $certification->kit->status);
    }

    public function test_reopening_asks_for_a_reason_and_keeps_who_reopened_it_while_the_kit_stays_used(): void
    {
        $certification = $this->certification(internalDone: 1, externalDone: 1);
        Livewire::actingAs($this->inspector)->test(CertificationsTable::class)->call('close', $certification->id);
        $supervisor = User::factory()->create(['name' => 'Supervisor Dos']);

        Livewire::actingAs($supervisor)->test(ReopenModal::class)
            ->dispatch('reopen-certification', certificationId: $certification->id)
            ->assertSet('form.openModal', true)
            ->set('form.reason', '  ')
            ->call('reopen')
            ->assertHasErrors(['form.reason' => 'required'])
            ->set('form.reason', ' Foto del sello borrosa ')
            ->call('reopen')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('refreshDatatable');

        $certification->refresh();
        $reopening = CertificationReopening::query()->sole();
        $this->assertNull($certification->closed_at);
        $this->assertSame(CertificationStatus::Reopened, $certification->status());
        $this->assertSame(['Foto del sello borrosa', $supervisor->id, $this->inspector->id], [$reopening->reason, $reopening->reopened_by, $reopening->closed_by]);
        $this->assertSame(KitStatus::Used, $certification->kit->status);

        Livewire::actingAs($this->inspector)->test(CertificationsTable::class)
            ->assertSeeText('Reaperturada')
            ->assertSeeHtml('title="Cerrar de nuevo"')
            ->call('close', $certification->id)
            ->assertDispatched('toast', icon: 'success');

        $this->assertSame(CertificationStatus::Closed, $certification->refresh()->status());
    }

    public function test_only_a_closed_certification_is_reopened(): void
    {
        $certification = $this->certification(internalDone: 1, externalDone: 1);

        Livewire::actingAs($this->inspector)->test(ReopenModal::class)
            ->dispatch('reopen-certification', certificationId: $certification->id)
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'warning');
    }

    public function test_the_detail_shows_who_reopened_it_when_and_why(): void
    {
        $certification = $this->certification(internalDone: 1, externalDone: 1);
        CertificationReopening::factory()->create([
            'certification_id' => $certification->id,
            'reason' => 'Foto del sello borrosa',
            'reopened_by' => User::factory()->create(['name' => 'Supervisor Dos'])->id,
        ]);

        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.show', $certification))
            ->assertOk()
            ->assertSeeInOrder(['Reaperturada', 'Supervisor Dos', 'Motivo: «Foto del sello borrosa»']);
    }

    public function test_a_closed_certification_cannot_get_or_lose_photos(): void
    {
        Storage::fake('local');
        $certification = $this->certification(internalDone: 1, externalDone: 1);
        $certification->update(['closed_at' => now(), 'closed_by' => $this->inspector->id]);
        $step = $certification->steps()->where('type', 'internal')->sole();
        $photo = $step->photos()->sole();

        Livewire::actingAs($this->inspector)->test(StepForm::class, ['certification' => $certification, 'type' => $step->type])
            ->assertSeeText('Certificación cerrada: solo lectura')
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('savePhoto', $step->id, null)
            ->assertReturned(false)
            ->call('deletePhoto', $step->id, $photo->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Certificación cerrada');

        $this->assertSame([$photo->id], $step->photos()->pluck('id')->all());
    }

    /**
     * A certification of a dispatched kit with two devices, one internal and one external section, and the given
     * number of those sections with a photo.
     */
    private function certification(int $internalDone, int $externalDone, string $container = 'ABCU1234567'): Certification
    {
        $kit = Kit::factory()->create(['status' => KitStatus::Dispatched]);
        $kit->devices()->attach(SecurityDevice::factory()->count(2)->create(['status' => SecurityDeviceStatus::InKit])->modelKeys());
        $certification = Certification::factory()->create(['container' => $container, 'kit_id' => $kit->id]);

        $internal = CertificationStep::factory()->create(['certification_id' => $certification->id, 'position' => 1]);
        $external = CertificationStep::factory()->external()->create(['certification_id' => $certification->id, 'position' => 2]);

        CertificationPhoto::factory()->count($internalDone)->create(['certification_step_id' => $internal->id, 'uploaded_by' => $this->inspector->id]);
        CertificationPhoto::factory()->count($externalDone)->create(['certification_step_id' => $external->id, 'uploaded_by' => $this->inspector->id]);

        return $certification;
    }
}
