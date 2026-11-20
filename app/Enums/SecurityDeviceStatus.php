<?php

namespace App\Enums;

enum SecurityDeviceStatus: string
{
    case Inactive = 'inactive';
    case Available = 'available';
    case InKit = 'in_kit';
    case Used = 'used';
    case Damaged = 'damaged';
    case Dispatched = 'dispatched';
    case Discarded = 'discarded';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Inactive => 'Inactivo',
            self::Available => 'Disponible',
            self::InKit => 'En un kit',
            self::Used => 'Usado',
            self::Damaged => 'Dañado',
            self::Dispatched => 'Despachado',
            self::Discarded => 'Descartado',
        };
    }

    /**
     * Get the name of the status for a count of devices, as in "180 Disponibles".
     */
    public function pluralLabel(): string
    {
        return match ($this) {
            self::Inactive => 'Inactivos',
            self::Available => 'Disponibles',
            self::InKit => 'En un kit',
            self::Used => 'Usados',
            self::Damaged => 'Dañados',
            self::Dispatched => 'Despachados',
            self::Discarded => 'Descartados',
        };
    }

    /**
     * Get the variant of the badge that shows the status.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Inactive => 'muted',
            self::Available => 'success',
            self::InKit => 'info',
            self::Used => 'neutral',
            self::Damaged => 'warning',
            self::Dispatched => 'purple',
            self::Discarded => 'danger',
        };
    }

    /**
     * Check whether a device in this status can be discarded from its lot (T65): only while it is Inactive or Available.
     */
    public function canBeDiscarded(): bool
    {
        return $this === self::Inactive || $this === self::Available;
    }

    /**
     * Get the options of a select, as stored value and name shown on screen.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $status): array => ['value' => $status->value, 'label' => $status->label()], self::cases()),
            'label',
            'value',
        );
    }
}
