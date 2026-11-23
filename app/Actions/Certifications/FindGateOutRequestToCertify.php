<?php

namespace App\Actions\Certifications;

use App\Models\Certification;
use App\Services\Sigla1LookupResult;
use App\Services\Sigla1LookupStatus;
use App\Services\Sigla1Service;

/**
 * @phpstan-import-type GateOutRequest from Sigla1LookupResult
 */
class FindGateOutRequestToCertify
{
    public function __construct(private Sigla1Service $sigla1) {}

    /**
     * Look up the gate out request of a booking and a container in SIGLA 1.0 (H17, criterion 1). When SIGLA 1.0
     * sends more than one, the one dispatched last is taken (decision of the user of 2026-11-17). The certification
     * that the request already has comes along, so the screen offers it instead of starting another (criterion 6).
     *
     * @return array{status: Sigla1LookupStatus, gateOutRequest: GateOutRequest|null, certification: Certification|null}
     */
    public function handle(string $booking, string $container): array
    {
        $result = $this->sigla1->findGateOutRequests($booking, $container);
        $gateOutRequest = $this->latest($result->gateOutRequests);

        return [
            'status' => $result->status,
            'gateOutRequest' => $gateOutRequest,
            'certification' => $gateOutRequest === null
                ? null
                : Certification::query()->where('gate_out_request_reference', $gateOutRequest['reference'])->first(),
        ];
    }

    /**
     * Get the request with the latest dispatch date. SIGLA 1.0 sends it as Y-m-d H:i:s, so comparing the text works;
     * a request without a date goes last.
     *
     * @param  list<GateOutRequest>  $gateOutRequests
     * @return GateOutRequest|null
     */
    private function latest(array $gateOutRequests): ?array
    {
        usort($gateOutRequests, fn (array $first, array $second): int => strcmp(
            (string) $second['dispatched_at'],
            (string) $first['dispatched_at'],
        ));

        return $gateOutRequests[0] ?? null;
    }
}
