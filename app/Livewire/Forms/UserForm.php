<?php

namespace App\Livewire\Forms;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Form;

class UserForm extends Form
{
    use ProfileValidationRules;

    #[Locked]
    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    /**
     * Fill the form with the user being edited.
     */
    public function setUser(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return $this->profileRules($this->userId);
    }

    /**
     * Create the user with a random password and return it in plain text, the only time it is available.
     */
    public function store(): string
    {
        $this->validate();

        $password = Str::password(16);

        User::query()->create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $password,
        ]);

        return $password;
    }

    /**
     * Save the changes to the given user.
     */
    public function update(User $user): void
    {
        $this->validate();

        $user->update($this->only(['name', 'email']));
    }
}
