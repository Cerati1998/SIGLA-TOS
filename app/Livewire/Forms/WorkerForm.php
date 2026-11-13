<?php

namespace App\Livewire\Forms;

use App\Concerns\LooksUpDocuments;
use App\Models\Identity;
use App\Models\Provider;
use App\Models\Worker;
use App\Services\SunatLookupResult;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class WorkerForm extends Form
{
    use LooksUpDocuments;

    /**
     * The digits of each document type whose name is looked up: only the DNI, and only when creating, as in users.
     *
     * @var array<array-key, int>
     */
    public const array LOOKUP_DIGITS = [Identity::DNI => 8];

    /**
     * Where a worker comes from: an own worker or one of a provider.
     */
    public const string OWN = 'own';

    public const string FROM_PROVIDER = 'provider';

    public bool $openModal = false;

    #[Locked]
    public ?int $workerId = null;

    /**
     * The provider the worker already had, kept as a choice even if it was deactivated afterwards.
     */
    #[Locked]
    public ?int $savedProviderId = null;

    public string $identityId = Identity::DNI;

    public string $documentNumber = '';

    public string $fullName = '';

    public string $origin = self::OWN;

    public string $providerId = '';

    /**
     * Fill the form with the worker being edited.
     */
    public function setWorker(Worker $worker): void
    {
        $this->workerId = $worker->id;
        $this->savedProviderId = $worker->provider_id;
        $this->identityId = $worker->identity_id;
        $this->documentNumber = $worker->document_number;
        $this->fullName = $worker->full_name;
        $this->origin = $worker->is_own ? self::OWN : self::FROM_PROVIDER;
        $this->providerId = (string) $worker->provider_id;
        $this->rememberLookedUpDocument();
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $digits = self::LOOKUP_DIGITS[$this->identityId] ?? null;

        return [
            'identityId' => ['required', 'string', Rule::exists(Identity::class, 'id')],
            'documentNumber' => [
                'required',
                'string',
                'max:15',
                ...($digits === null ? [] : ["digits:{$digits}"]),
                Rule::unique(Worker::class, 'document_number')->where('identity_id', $this->identityId)->ignore($this->workerId),
            ],
            'fullName' => ['required', 'string', 'max:255'],
            'origin' => ['required', Rule::in([self::OWN, self::FROM_PROVIDER])],
            'providerId' => [
                'exclude_unless:origin,'.self::FROM_PROVIDER,
                'required',
                'integer',
                Rule::exists(Provider::class, 'id')->where(fn (Builder $query) => $query
                    ->whereNull('deleted_at')
                    ->orWhere('id', $this->savedProviderId)),
            ],
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
            'documentNumber.digits' => 'El DNI debe tener :digits dígitos.',
            'documentNumber.unique' => 'Ya existe un trabajador con este documento, aunque esté desactivado.',
            'providerId.required' => 'Elige el proveedor del trabajador.',
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
            'fullName' => 'nombres y apellidos',
            'origin' => 'procedencia',
            'providerId' => 'proveedor',
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
     * Get the providers to choose from: the active ones and the one the worker already had.
     *
     * @return array<int, string>
     */
    public function providers(): array
    {
        return Provider::withTrashed()
            ->where(fn ($query) => $query->whereNull('deleted_at')->orWhere('id', $this->savedProviderId))
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'deleted_at'])
            ->mapWithKeys(fn (Provider $provider): array => [
                $provider->id => $provider->trashed() ? "{$provider->business_name} (desactivado)" : $provider->business_name,
            ])
            ->all();
    }

    /**
     * Create the worker.
     */
    public function store(): void
    {
        $this->validateTrimmed();

        Worker::query()->create($this->workerAttributes());
    }

    /**
     * Save the changes to the given worker.
     */
    public function update(Worker $worker): void
    {
        $this->validateTrimmed();

        $worker->update($this->workerAttributes());
    }

    /**
     * Get the rules that check the document before looking it up: only a DNI with its digits.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function lookupRules(): array
    {
        return [
            'identityId' => ['required', 'string', Rule::in(array_keys(self::LOOKUP_DIGITS))],
            'documentNumber' => ['required', 'string', 'digits:'.self::LOOKUP_DIGITS[Identity::DNI]],
        ];
    }

    /**
     * Put the name the service found in the form, as given names and then surnames, capitalized, as in users.
     */
    protected function fillFromLookup(SunatLookupResult $result): void
    {
        $name = collect([$result->data['first_name'], $result->data['first_last_name'], $result->data['second_last_name']])
            ->filter()
            ->implode(' ');

        $this->fullName = Str::title(mb_strtolower($name === '' ? (string) $result->data['full_name'] : $name));
    }

    /**
     * Validate the data as it will be stored, without spaces around it.
     */
    private function validateTrimmed(): void
    {
        $this->documentNumber = trim($this->documentNumber);
        $this->fullName = trim($this->fullName);

        $this->validate();
    }

    /**
     * Get the columns of the worker that this form writes: an own worker has no provider.
     *
     * @return array{identity_id: string, document_number: string, full_name: string, is_own: bool, provider_id: int|null}
     */
    private function workerAttributes(): array
    {
        $isOwn = $this->origin === self::OWN;

        return [
            'identity_id' => $this->identityId,
            'document_number' => $this->documentNumber,
            'full_name' => $this->fullName,
            'is_own' => $isOwn,
            'provider_id' => $isOwn ? null : (int) $this->providerId,
        ];
    }
}
