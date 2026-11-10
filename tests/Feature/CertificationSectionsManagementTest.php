<?php

namespace Tests\Feature;

use App\Enums\CertificationSectionType;
use App\Livewire\CertificationSections\CreateModal;
use App\Livewire\CertificationSections\EditModal;
use App\Livewire\CertificationSections\OrderModal;
use App\Livewire\Tables\CertificationSectionsTable;
use App\Models\CertificationSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CertificationSectionsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.certification-sections.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_in_order_with_the_deactivated_sections_last(): void
    {
        $this->section('Segunda Sección', 2, CertificationSectionType::Internal);
        $this->section('Primera Sección', 1, CertificationSectionType::External);
        CertificationSection::factory()->deactivated()->create(['name' => 'Sección Antigua']);

        $this->get(route('intranet.certification-sections.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Tipos de dispositivo', 'Secciones de certificación', 'Configuración'])
            ->assertSeeTextInOrder(['Primera Sección', 'Externa', 'Activa', 'Segunda Sección', 'Interna', 'Sección Antigua', 'Desactivada']);
    }

    public function test_an_empty_list_invites_to_create_the_first_section(): void
    {
        Livewire::test(CertificationSectionsTable::class)
            ->assertSeeText('Todavía no hay secciones. Crea la primera con «Nueva sección».');

        $this->section('Evaporadores', 1);

        Livewire::test(CertificationSectionsTable::class)
            ->set('search', 'No existe')
            ->assertSeeText('No encontramos secciones con estos filtros.');
    }

    public function test_the_list_filters_by_name_type_and_status(): void
    {
        $this->section('Evaporadores', 1, CertificationSectionType::Internal);
        $this->section('Techo', 2, CertificationSectionType::External);
        CertificationSection::factory()->deactivated()->create(['name' => 'Piso Antiguo', 'type' => CertificationSectionType::Internal]);

        Livewire::test(CertificationSectionsTable::class)
            ->set('search', 'Evapo')
            ->assertSeeText('Evaporadores')
            ->assertDontSeeText('Techo');

        Livewire::test(CertificationSectionsTable::class)
            ->set('filterComponents.type', 'external')
            ->assertSeeText('Techo')
            ->assertDontSeeText('Evaporadores');

        Livewire::test(CertificationSectionsTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Piso Antiguo')
            ->assertDontSeeText('Evaporadores');
    }

    public function test_a_new_section_goes_at_the_end_of_the_order(): void
    {
        $this->section('Primera Sección', 1);

        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.name', ' Fotografía de evaporadores ')
            ->set('form.type', 'internal')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Sección creada')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('certification_sections', ['name' => 'Fotografía de evaporadores', 'type' => 'internal', 'sort_order' => 2]);
    }

    public function test_the_name_and_type_are_required(): void
    {
        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.name' => 'required', 'form.type' => 'required'])
            ->assertSeeText('El campo nombre es obligatorio.')
            ->assertSeeText('Elige si la sección es interna o externa.');

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Sección')
            ->set('form.type', 'doors')
            ->call('save')
            ->assertHasErrors(['form.type']);

        $this->assertDatabaseCount('certification_sections', 0);
    }

    public function test_a_name_cannot_be_repeated_even_if_the_other_section_is_deactivated(): void
    {
        CertificationSection::factory()->deactivated()->create(['name' => 'Evaporadores']);

        Livewire::test(CreateModal::class)
            ->set('form.name', ' Evaporadores ')
            ->set('form.type', 'internal')
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique'])
            ->assertSeeText('Ya existe una sección con este nombre, aunque esté desactivada.');

        $this->assertDatabaseCount('certification_sections', 1);
    }

    public function test_the_name_and_type_of_a_section_can_be_edited_without_changing_its_place(): void
    {
        $section = $this->section('Nombre Anterior', 3, CertificationSectionType::Internal);

        Livewire::test(EditModal::class)
            ->dispatch('edit-certification-section', certificationSectionId: $section->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.name', 'Nombre Anterior')
            ->assertSet('form.type', 'internal')
            ->set('form.name', 'Nombre Nuevo')
            ->set('form.type', 'external')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Sección actualizada');

        $section->refresh();

        $this->assertSame('Nombre Nuevo', $section->name);
        $this->assertSame(CertificationSectionType::External, $section->type);
        $this->assertSame(3, $section->sort_order);
    }

    public function test_deactivating_takes_the_section_out_of_the_order_and_reactivating_puts_it_last(): void
    {
        $first = $this->section('Primera', 1);
        $second = $this->section('Segunda', 2);
        $third = $this->section('Tercera', 3);

        Livewire::test(CertificationSectionsTable::class)
            ->call('deactivate', $first->id)
            ->assertDispatched('toast', icon: 'success', title: 'Sección desactivada');

        $this->assertSoftDeleted($first);
        $this->assertNull($first->refresh()->sort_order);
        $this->assertSame([1, 2], [$second->refresh()->sort_order, $third->refresh()->sort_order]);

        Livewire::test(CertificationSectionsTable::class)
            ->call('reactivate', $first->id)
            ->assertDispatched('toast', icon: 'success', title: 'Sección reactivada');

        $this->assertNotSoftDeleted($first);
        $this->assertSame(3, $first->refresh()->sort_order);
    }

    public function test_dragging_a_section_saves_the_new_order_at_once(): void
    {
        $first = $this->section('Primera', 1);
        $second = $this->section('Segunda', 2);
        $third = $this->section('Tercera', 3);

        Livewire::test(OrderModal::class)
            ->assertSeeTextInOrder(['Primera', 'Segunda', 'Tercera'])
            ->call('sort', $third->id, 0)
            ->assertDispatched('refreshDatatable')
            ->assertSeeTextInOrder(['Tercera', 'Primera', 'Segunda']);

        $this->assertSame([2, 3, 1], [$first->refresh()->sort_order, $second->refresh()->sort_order, $third->refresh()->sort_order]);
    }

    public function test_the_up_and_down_buttons_move_a_section_one_place_and_stop_at_the_ends(): void
    {
        $first = $this->section('Primera', 1);
        $second = $this->section('Segunda', 2);

        Livewire::test(OrderModal::class)
            ->call('move', $second->id, -1)
            ->call('move', $second->id, -1);

        $this->assertSame([2, 1], [$first->refresh()->sort_order, $second->refresh()->sort_order]);

        Livewire::test(OrderModal::class)->call('move', $first->id, 1);

        $this->assertSame([2, 1], [$first->refresh()->sort_order, $second->refresh()->sort_order]);
    }

    public function test_the_order_only_lists_and_moves_active_sections(): void
    {
        $this->section('Activa', 1);
        $deactivated = CertificationSection::factory()->deactivated()->create(['name' => 'Desactivada']);

        Livewire::test(OrderModal::class)
            ->assertSeeText('Activa')
            ->assertDontSeeText('Desactivada')
            ->call('sort', $deactivated->id, 0)
            ->assertNotFound();

        $this->assertNull($deactivated->refresh()->sort_order);
    }

    /**
     * Create an active section at the given place of the order.
     */
    private function section(string $name, int $sortOrder, ?CertificationSectionType $type = null): CertificationSection
    {
        return CertificationSection::factory()->create([
            'name' => $name,
            'sort_order' => $sortOrder,
            'type' => $type ?? CertificationSectionType::Internal,
        ]);
    }
}
