<?php

namespace App\Livewire\Forms;

use App\Actions\Drivers\AddDriverToCarrier;
use App\Concerns\LooksUpDocuments;
use App\Models\Carrier;
use App\Models\CarrierDriver;
use App\Models\Driver;
use App\Models\Identity;
use App\Services\SunatLookupResult;
use App\Services\SunatService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class DriverForm extends Form
{
    use LooksUpDocuments {
        lookup as lookUpInService;
    }

    /**
     * The digits of a DNI.
     */
    public const int DNI_DIGITS = 8;

    public bool $openModal = false;

    #[Locked]
    public ?int $driverId = null;

    /**
     * The carrier the driver is added to, when the form adds one.
     */
    #[Locked]
    public ?int $carrierId = null;

    /**
     * The driver already registered with the DNI, whose data is shown but not changed when it is added.
     */
    #[Locked]
    public ?int $registeredDriverId = null;

    /**
     * A driver is always identified by the DNI.
     */
    #[Locked]
    public string $identityId = Identity::DNI;

    public string $documentNumber = '';

    public string $fullName = '';

    public string $license = '';

    /**
     * Fill the form with the driver being edited.
     */
    public function setDriver(Driver $driver): void
    {
        $this->driverId = $driver->id;
        $this->documentNumber = $driver->document_number;
        $this->fullName = $driver->full_name;
        $this->license = $driver->license;
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

        $rules['documentNumber'][] = Rule::unique(Driver::class, 'document_number')
            ->where('identity_id', Identity::DNI)
            ->ignore($this->driverId);

        return [
            ...$rules,
            'fullName' => ['required', 'string', 'max:255'],
            'license' => ['required', 'string', 'max:15', Rule::unique(Driver::class, 'license')->ignore($this->driverId)],
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
            'documentNumber.unique' => 'Ya existe un conductor con este DNI, aunque esté desactivado.',
            'license.unique' => 'Ya existe un conductor con este brevete, aunque esté desactivado.',
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
            'documentNumber' => 'DNI',
            'fullName' => 'nombres y apellidos',
            'license' => 'brevete',
        ];
    }

    /**
     * Look up the DNI to add a driver to the carrier: first among the registered drivers, and only when it is not
     * there, in the external service. Outside a carrier it only looks in the external service.
     */
    public function lookup(SunatService $sunat, bool $force = false): void
    {
        if ($this->carrierId === null) {
            $this->lookUpInService($sunat, $force);

            return;
        }

        $this->lookupStatus = null;
        $this->documentNumber = trim($this->documentNumber);
        $this->validate($this->lookupRules(), $this->messages(), $this->validationAttributes());

        $driver = $this->registeredDriver();

        if ($driver === null) {
            $this->forgetRegisteredDriver();
            $this->lookUpInService($sunat, $force);

            return;
        }

        $this->registeredDriverId = $driver->id;
        $this->fullName = $driver->full_name;
        $this->license = $driver->license;
        $this->rememberLookedUpDocument();

        $link = CarrierDriver::withTrashed()->where('carrier_id', $this->carrierId)->where('driver_id', $driver->id)->first();

        $this->lookupStatus = match (true) {
            $link === null => 'registered',
            $link->trashed() => 'inactive_in_carrier',
            default => 'in_carrier',
        };

        if ($this->lookupStatus === 'in_carrier') {
            $this->addError('documentNumber', 'Este conductor ya está en este transportista.');
        }
    }

    /**
     * Add the driver to the carrier: the registered one keeps its data, a new one is created with the form data.
     */
    public function storeForCarrier(AddDriverToCarrier $addDriverToCarrier): void
    {
        $this->documentNumber = trim($this->documentNumber);
        $this->validate($this->lookupRules(), $this->messages(), $this->validationAttributes());

        if ($this->registeredDriver() === null) {
            $this->validateNormalized();
        }

        $addDriverToCarrier->handle(
            Carrier::withTrashed()->findOrFail($this->carrierId),
            $this->documentNumber,
            ['full_name' => $this->fullName, 'license' => $this->license],
        );
    }

    /**
     * Forget the driver found among the registered ones, and its data, when the DNI changed to one that is not registered.
     */
    public function forgetRegisteredDriver(): void
    {
        if ($this->registeredDriverId === null) {
            return;
        }

        $this->registeredDriverId = null;
        $this->fullName = '';
        $this->license = '';
    }

    /**
     * Get the driver already registered with the DNI of the form, if there is one.
     */
    private function registeredDriver(): ?Driver
    {
        return Driver::withTrashed()
            ->where('identity_id', Identity::DNI)
            ->where('document_number', $this->documentNumber)
            ->first();
    }

    /**
     * Save the changes to the given driver.
     */
    public function update(Driver $driver): void
    {
        $this->validateNormalized();

        $driver->update($this->driverAttributes());
    }

    /**
     * Get the rules that check the format of the DNI, the ones checked before looking it up.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function lookupRules(): array
    {
        return [
            'documentNumber' => ['required', 'string', 'digits:'.self::DNI_DIGITS],
        ];
    }

    /**
     * Put the full name the service found in the form.
     */
    protected function fillFromLookup(SunatLookupResult $result): void
    {
        $this->fullName = (string) $result->data['full_name'];
    }

    /**
     * Validate the data as it will be stored: without spaces around it, and the license in capitals and without spaces.
     */
    private function validateNormalized(): void
    {
        $this->documentNumber = trim($this->documentNumber);
        $this->fullName = trim($this->fullName);
        $this->license = mb_strtoupper((string) preg_replace('/\s+/', '', $this->license));

        $this->validate();
    }

    /**
     * Get the columns of the driver that this form writes.
     *
     * @return array{identity_id: string, document_number: string, full_name: string, license: string}
     */
    private function driverAttributes(): array
    {
        return [
            'identity_id' => Identity::DNI,
            'document_number' => $this->documentNumber,
            'full_name' => $this->fullName,
            'license' => $this->license,
        ];
    }
}
