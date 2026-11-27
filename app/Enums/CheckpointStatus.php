<?php

namespace App\Enums;

/**
 * Where a checkpoint is, worked out from its closing and its reinspections; it is not stored (H36). It waits En
 * reinspección while a reinspection it started is open (H35). It is closed with an observation when it was closed with
 * an incorrect series in a step that does not ask for a reinspection (T91).
 */
enum CheckpointStatus: string
{
    case InProgress = 'in_progress';
    case InReinspection = 'in_reinspection';
    case Closed = 'closed';
    case ClosedWithObservation = 'closed_with_observation';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'En proceso',
            self::InReinspection => 'En reinspección',
            self::Closed => 'Cerrado',
            self::ClosedWithObservation => 'Cerrado con observación',
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
            self::ClosedWithObservation => 'warning',
        };
    }
}
