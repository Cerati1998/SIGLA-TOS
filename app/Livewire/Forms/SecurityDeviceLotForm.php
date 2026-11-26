<?php

namespace App\Livewire\Forms;

use App\Actions\SecurityDevices\CorrectSecurityDeviceLot;
use App\Enums\SecurityDeviceLotCorrection;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Livewire\Attributes\Locked;
use Livewire\Form;

/**
 * The correction of a lot of an announcement (H28, H37): its type, its series and its notes.
 */
class SecurityDeviceLotForm extends Form
{
    /**
     * The taken serial numbers listed in the error, at most.
     */
    public const int LISTED_TAKEN_SERIAL_NUMBERS = 20;

    public string $securityDeviceTypeId = '';

    public string $seriesPrefix = '';

    public string $minDigits = '';

    public string $seriesStart = '';

    public string $seriesEnd = '';

    public string $seriesSuffix = '';

    public string $notes = '';

    /**
     * Whether the series of the lot can no longer change, because one of its devices changed status (H28).
     */
    #[Locked]
    public bool $seriesLocked = false;

    /**
     * The type of the lot being corrected. It stays a valid choice even if it was deactivated later.
     *
     * @var array{securityDeviceTypeId: int|null}
     */
    #[Locked]
    public array $currentOriginIds = ['securityDeviceTypeId' => null];

