<?php

namespace App\Livewire\Forms;

use App\Actions\Kits\CreateKit;
use App\Models\Kit;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Livewire\Form;

class KitForm extends Form
{
    public string $pouchCode = '';

    public string $kitTypeId = '';

    public string $shippingLineId = '';

    /**
     * Get the validation rules of the form: the three data are required and only active kit types and shipping lines
     * can be chosen (H14, criteria 1 to 3).
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'pouchCode' => ['required', 'string', 'max:50', Rule::unique('kits', 'pouch_code')],
            'kitTypeId' => ['required', Rule::exists('kit_types', 'id')->where(fn (Builder $query) => $query->whereNull('deleted_at'))],
            'shippingLineId' => ['required', Rule::exists('shipping_lines', 'id')->where(fn (Builder $query) => $query->whereNull('deleted_at'))],
        ];
    }

    /**
     * Get the messages that change from the default ones.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'pouchCode.unique' => 'La funda :input ya es de otro kit.',
            'kitTypeId.exists' => 'Elige un tipo de kit activo.',
            'shippingLineId.exists' => 'Elige una línea naviera activa.',
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
            'pouchCode' => 'funda',
            'kitTypeId' => 'tipo de kit',
            'shippingLineId' => 'línea naviera',
        ];
    }

    /**
     * Validate the data and create the kit, empty and in Armando.
     */
    public function store(CreateKit $createKit, User $user): Kit
    {
        $this->pouchCode = mb_strtoupper(trim($this->pouchCode));

        $this->validate();

        return $createKit->handle([
            'pouchCode' => $this->pouchCode,
            'kitTypeId' => (int) $this->kitTypeId,
            'shippingLineId' => (int) $this->shippingLineId,
        ], $user);
    }
}
