<?php

namespace App\Livewire\Forms;

use App\Models\Branch;
use App\Models\YardZone;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class YardZoneForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $yardZoneId = null;

    /**
     * The branch the zone had when it was opened for editing: it can be kept even if it was deactivated.
     */
    #[Locked]
    public ?int $savedBranchId = null;

    public string $branchId = '';

    public string $name = '';

    public string $capacity = '';

    /**
     * Fill the form with the zone being edited.
     */
    public function setYardZone(YardZone $yardZone): void
    {
        $this->yardZoneId = $yardZone->id;
        $this->savedBranchId = $yardZone->branch_id;
        $this->branchId = (string) $yardZone->branch_id;
        $this->name = $yardZone->name;
        $this->capacity = (string) $yardZone->capacity;
    }

    /**
     * Get the validation rules of the form. The name is unique in its branch even among the deactivated zones, and the
     * branch must be active, unless it is the one the zone already had.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'branchId' => ['required', 'integer', Rule::exists(Branch::class, 'id')->where(
                fn (Builder $query): Builder => $query->whereNull('deleted_at')->orWhere('id', $this->savedBranchId),
            )],
            'name' => ['required', 'string', 'max:100', Rule::unique(YardZone::class)
                ->where('branch_id', (int) $this->branchId)
                ->ignore($this->yardZoneId)],
            'capacity' => ['required', 'integer', 'min:1', 'max:99999'],
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
            'branchId.required' => 'Elige la sucursal de la zona.',
            'branchId.integer' => 'Elige la sucursal de la zona.',
            'branchId.exists' => 'Elige una sucursal activa.',
            'name.required' => 'Escribe el nombre de la zona.',
            'name.unique' => 'Ya existe una zona con este nombre en la sucursal, aunque esté desactivada.',
            'capacity.required' => 'Escribe la capacidad de la zona.',
            'capacity.integer' => 'La capacidad debe ser un número entero mayor que 0.',
            'capacity.min' => 'La capacidad debe ser un número entero mayor que 0.',
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
            'branchId' => 'sucursal',
            'name' => 'nombre',
            'capacity' => 'capacidad',
        ];
    }

    /**
     * Create the zone.
     */
    public function store(): void
    {
        $this->validateTrimmed();

        YardZone::query()->create($this->yardZoneAttributes());
    }

    /**
     * Save the changes to the given zone.
     */
    public function update(YardZone $yardZone): void
    {
        $this->validateTrimmed();

        $yardZone->update($this->yardZoneAttributes());
    }

    /**
     * Validate the data as it will be stored, without spaces around.
     */
    private function validateTrimmed(): void
    {
        $this->name = trim($this->name);
        $this->capacity = trim($this->capacity);

        $this->validate();
    }

    /**
     * Get the columns of the zone that this form writes.
     *
     * @return array{branch_id: int, name: string, capacity: int}
     */
    private function yardZoneAttributes(): array
    {
        return [
            'branch_id' => (int) $this->branchId,
            'name' => $this->name,
            'capacity' => (int) $this->capacity,
        ];
    }
}
