<?php

namespace App\Enums;

enum SecurityDeviceKind: string
{
    case Seal = 'seal';
    case ContainerSeal = 'container_seal';
    case Tape = 'tape';
    case Other = 'other';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Seal => 'Sello',
            self::ContainerSeal => 'Precinto',
            self::Tape => 'Cinta',
            self::Other => 'Otro',
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
