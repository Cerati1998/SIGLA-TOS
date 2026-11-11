<?php

namespace App\Livewire\Forms;

use App\Enums\VehicleCategory;
use App\Enums\VehicleKind;
use App\Models\Vehicle;
use App\Models\VehicleBody;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class VehicleForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $vehicleId = null;

    /**
     * The body the vehicle had when it was opened for editing: it can be kept even if it was deactivated.
     */
    #[Locked]
    public ?int $savedBodyId = null;

    public string $plate = '';

    public string $brand = '';

    public string $kind = VehicleKind::Tractor->value;

    public string $vehicleBodyId = '';

    public string $category = VehicleCategory::N3->value;

    /**
     * Fill the form with the vehicle being edited.
     */
    public function setVehicle(Vehicle $vehicle): void
    {
        $this->vehicleId = $vehicle->id;
        $this->savedBodyId = $vehicle->vehicle_body_id;
        $this->plate = $vehicle->plate;
        $this->brand = $vehicle->brand;
        $this->kind = $vehicle->kind->value;
        $this->vehicleBodyId = (string) $vehicle->vehicle_body_id;
        $this->category = $vehicle->category->value;
    }

    /**
     * Get the validation rules of the form. The plate is unique even among the deactivated vehicles, and the body
     * must be active, unless it is the one the vehicle already had.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'plate' => ['required', 'string', 'alpha_num:ascii', 'max:10', Rule::unique(Vehicle::class)->ignore($this->vehicleId)],
            'brand' => ['required', 'string', 'max:100'],
            'kind' => ['required', Rule::enum(VehicleKind::class)],
            'vehicleBodyId' => ['required', 'integer', Rule::exists(VehicleBody::class, 'id')->where(
                fn (Builder $query): Builder => $query->whereNull('deleted_at')->orWhere('id', $this->savedBodyId),
            )],
            'category' => ['required', Rule::enum(VehicleCategory::class)],
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
            'plate.unique' => 'Ya existe un vehículo con esta placa, aunque esté desactivado.',
            'plate.alpha_num' => 'La placa solo lleva letras y números.',
            'vehicleBodyId.required' => 'Elige la carrocería.',
            'vehicleBodyId.exists' => 'Elige una carrocería activa.',
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
            'plate' => 'placa',
            'brand' => 'marca',
            'kind' => 'tipo',
            'vehicleBodyId' => 'carrocería',
            'category' => 'categoría',
        ];
    }

    /**
     * Create the vehicle.
     */
    public function store(): void
    {
        $this->validateNormalized();

        Vehicle::query()->create($this->vehicleAttributes());
    }

    /**
     * Save the changes to the given vehicle.
     */
    public function update(Vehicle $vehicle): void
    {
        $this->validateNormalized();

        $vehicle->update($this->vehicleAttributes());
    }

    /**
     * Validate the data as it will be stored: the plate in capitals, without spaces or hyphens.
     */
    private function validateNormalized(): void
    {
        $this->plate = Vehicle::normalizePlate($this->plate);
        $this->brand = trim($this->brand);

        $this->validate();
    }

    /**
     * Get the columns of the vehicle that this form writes.
     *
     * @return array{plate: string, brand: string, kind: string, vehicle_body_id: int, category: string}
     */
    private function vehicleAttributes(): array
    {
        return [
            'plate' => $this->plate,
            'brand' => $this->brand,
            'kind' => $this->kind,
            'vehicle_body_id' => (int) $this->vehicleBodyId,
            'category' => $this->category,
        ];
    }
}
