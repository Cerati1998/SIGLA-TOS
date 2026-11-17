<?php

namespace App\Livewire\Forms;

use App\Actions\KitDispatches\DispatchKits;
use App\Models\KitDispatch;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

/**
 * The window «Despacho masivo de kits» (H15): who receives the kits and which kits are marked.
 */
class KitDispatchForm extends Form
{
    public bool $openModal = false;

    public ?int $receiverId = null;

    /**
     * The ids of the marked kits, by scanning their pouch or with their checkbox.
     *
     * @var list<int>
     */
    public array $kitIds = [];

    /**
     * Mark a kit. Return false when it was marked already.
     */
    public function mark(int $kitId): bool
    {
        $kitIds = $this->markedKitIds();

        if (in_array($kitId, $kitIds, true)) {
            return false;
        }

        $this->kitIds = [...$kitIds, $kitId];

        return true;
    }

    /**
     * Get the ids of the marked kits as integers: a checkbox sends them as text.
     *
     * @return list<int>
     */
    public function markedKitIds(): array
    {
        return array_values(array_unique(array_map('intval', $this->kitIds)));
    }

    /**
     * Validate the window and dispatch the marked kits to the worker. A kit that can no longer be dispatched stops it,
     * with the reason under the marked kits.
     */
    public function store(DispatchKits $dispatchKits, User $user): KitDispatch
    {
        $this->validate();

        try {
            return $dispatchKits->handle(Worker::query()->findOrFail($this->receiverId), $this->markedKitIds(), $user);
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(['form.kitIds' => $exception->errors()['kitIds'] ?? []]);
        }
    }

    /**
     * Get the validation rules of the window.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'receiverId' => ['required', 'integer', Rule::exists(Worker::class, 'id')->where(fn (Builder $query) => $query->whereNull('deleted_at'))],
            'kitIds' => ['required', 'array', 'min:1'],
            'kitIds.*' => ['integer'],
        ];
    }

    /**
     * Get the messages of the rules that need their own wording.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'receiverId.required' => 'Elige quién recibe los kits.',
            'receiverId.exists' => 'Elige un trabajador activo.',
            'kitIds.required' => 'Marca al menos un kit para despachar.',
            'kitIds.min' => 'Marca al menos un kit para despachar.',
        ];
    }
}
