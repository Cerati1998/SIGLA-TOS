<?php

namespace App\Livewire\Forms;

use App\Models\ContainerType;
use App\Models\KitType;
use App\Models\SecurityDeviceType;
use Closure;
use Illuminate\Database\Eloquent\Collection;
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
     * One entry per security device type that can be chosen, keyed by its id: whether the kit type carries it and how many.
     *
     * @var array<int, array{on: bool, quantity: int|string}>
     */
    public array $devices = [];

    /**
     * Prepare the form for a new kit type: every active security device type, turned off.
     */
    public function fillDevices(): void
    {
        $this->devices = $this->deviceTypes()
            ->mapWithKeys(fn (SecurityDeviceType $deviceType): array => [$deviceType->id => ['on' => false, 'quantity' => 1]])
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

        $quantities = $kitType->securityDeviceTypes->mapWithKeys(
            fn (SecurityDeviceType $deviceType): array => [$deviceType->id => (int) $deviceType->getRelationValue('pivot')->getAttribute('quantity')],
        );

        $this->savedDeviceTypeIds = $quantities->keys()->all();
        $this->devices = $this->deviceTypes()
            ->mapWithKeys(fn (SecurityDeviceType $deviceType): array => [$deviceType->id => [
                'on' => $quantities->has($deviceType->id),
                'quantity' => $quantities->get($deviceType->id, 1),
            ]])
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
     * least one security device type, and every quantity is a whole number greater than zero.
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
            'devices' => ['array', $this->carriesAtLeastOneDevice(...), $this->onlyChoosableDevices(...)],
        ];

        foreach (array_keys($this->selectedDevices()) as $deviceTypeId) {
            $rules["devices.{$deviceTypeId}.quantity"] = ['required', 'integer', 'min:1', 'max:'.self::MAX_QUANTITY];
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
        return [
            'name.required' => 'Escribe el nombre del tipo de kit.',
            'name.unique' => 'Ya existe un tipo de kit con este nombre, aunque esté desactivado.',
            'containerTypeId.required' => 'Elige el tipo de contenedor.',
            'containerTypeId.exists' => 'Elige un tipo de contenedor activo.',
            'devices.*.quantity.required' => 'Escribe una cantidad entera mayor que cero.',
            'devices.*.quantity.integer' => 'Escribe una cantidad entera mayor que cero.',
            'devices.*.quantity.min' => 'Escribe una cantidad entera mayor que cero.',
            'devices.*.quantity.max' => 'La cantidad no puede pasar de '.self::MAX_QUANTITY.'.',
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
     * Fail when the kit type carries no security device type.
     */
    private function carriesAtLeastOneDevice(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->selectedDevices() === []) {
            $fail('Un tipo de kit debe llevar al menos un dispositivo.');
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
     * Get the security device types the kit type carries, as id => quantity. The entries come from the browser, so
     * one without its keys counts as turned off or without quantity.
     *
     * @return array<int, int|string>
     */
    private function selectedDevices(): array
    {
        $selected = [];

        foreach ($this->devices as $deviceTypeId => $device) {
            if (data_get($device, 'on') === true) {
                $selected[$deviceTypeId] = data_get($device, 'quantity', '');
            }
        }

        return $selected;
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
     * Get the rows of the pivot table: each security device type the kit type carries, with its quantity.
     *
     * @return array<int, array{quantity: int}>
     */
    private function pivotRows(): array
    {
        return array_map(fn (int|string $quantity): array => ['quantity' => (int) $quantity], $this->selectedDevices());
    }
}
