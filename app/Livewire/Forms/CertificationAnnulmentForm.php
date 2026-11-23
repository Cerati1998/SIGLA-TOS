<?php

namespace App\Livewire\Forms;

use App\Actions\Certifications\AnnulCertification;
use App\Enums\CertificationKitDecision;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

/**
 * The reason to annul the assignment of the kit of a certification and what is done with the kit (H32).
 */
class CertificationAnnulmentForm extends Form
{
    public const int REASON_MAX = 500;

    public bool $openModal = false;

    #[Locked]
    public ?int $certificationId = null;

    #[Locked]
    public string $container = '';

    #[Locked]
    public string $pouchCode = '';

    public string $reason = '';

    public string $kitDecision = '';

    /**
     * Fix the certification whose assignment is annulled.
     */
    public function setCertification(Certification $certification): void
    {
        $this->certificationId = $certification->id;
        $this->container = $certification->container;
        $this->pouchCode = $certification->kit->pouch_code;
    }

    /**
     * Annul the assignment with the reason written and the decision chosen.
     */
    public function annul(AnnulCertification $annulCertification, User $user): void
    {
        $this->reason = trim($this->reason);
        $this->validate();

        $annulCertification->handle(
            Certification::query()->findOrFail($this->certificationId),
            $this->reason,
            CertificationKitDecision::from($this->kitDecision),
            $user,
        );
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:'.self::REASON_MAX],
            'kitDecision' => ['required', Rule::enum(CertificationKitDecision::class)],
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
            'reason.required' => 'Escribe el motivo de la anulación.',
            'reason.max' => 'El motivo puede tener hasta '.self::REASON_MAX.' caracteres.',
            'kitDecision.required' => 'Elige qué se hace con el kit, según responda la naviera.',
            'kitDecision.enum' => 'Elige qué se hace con el kit, según responda la naviera.',
        ];
    }
}
