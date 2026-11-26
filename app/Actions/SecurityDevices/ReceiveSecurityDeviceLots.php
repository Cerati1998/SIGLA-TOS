<?php

namespace App\Actions\SecurityDevices;

use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class ReceiveSecurityDeviceLots
{
    /**
     * The rows written or looked up per query.
     */
    private const int CHUNK_SIZE = 1000;

    /**
     * Save an announcement of the entry of lots with its new lots and one device per serial number, all or nothing
     * (H37). The announcement may be new or already saved, when bodega adds lots to it. The devices are born Inactive
     * until bodega registers the approval of their shipping line (H29), or Available when their type does not require
     * it (T62). Return false, with nothing saved, when one of the serial numbers already exists.
     *
     * @param  list<SecurityDeviceLot>  $lots  The new lots, not saved yet, each with its type and its series.
     */
    public function handle(SecurityDeviceAnnouncement $announcement, array $lots, User $user): bool
    {
        try {
            return DB::transaction(function () use ($announcement, $lots, $user): bool {
                if (array_filter($this->takenSerialNumbersByLot($lots)) !== []) {
                    return false;
                }

                if (! $announcement->exists) {
                    $announcement->created_by = $user->id;
                    $announcement->save();
                }

                foreach ($lots as $lot) {
                    $serialNumbers = $lot->serialNumbers();
                    $lot->quantity = count($serialNumbers);
                    $announcement->lots()->save($lot);
                    $this->createDevices($lot, $serialNumbers, $lot->securityDeviceType);
                }

                return true;
            });
        } catch (UniqueConstraintViolationException) {
            return false;
        }
    }

    /**
     * Get, for each lot, its serial numbers that already exist in SIGLA, by the position of the lot.
     *
     * @param  list<SecurityDeviceLot>  $lots
     * @return array<int, list<string>>
     */
    public function takenSerialNumbersByLot(array $lots): array
    {
        return array_map(fn (SecurityDeviceLot $lot): array => $this->takenSerialNumbers($lot->serialNumbers(), $lot->securityDeviceType), $lots);
    }

    /**
     * Get the given serial numbers that already exist for the type, in the order they were given (T62). A type that uses
     * a series has its serial numbers unique in all of SIGLA. A type that does not use one has them unique only within
     * itself, so it only collides with its own devices and with the ones of the types that use a series.
     * The devices of the lot being corrected, when given, do not count.
     *
     * @param  list<string>  $serialNumbers
     * @return list<string>
     */
    public function takenSerialNumbers(array $serialNumbers, SecurityDeviceType $type, ?SecurityDeviceLot $exceptLot = null): array
    {
        $taken = [];

        foreach (array_chunk($serialNumbers, self::CHUNK_SIZE) as $chunk) {
            $taken = [...$taken, ...SecurityDevice::query()
                ->whereIn('serial_number', $chunk)
                ->unless($type->uses_series, fn ($query) => $query->whereIn('serial_number_scope', [0, $type->id]))
                ->when($exceptLot, fn ($query, SecurityDeviceLot $lot) => $query->where('security_device_lot_id', '!=', $lot->id))
                ->pluck('serial_number')
                ->all()];
        }

        $takenKeys = array_flip(array_map('mb_strtoupper', $taken));

        return array_values(array_filter($serialNumbers, fn (string $serialNumber): bool => isset($takenKeys[mb_strtoupper($serialNumber)])));
    }

    /**
     * Insert the devices of the lot in chunks, born with the status of their type: Inactive until their approval is
     * registered (H29), or Available when the type does not require it (T62).
     *
     * @param  list<string>  $serialNumbers
     */
    public function createDevices(SecurityDeviceLot $lot, array $serialNumbers, SecurityDeviceType $type): void
    {
        $now = now();
        $status = $type->initialDeviceStatus()->value;
        $scope = $type->serialNumberScope();

        foreach (array_chunk($serialNumbers, self::CHUNK_SIZE) as $chunk) {
            SecurityDevice::query()->insert(array_map(fn (string $serialNumber): array => [
                'security_device_lot_id' => $lot->id,
                'serial_number_scope' => $scope,
                'serial_number' => $serialNumber,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk));
        }
    }
}
