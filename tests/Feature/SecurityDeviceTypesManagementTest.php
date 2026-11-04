<?php

namespace Tests\Feature;

use App\Enums\SecurityDeviceKind;
use App\Livewire\SecurityDeviceTypes\CreateModal;
use App\Livewire\SecurityDeviceTypes\EditModal;
use App\Livewire\Tables\SecurityDeviceTypesTable;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityDeviceTypesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.security-device-types.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_active_and_deactivated_types(): void
    {
        SecurityDeviceType::factory()->create(['name' => 'Sello Vigente', 'kind' => SecurityDeviceKind::ContainerSeal]);
        SecurityDeviceType::factory()->trashed()->create(['name' => 'Sello Antiguo']);

        $this->get(route('intranet.security-device-types.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Líneas navieras', 'Tipos de dispositivo', 'Configuración'])
            ->assertSee(route('intranet.security-device-types.index'))
            ->assertSeeText('Sello Vigente')
            ->assertSeeText('Precinto')
            ->assertSeeText('Sello Antiguo')
            ->assertSeeText('Activo')
            ->assertSeeText('Inactivo');
    }

    public function test_each_row_receives_its_type_id(): void
    {
        $securityDeviceType = SecurityDeviceType::factory()->create();

        Livewire::test(SecurityDeviceTypesTable::class)->assertSeeHtml("id: {$securityDeviceType->id},");
    }

    public function test_the_list_is_ordered_by_name(): void
    {
        SecurityDeviceType::factory()->create(['name' => 'Zeta Sello']);
        SecurityDeviceType::factory()->create(['name' => 'Alfa Sello']);

        Livewire::test(SecurityDeviceTypesTable::class)->assertSeeInOrder(['Alfa Sello', 'Zeta Sello']);
    }

    public function test_the_list_filters_by_search_and_status(): void
    {
        SecurityDeviceType::factory()->create(['name' => 'Sello Vigente']);
        SecurityDeviceType::factory()->trashed()->create(['name' => 'Cinta Antigua']);

        Livewire::test(SecurityDeviceTypesTable::class)
            ->set('search', 'Antigua')
            ->assertSeeText('Cinta Antigua')
            ->assertDontSeeText('Sello Vigente');

        Livewire::test(SecurityDeviceTypesTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Cinta Antigua')
            ->assertDontSeeText('Sello Vigente');
    }

    public function test_a_type_can_be_created_with_each_kind(): void
    {
        foreach (SecurityDeviceKind::cases() as $kind) {
            Livewire::test(CreateModal::class)
                ->set('form.openModal', true)
                ->set('form.name', " Tipo {$kind->value} ")
                ->set('form.kind', $kind->value)
                ->call('save')
                ->assertHasNoErrors()
                ->assertSet('form.openModal', false)
                ->assertDispatched('toast', icon: 'success', title: 'Tipo de dispositivo creado')
                ->assertDispatched('refreshDatatable');

            $this->assertDatabaseHas('security_device_types', ['name' => "Tipo {$kind->value}", 'kind' => $kind->value]);
        }
    }

    public function test_the_create_modal_offers_the_kinds_in_spanish(): void
    {
        Livewire::test(CreateModal::class)
            ->assertSeeInOrder(['Sello', 'Precinto', 'Cinta', 'Otro']);
    }

    public function test_the_fields_of_a_type_are_validated(): void
    {
        $this->app->setLocale('es');

        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.name' => 'required', 'form.kind' => 'required'])
            ->assertSeeText('El campo nombre es obligatorio.')
            ->assertSeeText('El campo tipo es obligatorio.')
            ->assertNotDispatched('toast');

        Livewire::test(CreateModal::class)
            ->set('form.name', str_repeat('A', 101))
            ->set('form.kind', 'no-existe')
            ->call('save')
            ->assertHasErrors(['form.name' => 'max', 'form.kind'])
            ->assertSeeText('El valor seleccionado en tipo no es válido.');

        $this->assertDatabaseCount('security_device_types', 0);
    }

    public function test_a_name_cannot_be_repeated_even_if_the_other_type_is_deactivated(): void
    {
        $this->app->setLocale('es');

        SecurityDeviceType::factory()->trashed()->create(['name' => 'Sello GSW']);

        Livewire::test(CreateModal::class)
            ->set('form.name', ' Sello GSW ')
            ->set('form.kind', SecurityDeviceKind::Seal->value)
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique'])
            ->assertSeeText('El valor de nombre ya está en uso.');

        $this->assertDatabaseCount('security_device_types', 1);
    }

    public function test_the_name_of_a_type_can_be_edited_but_not_its_kind(): void
    {
        $securityDeviceType = SecurityDeviceType::factory()->create(['name' => 'Nombre Anterior', 'kind' => SecurityDeviceKind::Tape]);

        Livewire::test(EditModal::class)
            ->dispatch('edit-security-device-type', securityDeviceTypeId: $securityDeviceType->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.name', 'Nombre Anterior')
            ->assertSet('form.kindLabel', 'Cinta')
            ->set('form.name', 'Nombre Nuevo')
            ->set('form.kind', SecurityDeviceKind::Seal->value)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Tipo de dispositivo actualizado')
            ->assertDispatched('refreshDatatable');

        $securityDeviceType->refresh();

        $this->assertSame('Nombre Nuevo', $securityDeviceType->name);
        $this->assertSame(SecurityDeviceKind::Tape, $securityDeviceType->kind);
    }

    public function test_a_type_can_keep_its_name_but_not_take_one_in_use(): void
    {
        SecurityDeviceType::factory()->create(['name' => 'Nombre Ocupado']);
        $securityDeviceType = SecurityDeviceType::factory()->create(['name' => 'Nombre Propio']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-security-device-type', securityDeviceTypeId: $securityDeviceType->id)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(EditModal::class)
            ->dispatch('edit-security-device-type', securityDeviceTypeId: $securityDeviceType->id)
            ->set('form.name', 'Nombre Ocupado')
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique']);

        $this->assertSame('Nombre Propio', $securityDeviceType->refresh()->name);
    }

    public function test_a_deactivated_type_can_still_be_edited(): void
    {
        $securityDeviceType = SecurityDeviceType::factory()->trashed()->create();

        Livewire::test(EditModal::class)
            ->dispatch('edit-security-device-type', securityDeviceTypeId: $securityDeviceType->id)
            ->set('form.name', 'Nombre Nuevo')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nombre Nuevo', $securityDeviceType->refresh()->name);
        $this->assertTrue($securityDeviceType->trashed());
    }

    public function test_a_type_is_deactivated_and_reactivated_instead_of_deleted(): void
    {
        $securityDeviceType = SecurityDeviceType::factory()->create();

        Livewire::test(SecurityDeviceTypesTable::class)
            ->assertSeeHtml('title="Desactivar"')
            ->assertDontSeeHtml('title="Eliminar"')
            ->call('deactivate', $securityDeviceType->id)
            ->assertDispatched('toast', icon: 'success', title: 'Tipo de dispositivo desactivado');

        $this->assertSoftDeleted($securityDeviceType);

        Livewire::test(SecurityDeviceTypesTable::class)
            ->assertSeeHtml('title="Reactivar"')
            ->assertDontSeeHtml('title="Eliminar"')
            ->call('reactivate', $securityDeviceType->id)
            ->assertDispatched('toast', icon: 'success', title: 'Tipo de dispositivo reactivado');

        $this->assertNotSoftDeleted($securityDeviceType);
    }

    public function test_repeating_an_action_on_a_type_only_warns(): void
    {
        $deactivated = SecurityDeviceType::factory()->trashed()->create();
        $active = SecurityDeviceType::factory()->create();

        Livewire::test(SecurityDeviceTypesTable::class)
            ->call('deactivate', $deactivated->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Tipo de dispositivo ya desactivado');

        Livewire::test(SecurityDeviceTypesTable::class)
            ->call('reactivate', $active->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Tipo de dispositivo ya activo');

        $this->assertSoftDeleted($deactivated);
        $this->assertNotSoftDeleted($active);
    }
}
