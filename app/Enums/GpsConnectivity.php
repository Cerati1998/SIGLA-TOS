<?php

namespace App\Enums;

/**
 * Whether a GPS device reports to 24HM, judged by its last report saved (H42): connected if it reported less than
 * 24 hours ago, without signal if it is older, and unchecked if there is no report saved yet.
 */
enum GpsConnectivity: string
{
    case Connected = 'connected';
    case NoSignal = 'no_signal';
    case Unchecked = 'unchecked';

    /**
     * How many hours a report counts as recent.
     */
    public const int RECENT_HOURS = 24;

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Connected => 'Conectado',
            self::NoSignal => 'Sin señal',
            self::Unchecked => 'Sin verificar',
        };
    }

    /**
     * Get the variant of the badge that shows it.
     */
    public function badge(): string
    {
        return match ($this) {
            self::Connected => 'success',
            self::NoSignal => 'warning',
            self::Unchecked => 'neutral',
        };
    }
}
