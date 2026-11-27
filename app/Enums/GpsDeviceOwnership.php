<?php

namespace App\Enums;

/**
 * Whether a GPS device belongs to the company or is rented from a provider (H40).
 */
enum GpsDeviceOwnership: string
{
    case Own = 'own';
    case Rented = 'rented';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Own => 'Propio',
            self::Rented => 'Alquilado',
        };
    }

    /**
     * Get the options of a select, as stored value and name shown on screen.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $ownership): array => ['value' => $ownership->value, 'label' => $ownership->label()], self::cases()),
            'label',
            'value',
        );
    }
}
