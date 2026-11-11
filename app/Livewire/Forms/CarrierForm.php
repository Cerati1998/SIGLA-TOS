<?php

namespace App\Livewire\Forms;

use App\Concerns\LooksUpDocuments;
use App\Models\Carrier;
use App\Models\Identity;
use App\Services\SunatLookupResult;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class CarrierForm extends Form
{
    use LooksUpDocuments;

    /**
     * The digits of a RUC.
     */
    public const int RUC_DIGITS = 11;

    public bool $openModal = false;

    #[Locked]
    public ?int $carrierId = null;

    /**
     * A carrier is always identified by its RUC.
     */
    #[Locked]
    public string $identityId = Identity::RUC;

    public string $documentNumber = '';

    public string $businessName = '';

    public string $address = '';

    public string $email = '';

    public string $phone = '';

    /**
     * Fill the form with the carrier being edited.
     */
    public function setCarrier(Carrier $carrier): void
    {
        $this->carrierId = $carrier->id;
        $this->documentNumber = $carrier->document_number;
        $this->businessName = $carrier->business_name;
        $this->address = (string) $carrier->address;
        $this->email = (string) $carrier->email;
        $this->phone = (string) $carrier->phone;
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

        $rules['documentNumber'][] = Rule::unique(Carrier::class, 'document_number')
            ->where('identity_id', Identity::RUC)
            ->ignore($this->carrierId);

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
            'documentNumber.digits' => 'El RUC debe tener :digits dígitos.',
            'documentNumber.unique' => 'Ya existe un transportista con este RUC, aunque esté desactivado.',
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
            'documentNumber' => 'RUC',
            'businessName' => 'razón social',
            'address' => 'dirección',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
        ];
    }

    /**
     * Create the carrier.
     */
    public function store(): void
    {
        $this->validateTrimmed();

        Carrier::query()->create($this->carrierAttributes());
    }

    /**
     * Save the changes to the given carrier.
     */
    public function update(Carrier $carrier): void
    {
        $this->validateTrimmed();

        $carrier->update($this->carrierAttributes());
    }

    /**
     * Get the rules that check the format of the RUC, the ones checked before looking it up.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function lookupRules(): array
    {
        return [
            'documentNumber' => ['required', 'string', 'digits:'.self::RUC_DIGITS],
        ];
    }

    /**
     * Put the business name and the address the service found in the form.
     */
    protected function fillFromLookup(SunatLookupResult $result): void
    {
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
     * Get the columns of the carrier that this form writes.
     *
     * @return array{identity_id: string, document_number: string, business_name: string, address: string|null, email: string|null, phone: string|null}
     */
    private function carrierAttributes(): array
    {
        return [
            'identity_id' => Identity::RUC,
            'document_number' => $this->documentNumber,
            'business_name' => $this->businessName,
            'address' => $this->address === '' ? null : $this->address,
            'email' => $this->email === '' ? null : $this->email,
            'phone' => $this->phone === '' ? null : $this->phone,
        ];
    }
}
