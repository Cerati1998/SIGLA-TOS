<?php

namespace App\Services;

/**
 * @phpstan-type Party array{document_number: string|null, name: string|null}
 * @phpstan-type Vehicle array{plate: string|null, brand: string|null, category: string|null, body: string|null, color: string|null}
 * @phpstan-type GateOutRequest array{
 *     reference: string,
 *     booking: string|null,
 *     container: string|null,
 *     shipping_line: string|null,
 *     product: string|null,
 *     vessel: string|null,
 *     voyage: string|null,
 *     destination: string|null,
 *     dispatched_at: string|null,
 *     client: Party,
 *     shipper: Party,
 *     carrier: Party,
 *     driver: array{name: string|null, license: string|null, document_number: string|null},
 *     tractor: Vehicle,
 *     semi_trailer: Vehicle,
 * }
 */
readonly class Sigla1LookupResult
{
    /**
     * @param  list<GateOutRequest>  $gateOutRequests  The requests found, empty unless the status is found.
     */
    public function __construct(
        public Sigla1LookupStatus $status,
        public array $gateOutRequests = [],
    ) {}
}
