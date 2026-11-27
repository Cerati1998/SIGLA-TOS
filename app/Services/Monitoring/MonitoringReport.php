<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Carbon;

/**
 * The last report of an asset of the monitoring platform, as it comes from 24HM: its alias, its codes and where and
 * when it reported for the last time (H41 and H42).
 */
readonly class MonitoringReport
{
    public function __construct(
        public int $assetId,
        public ?string $alias,
        public ?string $productCode,
        public ?string $deviceTypeCode,
        public ?Carbon $reportedAt,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $address,
        public ?string $serviceStatus,
        public ?string $operationalStatus,
        public ?float $supplyVoltage,
    ) {}
}
