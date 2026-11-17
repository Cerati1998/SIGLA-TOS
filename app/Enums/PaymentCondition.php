<?php

namespace App\Enums;

/**
 * How a client pays (H31): in cash, the default, or on credit.
 */
enum PaymentCondition: string
{
    case Cash = 'cash';
    case Credit = 'credit';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Contado',
            self::Credit => 'Crédito',
        };
    }
}
