<?php

namespace App\Enums;

/**
 * Where a certification is, worked out from its photos, its closing and its reopenings; it is not stored (H19 and
 * H20). A reopened certification stays Reaperturada until it is closed again.
 */
enum CertificationStatus: string
{
    case InProgress = 'in_progress';
    case ReadyToClose = 'ready_to_close';
    case Closed = 'closed';
    case Reopened = 'reopened';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'A medias',
            self::ReadyToClose => 'Lista para cerrar',
            self::Closed => 'Cerrada',
            self::Reopened => 'Reaperturada',
        };
    }

    /**
     * Get the variant of the badge that shows the status.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::InProgress => 'info',
            self::ReadyToClose => 'strong-success',
            self::Closed => 'success',
            self::Reopened => 'warning',
        };
    }
}
