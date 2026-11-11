<?php

namespace App\Enums;

enum VehicleKind: string
{
    case Tractor = 'tractor';
    case SemiTrailer = 'semi_trailer';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Tractor => 'Remolcador',
            self::SemiTrailer => 'Semirremolque',
        };
    }

    /**
     * Get the variant of the badge that shows the kind.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Tractor => 'info',
            self::SemiTrailer => 'neutral',
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
            array_map(fn (self $kind): array => ['value' => $kind->value, 'label' => $kind->label()], self::cases()),
            'label',
            'value',
        );
    }
}
