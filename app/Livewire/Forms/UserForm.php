<?php

namespace App\Livewire\Forms;

use App\Concerns\ProfileValidationRules;
use App\Models\Branch;
use App\Models\Identity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class UserForm extends Form
{
    use ProfileValidationRules;

    public bool $openModal = false;

    #[Locked]
    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $identityId = '';

    public string $documentNumber = '';

    public string $branchId = '';

    /**
     * Fill the form with the user being edited.
     */
    public function setUser(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->identityId = $user->identity_id;
        $this->documentNumber = $user->document_number;
        $this->branchId = (string) $user->branch_id;
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            ...$this->profileRules($this->userId),
            'identityId' => ['required', 'string', Rule::exists(Identity::class, 'id')],
            'documentNumber' => [
                'required',
                'string',
                'max:15',
                Rule::unique(User::class, 'document_number')->where('identity_id', $this->identityId)->ignore($this->userId),
            ],
            'branchId' => ['required', 'integer', Rule::exists(Branch::class, 'id')->withoutTrashed()],
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
            'identityId' => 'tipo de documento',
            'documentNumber' => 'número de documento',
            'branchId' => 'sucursal',
        ];
    }

    /**
     * Get the document types to choose from.
     *
     * @return Collection<int, Identity>
     */
    public function identities(): Collection
    {
        return Identity::query()->orderBy('id')->get(['id', 'description']);
    }

    /**
     * Get the active branches to choose from.
     *
     * @return Collection<int, Branch>
     */
    public function branches(): Collection
    {
        return Branch::query()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Create the user with a random password and return it in plain text, the only time it is available.
     */
    public function store(): string
    {
        $this->validate();

        $password = Str::password(16);

        User::query()->create([...$this->userAttributes(), 'password' => $password]);

        return $password;
    }

    /**
     * Save the changes to the given user.
     */
    public function update(User $user): void
    {
        $this->validate();

        $user->update($this->userAttributes());
    }

    /**
     * Get the columns of the user that this form writes.
     *
     * @return array{name: string, email: string, identity_id: string, document_number: string, branch_id: int}
     */
    private function userAttributes(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'identity_id' => $this->identityId,
            'document_number' => $this->documentNumber,
            'branch_id' => (int) $this->branchId,
        ];
    }
}
