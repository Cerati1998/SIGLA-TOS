<?php

namespace App\Enums;

/**
 * Where a checkpoint is, worked out from its closing and its reinspections; it is not stored (H36). It waits En
 * reinspección while a reinspection it started is open (H35).
 */
enum CheckpointStatus: string
{
    case InProgress = 'in_progress';
    case InReinspection = 'in_reinspection';
    case Closed = 'closed';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'En proceso',
            self::InReinspection => 'En reinspección',
            self::Closed => 'Cerrado',
        };
    }

    /**
     * Get the variant of the badge that shows the status.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::InProgress => 'info',
            self::InReinspection => 'highlight',
            self::Closed => 'success',
        };
    }
}
