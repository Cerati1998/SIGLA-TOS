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
}
