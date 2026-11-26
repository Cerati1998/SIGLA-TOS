<?php

namespace App\Actions\BulkDispatches;

use App\Enums\CertificationStatus;
use App\Models\BulkDispatch;
use App\Models\Certification;

/**
 * What the detail and the PDF of a bulk dispatch show (H30, criterion 11, and T87): its devices by type and, for the
 * operation, the assignment its kit is linked to.
 *
 * @phpstan-type Description array{bulkDispatch: BulkDispatch, types: list<array{type: string, ranges: list<string>, count: int, serialNumbers: list<string>}>, assignment: array<string, string>|null, assignmentStatus: CertificationStatus|null}
 */
class DescribeBulkDispatch
{
    public function __construct(private GroupSecurityDevices $groupSecurityDevices) {}

    /**
     * Describe the bulk dispatch, loading everything it shows at once.
     *
     * @return Description
     */
    public function handle(BulkDispatch $bulkDispatch): array
    {
        $bulkDispatch->loadMissing(['receiver', 'creator', 'client', 'kit.kitType', 'kit.certification.shippingLine', 'yardZone']);
        $certification = $bulkDispatch->kit?->certification;

        return [
            'bulkDispatch' => $bulkDispatch,
            'types' => $this->groupSecurityDevices->byType($bulkDispatch->deviceIds()),
            'assignment' => $certification === null ? null : $this->assignment($certification),
            'assignmentStatus' => $certification?->status(),
        ];
    }

    /**
     * Get the data of the certification the kit of the operation is linked to, by its label on screen (T87).
     *
     * @return array<string, string>
     */
    private function assignment(Certification $certification): array
    {
        $orDash = fn (?string $text): string => $text === null || $text === '' ? '—' : $text;

        return [
            'Contenedor' => $certification->container,
            'Solicitud' => $certification->gate_out_request_reference,
            'Cliente' => $orDash($certification->client),
            'Producto' => $orDash($certification->product),
            'Línea naviera' => $certification->shippingLine->code.' · '.$certification->shippingLine->name,
            'Booking' => $certification->booking,
            'Estado' => $certification->status()->label(),
            'Iniciada' => $certification->started_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i'),
        ];
    }
}
