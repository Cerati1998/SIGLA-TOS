<?php

namespace App\Enums;

enum CertificationSectionType: string
{
    case Internal = 'internal';
    case External = 'external';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Interna',
            self::External => 'Externa',
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
            array_map(fn (self $type): array => ['value' => $type->value, 'label' => $type->label()], self::cases()),
            'label',
            'value',
        );
    }
}
