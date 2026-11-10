<?php

namespace App\Livewire\Forms;

use App\Enums\CertificationSectionType;
use App\Models\CertificationSection;
use App\Services\CertificationSectionOrder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class CertificationSectionForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $certificationSectionId = null;

    public string $name = '';

    public string $type = '';

    /**
     * Fill the form with the section being edited.
     */
    public function setCertificationSection(CertificationSection $certificationSection): void
    {
        $this->certificationSectionId = $certificationSection->id;
        $this->name = $certificationSection->name;
        $this->type = $certificationSection->type->value;
    }

    /**
     * Get the validation rules of the form. The name is unique even among the deactivated sections.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique(CertificationSection::class)->ignore($this->certificationSectionId)],
            'type' => ['required', Rule::enum(CertificationSectionType::class)],
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
        ];
    }

    /**
     * Create the section at the end of the order.
     */
    public function store(CertificationSectionOrder $order): void
    {
        $this->validateWithTrimmedName();

        CertificationSection::create([
            'name' => $this->name,
            'type' => $this->type,
            'sort_order' => $order->nextPosition(),
        ]);
    }

    /**
     * Save the name and type of the given section.
     */
    public function update(CertificationSection $certificationSection): void
    {
        $this->validateWithTrimmedName();

        $certificationSection->update([
            'name' => $this->name,
            'type' => $this->type,
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
