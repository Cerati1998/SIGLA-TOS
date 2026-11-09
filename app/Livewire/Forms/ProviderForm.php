<?php

namespace App\Livewire\Forms;

use App\Concerns\LooksUpDocuments;
use App\Models\Identity;
use App\Models\Provider;
use App\Services\SunatLookupResult;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class ProviderForm extends Form
{
    use LooksUpDocuments;

    /**
     * The document types a provider can have, with the digits of each one.
     * PHP turns numeric ids such as `6` into integer keys, so the keys are typed as `array-key`.
     *
     * @var array<array-key, array{label: string, digits: int}>
     */
    public const array DOCUMENT_TYPES = [
        Identity::RUC => ['label' => 'RUC', 'digits' => 11],
        Identity::DNI => ['label' => 'DNI', 'digits' => 8],
    ];

    public bool $openModal = false;

    #[Locked]
    public ?int $providerId = null;

    public string $identityId = Identity::RUC;

    public string $documentNumber = '';

    public string $businessName = '';

    public string $address = '';

    public string $email = '';

    public string $phone = '';

    /**
     * Fill the form with the provider being edited.
     */
    public function setProvider(Provider $provider): void
    {
        $this->providerId = $provider->id;
        $this->identityId = $provider->identity_id;
        $this->documentNumber = $provider->document_number;
        $this->businessName = $provider->business_name;
        $this->address = (string) $provider->address;
        $this->email = (string) $provider->email;
        $this->phone = (string) $provider->phone;
        $this->rememberLookedUpDocument();
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $rules = $this->lookupRules();

        $rules['documentNumber'][] = Rule::unique(Provider::class, 'document_number')
            ->where('identity_id', $this->identityId)
            ->ignore($this->providerId);

        return [
            ...$rules,
            'businessName' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
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
            'documentNumber.digits' => 'El '.$this->documentLabel().' debe tener :digits dígitos.',
            'documentNumber.unique' => 'Ya existe un proveedor con este tipo y número de documento, aunque esté desactivado.',
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
            'businessName' => 'razón social',
            'address' => 'dirección',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
        ];
    }

    /**
     * Create the provider.
     */
    public function store(): void
    {
        $this->validateTrimmed();

        Provider::query()->create($this->providerAttributes());
    }

    /**
     * Save the changes to the given provider.
     */
    public function update(Provider $provider): void
    {
        $this->validateTrimmed();

        $provider->update($this->providerAttributes());
    }

    /**
     * Get the rules that check the type and the format of the document, the ones checked before looking it up.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function lookupRules(): array
    {
        $digits = self::DOCUMENT_TYPES[$this->identityId]['digits'] ?? null;

        return [
            'identityId' => ['required', 'string', Rule::in(array_keys(self::DOCUMENT_TYPES))],
            'documentNumber' => ['required', 'string', ...($digits === null ? [] : ["digits:{$digits}"])],
        ];
    }

    /**
     * Get the name of the chosen document type for the messages.
     */
    private function documentLabel(): string
    {
        return self::DOCUMENT_TYPES[$this->identityId]['label'] ?? 'documento';
    }

    /**
     * Put the data the service found in the form: with a DNI, the full name goes in the business name.
     */
    protected function fillFromLookup(SunatLookupResult $result): void
    {
        if ($this->identityId === Identity::DNI) {
            $this->businessName = (string) $result->data['full_name'];

            return;
        }

        $this->businessName = (string) $result->data['business_name'];
        $this->address = $result->data['address'] ?? $this->address;
    }

    /**
     * Validate the data as it will be stored, without spaces around it.
     */
    private function validateTrimmed(): void
    {
        foreach (['documentNumber', 'businessName', 'address', 'email', 'phone'] as $field) {
            $this->{$field} = trim($this->{$field});
        }

        $this->validate();
    }

    /**
     * Get the columns of the provider that this form writes.
     *
     * @return array{identity_id: string, document_number: string, business_name: string, address: string|null, email: string|null, phone: string|null}
     */
    private function providerAttributes(): array
    {
        return [
            'identity_id' => $this->identityId,
            'document_number' => $this->documentNumber,
            'business_name' => $this->businessName,
            'address' => $this->address === '' ? null : $this->address,
            'email' => $this->email === '' ? null : $this->email,
            'phone' => $this->phone === '' ? null : $this->phone,
        ];
    }
}
