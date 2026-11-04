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
     * Fill the form with the security device type being edited.
     */
    public function setSecurityDeviceType(SecurityDeviceType $securityDeviceType): void
    {
        $this->securityDeviceTypeId = $securityDeviceType->id;
        $this->kindLabel = $securityDeviceType->kind->label();
        $this->name = $securityDeviceType->name;
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
        ]);
    }

    /**
     * Save the new name of the given security device type.
     */
    public function update(SecurityDeviceType $securityDeviceType): void
    {
        $this->validateWithTrimmedName();

        $securityDeviceType->update(['name' => $this->name]);
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
