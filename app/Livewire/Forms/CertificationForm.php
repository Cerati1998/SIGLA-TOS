<?php

namespace App\Livewire\Forms;

use App\Actions\Certifications\StartCertification;
use App\Models\Certification;
use App\Models\Kit;
use App\Models\User;
use App\Models\YardZone;
use App\Services\Sigla1LookupResult;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * What the inspector writes or scans to start a certification (H17): the container and the booking of the request,
 * the pouch of the kit, the optional inspection sheet and the yard zone.
 *
 * @phpstan-import-type GateOutRequest from Sigla1LookupResult
 */
class CertificationForm extends Form
{
    /**
     * The four letters of the container, written apart from its numbers only on screen.
     */
    public string $containerOwner = '';

    /**
     * The seven numbers of the container.
     */
    public string $containerNumber = '';

    public string $booking = '';

    public string $pouchCode = '';

    public string $inspectionSheetCode = '';

    public string $yardZoneId = '';

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        /** @var User $user */
        $user = Auth::user();

        return [
            'containerOwner' => ['required', 'string', 'regex:/^[A-Z]{4}$/'],
            'containerNumber' => ['required', 'string', 'regex:/^\d{7}$/'],
            'booking' => ['required', 'string', 'max:50'],
            'pouchCode' => ['required', 'string', 'max:50'],
            'inspectionSheetCode' => ['nullable', 'string', 'max:50', $this->inspectionSheetIsFree(...)],
            'yardZoneId' => ['required', Rule::exists(YardZone::class, 'id')
                ->where(fn (Builder $query) => $query->whereNull('deleted_at')->where('branch_id', $user->branch_id))],
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
            'containerOwner.required' => 'Escribe las 4 letras del contenedor.',
            'containerOwner.regex' => 'Escribe las 4 letras del contenedor.',
            'containerNumber.required' => 'Escribe los 7 números del contenedor.',
            'containerNumber.regex' => 'Escribe los 7 números del contenedor.',
            'booking.required' => 'Escribe el booking.',
            'pouchCode.required' => 'Escanea o escribe el código de la funda.',
            'yardZoneId.required' => 'Elige la zona de patio.',
            'yardZoneId.exists' => 'Elige una zona de patio activa de tu sede.',
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
            'containerOwner' => 'letras del contenedor',
            'containerNumber' => 'números del contenedor',
            'booking' => 'booking',
            'pouchCode' => 'funda',
            'inspectionSheetCode' => 'formato',
            'yardZoneId' => 'zona de patio',
        ];
    }

    /**
     * Get the container as SIGLA 1.0 knows it: its letters and its numbers together, as in ABCU1234567.
     */
    public function container(): string
    {
        return $this->containerOwner.$this->containerNumber;
    }

    /**
     * Validate the container and the booking, to look up the request (criterion 1).
     */
    public function validateRequest(): void
    {
        $this->containerOwner = mb_strtoupper(trim($this->containerOwner));
        $this->containerNumber = trim($this->containerNumber);
        $this->booking = mb_strtoupper(trim($this->booking));

        $this->validate(Arr::only($this->rules(), ['containerOwner', 'containerNumber', 'booking']));
    }

    /**
     * Validate the pouch code, to look up the kit (criterion 7).
     */
    public function validatePouch(): void
    {
        $this->pouchCode = mb_strtoupper(trim($this->pouchCode));

        $this->validate(Arr::only($this->rules(), ['pouchCode']));
    }

    /**
     * Validate the inspection sheet and the yard zone, and start the certification with the request and the kit
     * already found.
     *
     * @param  GateOutRequest  $gateOutRequest
     */
    public function store(StartCertification $startCertification, array $gateOutRequest, Kit $kit, User $inspector): Certification
    {
        $this->inspectionSheetCode = mb_strtoupper(trim($this->inspectionSheetCode));

        $this->validate(Arr::only($this->rules(), ['inspectionSheetCode', 'yardZoneId']));

        return $startCertification->handle(
            $gateOutRequest,
            $kit,
            $this->inspectionSheetCode === '' ? null : $this->inspectionSheetCode,
            YardZone::query()->findOrFail((int) $this->yardZoneId),
            $inspector,
        );
    }

    /**
     * An inspection sheet goes in one certification only (decision of the user of 2026-11-17).
     */
    private function inspectionSheetIsFree(string $attribute, mixed $value, Closure $fail): void
    {
        $certification = Certification::query()->where('inspection_sheet_code', $value)->first();

        if ($certification !== null) {
            $fail(sprintf('El formato %s ya está en la certificación del contenedor %s.', $value, $certification->container));
        }
    }
}
