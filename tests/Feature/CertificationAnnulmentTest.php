<?php

namespace Tests\Feature;

use App\Actions\Certifications\FindKitToCertify;
use App\Enums\CertificationKitDecision;
use App\Enums\CertificationStatus;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\Certifications\AnnulModal;
use App\Livewire\Inspections\StepForm;
use App\Livewire\Tables\CertificationsTable;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationStep;
use App\Models\Kit;
use App\Models\SecurityDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * H32: annul the assignment of a kit to its certification, reusing or disabling the kit.
 */
class CertificationAnnulmentTest extends TestCase
{
    use RefreshDatabase;

    private User $inspector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('es');
        $this->inspector = User::factory()->create(['name' => 'Inspector Uno']);
        $this->actingAs($this->inspector);
    }

    public function test_the_list_offers_the_annulment_only_while_the_certification_is_not_closed(): void
    {
        $open = $this->certification();
        $closed = $this->certification('TGHU5550123');
        $closed->update(['closed_at' => now(), 'closed_by' => $this->inspector->id]);

        Livewire::test(CertificationsTable::class)
            ->assertSeeHtml('aria-label="Anular la asignación de ABCU1234567"')
            ->assertDontSeeHtml('aria-label="Anular la asignación de TGHU5550123"');

        Livewire::test(AnnulModal::class)
            ->call('open', $closed->id)
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'warning');

        Livewire::test(AnnulModal::class)
            ->call('open', $open->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.container', 'ABCU1234567');
    }

    public function test_the_reason_and_the_decision_about_the_kit_are_required(): void
    {
        $certification = $this->certification();

        Livewire::test(AnnulModal::class)
            ->call('open', $certification->id)
            ->call('annul')
            ->assertHasErrors(['form.reason' => 'required', 'form.kitDecision' => 'required']);

        $this->assertFalse($certification->refresh()->isAnnulled());
    }

    public function test_reusing_the_kit_annuls_the_certification_and_lets_the_kit_be_assigned_again(): void
    {
        $certification = $this->certification();
        $kit = $certification->kit;

        $this->annul($certification, CertificationKitDecision::Reuse);

        $certification->refresh();
        $this->assertSame(CertificationStatus::Annulled, $certification->status());
        $this->assertSame('La naviera canceló el despacho.', $certification->annulment_reason);
        $this->assertTrue($certification->annuller?->is($this->inspector));
        $this->assertSame(CertificationKitDecision::Reuse, $certification->kit_decision);

        $this->assertSame(KitStatus::Dispatched, $kit->refresh()->status);
        $this->assertNull($kit->certification);
        $this->assertSame(2, $kit->devices()->where('status', SecurityDeviceStatus::InKit->value)->count());
        $this->assertDatabaseCount('certification_step_security_device', 0);
        $this->assertStringNotContainsString('ya está en la certificación', (string) app(FindKitToCertify::class)->rejectionReason($kit, null));

        Certification::factory()->create(['container' => 'TGHU5550123', 'kit_id' => $kit->id]);
        $this->assertSame('TGHU5550123', $kit->refresh()->certification?->container);
    }

    public function test_disabling_the_kit_discards_all_its_devices(): void
    {
        $certification = $this->certification();
        $kit = $certification->kit;

        $this->annul($certification, CertificationKitDecision::Disable);

        $this->assertSame(KitStatus::Disabled, $kit->refresh()->status);
        $this->assertSame(
            [SecurityDeviceStatus::Discarded->value],
            $kit->devices()->distinct()->pluck('status')->map->value->all(),
        );
        $this->assertSame('Asignación anulada: La naviera canceló el despacho.', $kit->devices()->value('discard_reason'));
        $this->assertSame('El kit '.$kit->pouch_code.' está Inhabilitado y todavía no se despachó.', app(FindKitToCertify::class)->rejectionReason($kit, null));
    }

    public function test_an_annulled_certification_is_shown_as_such_and_can_neither_be_edited_nor_closed(): void
    {
        $certification = $this->certification();
        $this->annul($certification, CertificationKitDecision::Reuse);

        Livewire::test(CertificationsTable::class)
            ->set('filterComponents.status', CertificationStatus::Annulled->value)
            ->assertSeeText('ABCU1234567')
            ->assertSeeText('Anulada')
            ->assertDontSeeHtml('aria-label="Anular la asignación de ABCU1234567"')
            ->call('close', $certification->id)
            ->assertDispatched('toast', icon: 'warning');

        $this->get(route('intranet.certifications.show', $certification))
            ->assertOk()
            ->assertSeeText('Asignación anulada')
            ->assertSeeText('La naviera canceló el despacho.')
            ->assertSeeText('Reutilizar el kit');

        $step = $certification->steps()->firstOrFail();
        Livewire::test(StepForm::class, ['certification' => $certification, 'type' => $step->type])
            ->call('deletePhoto', $step->id, $step->photos()->value('id'))
            ->assertDispatched('toast', icon: 'warning', title: 'Asignación anulada');

        $this->assertSame(1, $step->photos()->count());
    }

    private function annul(Certification $certification, CertificationKitDecision $decision): void
    {
        Livewire::test(AnnulModal::class)
            ->call('open', $certification->id)
            ->set('form.reason', ' La naviera canceló el despacho. ')
            ->set('form.kitDecision', $decision->value)
            ->call('annul')
            ->assertHasNoErrors()
            ->assertDispatched('toast', icon: 'success', title: 'Asignación anulada');
    }

    /**
     * Create a certification in progress, with a dispatched kit of two devices placed in its internal section, which
     * has one photo.
     */
    private function certification(string $container = 'ABCU1234567'): Certification
    {
        $kit = Kit::factory()->create(['status' => KitStatus::Dispatched]);
        $devices = SecurityDevice::factory()->count(2)->create(['status' => SecurityDeviceStatus::InKit]);
        $kit->devices()->attach($devices->modelKeys());
        $certification = Certification::factory()->create(['container' => $container, 'kit_id' => $kit->id]);

        $internal = CertificationStep::factory()->create(['certification_id' => $certification->id, 'position' => 1]);
        CertificationStep::factory()->external()->create(['certification_id' => $certification->id, 'position' => 2]);
        $internal->devices()->attach($devices->modelKeys());
        CertificationPhoto::factory()->create(['certification_step_id' => $internal->id, 'uploaded_by' => $this->inspector->id]);

        return $certification;
    }
}
