<?php

namespace App\Enums;

/**
 * What is done with the kit of a certification whose assignment is annulled, as its shipping line answers (H32).
 */
enum CertificationKitDecision: string
{
    case Reuse = 'reuse';
    case Disable = 'disable';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Reuse => 'Reutilizar el kit',
            self::Disable => 'Inhabilitar el kit',
        };
    }

    /**
     * Get what happens with the kit, shown under its option.
     */
    public function description(): string
    {
        return match ($this) {
            self::Reuse => 'Vuelve a Despachado con sus dispositivos y se puede asignar a otra certificación.',
            self::Disable => 'Queda Inhabilitado y todos sus dispositivos pasan a Descartado.',
        };
    }
}
