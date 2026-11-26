<?php

namespace App\Livewire\Forms;

use App\Actions\Reinspections\StartReinspection;
use App\Models\Checkpoint;
use App\Models\Reinspection;
use App\Models\SecurityDeviceReplacement;
use App\Models\User;
use Livewire\Form;

/**
 * The reason to start a reinspection from a checkpoint with incorrect series (H35, criterion 1). It becomes the reason
 * of each replacement and of each discard, so it fits in theirs.
 */
class ReinspectionForm extends Form
{
    public const int REASON_MAX = SecurityDeviceReplacement::REASON_MAX;

    public bool $openModal = false;

    public string $reason = '';

    /**
     * Start the reinspection with the reason written.
     */
    public function start(StartReinspection $startReinspection, Checkpoint $checkpoint, User $user): Reinspection
    {
        $this->reason = trim($this->reason);
        $this->validate();

        return $startReinspection->handle($checkpoint, $this->reason, $user);
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:'.self::REASON_MAX],
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
            'reason.required' => 'Escribe el motivo de la reinspección.',
            'reason.max' => 'El motivo puede tener hasta '.self::REASON_MAX.' caracteres.',
        ];
    }
}
