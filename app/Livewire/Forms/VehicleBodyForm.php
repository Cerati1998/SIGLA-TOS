<?php

namespace App\Livewire\Forms;

use App\Models\VehicleBody;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class VehicleBodyForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $vehicleBodyId = null;

    public string $name = '';

    /**
     * Fill the form with the body being edited.
     */
    public function setVehicleBody(VehicleBody $vehicleBody): void
    {
        $this->vehicleBodyId = $vehicleBody->id;
        $this->name = $vehicleBody->name;
    }

    /**
     * Get the validation rules of the form. The name is unique even among the deactivated bodies.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique(VehicleBody::class)->ignore($this->vehicleBodyId)],
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
            'name.unique' => 'Ya existe una carrocería con este nombre, aunque esté desactivada.',
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
        ];
    }

    /**
     * Create the body.
     */
    public function store(): void
    {
        $this->validateNormalized();

        VehicleBody::query()->create(['name' => $this->name]);
    }

    /**
     * Save the changes to the given body.
     */
    public function update(VehicleBody $vehicleBody): void
    {
        $this->validateNormalized();

        $vehicleBody->update(['name' => $this->name]);
    }

    /**
     * Validate the name as it will be stored: in capitals and with single spaces.
     */
    private function validateNormalized(): void
    {
        $this->name = VehicleBody::normalizeName($this->name);

        $this->validate();
    }
}
