<?php

namespace App\Livewire\Forms;

use App\Enums\SecurityDeviceKind;
use App\Models\ContainerType;
use App\Models\KitType;
use App\Models\SecurityDeviceType;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class KitTypeForm extends Form
{
    /**
     * The most of one security device type a kit type can carry.
     */
    public const int MAX_QUANTITY = 999;

    public bool $openModal = false;

    #[Locked]
    public ?int $kitTypeId = null;

    /**
     * The container type the kit type had when it was opened for editing: it can be kept even if it was deactivated.
     */
    #[Locked]
    public ?int $savedContainerTypeId = null;

    /**
     * The security device types the kit type carried when it was opened for editing: they can be kept even if they
     * were deactivated.
     *
     * @var array<int, int>
     */
    #[Locked]
    public array $savedDeviceTypeIds = [];

    public string $name = '';

    public string $containerTypeId = '';

    /**
     * One entry per security device type that can be chosen, keyed by its id: whether the kit type carries it and how
     * many. A type split between internal and external carries how many go to each instead of a single quantity.
     *
     * @var array<int, array{on: bool, quantity?: int|string, internal?: int|string, external?: int|string}>
     */
    public array $devices = [];

    /**
     * Prepare the form for a new kit type: every active security device type, turned off.
     */
    public function fillDevices(): void
    {
        $this->devices = $this->deviceTypes()
            ->mapWithKeys(fn (SecurityDeviceType $deviceType): array => [$deviceType->id => $this->emptyDevice($deviceType)])
            ->all();
    }

    /**
     * Fill the form with the kit type being edited.
     */
    public function setKitType(KitType $kitType): void
    {
        $this->kitTypeId = $kitType->id;
        $this->savedContainerTypeId = $kitType->container_type_id;
        $this->name = $kitType->name;
        $this->containerTypeId = (string) $kitType->container_type_id;

        $carried = $kitType->securityDeviceTypes->mapWithKeys(
            fn (SecurityDeviceType $deviceType): array => [$deviceType->id => $deviceType->getRelationValue('pivot')],
        );

        $this->savedDeviceTypeIds = $carried->keys()->all();
        $this->devices = $this->deviceTypes()
            ->mapWithKeys(fn (SecurityDeviceType $deviceType): array => [$deviceType->id => $carried->has($deviceType->id)
                ? $this->carriedDevice($deviceType, $carried->get($deviceType->id))
                : $this->emptyDevice($deviceType)])
            ->all();
    }

    /**
     * Get the security device types that can be chosen: the active ones, plus the ones the kit type already carries.
     *
     * @return Collection<int, SecurityDeviceType>
     */
    public function deviceTypes(): Collection
    {
        return SecurityDeviceType::withTrashed()
            ->where(fn ($query) => $query->whereNull('deleted_at')->orWhereIn('id', $this->savedDeviceTypeIds))
            ->orderBy('name')
            ->get();
    }

    /**
     * Get the container types that can be chosen, as id => name: the active ones, plus the one the kit type already has.
     *
     * @return array<int, string>
     */
    public function containerTypeOptions(): array
    {
        return ContainerType::withTrashed()
            ->where(fn ($query) => $query->whereNull('deleted_at')->orWhere('id', $this->savedContainerTypeId))
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (ContainerType $type): array => [$type->id => $type->trashed() ? "{$type->label()} (desactivado)" : $type->label()])
            ->all();
    }

    /**
     * Get the validation rules of the form. The name is unique even among the deactivated kit types, it carries at
     * least one security device type and exactly one pouch (T76), and every quantity is a whole number greater than
     * zero. A type split between internal and external takes two whole numbers from zero that add up to at least one.
     * The pouch has no quantity: a kit carries one.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100', Rule::unique(KitType::class)->ignore($this->kitTypeId)],
            'containerTypeId' => ['required', 'integer', Rule::exists(ContainerType::class, 'id')->where(
                fn (Builder $query): Builder => $query->whereNull('deleted_at')->orWhere('id', $this->savedContainerTypeId),
            )],
            'devices' => ['array', $this->carriesAtLeastOneDevice(...), $this->onlyChoosableDevices(...), $this->carriesOnePouch(...)],
        ];

        $splitIds = $this->splitDeviceTypeIds();
        $pouchIds = $this->pouchDeviceTypeIds();

        foreach (array_keys($this->selectedDevices()) as $deviceTypeId) {
            if (in_array($deviceTypeId, $pouchIds, true)) {
                continue;
            }

            if (in_array($deviceTypeId, $splitIds, true)) {
                $rules["devices.{$deviceTypeId}.internal"] = ['required', 'integer', 'min:0', 'max:'.self::MAX_QUANTITY];
                $rules["devices.{$deviceTypeId}.external"] = ['required', 'integer', 'min:0', 'max:'.self::MAX_QUANTITY];
                $rules["devices.{$deviceTypeId}"] = [$this->splitAddsUp(...)];
            } else {
                $rules["devices.{$deviceTypeId}.quantity"] = ['required', 'integer', 'min:1', 'max:'.self::MAX_QUANTITY];
            }
        }

        return $rules;
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        $tooMany = 'La cantidad no puede pasar de '.self::MAX_QUANTITY.'.';
        $internal = 'Escribe cuántas van a la asignación interna, desde cero.';
        $external = 'Escribe cuántas van a la asignación externa, desde cero.';

        return [
            'name.required' => 'Escribe el nombre del tipo de kit.',
            'name.unique' => 'Ya existe un tipo de kit con este nombre, aunque esté desactivado.',
            'containerTypeId.required' => 'Elige el tipo de contenedor.',
            'containerTypeId.exists' => 'Elige un tipo de contenedor activo.',
            'devices.*.quantity.required' => 'Escribe una cantidad entera mayor que cero.',
            'devices.*.quantity.integer' => 'Escribe una cantidad entera mayor que cero.',
            'devices.*.quantity.min' => 'Escribe una cantidad entera mayor que cero.',
            'devices.*.quantity.max' => $tooMany,
            'devices.*.internal.required' => $internal,
            'devices.*.internal.integer' => $internal,
            'devices.*.internal.min' => $internal,
            'devices.*.internal.max' => $tooMany,
            'devices.*.external.required' => $external,
            'devices.*.external.integer' => $external,
            'devices.*.external.min' => $external,
            'devices.*.external.max' => $tooMany,
        ];
    }

    /**
     * Get the names of the fields in the validation messages.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'containerTypeId' => 'tipo de contenedor',
            'devices' => 'dispositivos',
            'devices.*.quantity' => 'cantidad',
            'devices.*.internal' => 'interna',
            'devices.*.external' => 'externa',
        ];
    }

    /**
     * Create the kit type with the security device types it carries.
     */
    public function store(): void
    {
        $this->validateTrimmed();

        DB::transaction(function (): void {
            $kitType = KitType::query()->create($this->kitTypeAttributes());
            $kitType->securityDeviceTypes()->sync($this->pivotRows());
        });
    }

    /**
     * Save the changes to the given kit type. The kits already built keep what they carry.
     */
    public function update(KitType $kitType): void
    {
        $this->validateTrimmed();

        DB::transaction(function () use ($kitType): void {
            $kitType->update($this->kitTypeAttributes());
            $kitType->securityDeviceTypes()->sync($this->pivotRows());
        });
    }

    /**
     * Get the entry of a security device type the kit type does not carry yet.
     *
     * @return array{on: bool, quantity?: int, internal?: int, external?: int}
     */
    private function emptyDevice(SecurityDeviceType $deviceType): array
    {
        return $deviceType->splits_internal_external
            ? ['on' => false, 'internal' => 0, 'external' => 0]
            : ['on' => false, 'quantity' => 1];
    }

    /**
     * Get the entry of a security device type the kit type carries. A type that started to be split after the kit type
     * was saved starts its split at zero, so the admin decides it.
     *
     * @return array{on: bool, quantity?: int, internal?: int, external?: int}
     */
    private function carriedDevice(SecurityDeviceType $deviceType, Pivot $pivot): array
    {
        if (! $deviceType->splits_internal_external) {
            return ['on' => true, 'quantity' => (int) $pivot->getAttribute('quantity')];
        }

        return [
            'on' => true,
            'internal' => (int) $pivot->getAttribute('internal_quantity'),
            'external' => (int) $pivot->getAttribute('external_quantity'),
        ];
    }

    /**
     * Fail when the kit type carries no security device type.
     */
    private function carriesAtLeastOneDevice(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->selectedDevices() === []) {
            $fail('Un tipo de kit debe llevar al menos un dispositivo.');
        }
    }

    /**
     * Fail when the kit type carries no pouch, or more than one: every kit is kept in a single pouch (T76). It is told
     * only once the kit type carries something, so an empty kit type gets a single message.
     */
    private function carriesOnePouch(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->selectedDevices() === []) {
            return;
        }

        $pouches = count(array_intersect(array_keys($this->selectedDevices()), $this->pouchDeviceTypeIds()));

        if ($pouches === 0) {
            $fail('Todo tipo de kit lleva una funda: enciende una.');
        } elseif ($pouches > 1) {
            $fail('Un tipo de kit lleva una sola funda: deja encendida solo una.');
        }
    }

    /**
     * Fail when a chosen security device type is not active and the kit type did not carry it already.
     */
    private function onlyChoosableDevices(string $attribute, mixed $value, Closure $fail): void
    {
        $choosableIds = $this->deviceTypes()->modelKeys();

        if (array_diff(array_keys($this->selectedDevices()), $choosableIds) !== []) {
            $fail('Elige solo tipos de dispositivo activos.');
        }
    }

    /**
     * Fail when the internal and the external quantities of a split type do not add up to between one and the most.
     */
    private function splitAddsUp(string $attribute, mixed $value, Closure $fail): void
    {
        $total = $this->splitTotal(is_array($value) ? $value : []);

        if ($total < 1) {
            $fail('Reparte al menos una entre interna y externa.');
        } elseif ($total > self::MAX_QUANTITY) {
            $fail('Entre interna y externa no pueden pasar de '.self::MAX_QUANTITY.'.');
        }
    }

    /**
     * Get the ids of the choosable security device types split between internal and external.
     *
     * @return array<int, int>
     */
    private function splitDeviceTypeIds(): array
    {
        return $this->deviceTypes()
            ->filter(fn (SecurityDeviceType $deviceType): bool => $deviceType->splits_internal_external)
            ->modelKeys();
    }

    /**
     * Get the ids of the choosable security device types of kind Funda (T76).
     *
     * @return array<int, int>
     */
    private function pouchDeviceTypeIds(): array
    {
        return $this->deviceTypes()
            ->filter(fn (SecurityDeviceType $deviceType): bool => $deviceType->kind === SecurityDeviceKind::Pouch)
            ->modelKeys();
    }

    /**
     * Get the security device types the kit type carries, as id => entry. The entries come from the browser, so one
     * without its keys counts as turned off or without quantity.
     *
     * @return array<int, array<string, mixed>>
     */
    private function selectedDevices(): array
    {
        return array_filter(
            $this->devices,
            fn (mixed $device): bool => data_get($device, 'on') === true,
        );
    }

    /**
     * Add up the internal and the external quantities of a split entry.
     *
     * @param  array<string, mixed>  $device
     */
    private function splitTotal(array $device): int
    {
        return (int) data_get($device, 'internal', 0) + (int) data_get($device, 'external', 0);
    }

    /**
     * Validate the data with the name trimmed, as it will be stored.
     */
    private function validateTrimmed(): void
    {
        $this->name = trim($this->name);

        $this->validate();
    }

    /**
     * Get the columns of the kit type that this form writes.
     *
     * @return array{name: string, container_type_id: int}
     */
    private function kitTypeAttributes(): array
    {
        return [
            'name' => $this->name,
            'container_type_id' => (int) $this->containerTypeId,
        ];
    }

    /**
     * Get the rows of the pivot table: each security device type the kit type carries, with its quantity. A split type
     * also keeps how many go to the internal and to the external assignment, and its quantity is their sum. The pouch
     * always goes once (T76).
     *
     * @return array<int, array{quantity: int, internal_quantity: int|null, external_quantity: int|null}>
     */
    private function pivotRows(): array
    {
        $splitIds = $this->splitDeviceTypeIds();
        $pouchIds = $this->pouchDeviceTypeIds();
        $rows = [];

        foreach ($this->selectedDevices() as $deviceTypeId => $device) {
            if (in_array($deviceTypeId, $pouchIds, true)) {
                $rows[$deviceTypeId] = ['quantity' => 1, 'internal_quantity' => null, 'external_quantity' => null];

                continue;
            }

            $rows[$deviceTypeId] = in_array($deviceTypeId, $splitIds, true)
                ? [
                    'quantity' => $this->splitTotal($device),
                    'internal_quantity' => (int) data_get($device, 'internal'),
                    'external_quantity' => (int) data_get($device, 'external'),
                ]
                : [
                    'quantity' => (int) data_get($device, 'quantity'),
                    'internal_quantity' => null,
                    'external_quantity' => null,
                ];
        }

        return $rows;
    }
}
