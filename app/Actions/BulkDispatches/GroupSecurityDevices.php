<?php

namespace App\Actions\BulkDispatches;

use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use Illuminate\Support\Collection;

/**
 * Group the devices of a bulk dispatch by type and by stretches of consecutive correlatives of a lot, to show them as
 * "Sello botella · SB001061 – SB001062 · 2" (H30, criteria 6 and 11).
 */
class GroupSecurityDevices
{
    /**
     * The rows looked up per query.
     */
    private const int CHUNK_SIZE = 1000;

    /**
     * Get the stretches of the given devices, ordered by type and by serial number. The key of a stretch names its lot
     * and its first and last correlatives, to take it out of the dispatch.
     *
     * @param  list<int>  $deviceIds
     * @return list<array{key: string, type: string, range: string, count: int, deviceIds: list<int>, serialNumbers: list<string>}>
     */
    public function stretches(array $deviceIds): array
    {
        if ($deviceIds === []) {
            return [];
        }

        $devices = collect();

        foreach (array_chunk($deviceIds, self::CHUNK_SIZE) as $chunk) {
            $devices = $devices->concat(SecurityDevice::query()->whereKey($chunk)->get(['id', 'security_device_lot_id', 'serial_number']));
        }

        $lots = SecurityDeviceLot::query()
            ->with('announcement.securityDeviceType')
            ->whereKey($devices->pluck('security_device_lot_id')->unique()->all())
            ->get()
            ->keyBy('id');

        $stretches = $devices
            ->groupBy('security_device_lot_id')
            ->flatMap(fn (Collection $lotDevices, int $lotId): array => $this->stretchesOfLot($lots->get($lotId), $lotDevices))
            ->sortBy([['type', 'asc'], ['first', 'asc']])
            ->all();

        return array_values(array_map(fn (array $stretch): array => [
            'key' => $stretch['key'],
            'type' => $stretch['type'],
            'range' => $stretch['range'],
            'count' => $stretch['count'],
            'deviceIds' => $stretch['deviceIds'],
            'serialNumbers' => $stretch['serialNumbers'],
        ], $stretches));
    }

    /**
     * Get the stretches grouped by type, for the detail of a dispatch: each type with its ranges, its quantity and its
     * serial numbers.
     *
     * @param  list<int>  $deviceIds
     * @return list<array{type: string, ranges: list<string>, count: int, serialNumbers: list<string>}>
     */
    public function byType(array $deviceIds): array
    {
        $types = [];

        foreach ($this->stretches($deviceIds) as $stretch) {
            $type = $types[$stretch['type']] ?? ['type' => $stretch['type'], 'ranges' => [], 'count' => 0, 'serialNumbers' => []];
            $type['ranges'][] = $stretch['range'];
            $type['count'] += $stretch['count'];
            $type['serialNumbers'] = [...$type['serialNumbers'], ...$stretch['serialNumbers']];
            $types[$stretch['type']] = $type;
        }

        return array_values($types);
    }

    /**
     * Split the devices of a lot into stretches of consecutive correlatives.
     *
     * @param  Collection<int, SecurityDevice>  $devices
     * @return list<array{key: string, type: string, first: string, range: string, count: int, deviceIds: list<int>, serialNumbers: list<string>}>
     */
    private function stretchesOfLot(?SecurityDeviceLot $lot, Collection $devices): array
    {
        if ($lot === null) {
            return [];
        }

        $announcement = $lot->announcement;
        $sorted = $devices
            ->map(fn (SecurityDevice $device): array => ['id' => $device->id, 'serialNumber' => $device->serial_number, 'correlative' => (int) $announcement->correlativeOf($device->serial_number)])
            ->sortBy('correlative')
            ->values();

        $stretches = [];
        $current = [];

        foreach ($sorted as $item) {
            $previous = end($current);

            if ($previous !== false && $item['correlative'] !== $previous['correlative'] + 1) {
                $stretches[] = $current;
                $current = [];
            }

            $current[] = $item;
        }

        $stretches[] = $current;

        return array_map(function (array $stretch) use ($lot, $announcement): array {
            $first = $stretch[0];
            $last = $stretch[count($stretch) - 1];

            return [
                'key' => "{$lot->id}:{$first['correlative']}:{$last['correlative']}",
                'type' => $announcement->securityDeviceType->name,
                'first' => $first['serialNumber'],
                'range' => count($stretch) === 1 ? $first['serialNumber'] : $first['serialNumber'].' – '.$last['serialNumber'],
                'count' => count($stretch),
                'deviceIds' => array_column($stretch, 'id'),
                'serialNumbers' => array_column($stretch, 'serialNumber'),
            ];
        }, $stretches);
    }
}
