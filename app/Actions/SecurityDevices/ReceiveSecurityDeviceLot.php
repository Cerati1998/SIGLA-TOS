<?php

namespace App\Actions\SecurityDevices;

use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class ReceiveSecurityDeviceLot
{
    /**
     * The most devices a lot can bring (H07, criterion 10).
     */
    public const int MAX_DEVICES = 10000;

    /**
     * The rows written or looked up per query.
     */
    private const int CHUNK_SIZE = 1000;

    /**
     * Save the announcement, its lot and one available device per serial number, all or nothing.
     * Return null, with nothing saved, when one of the serial numbers already exists.
     *
     * @param  array{received_at: string, dispatch_number: string, notes: string|null}  $lotAttributes
     */
    public function handle(SecurityDeviceAnnouncement $announcement, array $lotAttributes, User $user): ?SecurityDeviceLot
    {
        $serialNumbers = $announcement->serialNumbers();

        try {
            return DB::transaction(function () use ($announcement, $lotAttributes, $user, $serialNumbers): ?SecurityDeviceLot {
                if ($this->takenSerialNumbers($serialNumbers) !== []) {
                    return null;
                }

                $announcement->save();

                $lot = $announcement->lot()->create([
                    ...$lotAttributes,
                    'quantity' => count($serialNumbers),
                    'created_by' => $user->id,
                ]);

                $this->createAvailableDevices($lot, $serialNumbers);

                return $lot;
            });
        } catch (UniqueConstraintViolationException) {
            return null;
        }
    }

    /**
     * Get the given serial numbers that already exist, in the order they were given.
     *
     * @param  list<string>  $serialNumbers
     * @return list<string>
     */
    public function takenSerialNumbers(array $serialNumbers): array
    {
        $taken = [];

        foreach (array_chunk($serialNumbers, self::CHUNK_SIZE) as $chunk) {
            $taken = [...$taken, ...SecurityDevice::query()->whereIn('serial_number', $chunk)->pluck('serial_number')->all()];
        }

        $takenKeys = array_flip(array_map('mb_strtoupper', $taken));

        return array_values(array_filter($serialNumbers, fn (string $serialNumber): bool => isset($takenKeys[mb_strtoupper($serialNumber)])));
    }

    /**
     * Insert the devices of the lot in chunks, all of them available.
     *
     * @param  list<string>  $serialNumbers
     */
    private function createAvailableDevices(SecurityDeviceLot $lot, array $serialNumbers): void
    {
        $now = now();

        foreach (array_chunk($serialNumbers, self::CHUNK_SIZE) as $chunk) {
            SecurityDevice::query()->insert(array_map(fn (string $serialNumber): array => [
                'security_device_lot_id' => $lot->id,
                'serial_number' => $serialNumber,
                'status' => SecurityDeviceStatus::Available->value,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk));
        }
    }
}
