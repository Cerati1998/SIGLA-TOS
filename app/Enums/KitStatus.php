<?php

namespace App\Enums;

enum KitStatus: string
{
    case Building = 'building';
    case Built = 'built';
    case Dispatched = 'dispatched';
    case Used = 'used';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Building => 'Armando',
            self::Built => 'Armado',
            self::Dispatched => 'Despachado',
            self::Used => 'Usado',
        };
    }

    /**
     * Get the variant of the badge that shows the status.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Building => 'warning',
            self::Built => 'success',
            self::Dispatched => 'info',
            self::Used => 'neutral',
        };
    }

    /**
     * Whether security devices can still be added to or removed from a kit with this status: only until it is
     * dispatched (H14, criterion 9).
     */
    public function contentsCanChange(): bool
    {
        return $this === self::Building || $this === self::Built;
    }
}
