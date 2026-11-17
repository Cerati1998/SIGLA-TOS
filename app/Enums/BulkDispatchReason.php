<?php

namespace App\Enums;

/**
 * Why the security devices of a bulk dispatch leave the warehouse (H30): sold to a client, or for the operation, to
 * a kit already dispatched.
 */
enum BulkDispatchReason: string
{
    case Sale = 'sale';
    case Operation = 'operation';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Venta',
            self::Operation => 'Operación',
        };
    }

    /**
     * Get the variant of the badge that shows the reason.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Sale => 'success',
            self::Operation => 'info',
        };
    }
}
