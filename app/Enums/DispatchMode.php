<?php

namespace App\Enums;

/**
 * How the devices of a shipping line leave the warehouse, in a kit or in bulk (T82): in any order, the default, or in
 * the order of their correlatives, without skipping any.
 */
enum DispatchMode: string
{
    case Random = 'random';
    case Linear = 'linear';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Random => 'Aleatorio',
            self::Linear => 'Lineal',
        };
    }

    /**
     * Get what the mode means, shown under its name.
     */
    public function description(): string
    {
        return match ($this) {
            self::Random => 'Los dispositivos salen en cualquier orden.',
            self::Linear => 'Los dispositivos salen en orden de correlativo, sin saltarse ninguno, al armar un kit y al despachar a granel.',
        };
    }
}
