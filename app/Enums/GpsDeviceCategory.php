<?php

namespace App\Enums;

/**
 * The category of a GPS device model (H39). It is a fixed list, without a screen, by decision of the user of 2026-11-27.
 */
enum GpsDeviceCategory: string
{
    case ContainerLock = 'container_lock';
    case DoorLock = 'door_lock';
    case LightVehicleLock = 'light_vehicle_lock';
    case PortableLock = 'portable_lock';
    case PortableTracker = 'portable_tracker';
    case HopperControl = 'hopper_control';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::ContainerLock => 'Candado para contenedor',
            self::DoorLock => 'Cerradura de puerta',
            self::LightVehicleLock => 'Candado para unidad liviana',
            self::PortableLock => 'Candado portátil',
            self::PortableTracker => 'GPS portátil',
            self::HopperControl => 'Control de tolva',
        };
    }

    /**
     * Get the options of a select, as stored value and name shown on screen.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $category): array => ['value' => $category->value, 'label' => $category->label()], self::cases()),
            'label',
            'value',
        );
    }
}
