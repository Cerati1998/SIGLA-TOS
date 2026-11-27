<?php

namespace Tests\Feature;

use App\Actions\Certifications\DistributeKitDevices;
use App\Enums\CertificationSectionType;
use App\Enums\CheckpointSectionKind;
use App\Livewire\CertificationSections\CreateModal;
use App\Livewire\CertificationSections\EditModal;
use App\Livewire\Tables\CertificationSectionsTable;
use App\Models\CertificationSection;
use App\Models\Kit;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Database\Seeders\CertificationSectionCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * H33 · Secciones de checkpoint.
 */
class CheckpointSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');
    }

    public function test_a_checkpoint_section_of_photos_goes_at_the_end_and_carries_no_devices(): void
    {
        $this->section('Evaporadores cerrados', 1, CertificationSectionType::External);
        $deviceType = SecurityDeviceType::factory()->create();

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Placa, cédula y transporte')
            ->set('form.type', 'checkpoint')
            ->set('form.checkpointKind', 'photos')
            ->set('form.devices', [['id' => $deviceType->id, 'quantity' => 2]])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', icon: 'success', title: 'Sección creada');

        $section = CertificationSection::query()->where('type', CertificationSectionType::Checkpoint)->sole();

        $this->assertSame(CheckpointSectionKind::Photos, $section->checkpoint_kind);
        $this->assertSame(2, $section->sort_order);
        $this->assertCount(0, $section->securityDeviceTypes);
        $this->assertCount(0, $section->verifiedSections);
    }

    public function test_a_verification_is_linked_to_internal_and_external_sections_and_the_list_shows_them(): void
    {
        $evaporators = $this->section('Evaporadores cerrados', 1, CertificationSectionType::External);
        $floorKit = $this->section('Kit de precintos en piso', 2, CertificationSectionType::Internal);

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Verificación de cintas')
            ->set('form.type', 'checkpoint')
            ->set('form.checkpointKind', 'verification')
            ->set('form.verifiedSectionIds', [$evaporators->id, $floorKit->id])
            ->call('save')
            ->assertHasNoErrors();

        $verification = CertificationSection::query()->where('name', 'Verificación de cintas')->sole();

        $this->assertSame(CheckpointSectionKind::Verification, $verification->checkpoint_kind);
        $this->assertEqualsCanonicalizing([$evaporators->id, $floorKit->id], $verification->verifiedSections->modelKeys());

        Livewire::test(CertificationSectionsTable::class)
            ->set('filterComponents.type', 'checkpoint')
            ->assertSeeText('Verificación de cintas')
            ->assertSeeText('Verifica: Evaporadores cerrados, Kit de precintos en piso');
    }

    public function test_a_checkpoint_section_needs_what_it_asks_for_and_a_verification_at_least_one_section(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.name', 'Paso sin clase')
            ->set('form.type', 'checkpoint')
            ->call('save')
            ->assertHasErrors(['form.checkpointKind' => 'required'])
            ->assertSeeText('Elige si el paso pide fotos o verifica series.');

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Verificación vacía')
            ->set('form.type', 'checkpoint')
            ->set('form.checkpointKind', 'verification')
            ->call('save')
            ->assertHasErrors(['form.verifiedSectionIds' => 'required'])
            ->assertSeeText('Elige al menos una sección que este paso verifique.');

        $this->assertDatabaseCount('certification_sections', 0);
    }

    public function test_a_verification_only_links_active_internal_or_external_sections(): void
    {
        $otherStep = CertificationSection::factory()->checkpoint()->create(['name' => 'Otro paso', 'sort_order' => 1]);
        $deactivated = CertificationSection::factory()->deactivated()->create(['name' => 'Sección antigua']);

        foreach ([$otherStep, $deactivated] as $notVerifiable) {
            Livewire::test(CreateModal::class)
                ->set('form.name', 'Verificación')
                ->set('form.type', 'checkpoint')
                ->set('form.checkpointKind', 'verification')
                ->set('form.verifiedSectionIds', [$notVerifiable->id])
                ->call('save')
                ->assertHasErrors(['form.verifiedSectionIds'])
                ->assertSeeText('Elige solo secciones internas o externas activas.');
        }

        $this->assertDatabaseCount('certification_section_verifications', 0);
    }

    public function test_a_deactivated_section_stays_linked_until_the_admin_removes_it(): void
    {
        $evaporators = $this->section('Evaporadores cerrados', 1, CertificationSectionType::External);
        $fan = $this->section('Ventilador', 2, CertificationSectionType::External);
        $verification = CertificationSection::factory()->checkpoint([$evaporators, $fan])->create(['name' => 'Verificación de cintas', 'sort_order' => 3]);

        Livewire::test(CertificationSectionsTable::class)->call('deactivate', $fan->id);

        Livewire::test(CertificationSectionsTable::class)
            ->set('filterComponents.type', 'checkpoint')
            ->assertSeeText('Verifica: Evaporadores cerrados, Ventilador (desactivada)');

        Livewire::test(EditModal::class)
            ->dispatch('edit-certification-section', certificationSectionId: $verification->id)
            ->assertSet('form.checkpointKind', 'verification')
            ->assertSet('form.verifiedSectionIds', [$evaporators->id, $fan->id])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEqualsCanonicalizing([$evaporators->id, $fan->id], $verification->refresh()->verifiedSections->modelKeys());

        Livewire::test(EditModal::class)
            ->dispatch('edit-certification-section', certificationSectionId: $verification->id)
            ->set('form.verifiedSectionIds', [$evaporators->id])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame([$evaporators->id], $verification->refresh()->verifiedSections->modelKeys());
    }

    public function test_a_name_cannot_be_repeated_among_checkpoint_sections_but_can_with_another_type(): void
    {
        $this->section('Panorámica de puertas', 1, CertificationSectionType::External);
        CertificationSection::factory()->checkpoint()->create(['name' => 'Sello cable', 'sort_order' => 2]);

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Sello cable')
            ->set('form.type', 'checkpoint')
            ->set('form.checkpointKind', 'photos')
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique'])
            ->assertSeeText('Ya existe una sección de checkpoint con este nombre, aunque esté desactivada.');

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Panorámica de puertas')
            ->set('form.type', 'checkpoint')
            ->set('form.checkpointKind', 'photos')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_a_section_a_verification_uses_cannot_become_a_checkpoint_section(): void
    {
        $evaporators = $this->section('Evaporadores cerrados', 1, CertificationSectionType::External);
        CertificationSection::factory()->checkpoint([$evaporators])->create(['sort_order' => 2]);

        Livewire::test(EditModal::class)
            ->dispatch('edit-certification-section', certificationSectionId: $evaporators->id)
            ->set('form.type', 'checkpoint')
            ->set('form.checkpointKind', 'photos')
            ->call('save')
            ->assertHasErrors(['form.type'])
            ->assertSeeText('Un paso del checkpoint verifica esta sección: quítala de ese paso antes de cambiarla a Checkpoint.');

        $this->assertSame(CertificationSectionType::External, $evaporators->refresh()->type);
    }

    public function test_turning_a_checkpoint_section_into_an_internal_one_drops_its_links(): void
    {
        $evaporators = $this->section('Evaporadores cerrados', 1, CertificationSectionType::External);
        $verification = CertificationSection::factory()->checkpoint([$evaporators])->create(['sort_order' => 2]);

        Livewire::test(EditModal::class)
            ->dispatch('edit-certification-section', certificationSectionId: $verification->id)
            ->set('form.type', 'internal')
            ->call('save')
            ->assertHasNoErrors();

        $verification->refresh();

        $this->assertSame(CertificationSectionType::Internal, $verification->type);
        $this->assertNull($verification->checkpoint_kind);
        $this->assertCount(0, $verification->verifiedSections);
    }

    public function test_checkpoint_sections_stay_out_of_the_certification(): void
    {
        $internal = $this->section('Piso', 1, CertificationSectionType::Internal);
        $external = $this->section('Techo', 2, CertificationSectionType::External);
        CertificationSection::factory()->checkpoint([$external])->create(['sort_order' => 3]);

        $steps = app(DistributeKitDevices::class)->handle(Kit::factory()->create())['steps'];

        $this->assertSame([$internal->id, $external->id], array_map(fn (array $step): int => $step['section']->id, $steps));

        $this->get(route('intranet.inspections.index', 'internal'))->assertOk();
        $this->get('/intranet/inspections/checkpoint')->assertNotFound();
    }

    public function test_the_catalog_loads_the_agreed_sections_once_and_keeps_the_existing_ones(): void
    {
        $existing = $this->section('Techo', 7, CertificationSectionType::External);

        app(CertificationSectionCatalogSeeder::class)->run();
        app(CertificationSectionCatalogSeeder::class)->run();

        $this->assertSame(17, CertificationSection::query()->where('type', CertificationSectionType::Internal)->count());
        $this->assertSame(16, CertificationSection::query()->where('type', CertificationSectionType::External)->count());
        $this->assertSame(12, CertificationSection::query()->where('type', CertificationSectionType::Checkpoint)->count());
        $this->assertSame(7, $existing->refresh()->sort_order);

        $verification = CertificationSection::query()->where('name', 'Verificación de cintas de evaporadores')->sole();

        $this->assertSame(CheckpointSectionKind::Verification, $verification->checkpoint_kind);
        $this->assertSame(['Evaporadores cerrados', 'Ventilador'], $verification->verifiedSections->pluck('name')->sort()->values()->all());
        $this->assertSame(CheckpointSectionKind::Photos, CertificationSection::query()->where('name', 'Sello cable')->sole()->checkpoint_kind);
        $this->assertDatabaseCount('certification_section_verifications', 4);
        $this->assertSame(['Verificación de cinta de puerta'], CertificationSection::query()->where('skips_reinspection', true)->pluck('name')->all());
    }

    /**
     * Create an active section at the given place of the order.
     */
    private function section(string $name, int $sortOrder, CertificationSectionType $type): CertificationSection
    {
        return CertificationSection::factory()->create(['name' => $name, 'sort_order' => $sortOrder, 'type' => $type]);
    }
}
