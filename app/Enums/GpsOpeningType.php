<?php

namespace App\Enums;

/**
 * How a GPS device model opens (H39): remotely, by hand, or not at all, as the portable and hopper trackers.
 */
enum GpsOpeningType: string
{
    case Remote = 'remote';
    case Manual = 'manual';
    case None = 'none';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Remote => 'Remota',
            self::Manual => 'Manual',
            self::None => 'Sin apertura',
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
            array_map(fn (self $type): array => ['value' => $type->value, 'label' => $type->label()], self::cases()),
            'label',
            'value',
        );
    }
}
