<?php

namespace App\Livewire\Forms;

use App\Enums\SecurityDeviceKind;
use App\Models\SecurityDeviceType;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class SecurityDeviceTypeForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $securityDeviceTypeId = null;

    #[Locked]
    public string $kindLabel = '';

    public string $name = '';

    public string $kind = '';

    /**
     * Whether its quantity in a kit type is split between the internal and the external assignment.
     */
    public bool $splitsInternalExternal = false;

    /**
     * Fill the form with the security device type being edited.
     */
    public function setSecurityDeviceType(SecurityDeviceType $securityDeviceType): void
    {
        $this->securityDeviceTypeId = $securityDeviceType->id;
        $this->kindLabel = $securityDeviceType->kind->label();
        $this->name = $securityDeviceType->name;
        $this->splitsInternalExternal = $securityDeviceType->splits_internal_external;
    }

    /**
     * Get the validation rules of the form. The kind is only chosen when creating.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100', Rule::unique(SecurityDeviceType::class)->ignore($this->securityDeviceTypeId)],
            'splitsInternalExternal' => ['boolean'],
        ];

        if ($this->securityDeviceTypeId === null) {
            $rules['kind'] = ['required', Rule::enum(SecurityDeviceKind::class)];
        }

        return $rules;
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
            'kind' => 'tipo',
            'splitsInternalExternal' => 'reparto entre interna y externa',
        ];
    }

    /**
     * Create the security device type.
     */
    public function store(): void
    {
        $this->validateWithTrimmedName();

        SecurityDeviceType::create([
            'name' => $this->name,
            'kind' => $this->kind,
            'splits_internal_external' => $this->splitsInternalExternal,
        ]);
    }

    /**
     * Save the new name of the given security device type and whether it is split between internal and external. The
     * kit types already saved keep their quantities until they are edited.
     */
    public function update(SecurityDeviceType $securityDeviceType): void
    {
        $this->validateWithTrimmedName();

        $securityDeviceType->update([
            'name' => $this->name,
            'splits_internal_external' => $this->splitsInternalExternal,
        ]);
    }

    /**
     * Validate the name as it will be stored.
     */
    private function validateWithTrimmedName(): void
    {
        $this->name = trim($this->name);

        $this->validate();
    }
}
