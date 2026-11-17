<?php

namespace App\Actions\KitDispatches;

use App\Enums\KitStatus;
use App\Models\Kit;

class FindKitToDispatch
{
    /**
     * Find the kit with the scanned or written pouch code, or get why it cannot be dispatched, to show it on screen.
     */
    public function handle(string $pouchCode): Kit|string
    {
        $kit = Kit::query()
            ->with(['kitType', 'dispatches'])
            ->where('pouch_code', $pouchCode)
            ->first();

        if ($kit === null) {
            return sprintf('No encontramos un kit con la funda %s.', $pouchCode);
        }

        return $this->rejectionReason($kit) ?? $kit;
    }

    /**
     * Get why the kit cannot be dispatched, or null when it can: only a kit Armado that is in no dispatch (H15,
     * criteria 3 and 4).
     */
    public function rejectionReason(Kit $kit): ?string
    {
        $dispatch = $kit->dispatch();

        if ($dispatch !== null) {
            return sprintf('Ya está despachado en el despacho %s.', $dispatch->number());
        }

        return match ($kit->status) {
            KitStatus::Built => null,
            KitStatus::Building => sprintf('Está Armando: le faltan %s. Complétalo antes de despacharlo.', $kit->missingDescription()),
            KitStatus::Dispatched => 'Ya está despachado.',
            KitStatus::Used => 'Ya se usó en una certificación.',
        };
    }
}
