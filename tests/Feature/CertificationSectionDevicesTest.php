<?php

namespace Tests\Feature;

use App\Enums\CertificationSectionType;
use App\Enums\SecurityDeviceKind;
use App\Livewire\CertificationSections\CreateModal;
use App\Livewire\CertificationSections\EditModal;
use App\Livewire\Tables\CertificationSectionsTable;
use App\Models\CertificationSection;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CertificationSectionDevicesTest extends TestCase
{
    use RefreshDatabase;

    private SecurityDeviceType $seal;

    private SecurityDeviceType $tape;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');

        $this->seal = SecurityDeviceType::factory()->create(['name' => 'Sello botella', 'kind' => SecurityDeviceKind::Seal]);
        $this->tape = SecurityDeviceType::factory()->create(['name' => 'Cinta interna', 'kind' => SecurityDeviceKind::Tape]);
    }

    public function test_the_list_shows_what_each_section_carries_or_that_it_only_asks_for_a_photo(): void
    {
        CertificationSection::factory()->carrying($this->seal, 2)->carrying($this->tape, 1)->create(['name' => 'Fotografía de evaporadores']);
        CertificationSection::factory()->create(['name' => 'Placa de datos']);

        Livewire::test(CertificationSectionsTable::class)
            ->assertSeeText('Lleva')
            ->assertSeeInOrder(['Fotografía de evaporadores', '1', 'Cinta interna', '2', 'Sello botella'])
            ->assertSeeInOrder(['Placa de datos', 'Solo foto']);
    }

    public function test_a_new_section_is_created_with_the_device_types_it_carries(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.name', 'Fotografía de evaporadores')
            ->set('form.type', CertificationSectionType::Internal->value)
            ->set('form.devices', [['id' => $this->seal->id, 'quantity' => '2'], ['id' => $this->tape->id, 'quantity' => 1]])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Sección creada');

        $this->assertEquals([$this->seal->id => 2, $this->tape->id => 1], $this->carried(CertificationSection::query()->sole()));
    }

    public function test_a_section_can_carry_no_device_and_only_ask_for_a_photo(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.name', 'Placa de datos')
            ->set('form.type', CertificationSectionType::Internal->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, CertificationSection::query()->count());
        $this->assertDatabaseCount('certification_section_security_device_type', 0);
    }

    public function test_only_active_device_types_are_offered_and_accepted(): void
    {
        $deactivated = SecurityDeviceType::factory()->trashed()->create(['name' => 'Sello antiguo']);

        Livewire::test(CreateModal::class)
            ->assertViewHas('deviceTypes', fn (array $options): bool => array_column($options, 'name') === ['Cinta interna', 'Sello botella'])
            ->set('form.name', 'Puertas')
            ->set('form.type', CertificationSectionType::External->value)
            ->set('form.devices', [['id' => $deactivated->id, 'quantity' => 1]])
            ->call('save')
            ->assertHasErrors(['form.devices'])
            ->assertSeeText('Elige solo tipos de dispositivo activos.');

        $this->assertDatabaseCount('certification_sections', 0);
    }

    public function test_a_device_type_goes_once_and_its_quantity_is_a_whole_number_greater_than_zero(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.name', 'Puertas')
            ->set('form.type', CertificationSectionType::External->value)
            ->set('form.devices', [['id' => $this->seal->id, 'quantity' => 1], ['id' => $this->seal->id, 'quantity' => 2]])
            ->call('save')
            ->assertHasErrors(['form.devices.0.id' => 'distinct'])
            ->assertSeeText('Un tipo de dispositivo va una sola vez en la sección: cambia su cantidad.');

        foreach (['0', '-1', '2.5', ''] as $quantity) {
            Livewire::test(CreateModal::class)
                ->set('form.name', 'Puertas')
                ->set('form.type', CertificationSectionType::External->value)
                ->set('form.devices', [['id' => $this->seal->id, 'quantity' => $quantity]])
                ->call('save')
                ->assertHasErrors(['form.devices.0.quantity'])
                ->assertSeeText('Escribe una cantidad entera mayor que cero.');
        }

        $this->assertDatabaseCount('certification_sections', 0);
    }

    public function test_editing_a_section_changes_its_quantities_and_removes_or_adds_device_types(): void
    {
        $section = CertificationSection::factory()->carrying($this->seal, 2)->carrying($this->tape, 1)->create(['name' => 'Fotografía de evaporadores']);
        $cable = SecurityDeviceType::factory()->create(['name' => 'Sello cable']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-certification-section', certificationSectionId: $section->id)
            ->assertSet('form.devices', [['id' => $this->tape->id, 'quantity' => 1], ['id' => $this->seal->id, 'quantity' => 2]])
            ->set('form.devices', [['id' => $this->seal->id, 'quantity' => 3], ['id' => $cable->id, 'quantity' => 1]])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', icon: 'success', title: 'Sección actualizada');

        $this->assertEquals([$this->seal->id => 3, $cable->id => 1], $this->carried($section));
    }

    public function test_a_deactivated_device_type_is_kept_until_it_is_removed(): void
    {
        $plastic = SecurityDeviceType::factory()->create(['name' => 'Sello plástico']);
        $section = CertificationSection::factory()->carrying($plastic, 2)->create();
        $plastic->delete();

        Livewire::test(EditModal::class)
            ->dispatch('edit-certification-section', certificationSectionId: $section->id)
            ->assertViewHas('deviceTypes', fn (array $options): bool => in_array(['id' => $plastic->id, 'name' => 'Sello plástico', 'kind' => $plastic->kind->label(), 'trashed' => true], $options, true))
            ->set('form.name', 'Sección renombrada')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals([$plastic->id => 2], $this->carried($section));

        Livewire::test(EditModal::class)
            ->dispatch('edit-certification-section', certificationSectionId: $section->id)
            ->set('form.devices', [])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame([], $this->carried($section));

        Livewire::test(EditModal::class)
            ->dispatch('edit-certification-section', certificationSectionId: $section->id)
            ->assertViewHas('deviceTypes', fn (array $options): bool => ! in_array('Sello plástico', array_column($options, 'name'), true))
            ->set('form.devices', [['id' => $plastic->id, 'quantity' => 1]])
            ->call('save')
            ->assertHasErrors(['form.devices']);
    }

    /**
     * Get what the given section carries, as security device type id => quantity.
     *
     * @return array<int, int>
     */
    private function carried(CertificationSection $section): array
    {
        return $section->securityDeviceTypes()->get()
            ->mapWithKeys(fn (SecurityDeviceType $deviceType): array => [$deviceType->id => (int) $deviceType->getRelationValue('pivot')->getAttribute('quantity')])
            ->all();
    }
}
