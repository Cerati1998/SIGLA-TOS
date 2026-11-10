<?php

namespace App\Enums;

enum SecurityDeviceStatus: string
{
    case Available = 'available';
    case InKit = 'in_kit';
    case Used = 'used';
    case Damaged = 'damaged';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::InKit => 'En un kit',
            self::Used => 'Usado',
            self::Damaged => 'Dañado',
        };
    }

    /**
     * Get the name of the status for a count of devices, as in "180 Disponibles".
     */
    public function pluralLabel(): string
    {
        return match ($this) {
            self::Available => 'Disponibles',
            self::InKit => 'En un kit',
            self::Used => 'Usados',
            self::Damaged => 'Dañados',
        };
    }

    /**
     * Get the variant of the badge that shows the status.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::InKit => 'info',
            self::Used => 'neutral',
            self::Damaged => 'warning',
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
            array_map(fn (self $status): array => ['value' => $status->value, 'label' => $status->label()], self::cases()),
            'label',
            'value',
        );
    }
}
