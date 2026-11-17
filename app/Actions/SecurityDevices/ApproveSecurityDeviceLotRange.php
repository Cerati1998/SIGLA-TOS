<?php

namespace App\Actions\SecurityDevices;

use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceLotApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveSecurityDeviceLotRange
{
    /**
     * The rows looked up or updated per query.
     */
    private const int CHUNK_SIZE = 1000;

    /**
     * Count what an approval of the given stretch of correlatives would do: how many inactive devices become available,
     * the first and the last of them, and how many devices of the stretch were approved already (H29, criterion 7).
     *
     * @return array{toApprove: int, alreadyApproved: int, firstSerialNumber: string|null, lastSerialNumber: string|null}
     */
    public function preview(SecurityDeviceLot $lot, int $correlativeStart, int $correlativeEnd): array
    {
        $statuses = $this->statusesBySerialNumber($lot, $correlativeStart, $correlativeEnd);
        $inactive = array_keys(array_filter($statuses, fn (SecurityDeviceStatus $status): bool => $status === SecurityDeviceStatus::Inactive));

        return [
            'toApprove' => count($inactive),
            'alreadyApproved' => count($statuses) - count($inactive),
            'firstSerialNumber' => $inactive[0] ?? null,
            'lastSerialNumber' => $inactive === [] ? null : $inactive[array_key_last($inactive)],
        ];
    }

    /**
     * Register the approval of the given stretch of correlatives, all or nothing: its inactive devices become available
     * and are linked to the new record of the approvals log. The devices approved already are left as they are.
     * Return null, with nothing saved, when no device of the stretch is inactive.
     */
    public function handle(SecurityDeviceLot $lot, int $correlativeStart, int $correlativeEnd, User $user): ?SecurityDeviceLotApproval
    {
        return DB::transaction(function () use ($lot, $correlativeStart, $correlativeEnd, $user): ?SecurityDeviceLotApproval {
            $inactiveIds = $this->inactiveDeviceIds($lot, $correlativeStart, $correlativeEnd);

            if ($inactiveIds === []) {
                return null;
            }

            $approval = $lot->approvals()->create([
                'correlative_start' => $correlativeStart,
                'correlative_end' => $correlativeEnd,
                'approved_count' => count($inactiveIds),
                'approved_by' => $user->id,
            ]);

            foreach (array_chunk($inactiveIds, self::CHUNK_SIZE) as $chunk) {
                SecurityDevice::query()->whereKey($chunk)->update([
                    'status' => SecurityDeviceStatus::Available->value,
                    'security_device_lot_approval_id' => $approval->id,
                    'updated_at' => now(),
                ]);
            }

            return $approval;
        });
    }

    /**
     * Get the status of each device of the stretch, keyed by its serial number in the order of the correlatives.
     * A correlative without a device is skipped.
     *
     * @return array<string, SecurityDeviceStatus>
     */
    private function statusesBySerialNumber(SecurityDeviceLot $lot, int $correlativeStart, int $correlativeEnd): array
    {
        $statuses = [];

        foreach (array_chunk($this->serialNumbers($lot, $correlativeStart, $correlativeEnd), self::CHUNK_SIZE) as $chunk) {
            $found = $lot->devices()->whereIn('serial_number', $chunk)->pluck('status', 'serial_number');

            foreach ($chunk as $serialNumber) {
                if ($found->has($serialNumber)) {
                    $statuses[$serialNumber] = $found->get($serialNumber);
                }
            }
        }

        return $statuses;
    }

    /**
     * Get the ids of the inactive devices of the stretch, locked until the approval is saved.
     *
     * @return list<int>
     */
    private function inactiveDeviceIds(SecurityDeviceLot $lot, int $correlativeStart, int $correlativeEnd): array
    {
        $ids = [];

        foreach (array_chunk($this->serialNumbers($lot, $correlativeStart, $correlativeEnd), self::CHUNK_SIZE) as $chunk) {
            $ids = [...$ids, ...$lot->devices()
                ->whereIn('serial_number', $chunk)
                ->where('status', SecurityDeviceStatus::Inactive->value)
                ->lockForUpdate()
                ->get(['id'])
                ->map(fn (SecurityDevice $device): int => $device->id)
                ->all()];
        }

        return $ids;
    }

    /**
     * Get the serial numbers of the stretch, from the first correlative to the last one.
     *
     * @return list<string>
     */
    private function serialNumbers(SecurityDeviceLot $lot, int $correlativeStart, int $correlativeEnd): array
    {
        return array_map($lot->announcement->serialNumber(...), range($correlativeStart, $correlativeEnd));
    }
}
