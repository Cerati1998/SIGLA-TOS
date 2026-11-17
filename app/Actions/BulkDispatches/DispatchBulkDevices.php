<?php

namespace App\Actions\BulkDispatches;

use App\Enums\SecurityDeviceStatus;
use App\Models\BulkDispatch;
use App\Models\SecurityDevice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispatchBulkDevices
{
    /**
     * How many rejected devices the message names at most.
     */
    private const int NAMED_REJECTIONS = 10;

    /**
     * The rows written per query.
     */
    private const int CHUNK_SIZE = 1000;

    public function __construct(private FindDeviceToBulkDispatch $findDeviceToBulkDispatch) {}

    /**
     * Dispatch the devices in bulk, all or none (H30, criterion 10). Each device is checked again under a lock, in case
     * another user put it in a kit or dispatched it after it was chosen. The devices become Despachado.
     *
     * @param  array{receiver_id: int, reason: string, client_id: int|null, kit_id: int|null, yard_zone_id: int, notes: string|null}  $attributes
     * @param  list<int>  $deviceIds
     *
     * @throws ValidationException With the key "deviceIds", when a device can no longer be dispatched.
     */
    public function handle(array $attributes, array $deviceIds, User $user): BulkDispatch
    {
        return DB::transaction(function () use ($attributes, $deviceIds, $user): BulkDispatch {
            $deviceIds = array_values(array_unique($deviceIds));
            $rejections = [];
            $found = 0;

            foreach (array_chunk($deviceIds, self::CHUNK_SIZE) as $chunk) {
                $devices = SecurityDevice::query()
                    ->with(['kits', 'bulkDispatches'])
                    ->whereKey($chunk)
                    ->lockForUpdate()
                    ->get();

                $found += $devices->count();

                foreach ($devices as $device) {
                    $reason = $this->findDeviceToBulkDispatch->rejectionReason($device);

                    if ($reason !== null) {
                        $rejections[] = "{$device->serial_number}: {$reason}";
                    }
                }
            }

            if ($found !== count($deviceIds)) {
                $rejections[] = 'Uno de los dispositivos elegidos ya no existe.';
            }

            if ($rejections !== []) {
                throw ValidationException::withMessages(['deviceIds' => array_slice($rejections, 0, self::NAMED_REJECTIONS)]);
            }

            $dispatch = BulkDispatch::query()->create([...$attributes, 'created_by' => $user->id]);
            $now = now();

            foreach (array_chunk($deviceIds, self::CHUNK_SIZE) as $chunk) {
                DB::table('bulk_dispatch_items')->insert(array_map(fn (int $deviceId): array => [
                    'bulk_dispatch_id' => $dispatch->id,
                    'security_device_id' => $deviceId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk));

                SecurityDevice::query()->whereKey($chunk)->update([
                    'status' => SecurityDeviceStatus::Dispatched->value,
                    'updated_at' => $now,
                ]);
            }

            return $dispatch;
        });
    }
}
