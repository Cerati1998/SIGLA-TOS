<?php

namespace App\Livewire\Forms;

use App\Actions\Certifications\ReopenCertification;
use App\Models\Certification;
use App\Models\User;
use Livewire\Attributes\Locked;
use Livewire\Form;

/**
 * The reason to reopen a closed certification (H19, criterion 7).
 */
class CertificationReopeningForm extends Form
{
    public const int REASON_MAX = 500;

    public bool $openModal = false;

    #[Locked]
    public ?int $certificationId = null;

    #[Locked]
    public string $container = '';

    public string $reason = '';

    /**
     * Fix the certification that is reopened.
     */
    public function setCertification(Certification $certification): void
    {
        $this->certificationId = $certification->id;
        $this->container = $certification->container;
    }

    /**
     * Reopen the certification with the reason written.
     */
    public function reopen(ReopenCertification $reopenCertification, User $user): void
    {
        $this->validate();

        $reopenCertification->handle(Certification::query()->findOrFail($this->certificationId), trim($this->reason), $user);
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
            'reason.required' => 'Escribe el motivo de la reapertura.',
            'reason.max' => 'El motivo puede tener hasta '.self::REASON_MAX.' caracteres.',
        ];
    }
}
