<?php

namespace App\Enums;

/**
 * The side a security device goes to when its type is split between the internal and the external assignment, as
 * the inspection tape (T55).
 */
enum KitAssignment: string
{
    case External = 'external';
    case Internal = 'internal';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::External => 'Externa',
            self::Internal => 'Interna',
        };
    }
}
