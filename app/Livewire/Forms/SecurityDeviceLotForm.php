<?php

namespace App\Livewire\Forms;

use App\Actions\SecurityDevices\CorrectSecurityDeviceLot;
use App\Actions\SecurityDevices\ReceiveSecurityDeviceLot;
use App\Enums\SecurityDeviceLotCorrection;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Livewire\Attributes\Locked;
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

    public string $seriesSuffix = '';

    public string $notes = '';

    /**
     * Whether the series of the lot being corrected can no longer change, because one of its devices changed status (H28).
     */
    #[Locked]
    public bool $seriesLocked = false;

    /**
     * The origin of the lot being corrected. It stays a valid choice even if it was deactivated later.
     *
     * @var array{shippingLineId: int|null, providerId: int|null, securityDeviceTypeId: int|null}
     */
    #[Locked]
    public array $currentOriginIds = ['shippingLineId' => null, 'providerId' => null, 'securityDeviceTypeId' => null];

    /**
     * Start the form with today as the reception date.
     */
    public function setDefaults(): void
    {
        $this->receivedAt = now('America/Lima')->toDateString();
    }

    /**
     * Fill the form with the lot being corrected (H28).
     */
    public function setLot(SecurityDeviceLot $lot): void
    {
        $announcement = $lot->announcement;

        $this->shippingLineId = (string) $announcement->shipping_line_id;
        $this->providerId = (string) $announcement->provider_id;
        $this->securityDeviceTypeId = (string) $announcement->security_device_type_id;
        $this->currentOriginIds = [
            'shippingLineId' => $announcement->shipping_line_id,
            'providerId' => $announcement->provider_id,
            'securityDeviceTypeId' => $announcement->security_device_type_id,
        ];
        $this->receivedAt = $lot->received_at->toDateString();
        $this->dispatchNumber = $lot->dispatch_number;
        $this->notes = (string) $lot->notes;
        $this->seriesLocked = ! $lot->seriesCanBeCorrected();
        $this->setSeries($announcement);
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'shippingLineId' => ['required', $this->activeOrCurrent('shipping_lines', 'shippingLineId')],
            'providerId' => ['required', $this->activeOrCurrent('providers', 'providerId')],
            'securityDeviceTypeId' => ['required', $this->activeOrCurrent('security_device_types', 'securityDeviceTypeId')],
            'receivedAt' => ['required', 'date_format:Y-m-d'],
            'dispatchNumber' => ['required', 'string', 'max:50'],
            'seriesPrefix' => ['required', 'alpha_num:ascii', 'max:20'],
            'minDigits' => ['required', 'integer', 'between:1,15'],
            'seriesStart' => ['required', 'digits_between:1,15'],
            'seriesEnd' => ['required', 'digits_between:1,15'],
            'seriesSuffix' => ['nullable', 'alpha:ascii', 'max:10'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Accept an active record, or the one the lot being corrected already has.
     *
     * @param  'shippingLineId'|'providerId'|'securityDeviceTypeId'  $field
     */
    private function activeOrCurrent(string $table, string $field): Exists
    {
        $currentId = $this->currentOriginIds[$field];

        return Rule::exists($table, 'id')->where(fn (Builder $query) => $query->where(
            fn (Builder $active) => $active->whereNull('deleted_at')->when($currentId, fn (Builder $current, int $id) => $current->orWhere('id', $id)),
        ));
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
            'seriesSuffix.alpha' => 'El final de serie solo lleva letras.',
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
            'seriesPrefix' => 'inicio de serie',
            'minDigits' => 'mínimo de dígitos',
            'seriesStart' => 'correlativo inicial',
            'seriesEnd' => 'correlativo final',
            'seriesSuffix' => 'final de serie',
            'notes' => 'observaciones',
        ];
    }

    /**
     * Save the lot with all its devices, or nothing when the range is wrong or a serial number is taken.
     */
    public function store(ReceiveSecurityDeviceLot $receiveLot, User $user): ?SecurityDeviceLot
    {
        if (! $this->validateLot()) {
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
     * Save the corrections of the lot (H28). Return null, with nothing saved, when the range is wrong, the series can no longer
     * change or a new serial number is taken.
     */
    public function update(CorrectSecurityDeviceLot $correctLot, SecurityDeviceLot $lot): ?SecurityDeviceLotCorrection
    {
        if ($this->seriesLocked) {
            $this->setSeries($lot->announcement);
        }

        if (! $this->validateLot()) {
            return null;
        }

        $correction = $correctLot->handle($lot, $this->announcementAttributes(), $this->lotAttributes());

        if ($correction === SecurityDeviceLotCorrection::SeriesLocked) {
            $this->seriesLocked = true;
            $this->setSeries($lot->announcement);
            $this->addError('serialNumbers', 'La serie ya no se puede cambiar porque algunos dispositivos del lote cambiaron de estado. Revisa los demás datos y guarda de nuevo.');

            return null;
        }

        if ($correction === SecurityDeviceLotCorrection::SerialNumbersTaken) {
            $this->addError('serialNumbers', $this->takenSerialNumbersMessage($correctLot->takenSerialNumbers($this->announcement(), $lot)));

            return null;
        }

        return $correction;
    }

    /**
     * Put the series of an announcement in the form.
     */
    private function setSeries(SecurityDeviceAnnouncement $announcement): void
    {
        $this->seriesPrefix = $announcement->series_prefix;
        $this->minDigits = (string) $announcement->min_digits;
        $this->seriesStart = (string) $announcement->series_start;
        $this->seriesEnd = (string) $announcement->series_end;
        $this->seriesSuffix = (string) $announcement->series_suffix;
    }

    /**
     * Clean up and validate the fields, and check the range.
     */
    private function validateLot(): bool
    {
        $this->seriesPrefix = mb_strtoupper(trim($this->seriesPrefix));
        $this->seriesSuffix = mb_strtoupper(trim($this->seriesSuffix));
        $this->dispatchNumber = trim($this->dispatchNumber);
        $this->validate();

        return $this->hasValidRange();
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
        return new SecurityDeviceAnnouncement($this->announcementAttributes());
    }

    /**
     * Get the columns of the announcement taken from the form.
     *
     * @return array{shipping_line_id: int, provider_id: int, security_device_type_id: int, series_prefix: string, min_digits: int, series_start: int, series_end: int, series_suffix: string|null}
     */
    private function announcementAttributes(): array
    {
        return [
            'shipping_line_id' => (int) $this->shippingLineId,
            'provider_id' => (int) $this->providerId,
            'security_device_type_id' => (int) $this->securityDeviceTypeId,
            'series_prefix' => $this->seriesPrefix,
            'min_digits' => (int) $this->minDigits,
            'series_start' => (int) $this->seriesStart,
            'series_end' => (int) $this->seriesEnd,
            'series_suffix' => $this->seriesSuffix ?: null,
        ];
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
