<?php

namespace App\Enums;

enum CertificationSectionType: string
{
    case Internal = 'internal';
    case External = 'external';
    case Checkpoint = 'checkpoint';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Interna',
            self::External => 'Externa',
            self::Checkpoint => 'Checkpoint',
        };
    }

    /**
     * Get the variant of the badge that shows the type.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Internal => 'info',
            self::External => 'neutral',
            self::Checkpoint => 'warning',
        };
    }

    /**
     * Whether the sections of this type belong to an inspection of the certification (H18). The checkpoint ones
     * belong to the checkpoint (H33): they are never copied into a certification.
     */
    public function isInspection(): bool
    {
        return $this !== self::Checkpoint;
    }

    /**
     * Get the types of the inspections of a certification: internal and external (H18).
     *
     * @return list<self>
     */
    public static function inspectionTypes(): array
    {
        return [self::Internal, self::External];
    }

    /**
     * Get the options of a select, as stored value and name shown on screen.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $type): array => ['value' => $type->value, 'label' => $type->label()], self::cases()),
            'label',
            'value',
        );
    }
}
