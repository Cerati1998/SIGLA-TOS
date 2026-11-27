<?php

namespace App\Services\Monitoring;

/**
 * A monitoring platform that the GPS devices report to (E10). Today the only one is 24HM (`Gps24hmClient`); another
 * provider adds its own client with this same contract, and its story decides how a device knows its platform.
 */
interface MonitoringPlatform
{
    /**
     * Get the last report of the given asset.
     *
     * @throws MonitoringPlatformException
     */
    public function asset(int $assetId): MonitoringReport;
}
