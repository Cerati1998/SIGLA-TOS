<?php

namespace Tests\Feature;

use App\Enums\SecurityDeviceKind;
use App\Livewire\KitTypes\CreateModal;
use App\Livewire\KitTypes\EditModal;
use App\Livewire\Tables\KitTypesTable;
use App\Models\ContainerType;
use App\Models\KitType;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Database\Seeders\ContainerTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KitTypesManagementTest extends TestCase
{
    use RefreshDatabase;

    private ContainerType $reefer;

    private SecurityDeviceType $tape;

    private SecurityDeviceType $seal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());

        $this->reefer = ContainerType::factory()->reefer()->create(['code' => '40RF']);
        $this->tape = SecurityDeviceType::factory()->create(['name' => 'Cinta externa', 'kind' => SecurityDeviceKind::Tape]);
        $this->seal = SecurityDeviceType::factory()->create(['name' => 'Sello botella', 'kind' => SecurityDeviceKind::Seal]);
    }

    public function test_the_seeder_loads_the_container_types_without_repeating_them(): void
    {
        ContainerType::factory()->trashed()->create(['code' => '20RF', 'iso_code' => '22R1']);

        $this->seed(ContainerTypeSeeder::class);
        $this->seed(ContainerTypeSeeder::class);

        $this->assertDatabaseCount('container_types', count(ContainerTypeSeeder::TYPES));
        $this->assertDatabaseHas('container_types', ['code' => '40PF', 'iso_code' => '45P3', 'is_reefer' => false]);
        $this->assertSoftDeleted('container_types', ['code' => '20RF']);
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.kit-types.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_from_bodega_to_the_list_with_what_each_kit_type_carries(): void
    {
        KitType::factory()->for($this->reefer)->carrying($this->tape, 6)->carrying($this->seal, 2)->create(['name' => 'Kit de seguridad reefer']);
        KitType::factory()->trashed()->create(['name' => 'Kit antiguo']);

        $this->get(route('intranet.kit-types.index'))
            ->assertOk()
            ->assertSeeInOrder(['Bodega', 'Lotes de dispositivos', 'Tipos de kit', 'Certificación'])
            ->assertSee(route('intranet.kit-types.index'))
            ->assertSeeText('Kit de seguridad reefer')
            ->assertSeeText("40RF · 40'-RFHC Reefer High Cube")
            ->assertSeeText('8 dispositivos · 2 tipos')
            ->assertSeeText('Cinta externa')
            ->assertSeeText('Kit antiguo')
            ->assertSeeText('Desactivado');
    }

    public function test_the_list_is_ordered_by_name_and_filters_by_search_container_type_and_status(): void
    {
        $first = KitType::factory()->for($this->reefer)->create(['name' => 'Kit A reefer']);
        KitType::factory()->create(['name' => 'Kit M estándar']);
        KitType::factory()->for($this->reefer)->trashed()->create(['name' => 'Kit Z desactivado']);

        Livewire::test(KitTypesTable::class)
            ->assertSeeInOrder(['Kit A reefer', 'Kit M estándar', 'Kit Z desactivado'])
            ->assertSeeHtml("id: {$first->id},");

        Livewire::test(KitTypesTable::class)
            ->set('search', 'estándar')
            ->assertSeeText('Kit M estándar')
            ->assertDontSeeText('Kit A reefer');

        Livewire::test(KitTypesTable::class)
            ->set('filterComponents.container_type', (string) $this->reefer->id)
            ->assertSeeText('Kit A reefer')
            ->assertDontSeeText('Kit M estándar');

        Livewire::test(KitTypesTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Kit Z desactivado')
            ->assertDontSeeText('Kit A reefer');
    }

    public function test_an_empty_list_invites_to_create_the_first_kit_type(): void
    {
        Livewire::test(KitTypesTable::class)
            ->assertSeeText('Todavía no hay tipos de kit. Crea el primero con «Nuevo».');
    }

    public function test_a_kit_type_is_created_with_the_active_device_types_it_carries_and_their_quantities(): void
    {
        $deactivated = SecurityDeviceType::factory()->trashed()->create(['name' => 'Sello antiguo']);

        Livewire::test(CreateModal::class)
            ->assertSet('form.devices', [
                $this->tape->id => ['on' => false, 'quantity' => 1],
                $this->seal->id => ['on' => false, 'quantity' => 1],
            ])
            ->assertDontSeeText($deactivated->name)
            ->set('form.openModal', true)
            ->set('form.name', ' Kit de seguridad reefer ')
            ->set('form.containerTypeId', (string) $this->reefer->id)
            ->set("form.devices.{$this->tape->id}.on", true)
            ->set("form.devices.{$this->tape->id}.quantity", '6')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertSet("form.devices.{$this->tape->id}.on", false)
            ->assertDispatched('toast', icon: 'success', title: 'Tipo de kit creado')
            ->assertDispatched('refreshDatatable');

        $kitType = KitType::query()->sole();

        $this->assertSame('Kit de seguridad reefer', $kitType->name);
        $this->assertTrue($kitType->containerType->is($this->reefer));
        $this->assertDatabaseCount('kit_type_security_device_type', 1);
        $this->assertDatabaseHas('kit_type_security_device_type', ['kit_type_id' => $kitType->id, 'security_device_type_id' => $this->tape->id, 'quantity' => 6]);
    }

    public function test_a_kit_type_needs_a_name_a_container_type_and_at_least_one_device(): void
    {
        Livewire::test(CreateModal::class)
            ->call('save')
            ->assertHasErrors(['form.name' => 'required', 'form.containerTypeId' => 'required', 'form.devices'])
            ->assertSeeText('Un tipo de kit debe llevar al menos un dispositivo.')
            ->assertNotDispatched('toast');

        $this->assertDatabaseCount('kit_types', 0);
    }

    public function test_the_quantity_is_a_whole_number_greater_than_zero(): void
    {
        foreach (['0', '-2', '1.5', ''] as $quantity) {
            Livewire::test(CreateModal::class)
                ->set('form.name', 'Kit de prueba')
                ->set('form.containerTypeId', (string) $this->reefer->id)
                ->set("form.devices.{$this->tape->id}.on", true)
                ->set("form.devices.{$this->tape->id}.quantity", $quantity)
                ->call('save')
                ->assertHasErrors(["form.devices.{$this->tape->id}.quantity"])
                ->assertSeeText('Escribe una cantidad entera mayor que cero.');
        }

        $this->assertDatabaseCount('kit_types', 0);
    }

    public function test_the_name_is_unique_even_among_the_deactivated_kit_types(): void
    {
        KitType::factory()->trashed()->create(['name' => 'Kit de cambio de sellos']);

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Kit de cambio de sellos')
            ->set('form.containerTypeId', (string) $this->reefer->id)
            ->set("form.devices.{$this->seal->id}.on", true)
            ->call('save')
            ->assertHasErrors(['form.name' => 'unique'])
            ->assertSeeText('Ya existe un tipo de kit con este nombre, aunque esté desactivado.');
    }

    public function test_only_active_container_types_and_device_types_can_be_chosen(): void
    {
        $oldContainer = ContainerType::factory()->trashed()->create();
        $oldDevice = SecurityDeviceType::factory()->trashed()->create();

        Livewire::test(CreateModal::class)
            ->set('form.name', 'Kit de prueba')
            ->set('form.containerTypeId', (string) $oldContainer->id)
            ->set("form.devices.{$oldDevice->id}", ['on' => true, 'quantity' => 1])
            ->call('save')
            ->assertHasErrors(['form.containerTypeId' => 'exists', 'form.devices'])
            ->assertSeeText('Elige solo tipos de dispositivo activos.');

        $this->assertDatabaseCount('kit_types', 0);
    }

    public function test_a_kit_type_is_edited_and_its_deactivated_device_type_is_kept_until_it_is_turned_off(): void
    {
        $oldSeal = SecurityDeviceType::factory()->create(['name' => 'Sello plástico']);
        $kitType = KitType::factory()->for($this->reefer)->carrying($this->tape, 6)->carrying($oldSeal, 2)->create(['name' => 'Kit de seguridad reefer']);
        $oldSeal->delete();

        $component = Livewire::test(EditModal::class)
            ->dispatch('edit-kit-type', kitTypeId: $kitType->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.name', 'Kit de seguridad reefer')
            ->assertSet("form.devices.{$this->tape->id}", ['on' => true, 'quantity' => 6])
            ->assertSet("form.devices.{$oldSeal->id}", ['on' => true, 'quantity' => 2])
            ->assertSet("form.devices.{$this->seal->id}", ['on' => false, 'quantity' => 1])
            ->assertSeeText('Se conserva mientras no lo apagues');

        $component
            ->set("form.devices.{$this->tape->id}.quantity", '4')
            ->set("form.devices.{$this->seal->id}.on", true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Tipo de kit actualizado');

        $this->assertEquals([$this->seal->id => 1, $oldSeal->id => 2, $this->tape->id => 4], $this->carried($kitType));

        Livewire::test(EditModal::class)
            ->dispatch('edit-kit-type', kitTypeId: $kitType->id)
            ->set("form.devices.{$oldSeal->id}.on", false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals([$this->seal->id => 1, $this->tape->id => 4], $this->carried($kitType));

        Livewire::test(EditModal::class)
            ->dispatch('edit-kit-type', kitTypeId: $kitType->id)
            ->assertSet("form.devices.{$oldSeal->id}", null)
            ->assertDontSeeText('Sello plástico');
    }

    public function test_a_kit_type_keeps_its_deactivated_container_type_when_edited(): void
    {
        $kitType = KitType::factory()->for($this->reefer)->carrying($this->tape, 6)->create();
        $this->reefer->delete();

        Livewire::test(EditModal::class)
            ->dispatch('edit-kit-type', kitTypeId: $kitType->id)
            ->assertSeeText('(desactivado)')
            ->set('form.name', 'Kit renombrado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kit_types', ['id' => $kitType->id, 'name' => 'Kit renombrado', 'container_type_id' => $this->reefer->id]);
    }

    public function test_a_kit_type_is_deactivated_and_reactivated(): void
    {
        $kitType = KitType::factory()->create();

        Livewire::test(KitTypesTable::class)
            ->assertSeeHtml('title="Desactivar"')
            ->call('deactivate', $kitType->id)
            ->assertDispatched('toast', icon: 'success', title: 'Tipo de kit desactivado')
            ->call('deactivate', $kitType->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Tipo de kit ya desactivado');

        $this->assertSoftDeleted($kitType);

        Livewire::test(KitTypesTable::class)
            ->assertSeeHtml('title="Reactivar"')
            ->call('reactivate', $kitType->id)
            ->assertDispatched('toast', icon: 'success', title: 'Tipo de kit reactivado')
            ->call('reactivate', $kitType->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Tipo de kit ya activo');

        $this->assertNotSoftDeleted($kitType);
    }

    /**
     * Get what the given kit type carries, as security device type id => quantity.
     *
     * @return array<int, int>
     */
    private function carried(KitType $kitType): array
    {
        return $kitType->securityDeviceTypes()->get()
            ->mapWithKeys(fn (SecurityDeviceType $deviceType): array => [$deviceType->id => (int) $deviceType->getRelationValue('pivot')->getAttribute('quantity')])
            ->all();
    }
}
