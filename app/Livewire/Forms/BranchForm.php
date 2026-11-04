<?php

namespace App\Livewire\Forms;

use App\Actions\Branches\CreateBranch;
use App\Models\Branch;
use Livewire\Attributes\Locked;
use Livewire\Form;

class BranchForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $branchId = null;

    #[Locked]
    public string $code = '';

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $website = '';

    public string $ubigeo = '';

    public string $address = '';

    /**
     * Fill the form with the branch being edited.
     */
    public function setBranch(Branch $branch): void
    {
        $this->branchId = $branch->id;
        $this->code = $branch->code;
        $this->name = $branch->name;
        $this->phone = (string) $branch->phone;
        $this->email = (string) $branch->email;
        $this->website = (string) $branch->website;
        $this->ubigeo = $branch->ubigeo;
        $this->address = $branch->address;
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'ubigeo' => ['required', 'digits:6'],
            'address' => ['required', 'string', 'max:255'],
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
            'phone' => 'teléfono',
            'email' => 'correo electrónico',
            'website' => 'sitio web',
            'ubigeo' => 'ubigeo',
            'address' => 'dirección',
        ];
    }

    /**
     * Create the branch and return it, with the code it was given.
     */
    public function store(): Branch
    {
        $this->validate();

        return (new CreateBranch)->handle($this->branchAttributes());
    }

    /**
     * Save the changes to the given branch.
     */
    public function update(Branch $branch): void
    {
        $this->validate();

        $branch->update($this->branchAttributes());
    }

    /**
     * Get the columns of the branch that this form writes.
     *
     * @return array<string, string|null>
     */
    private function branchAttributes(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'website' => $this->website ?: null,
            'ubigeo' => $this->ubigeo,
            'address' => $this->address,
        ];
    }
}
