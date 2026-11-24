<?php

namespace App\Actions\SecurityDevices;

use App\Enums\SecurityDeviceLotCorrection;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class CorrectSecurityDeviceLot
{
    public function __construct(private ReceiveSecurityDeviceLots $receiveLots) {}

    /**
     * Save the corrected type, series and notes of a lot, all or nothing (H28, H37). Its announcement is corrected apart.
     * When the series changes, every device of the lot is built again, with the status its type is born with, as long
     * as none of them changed status and none of the new serial numbers exists already (T62: in all of SIGLA, or only in
     * its type when the type does not use a series). The type only changes to one with the same boxes, so when it
     * changes and does not use a series, its serial numbers move to the new type, if they are free there.
     *
     * @param  array{security_device_type_id: int, series_prefix: string, min_digits: int, series_start: int, series_end: int, series_suffix: string|null, notes: string|null}  $attributes
     */
    public function handle(SecurityDeviceLot $lot, array $attributes): SecurityDeviceLotCorrection
    {
        try {
            return DB::transaction(function () use ($lot, $attributes): SecurityDeviceLotCorrection {
                $lot->fill($attributes);
                $typeChanged = $lot->isDirty('security_device_type_id');
                $type = $typeChanged ? SecurityDeviceType::withTrashed()->findOrFail($lot->security_device_type_id) : $lot->securityDeviceType;

                if (! $lot->isDirty(SecurityDeviceLot::SERIES_COLUMNS)) {
                    if ($typeChanged && ! $type->uses_series) {
                        if ($this->receiveLots->takenSerialNumbers($lot->serialNumbers(), $type, $lot) !== []) {
                            return SecurityDeviceLotCorrection::SerialNumbersTaken;
                        }

                        $lot->devices()->update(['serial_number_scope' => $type->serialNumberScope()]);
                    }

                    $lot->save();

                    return SecurityDeviceLotCorrection::Updated;
                }

                if (! $lot->seriesCanBeCorrected()) {
                    return SecurityDeviceLotCorrection::SeriesLocked;
                }

                $serialNumbers = $lot->serialNumbers();

                if ($this->receiveLots->takenSerialNumbers($serialNumbers, $type, $lot) !== []) {
                    return SecurityDeviceLotCorrection::SerialNumbersTaken;
                }

                $lot->devices()->delete();
                $lot->quantity = count($serialNumbers);
                $lot->save();
                $this->receiveLots->createDevices($lot, $serialNumbers, $type);

                return SecurityDeviceLotCorrection::Regenerated;
            });
        } catch (UniqueConstraintViolationException) {
            return SecurityDeviceLotCorrection::SerialNumbersTaken;
        } finally {
            $lot->refresh();
        }
    }

    /**
     * Get the serial numbers of the corrected lot, not saved yet, that already exist for its type in another lot.
     *
     * @return list<string>
     */
    public function takenSerialNumbers(SecurityDeviceLot $correctedLot, SecurityDeviceLot $lot): array
    {
        $type = SecurityDeviceType::withTrashed()->findOrFail($correctedLot->security_device_type_id);

        return $this->receiveLots->takenSerialNumbers($correctedLot->serialNumbers(), $type, $lot);
    }
}
