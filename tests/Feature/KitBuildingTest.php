<?php

namespace Tests\Feature;

use App\Enums\KitAssignment;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\Kits\CreateForm;
use App\Livewire\Kits\DeviceScanner;
use App\Livewire\Tables\KitsTable;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KitBuildingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ShippingLine $maersk;

    private SecurityDeviceType $inspectionTape;

    private SecurityDeviceType $bottleSeal;

    private SecurityDeviceType $pouch;

    private KitType $kitType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Bodeguero Uno']);
        $this->actingAs($this->user);

        $this->maersk = ShippingLine::factory()->create(['code' => 'MSK', 'name' => 'Maersk']);
        $this->inspectionTape = SecurityDeviceType::factory()->splitsInternalExternal()->create(['name' => 'Cinta de inspección']);
        $this->bottleSeal = SecurityDeviceType::factory()->create(['name' => 'Sello botella']);
        $this->pouch = SecurityDeviceType::factory()->pouch()->create(['name' => 'Funda']);
        $this->kitType = KitType::factory()
            ->carrying($this->pouch, 1)
            ->carryingSplit($this->inspectionTape, internal: 2, external: 1)
            ->carrying($this->bottleSeal, 1)
            ->create(['name' => 'Kit de seguridad reefer']);
        $this->device($this->pouch, 'KIT-000046');
    }

    public function test_the_warehouse_creates_an_empty_kit_in_armando_with_a_copy_of_what_its_kit_type_carries(): void
    {
        Livewire::test(CreateForm::class)
            ->set('form.pouchCode', ' kit-000046 ')
            ->set('form.kitTypeId', (string) $this->kitType->id)
            ->set('form.shippingLineId', (string) $this->maersk->id)
            ->call('save')
            ->assertHasNoErrors();

        $kit = Kit::query()->sole();

        $this->assertSame(['KIT-000046', KitStatus::Building, $this->user->id], [$kit->pouch_code, $kit->status, $kit->created_by]);
        $this->assertNotNull($kit->created_at);
        $this->assertSame(['KIT-000046'], $kit->devices()->pluck('serial_number')->all());
        $this->assertSame(SecurityDeviceStatus::InKit, SecurityDevice::query()->where('serial_number', 'KIT-000046')->value('status'));
        $this->assertSame(
            [[$this->inspectionTape->id, 3, 2, 1], [$this->pouch->id, 1, null, null], [$this->bottleSeal->id, 1, null, null]],
            $kit->securityDeviceTypes->map(fn (SecurityDeviceType $type): array => [
                $type->id,
                $type->getRelationValue('pivot')->quantity,
                $type->getRelationValue('pivot')->internal_quantity,
                $type->getRelationValue('pivot')->external_quantity,
            ])->all(),
        );
    }

    public function test_the_pouch_the_kit_type_and_the_shipping_line_are_required_and_only_active_ones_are_accepted(): void
    {
        Kit::factory()->create(['pouch_code' => 'KIT-000031']);
        $inactiveKitType = KitType::factory()->carrying($this->bottleSeal, 1)->create(['deleted_at' => now()]);
        $inactiveShippingLine = ShippingLine::factory()->create(['deleted_at' => now()]);

        Livewire::test(CreateForm::class)
            ->call('save')
            ->assertHasErrors(['form.pouchCode' => 'required', 'form.kitTypeId' => 'required', 'form.shippingLineId' => 'required']);

        Livewire::test(CreateForm::class)
            ->set('form.pouchCode', 'kit-000031')
            ->set('form.kitTypeId', (string) $inactiveKitType->id)
            ->set('form.shippingLineId', (string) $inactiveShippingLine->id)
            ->call('save')
            ->assertHasErrors(['form.pouchCode' => 'unique', 'form.kitTypeId' => 'exists', 'form.shippingLineId' => 'exists'])
            ->assertSeeText('La funda KIT-000031 ya es de otro kit.')
            ->assertSeeText('Elige un tipo de kit activo.')
            ->assertSeeText('Elige una línea naviera activa.');

        $this->assertSame(1, Kit::query()->count());
    }

    public function test_the_pouch_must_be_an_available_pouch_of_the_shipping_line_and_of_the_pouch_type_of_the_kit_type(): void
    {
        $this->device($this->bottleSeal, 'SLL0000044');
        $this->device(SecurityDeviceType::factory()->pouch()->create(['name' => 'Funda grande']), 'FGR0000001');
        $this->device($this->pouch, 'FHL0000001', shippingLine: ShippingLine::factory()->create(['code' => 'HLCU', 'name' => 'Hapag-Lloyd']));
        $this->device($this->pouch, 'FMK0000002', SecurityDeviceStatus::Inactive);
        $this->device($this->pouch, 'FMK0000003', SecurityDeviceStatus::Used);

        $rejections = [
            'FMK0000999' => 'No hay ninguna funda con el código FMK0000999.',
            'SLL0000044' => 'SLL0000044 es un dispositivo Sello botella, no una funda.',
            'FGR0000001' => 'La funda FGR0000001 es del tipo Funda grande y el tipo de kit lleva Funda.',
            'FHL0000001' => 'La funda FHL0000001 es de HLCU · Hapag-Lloyd y el kit es de MSK · Maersk.',
            'FMK0000002' => 'La funda FMK0000002 está Inactiva: falta registrar la aprobación de la naviera.',
            'FMK0000003' => 'La funda FMK0000003 está Usado y solo se usa una Disponible.',
        ];

        foreach ($rejections as $pouchCode => $reason) {
            Livewire::test(CreateForm::class)
                ->set('form.pouchCode', $pouchCode)
                ->set('form.kitTypeId', (string) $this->kitType->id)
                ->set('form.shippingLineId', (string) $this->maersk->id)
                ->call('save')
                ->assertHasErrors(['form.pouchCode'])
                ->assertSeeText($reason)
                ->assertNoRedirect();
        }

        $withoutPouch = KitType::factory()->carrying($this->bottleSeal, 1)->create(['name' => 'Kit sin funda']);

        Livewire::test(CreateForm::class)
            ->set('form.pouchCode', 'KIT-000046')
            ->set('form.kitTypeId', (string) $withoutPouch->id)
            ->set('form.shippingLineId', (string) $this->maersk->id)
            ->call('save')
            ->assertSeeText('El tipo de kit Kit sin funda no lleva funda. Edítalo en Tipos de kit y enciende una.');

        $this->assertSame(0, Kit::query()->count());
        $this->assertSame(SecurityDeviceStatus::Available, SecurityDevice::query()->where('serial_number', 'KIT-000046')->value('status'));
    }

    public function test_the_kit_shows_one_field_per_slot_with_the_external_tape_before_the_internal_one(): void
    {
        $kit = $this->kit();

        $this->get(route('intranet.kits.show', $kit))
            ->assertOk()
            ->assertSeeTextInOrder(['Funda', '1 de 1', 'Entró con la cabecera. No se quita.', 'Cinta de inspección', 'Externa', '0 de 1', 'Cinta de inspección', 'Interna', '0 de 2', 'Sello botella', '0 de 1'])
            ->assertDontSee('data-slot-input="'.Kit::slotKey($this->pouch->id, null).'"', false)
            ->assertSeeText($kit->week())
            ->assertSeeText('Armando');
    }

    public function test_a_scanned_device_goes_in_its_slot_and_the_kit_becomes_armado_once_every_slot_is_full(): void
    {
        $kit = $this->kit();
        $externalTape = $this->device($this->inspectionTape, 'CTA0001201');
        $internalTapes = [$this->device($this->inspectionTape, 'CTA0001202'), $this->device($this->inspectionTape, 'CTA0001203')];
        $seal = $this->device($this->bottleSeal, 'SLL0000044');

        $scanner = Livewire::test(DeviceScanner::class, ['kit' => $kit]);

        $scanner->call('add', $this->slot(KitAssignment::External), ' cta0001201 ')
            ->assertReturned(['ok' => true, 'message' => 'Cinta de inspección · Externa · agregado']);
        $scanner->call('add', $this->slot(KitAssignment::Internal), 'CTA0001202');
        $scanner->call('add', $this->slot(KitAssignment::Internal), 'CTA0001203');

        $this->assertSame(KitStatus::Building, $kit->refresh()->status);

        $scanner->call('add', Kit::slotKey($this->bottleSeal->id, null), 'SLL0000044')
            ->assertReturned(['ok' => true, 'message' => 'Sello botella · agregado'])
            ->assertSeeText('Kit completo.');

        $this->assertSame(KitStatus::Built, $kit->refresh()->status);
        $this->assertSame(
            ['KIT-000046' => null, 'CTA0001201' => 'external', 'CTA0001202' => 'internal', 'CTA0001203' => 'internal', 'SLL0000044' => null],
            $kit->devices->mapWithKeys(fn (SecurityDevice $device): array => [$device->serial_number => $device->getRelationValue('pivot')->assignment])->all(),
        );

        foreach ([$externalTape, ...$internalTapes, $seal] as $device) {
            $this->assertSame(SecurityDeviceStatus::InKit, $device->refresh()->status);
        }
    }

    public function test_a_device_that_cannot_go_in_the_slot_is_rejected_with_the_reason_and_nothing_changes(): void
    {
        $kit = $this->kit();
        $otherKit = Kit::factory()->for($this->kitType)->for($this->maersk)->create(['pouch_code' => 'KIT-000045']);
        $inOtherKit = $this->device($this->inspectionTape, 'CTA0000001', SecurityDeviceStatus::InKit);
        $otherKit->devices()->attach($inOtherKit->id, ['assignment' => 'internal']);
        $this->device($this->inspectionTape, 'GSW0000330', SecurityDeviceStatus::Inactive);
        $this->device($this->inspectionTape, 'CTA0000002', SecurityDeviceStatus::Used);
        $this->device($this->inspectionTape, 'HPL0000088', shippingLine: ShippingLine::factory()->create(['code' => 'HPL']));
        $this->device(SecurityDeviceType::factory()->create(['name' => 'Sello cable']), 'SLC0000010');
        $this->device($this->bottleSeal, 'SLL0000045');

        $scanner = Livewire::test(DeviceScanner::class, ['kit' => $kit]);
        $internal = $this->slot(KitAssignment::Internal);

        $rejections = [
            'XXX0009999' => 'No encontramos un dispositivo con esa serie.',
            'CTA0000001' => 'Ya está en el kit KIT-000045.',
            'GSW0000330' => 'No está activado para despacho. Actívalo desde su lote antes de agregarlo.',
            'CTA0000002' => 'Está Usado; solo se agregan dispositivos disponibles.',
            'HPL0000088' => 'Es de la naviera HPL; este kit es de MSK.',
            'SLC0000010' => 'El tipo Sello cable no va en este tipo de kit.',
            'SLL0000045' => 'Es Sello botella, no Cinta de inspección. Escanéalo en su campo.',
        ];

        foreach ($rejections as $serialNumber => $reason) {
            $scanner->call('add', $internal, $serialNumber)->assertReturned(['ok' => false, 'message' => $reason]);
        }

        $this->assertSame(1, $kit->devices()->count());
        $this->assertSame(SecurityDeviceStatus::Available, SecurityDevice::query()->where('serial_number', 'SLL0000045')->value('status'));
    }

    public function test_a_slot_does_not_take_more_devices_than_the_kit_type_asks_for(): void
    {
        $kit = $this->kit();
        $this->device($this->inspectionTape, 'CTA0001201');
        $this->device($this->inspectionTape, 'CTA0001202');

        $scanner = Livewire::test(DeviceScanner::class, ['kit' => $kit]);
        $external = $this->slot(KitAssignment::External);

        $scanner->call('add', $external, 'CTA0001201');
        $scanner->call('add', $external, 'CTA0001201')->assertReturned(['ok' => false, 'message' => 'Ya está en este kit.']);
        $scanner->call('add', $external, 'CTA0001202')
            ->assertReturned(['ok' => false, 'message' => 'El kit ya tiene 1 de 1 en Cinta de inspección · Externa.']);

        $this->assertSame(2, $kit->devices()->count());
    }

    public function test_removing_a_device_makes_it_available_again_and_the_kit_goes_back_to_armando(): void
    {
        $kit = $this->kit();
        $devices = [
            [$this->device($this->inspectionTape, 'CTA0001201'), KitAssignment::External],
            [$this->device($this->inspectionTape, 'CTA0001202'), KitAssignment::Internal],
            [$this->device($this->inspectionTape, 'CTA0001203'), KitAssignment::Internal],
            [$this->device($this->bottleSeal, 'SLL0000044'), null],
        ];

        $scanner = Livewire::test(DeviceScanner::class, ['kit' => $kit]);

        foreach ($devices as [$device, $assignment]) {
            $scanner->call('add', Kit::slotKey($device->lot->security_device_type_id, $assignment), $device->serial_number);
        }

        $this->assertSame(KitStatus::Built, $kit->refresh()->status);

        $scanner->call('remove', $devices[1][0]->id)
            ->assertDispatched('toast', icon: 'success', title: 'Dispositivo quitado', text: 'CTA0001202 volvió a quedar Disponible.');

        $this->assertSame(KitStatus::Building, $kit->refresh()->status);
        $this->assertSame(SecurityDeviceStatus::Available, $devices[1][0]->refresh()->status);
        $this->assertSame(4, $kit->devices()->count());

        $pouchId = (int) SecurityDevice::query()->where('serial_number', 'KIT-000046')->value('id');

        $scanner->call('remove', $pouchId)
            ->assertDispatched('toast', icon: 'warning', title: 'No se quitó', text: 'La funda no se quita: es la cabecera del kit.');

        $this->assertSame(SecurityDeviceStatus::InKit, SecurityDevice::query()->whereKey($pouchId)->value('status'));
    }

    public function test_the_contents_of_a_dispatched_kit_no_longer_change(): void
    {
        $kit = $this->kit();
        $seal = $this->device($this->bottleSeal, 'SLL0000044', SecurityDeviceStatus::InKit);
        $kit->devices()->attach($seal->id);
        $kit->update(['status' => KitStatus::Dispatched]);
        $this->device($this->bottleSeal, 'SLL0000046');

        $scanner = Livewire::test(DeviceScanner::class, ['kit' => $kit]);

        $scanner->call('add', Kit::slotKey($this->bottleSeal->id, null), 'SLL0000046')
            ->assertReturned(['ok' => false, 'message' => 'El kit ya se despachó: su contenido no cambia.']);
        $scanner->call('remove', $seal->id)
            ->assertDispatched('toast', icon: 'warning', title: 'No se quitó', text: 'El kit ya se despachó: su contenido no cambia.');

        $this->assertSame(['KIT-000046', 'SLL0000044'], $kit->devices()->pluck('serial_number')->all());
    }

    public function test_editing_the_kit_type_does_not_change_what_a_kit_built_from_it_asks_for(): void
    {
        $kit = $this->kit();

        $this->kitType->securityDeviceTypes()->updateExistingPivot($this->bottleSeal->id, ['quantity' => 5]);

        $this->assertSame(1, collect($kit->refresh()->slots())->firstWhere('key', Kit::slotKey($this->bottleSeal->id, null))['quantity']);
    }

    public function test_the_kits_list_shows_each_kit_with_its_progress_and_a_link_to_open_it(): void
    {
        $kit = $this->kit();
        $seal = $this->device($this->bottleSeal, 'SLL0000044', SecurityDeviceStatus::InKit);
        $kit->devices()->attach($seal->id);

        $this->get(route('intranet.kits.index'))
            ->assertOk()
            ->assertSee(route('intranet.kits.create'));

        Livewire::test(KitsTable::class)
            ->assertSeeText('KIT-000046')
            ->assertSeeText('Kit de seguridad reefer')
            ->assertSeeText('MSK')
            ->assertSeeText('2 de 5')
            ->assertSeeText('Armando')
            ->assertSeeHtml('href="'.route('intranet.kits.show', $kit).'"');
    }

    /**
     * Create the kit KIT-000046 of the Maersk line from the kit type, as the warehouse does.
     */
    private function kit(): Kit
    {
        Livewire::test(CreateForm::class)
            ->set('form.pouchCode', 'KIT-000046')
            ->set('form.kitTypeId', (string) $this->kitType->id)
            ->set('form.shippingLineId', (string) $this->maersk->id)
            ->call('save');

        return Kit::query()->where('pouch_code', 'KIT-000046')->sole();
    }

    /**
     * Get the key of a side of the inspection tape.
     */
    private function slot(KitAssignment $assignment): string
    {
        return Kit::slotKey($this->inspectionTape->id, $assignment);
    }

    /**
     * Create a device of the given type with the given serial number, in a lot of the given shipping line (Maersk by
     * default), available by default.
     */
    private function device(SecurityDeviceType $type, string $serialNumber, SecurityDeviceStatus $status = SecurityDeviceStatus::Available, ?ShippingLine $shippingLine = null): SecurityDevice
    {
        $lot = SecurityDeviceLot::factory()
            ->for(SecurityDeviceAnnouncement::factory()->for($shippingLine ?? $this->maersk), 'announcement')
            ->for($type)
            ->create();

        return SecurityDevice::factory()->for($lot, 'lot')->create(['serial_number' => $serialNumber, 'status' => $status]);
    }
}
