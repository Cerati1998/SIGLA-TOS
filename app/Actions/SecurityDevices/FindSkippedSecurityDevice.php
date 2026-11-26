<?php

namespace App\Actions\SecurityDevices;

use App\Enums\DispatchMode;
use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;

/**
 * Keep the order of the correlatives of the shipping lines that dispatch in linear mode (T82). Their devices leave, in
 * a kit or in bulk, without skipping any: the next one is always the Available device with the lowest correlative of
 * its sequence, that is, of the same shipping line, device type and series (prefix and suffix). The devices that are
 * not Available, as the Discarded ones or the ones waiting for their approval, do not hold the order back.
 */
class FindSkippedSecurityDevice
{
    /**
     * Find the first Available device that the given ones would skip, or null when they keep the order. The devices
     * of shipping lines in random mode are never checked.
     *
     * @param  list<int>  $deviceIds  The devices about to leave, in a kit or in bulk.
     * @param  list<int>  $takenIds  The devices the same dispatch already took, which are not skipped.
     */
    public function handle(array $deviceIds, array $takenIds = []): ?SecurityDevice
    {
        $leaving = array_flip([...$takenIds, ...$deviceIds]);

        foreach ($this->linearSequences($deviceIds) as $sequence) {
            $skipped = $this->lowestAvailableBelow($sequence['lot'], $sequence['highest'], $leaving);

            if ($skipped !== null) {
                return $skipped->load('lot.announcement.shippingLine');
            }
        }

        return null;
    }

    /**
     * Get the message for a device that was scanned out of order.
     */
    public static function message(SecurityDevice $skipped): string
    {
        return sprintf('Falta despachar antes el %s. %s despacha en orden.', $skipped->serial_number, $skipped->lot->announcement->shippingLine->name);
    }

    /**
     * Get the message for a range that does not start at the next device.
     */
    public static function rangeMessage(SecurityDevice $skipped): string
    {
        return sprintf('El rango tiene que empezar en el %s. %s despacha en orden.', $skipped->serial_number, $skipped->lot->announcement->shippingLine->name);
    }

    /**
     * Group the devices of the shipping lines in linear mode by their sequence, with one of its lots and the highest
     * correlative leaving.
     *
     * @param  list<int>  $deviceIds
     * @return list<array{lot: SecurityDeviceLot, highest: int}>
     */
    private function linearSequences(array $deviceIds): array
    {
        $sequences = [];

        $devices = SecurityDevice::query()
            ->with('lot.announcement.shippingLine')
            ->whereKey($deviceIds)
            ->get(['id', 'serial_number', 'security_device_lot_id']);

        foreach ($devices as $device) {
            $lot = $device->lot;
            $correlative = $lot->correlativeOf($device->serial_number);

            if ($correlative === null || $lot->announcement->shippingLine->dispatch_mode !== DispatchMode::Linear) {
                continue;
            }

            $key = implode('|', [$lot->announcement->shipping_line_id, $lot->security_device_type_id, $lot->series_prefix, (string) $lot->series_suffix]);

            if (! isset($sequences[$key]) || $sequences[$key]['highest'] < $correlative) {
                $sequences[$key] = ['lot' => $lot, 'highest' => $correlative];
            }
        }

        return array_values($sequences);
    }

    /**
     * Get the Available device of the sequence of the lot with the lowest correlative below the given one, leaving out
     * the devices that are leaving.
     *
     * @param  array<int, int>  $leaving  The ids of the devices that are leaving, as keys.
     */
    private function lowestAvailableBelow(SecurityDeviceLot $lot, int $highest, array $leaving): ?SecurityDevice
    {
        $lots = SecurityDeviceLot::query()
            ->whereRelation('announcement', 'shipping_line_id', $lot->announcement->shipping_line_id)
            ->where('security_device_type_id', $lot->security_device_type_id)
            ->where('series_prefix', $lot->series_prefix)
            ->where('series_suffix', $lot->series_suffix)
            ->where('series_start', '<', $highest)
            ->get()
            ->keyBy('id');

        $lowest = null;
        $lowestCorrelative = $highest;

        $candidates = SecurityDevice::query()
            ->whereIn('security_device_lot_id', $lots->keys())
            ->where('status', SecurityDeviceStatus::Available->value)
            ->get(['id', 'serial_number', 'security_device_lot_id']);

        foreach ($candidates as $device) {
            $correlative = $lots->get($device->security_device_lot_id)?->correlativeOf($device->serial_number);

            if ($correlative !== null && $correlative < $lowestCorrelative && ! isset($leaving[$device->id])) {
                $lowest = $device;
                $lowestCorrelative = $correlative;
            }
        }

        return $lowest;
    }
}
