<?php

namespace App\Actions\Certifications;

use App\Enums\KitStatus;
use App\Models\Certification;
use App\Models\Kit;
use App\Services\ShippingLineRegistry;

class FindKitToCertify
{
    public function __construct(
        private ShippingLineRegistry $shippingLines,
        private DistributeKitDevices $distributeKitDevices,
    ) {}

    /**
     * Find the kit with the scanned or written pouch code, or get why it cannot be used to certify the unit of a
     * request of this shipping line, to show it on screen.
     */
    public function handle(string $pouchCode, ?string $shippingLineName): Kit|string
    {
        $kit = Kit::query()
            ->with(['kitType', 'shippingLine'])
            ->where('pouch_code', $pouchCode)
            ->first();

        if ($kit === null) {
            return sprintf('No hay ningún kit con la funda %s.', $pouchCode);
        }

        return $this->rejectionReason($kit, $shippingLineName) ?? $kit;
    }

    /**
     * Get why the kit cannot be used, or null when it can: any kit Despachado, whoever it was dispatched to
     * (criterion 7), of the same shipping line as the request (decision of the user of 2026-11-17), that is in no
     * other certification (criterion 8) and that has exactly what the active sections ask for (criterion 16).
     */
    public function rejectionReason(Kit $kit, ?string $shippingLineName): ?string
    {
        if ($kit->status !== KitStatus::Dispatched) {
            return $kit->status === KitStatus::Used
                ? sprintf('El kit %s ya se usó.', $kit->pouch_code)
                : sprintf('El kit %s está %s y todavía no se despachó.', $kit->pouch_code, $kit->status->label());
        }

        $certification = Certification::query()->where('kit_id', $kit->id)->whereNull('annulled_at')->first();

        if ($certification !== null) {
            return sprintf('El kit %s ya está en la certificación del contenedor %s.', $kit->pouch_code, $certification->container);
        }

        $requestShippingLine = $shippingLineName === null ? null : $this->shippingLines->find($shippingLineName);

        if ($requestShippingLine?->id !== $kit->shipping_line_id) {
            return sprintf(
                'El kit %s es de %s · %s y la solicitud es de %s.',
                $kit->pouch_code,
                $kit->shippingLine->code,
                $kit->shippingLine->name,
                $requestShippingLine === null ? ($shippingLineName ?? 'una naviera sin nombre') : "{$requestShippingLine->code} · {$requestShippingLine->name}",
            );
        }

        $problems = $this->distributeKitDevices->problems($kit);

        if ($problems !== []) {
            return sprintf(
                'El kit %s no calza con lo que piden las secciones activas: %s. Escanea otra funda o pide otro kit en bodega.',
                $kit->pouch_code,
                implode('; ', $problems),
            );
        }

        return null;
    }
}
