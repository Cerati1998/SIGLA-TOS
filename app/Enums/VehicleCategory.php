<?php

namespace App\Enums;

/**
 * The category of a vehicle: the fixed list that SIGLA 1.0 uses, N3 by default.
 */
enum VehicleCategory: string
{
    case N1 = 'N1';
    case N2 = 'N2';
    case N3 = 'N3';
    case O1 = 'O1';
    case O2 = 'O2';
    case O3 = 'O3';
    case O4 = 'O4';
    case Other = 'other';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return $this === self::Other ? 'Otro' : $this->value;
    }

    /**
     * Get the category from the text that SIGLA 1.0 sends, such as `N3` or `OTRO`. Any other text is Other.
     */
    public static function fromSigla1(?string $text): self
    {
        return self::tryFrom(mb_strtoupper(trim((string) $text))) ?? self::Other;
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
