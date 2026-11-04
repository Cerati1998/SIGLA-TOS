<?php

namespace Tests\Feature;

use App\Livewire\ShippingLines\CreateModal;
use App\Livewire\ShippingLines\EditModal;
use App\Livewire\Tables\ShippingLinesTable;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShippingLinesManagementTest extends TestCase
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

        $this->get(route('intranet.shipping-lines.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_active_and_deactivated_shipping_lines(): void
    {
        ShippingLine::factory()->create(['name' => 'Naviera Activa']);
        ShippingLine::factory()->trashed()->create(['name' => 'Naviera Cerrada']);

        $this->get(route('intranet.shipping-lines.index'))
            ->assertOk()
            ->assertSeeInOrder(['Maestros', 'Líneas navieras', 'Configuración'])
            ->assertSee(route('intranet.shipping-lines.index'))
            ->assertSeeText('Naviera Activa')
            ->assertSeeText('Naviera Cerrada')
            ->assertSeeText('Activa')
            ->assertSeeText('Inactiva');
    }

    public function test_each_row_receives_its_shipping_line_id(): void
    {
        $shippingLine = ShippingLine::factory()->create();

        Livewire::test(ShippingLinesTable::class)->assertSeeHtml("id: {$shippingLine->id},");
    }

    public function test_the_list_is_ordered_by_code(): void
    {
        ShippingLine::factory()->create(['code' => 'ZIM', 'name' => 'Segunda Naviera']);
        ShippingLine::factory()->create(['code' => 'CMA', 'name' => 'Primera Naviera']);

        Livewire::test(ShippingLinesTable::class)->assertSeeInOrder(['Primera Naviera', 'Segunda Naviera']);
    }

    public function test_the_list_filters_by_search_and_status(): void
    {
        ShippingLine::factory()->create(['code' => 'MSK', 'name' => 'Maersk']);
        ShippingLine::factory()->trashed()->create(['code' => 'HPL', 'name' => 'Hapag']);

        Livewire::test(ShippingLinesTable::class)
            ->set('search', 'MSK')
            ->assertSeeText('Maersk')
            ->assertDontSeeText('Hapag');

        Livewire::test(ShippingLinesTable::class)
            ->set('search', 'Hapag')
            ->assertSeeText('HPL')
            ->assertDontSeeText('Maersk');

        Livewire::test(ShippingLinesTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Hapag')
            ->assertDontSeeText('Maersk');
    }

    public function test_a_shipping_line_can_be_created_with_its_code_in_uppercase(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.code', ' msk ')
            ->set('form.name', 'Maersk')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Línea naviera creada')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('shipping_lines', ['code' => 'MSK', 'name' => 'Maersk']);
    }

    public function test_the_fields_of_a_shipping_line_are_validated(): void
    {
        $this->app->setLocale('es');

        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.code' => 'required', 'form.name' => 'required'])
            ->assertSeeText('El campo código es obligatorio.')
            ->assertNotDispatched('toast');

        Livewire::test(CreateModal::class)
            ->set('form.code', str_repeat('A', 21))
            ->set('form.name', str_repeat('A', 101))
            ->call('save')
            ->assertHasErrors(['form.code' => 'max', 'form.name' => 'max']);
    }

    public function test_a_code_cannot_be_repeated_even_if_the_other_shipping_line_is_deactivated(): void
    {
        $this->app->setLocale('es');

        ShippingLine::factory()->trashed()->create(['code' => 'MSK']);

        Livewire::test(CreateModal::class)
            ->set('form.code', 'msk')
            ->set('form.name', 'Otra Naviera')
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique'])
            ->assertSeeText('El valor de código ya está en uso.');

        $this->assertDatabaseMissing('shipping_lines', ['name' => 'Otra Naviera']);
    }

    public function test_a_shipping_line_can_be_edited_and_keep_its_code(): void
    {
        $shippingLine = ShippingLine::factory()->create(['code' => 'MSK', 'name' => 'Nombre Anterior']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-shipping-line', shippingLineId: $shippingLine->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.code', 'MSK')
            ->assertSet('form.name', 'Nombre Anterior')
            ->set('form.name', 'Nombre Nuevo')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Línea naviera actualizada')
            ->assertDispatched('refreshDatatable');

        $shippingLine->refresh();

        $this->assertSame('MSK', $shippingLine->code);
        $this->assertSame('Nombre Nuevo', $shippingLine->name);
    }

    public function test_the_code_of_a_shipping_line_can_be_changed_but_not_to_one_in_use(): void
    {
        ShippingLine::factory()->create(['code' => 'HPL']);
        $shippingLine = ShippingLine::factory()->create(['code' => 'MSK']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-shipping-line', shippingLineId: $shippingLine->id)
            ->set('form.code', 'HPL')
            ->call('save')
            ->assertHasErrors(['form.code' => 'unique']);

        $this->assertSame('MSK', $shippingLine->refresh()->code);

        Livewire::test(EditModal::class)
            ->dispatch('edit-shipping-line', shippingLineId: $shippingLine->id)
            ->set('form.code', 'mae')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('MAE', $shippingLine->refresh()->code);
    }

    public function test_a_deactivated_shipping_line_can_still_be_edited(): void
    {
        $shippingLine = ShippingLine::factory()->trashed()->create();

        Livewire::test(EditModal::class)
            ->dispatch('edit-shipping-line', shippingLineId: $shippingLine->id)
            ->set('form.name', 'Nombre Nuevo')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nombre Nuevo', $shippingLine->refresh()->name);
        $this->assertTrue($shippingLine->trashed());
    }

    public function test_a_shipping_line_can_be_deactivated_and_reactivated(): void
    {
        $shippingLine = ShippingLine::factory()->create();

        Livewire::test(ShippingLinesTable::class)
            ->assertSeeHtml('title="Desactivar"')
            ->call('deactivate', $shippingLine->id)
            ->assertDispatched('toast', icon: 'success', title: 'Línea naviera desactivada');

        $this->assertSoftDeleted($shippingLine);

        Livewire::test(ShippingLinesTable::class)
            ->assertSeeHtml('title="Reactivar"')
            ->call('reactivate', $shippingLine->id)
            ->assertDispatched('toast', icon: 'success', title: 'Línea naviera reactivada');

        $this->assertNotSoftDeleted($shippingLine);
    }

    public function test_repeating_an_action_on_a_shipping_line_only_warns(): void
    {
        $deactivated = ShippingLine::factory()->trashed()->create();
        $active = ShippingLine::factory()->create();

        Livewire::test(ShippingLinesTable::class)
            ->call('deactivate', $deactivated->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Línea naviera ya desactivada');

        Livewire::test(ShippingLinesTable::class)
            ->call('reactivate', $active->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Línea naviera ya activa');

        $this->assertSoftDeleted($deactivated);
        $this->assertNotSoftDeleted($active);
    }
}
