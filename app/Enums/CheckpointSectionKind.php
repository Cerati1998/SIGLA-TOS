<?php

namespace App\Enums;

/**
 * What a checkpoint section asks for (H33): photos, or marking each serial number of the certification sections it
 * verifies as right or wrong.
 */
enum CheckpointSectionKind: string
{
    case Photos = 'photos';
    case Verification = 'verification';

    /**
     * Get the name shown on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Photos => 'Fotos',
            self::Verification => 'Verificación',
        };
    }

    /**
     * Get the explanation shown under the name when the admin chooses it.
     */
    public function description(): string
    {
        return match ($this) {
            self::Photos => 'Toma al menos una foto.',
            self::Verification => 'Marca cada serie como correcta o incorrecta. No pide fotos.',
        };
    }
}
