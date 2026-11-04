<?php

namespace App\Livewire\Forms;

use App\Models\ShippingLine;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class ShippingLineForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $shippingLineId = null;

    public string $code = '';

    public string $name = '';

    /**
     * Fill the form with the shipping line being edited.
     */
    public function setShippingLine(ShippingLine $shippingLine): void
    {
        $this->shippingLineId = $shippingLine->id;
        $this->code = $shippingLine->code;
        $this->name = $shippingLine->name;
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique(ShippingLine::class)->ignore($this->shippingLineId)],
            'name' => ['required', 'string', 'max:100'],
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
            'code' => 'código',
            'name' => 'nombre',
        ];
    }

    /**
     * Create the shipping line.
     */
    public function store(): void
    {
        $this->validateWithNormalizedCode();

        ShippingLine::create($this->shippingLineAttributes());
    }

    /**
     * Save the changes to the given shipping line.
     */
    public function update(ShippingLine $shippingLine): void
    {
        $this->validateWithNormalizedCode();

        $shippingLine->update($this->shippingLineAttributes());
    }

    /**
     * Validate the code as it will be stored, so that "msk" and "MSK" count as the same one.
     */
    private function validateWithNormalizedCode(): void
    {
        $this->code = Str::upper(trim($this->code));

        $this->validate();
    }

    /**
     * Get the columns of the shipping line that this form writes.
     *
     * @return array<string, string>
     */
    private function shippingLineAttributes(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
        ];
    }
}