    /**
     * Fill the form with the lot being corrected.
     */
    public function setLot(SecurityDeviceLot $lot): void
    {
        $this->securityDeviceTypeId = (string) $lot->security_device_type_id;
        $this->currentOriginIds = ['securityDeviceTypeId' => $lot->security_device_type_id];
        $this->notes = (string) $lot->notes;
        $this->seriesLocked = ! $lot->seriesCanBeCorrected();
        $this->setSeries($lot);
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'securityDeviceTypeId' => ['required', self::activeOrCurrentType($this->currentOriginIds['securityDeviceTypeId']), $this->sameBoxesAsCurrentType(...)],
            ...self::seriesRules($this->selectedType()),
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get the rules of the series of a lot of the given type (H07). A type that does not use a series needs no prefix (T62).
     *
     * @return array<string, array<int, mixed>>
     */
    public static function seriesRules(?SecurityDeviceType $type): array
    {
        return [
            'seriesPrefix' => [Rule::requiredIf($type->uses_series ?? true), 'alpha_num:ascii', 'max:20'],
            'minDigits' => ['required', 'integer', 'between:1,15'],
            'seriesStart' => ['required', 'digits_between:1,15'],
            'seriesEnd' => ['required', 'digits_between:1,15'],
            'seriesSuffix' => ['nullable', 'alpha:ascii', 'max:10'],
        ];
    }

    /**
     * Accept an active type, or the one the lot being corrected already has.
     */
    public static function activeOrCurrentType(?int $currentId): Exists
    {
        return Rule::exists('security_device_types', 'id')->where(fn (Builder $query) => $query->where(
            fn (Builder $active) => $active->whereNull('deleted_at')->when($currentId, fn (Builder $current, int $id) => $current->orWhere('id', $id)),
        ));
    }

    /**
     * Let the lot change its type only to one with the same boxes, so the status and the serial numbers of its devices
     * stay right (H28, T62).
     */
    private function sameBoxesAsCurrentType(string $attribute, mixed $value, Closure $fail): void
    {
        $currentId = $this->currentOriginIds['securityDeviceTypeId'];
        $selected = $this->selectedType();

        if ($currentId === null || $selected === null || $selected->id === $currentId) {
            return;
        }

        $current = SecurityDeviceType::withTrashed()->find($currentId);

        if ($current !== null && ($current->uses_series !== $selected->uses_series || $current->requires_lot_approval !== $selected->requires_lot_approval)) {
            $fail(sprintf(
                'Solo puedes cambiarlo por un tipo que también %s y %s aprobación en lote, como %s.',
                $current->uses_series ? 'utilice serie' : 'no utilice serie',
                $current->requires_lot_approval ? 'requiera' : 'no requiera',
                $current->name,
            ));
        }
    }

    /**
     * Get the type of security device chosen in the form, if any.
     */
    public function selectedType(): ?SecurityDeviceType
    {
        return ctype_digit($this->securityDeviceTypeId) ? SecurityDeviceType::withTrashed()->find((int) $this->securityDeviceTypeId) : null;
    }

    /**
     * Get the messages that change from the default ones.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'securityDeviceTypeId.exists' => 'Elige un tipo de dispositivo activo.',
            ...self::seriesMessages(),
        ];
    }

    /**
     * Get the messages of the series that change from the default ones.
     *
     * @return array<string, string>
     */
    public static function seriesMessages(): array
    {
        return [
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
            'securityDeviceTypeId' => 'tipo de dispositivo',
            ...self::seriesAttributes(),
            'notes' => 'observaciones',
        ];
    }

    /**
     * Get the names of the fields of the series in the validation messages.
     *
     * @return array<string, string>
     */
    public static function seriesAttributes(): array
    {
        return [
            'seriesPrefix' => 'inicio de serie',
            'minDigits' => 'mínimo de dígitos',
            'seriesStart' => 'correlativo inicial',
            'seriesEnd' => 'correlativo final',
            'seriesSuffix' => 'final de serie',
        ];
    }

    /**
     * Save the corrections of the lot. Return null, with nothing saved, when the range is wrong, the series can no longer
     * change or a new serial number is taken.
     */
    public function update(CorrectSecurityDeviceLot $correctLot, SecurityDeviceLot $lot): ?SecurityDeviceLotCorrection
    {
        if ($this->seriesLocked) {
            $this->setSeries($lot);
        }

        $usesSeries = $this->selectedType()->uses_series ?? true;
        $this->seriesPrefix = $usesSeries ? mb_strtoupper(trim($this->seriesPrefix)) : '';
        $this->seriesSuffix = $usesSeries ? mb_strtoupper(trim($this->seriesSuffix)) : '';
        $this->validate();

        $rangeError = self::rangeError((int) $this->seriesStart, (int) $this->seriesEnd);

        if ($rangeError !== null) {
            $this->addError('seriesRange', $rangeError);

            return null;
        }

        $correction = $correctLot->handle($lot, $this->attributes());

        if ($correction === SecurityDeviceLotCorrection::SeriesLocked) {
            $this->seriesLocked = true;
            $this->setSeries($lot);
            $this->addError('serialNumbers', 'La serie ya no se puede cambiar porque algunos dispositivos del lote cambiaron de estado. Revisa los demás datos y guarda de nuevo.');

            return null;
        }

        if ($correction === SecurityDeviceLotCorrection::SerialNumbersTaken) {
            $correctedLot = new SecurityDeviceLot($this->attributes());
            $this->addError('serialNumbers', self::takenSerialNumbersMessage($correctLot->takenSerialNumbers($correctedLot, $lot), $correctedLot->securityDeviceType));

            return null;
        }

        return $correction;
    }

    /**
     * Put the series of a lot in the form.
     */
    private function setSeries(SecurityDeviceLot $lot): void
    {
        $this->seriesPrefix = $lot->series_prefix;
        $this->minDigits = (string) $lot->min_digits;
        $this->seriesStart = (string) $lot->series_start;
        $this->seriesEnd = (string) $lot->series_end;
        $this->seriesSuffix = (string) $lot->series_suffix;
    }

    /**
     * Get why a range cannot be a lot, or null when it can: the first correlative cannot be after the last one, and a lot
     * fits in an announcement (H07, criterion 3; H37, criterion 6).
     */
    public static function rangeError(int $start, int $end): ?string
    {
        $quantity = $end - $start + 1;

        if ($quantity < 1) {
            return 'El correlativo inicial no puede ser mayor que el final.';
        }

        if ($quantity > SecurityDeviceAnnouncement::MAX_DEVICES) {
            return sprintf(
                'Un lote puede tener como máximo %s dispositivos. Este rango tiene %s.',
                self::formatNumber(SecurityDeviceAnnouncement::MAX_DEVICES),
                self::formatNumber($quantity),
            );
        }

        return null;
    }

    /**
     * Get the columns of the lot taken from the form.
     *
     * @return array{security_device_type_id: int, series_prefix: string, min_digits: int, series_start: int, series_end: int, series_suffix: string|null, notes: string|null}
     */
    private function attributes(): array
    {
        return [
            'security_device_type_id' => (int) $this->securityDeviceTypeId,
            'series_prefix' => $this->seriesPrefix,
            'min_digits' => (int) $this->minDigits,
            'series_start' => (int) $this->seriesStart,
            'series_end' => (int) $this->seriesEnd,
            'series_suffix' => $this->seriesSuffix ?: null,
            'notes' => trim($this->notes) ?: null,
        ];
    }

    /**
     * Describe the serial numbers that already exist: all of them when they are few, the first ones and the total otherwise.
     * For a type that does not use a series, they exist in that type (T62).
     *
     * @param  list<string>  $takenSerialNumbers
     */
    public static function takenSerialNumbersMessage(array $takenSerialNumbers, SecurityDeviceType $type): string
    {
        $total = count($takenSerialNumbers);
        $listed = Arr::join(array_slice($takenSerialNumbers, 0, self::LISTED_TAKEN_SERIAL_NUMBERS), ', ', ' y ');
        $where = $type->uses_series ? 'en el sistema' : 'en '.$type->name;

        if ($total === 1) {
            return "1 serie ya existe {$where}: {$listed}. Revisa el rango.";
        }

        if ($total <= self::LISTED_TAKEN_SERIAL_NUMBERS) {
            return "{$total} series ya existen {$where}: {$listed}. Revisa el rango.";
        }

        return sprintf(
            '%s series ya existen %s. Las %d primeras: %s. Revisa el rango.',
            self::formatNumber($total),
            $where,
            self::LISTED_TAKEN_SERIAL_NUMBERS,
            $listed,
        );
    }

    /**
     * Format a whole number with a space between thousands, as in 10 000.
     */
    public static function formatNumber(int $number): string
    {
        return number_format($number, 0, ',', ' ');
    }
}
