<?php

namespace App\Actions\GpsDevices;

use App\Models\GpsDevice;
use App\Services\Monitoring\Gps24hmClient;
use App\Services\Monitoring\MonitoringPlatformException;

class CheckGpsDeviceConnectivity
{
    public function __construct(private Gps24hmClient $client) {}

    /**
     * Ask 24HM for the last report of the device and save it, with when it was checked (H42). If 24HM does not give
     * it, nothing changes and the reason comes in the exception.
     *
     * @throws MonitoringPlatformException
     */
    public function handle(GpsDevice $gpsDevice): GpsDevice
    {
        if ($gpsDevice->monitoring_asset_id === null) {
            throw new MonitoringPlatformException('Este dispositivo no tiene ID de 24HM. Agrégalo en su edición para poder verificarlo.');
        }

        $report = $this->client->asset($gpsDevice->monitoring_asset_id);

        $gpsDevice->update([
            'last_reported_at' => $report->reportedAt,
            'last_latitude' => $report->latitude,
            'last_longitude' => $report->longitude,
            'last_address' => $report->address,
            'service_status' => $report->serviceStatus,
            'operational_status' => $report->operationalStatus,
            'supply_voltage' => $report->supplyVoltage,
            'last_checked_at' => now(),
        ]);

        return $gpsDevice;
    }
}
