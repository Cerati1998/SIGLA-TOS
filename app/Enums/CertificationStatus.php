<?php

namespace App\Enums;

/**
 * Where a certification is, worked out from its photos, its closing, its reopenings and its annulment; it is not stored
 * (H19, H20 and H32). A reopened certification stays Reaperturada until it is closed again.
 */
enum CertificationStatus: string
{
    case InProgress = 'in_progress';
    case ReadyToClose = 'ready_to_close';
    case Closed = 'closed';
    case Reopened = 'reopened';
    case Annulled = 'annulled';

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
            self::Annulled => 'Anulada',
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
            self::Annulled => 'danger',
        };
    }
}
