<?php

namespace App\Livewire\Forms;

use App\Enums\CertificationSectionType;
use App\Models\CertificationSection;
use App\Models\SecurityDeviceType;
use App\Services\CertificationSectionOrder;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class CertificationSectionForm extends Form
{
    /**
     * The most of one security device type a section can ask for.
     */
    public const int MAX_QUANTITY = 999;

    public bool $openModal = false;

    #[Locked]
    public ?int $certificationSectionId = null;

    /**
     * The security device types the section carried when it was opened for editing: they can be kept even if they
     * were deactivated.
     *
     * @var array<int, int>
     */
    #[Locked]
    public array $savedDeviceTypeIds = [];

    public string $name = '';

    public string $type = '';

    /**
     * The security device types the inspector places in the section and how many of each (H12). It can be empty.
     *
     * @var list<array{id: int|string, quantity: int|string}>
     */
    public array $devices = [];

    /**
     * Fill the form with the section being edited.
     */
    public function setCertificationSection(CertificationSection $certificationSection): void
    {
        $this->certificationSectionId = $certificationSection->id;
        $this->name = $certificationSection->name;
        $this->type = $certificationSection->type->value;
        $this->savedDeviceTypeIds = $certificationSection->securityDeviceTypes->modelKeys();
        $this->devices = array_values($certificationSection->securityDeviceTypes
            ->map(fn (SecurityDeviceType $deviceType): array => [
                'id' => $deviceType->id,
                'quantity' => (int) $deviceType->getRelationValue('pivot')->getAttribute('quantity'),
            ])
            ->all());
    }

    /**
     * Get the security device types that can be chosen: the active ones, plus the ones the section already carries.
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
     * Get the security device types that can be chosen, as the browser needs them to add rows and show their names.
     *
     * @return list<array{id: int, name: string, kind: string, trashed: bool}>
     */
    public function deviceTypeOptions(): array
    {
        return array_values($this->deviceTypes()
            ->map(fn (SecurityDeviceType $deviceType): array => [
                'id' => $deviceType->id,
                'name' => $deviceType->name,
                'kind' => $deviceType->kind->label(),
                'trashed' => $deviceType->trashed(),
            ])
            ->all());
    }

    /**
     * Get the validation rules of the form. The name is unique even among the deactivated sections. A security
     * device type appears once, and its quantity is a whole number greater than zero.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique(CertificationSection::class)->ignore($this->certificationSectionId)],
            'type' => ['required', Rule::enum(CertificationSectionType::class)],
            'devices' => ['array', $this->onlyChoosableDevices(...)],
            'devices.*.id' => ['required', 'integer', 'distinct'],
            'devices.*.quantity' => ['required', 'integer', 'min:1', 'max:'.self::MAX_QUANTITY],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.unique' => 'Ya existe una sección con este nombre, aunque esté desactivada.',
            'type.required' => 'Elige si la sección es interna o externa.',
            'devices.*.id.distinct' => 'Un tipo de dispositivo va una sola vez en la sección: cambia su cantidad.',
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
            'type' => 'tipo',
            'devices' => 'dispositivos',
            'devices.*.id' => 'tipo de dispositivo',
            'devices.*.quantity' => 'cantidad',
        ];
    }

    /**
     * Create the section at the end of the order, with the security device types it carries.
     */
    public function store(CertificationSectionOrder $order): void
    {
        $this->validateWithTrimmedName();

        DB::transaction(function () use ($order): void {
            $certificationSection = CertificationSection::create([
                'name' => $this->name,
                'type' => $this->type,
                'sort_order' => $order->nextPosition(),
            ]);

            $certificationSection->securityDeviceTypes()->sync($this->pivotRows());
        });
    }

    /**
     * Save the name, the type and the security device types of the given section.
     */
    public function update(CertificationSection $certificationSection): void
    {
        $this->validateWithTrimmedName();

        DB::transaction(function () use ($certificationSection): void {
            $certificationSection->update([
                'name' => $this->name,
                'type' => $this->type,
            ]);

            $certificationSection->securityDeviceTypes()->sync($this->pivotRows());
        });
    }

    /**
     * Fail when a chosen security device type is not active and the section did not carry it already.
     */
    private function onlyChoosableDevices(string $attribute, mixed $value, Closure $fail): void
    {
        $chosenIds = array_map(fn (mixed $device): int => (int) data_get($device, 'id'), $this->devices);

        if (array_diff($chosenIds, $this->deviceTypes()->modelKeys()) !== []) {
            $fail('Elige solo tipos de dispositivo activos.');
        }
    }

    /**
     * Validate the name as it will be stored.
     */
    private function validateWithTrimmedName(): void
    {
        $this->name = trim($this->name);

        $this->validate();
    }

    /**
     * Get the rows of the pivot table: each security device type the section carries, with its quantity.
     *
     * @return array<int, array{quantity: int}>
     */
    private function pivotRows(): array
    {
        $rows = [];

        foreach ($this->devices as $device) {
            $rows[(int) data_get($device, 'id')] = ['quantity' => (int) data_get($device, 'quantity')];
        }

        return $rows;
    }
}
