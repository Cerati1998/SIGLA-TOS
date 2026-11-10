<?php

namespace App\Livewire\Forms;

use App\Actions\SecurityDevices\ReceiveSecurityDeviceLot;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Livewire\Form;

class SecurityDeviceLotForm extends Form
{
    /**
     * The taken serial numbers listed in the error, at most.
     */
    public const int LISTED_TAKEN_SERIAL_NUMBERS = 20;

    public string $shippingLineId = '';

    public string $providerId = '';

    public string $securityDeviceTypeId = '';

    public string $receivedAt = '';

    public string $dispatchNumber = '';

    public string $seriesPrefix = '';

    public string $minDigits = '';

    public string $seriesStart = '';

    public string $seriesEnd = '';

    public string $notes = '';

    /**
     * Start the form with today as the reception date.
     */
    public function setDefaults(): void
    {
        $this->receivedAt = now('America/Lima')->toDateString();
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'shippingLineId' => ['required', Rule::exists('shipping_lines', 'id')->withoutTrashed()],
            'providerId' => ['required', Rule::exists('providers', 'id')->withoutTrashed()],
            'securityDeviceTypeId' => ['required', Rule::exists('security_device_types', 'id')->withoutTrashed()],
            'receivedAt' => ['required', 'date_format:Y-m-d'],
            'dispatchNumber' => ['required', 'string', 'max:50'],
            'seriesPrefix' => ['required', 'alpha_num:ascii', 'max:20'],
            'minDigits' => ['required', 'integer', 'between:1,15'],
            'seriesStart' => ['required', 'digits_between:1,15'],
            'seriesEnd' => ['required', 'digits_between:1,15'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'shippingLineId.exists' => 'Elige una línea naviera activa.',
            'providerId.exists' => 'Elige un proveedor activo.',
            'securityDeviceTypeId.exists' => 'Elige un tipo de dispositivo activo.',
            'seriesStart.digits_between' => 'El correlativo inicial solo lleva números, hasta 15 dígitos.',
            'seriesEnd.digits_between' => 'El correlativo final solo lleva números, hasta 15 dígitos.',
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
            'shippingLineId' => 'línea naviera',
            'providerId' => 'proveedor',
            'securityDeviceTypeId' => 'tipo de dispositivo',
            'receivedAt' => 'fecha de recepción',
            'dispatchNumber' => 'número de guía',
            'seriesPrefix' => 'serie',
            'minDigits' => 'mínimo de dígitos',
            'seriesStart' => 'correlativo inicial',
            'seriesEnd' => 'correlativo final',
            'notes' => 'observaciones',
        ];
    }

    /**
     * Save the lot with all its devices, or nothing when the range is wrong or a serial number is taken.
     */
    public function store(ReceiveSecurityDeviceLot $receiveLot, User $user): ?SecurityDeviceLot
    {
        $this->seriesPrefix = mb_strtoupper(trim($this->seriesPrefix));
        $this->dispatchNumber = trim($this->dispatchNumber);
        $this->validate();

        if (! $this->hasValidRange()) {
            return null;
        }

        $announcement = $this->announcement();
        $lot = $receiveLot->handle($announcement, $this->lotAttributes(), $user);

        if ($lot === null) {
            $this->addError('serialNumbers', $this->takenSerialNumbersMessage($receiveLot->takenSerialNumbers($announcement->serialNumbers())));

            return null;
        }

        $this->reset();
        $this->setDefaults();

        return $lot;
    }

    /**
     * Check that the first correlative is not after the last one and that the range fits in a lot.
     */
    private function hasValidRange(): bool
    {
        $quantity = (int) $this->seriesEnd - (int) $this->seriesStart + 1;

        if ($quantity < 1) {
            $this->addError('seriesRange', 'El correlativo inicial no puede ser mayor que el final.');

            return false;
        }

        if ($quantity > ReceiveSecurityDeviceLot::MAX_DEVICES) {
            $this->addError('seriesRange', sprintf(
                'Un lote puede tener como máximo %s dispositivos. Este rango tiene %s.',
                $this->formatNumber(ReceiveSecurityDeviceLot::MAX_DEVICES),
                $this->formatNumber($quantity),
            ));

            return false;
        }

        return true;
    }

    /**
     * Build the announcement of the lot, not saved yet.
     */
    private function announcement(): SecurityDeviceAnnouncement
    {
        return new SecurityDeviceAnnouncement([
            'shipping_line_id' => (int) $this->shippingLineId,
            'provider_id' => (int) $this->providerId,
            'security_device_type_id' => (int) $this->securityDeviceTypeId,
            'series_prefix' => $this->seriesPrefix,
            'min_digits' => (int) $this->minDigits,
            'series_start' => (int) $this->seriesStart,
            'series_end' => (int) $this->seriesEnd,
        ]);
    }

    /**
     * Get the columns of the lot taken from the form.
     *
     * @return array{received_at: string, dispatch_number: string, notes: string|null}
     */
    private function lotAttributes(): array
    {
        return [
            'received_at' => $this->receivedAt,
            'dispatch_number' => $this->dispatchNumber,
            'notes' => trim($this->notes) ?: null,
        ];
    }

    /**
     * Describe the serial numbers that already exist: all of them when they are few, the first ones and the total otherwise.
     *
     * @param  list<string>  $takenSerialNumbers
     */
    private function takenSerialNumbersMessage(array $takenSerialNumbers): string
    {
        $total = count($takenSerialNumbers);
        $listed = Arr::join(array_slice($takenSerialNumbers, 0, self::LISTED_TAKEN_SERIAL_NUMBERS), ', ', ' y ');

        if ($total === 1) {
            return "1 serie ya existe en el sistema: {$listed}. Revisa el rango.";
        }

        if ($total <= self::LISTED_TAKEN_SERIAL_NUMBERS) {
            return "{$total} series ya existen en el sistema: {$listed}. Revisa el rango.";
        }

        return sprintf(
            '%s series ya existen en el sistema. Las %d primeras: %s. Revisa el rango.',
            $this->formatNumber($total),
            self::LISTED_TAKEN_SERIAL_NUMBERS,
            $listed,
        );
    }

    /**
     * Format a whole number with a space between thousands, as in 10 000.
     */
    private function formatNumber(int $number): string
    {
        return number_format($number, 0, ',', ' ');
    }
}
