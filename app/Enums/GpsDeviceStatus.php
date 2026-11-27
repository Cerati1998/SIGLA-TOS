<?php

namespace App\Enums;

/**
 * Where a GPS device is in its flow (H40). For now it is only available in the warehouse: the dispatch, the installation
 * and the removal add their states with their stories. A deactivated device is the one with `deleted_at`.
 */
enum GpsDeviceStatus: string
{
    case Available = 'available';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
        };
    }
}
