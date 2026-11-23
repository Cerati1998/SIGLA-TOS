<?php

namespace App\Actions\SecurityDevices;

use App\Enums\SecurityDeviceLotCorrection;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class CorrectSecurityDeviceLot
{
    /**
     * The columns of the announcement that build the serial numbers.
     */
    public const array SERIES_COLUMNS = ['series_prefix', 'min_digits', 'series_start', 'series_end', 'series_suffix'];

    public function __construct(private ReceiveSecurityDeviceLot $receiveLot) {}

    /**
     * Save the corrected data of the lot and its announcement, all or nothing (H28).
     * When the series changes, every device of the lot is built again, with the status its type is born with, as long
     * as none of them changed status and none of the new serial numbers exists already (T62: in all of SIGLA, or only in
     * its type when the type does not use a series). The type only changes to one with the same boxes, so when it
     * changes and does not use a series, its serial numbers move to the new type, if they are free there.
     *
     * @param  array{shipping_line_id: int, provider_id: int, security_device_type_id: int, series_prefix: string, min_digits: int, series_start: int, series_end: int, series_suffix: string|null}  $announcementAttributes
     * @param  array{received_at: string, dispatch_number: string, notes: string|null}  $lotAttributes
     */
    public function handle(SecurityDeviceLot $lot, array $announcementAttributes, array $lotAttributes): SecurityDeviceLotCorrection
    {
        try {
            return DB::transaction(function () use ($lot, $announcementAttributes, $lotAttributes): SecurityDeviceLotCorrection {
                $announcement = $lot->announcement->fill($announcementAttributes);
                $typeChanged = $announcement->isDirty('security_device_type_id');
                $type = $typeChanged ? SecurityDeviceType::withTrashed()->findOrFail($announcement->security_device_type_id) : $announcement->securityDeviceType;

                if (! $announcement->isDirty(self::SERIES_COLUMNS)) {
                    if ($typeChanged && ! $type->uses_series) {
                        if ($this->receiveLot->takenSerialNumbers($announcement->serialNumbers(), $type, $lot) !== []) {
                            return SecurityDeviceLotCorrection::SerialNumbersTaken;
                        }

                        $lot->devices()->update(['serial_number_scope' => $type->serialNumberScope()]);
                    }

                    $announcement->save();
                    $lot->update($lotAttributes);

                    return SecurityDeviceLotCorrection::Updated;
                }

                if (! $lot->seriesCanBeCorrected()) {
                    return SecurityDeviceLotCorrection::SeriesLocked;
                }

                $serialNumbers = $announcement->serialNumbers();

                if ($this->receiveLot->takenSerialNumbers($serialNumbers, $type, $lot) !== []) {
                    return SecurityDeviceLotCorrection::SerialNumbersTaken;
                }

                $lot->devices()->delete();
                $announcement->save();
                $lot->update([...$lotAttributes, 'quantity' => count($serialNumbers)]);
                $this->receiveLot->createDevices($lot, $serialNumbers, $type);

                return SecurityDeviceLotCorrection::Regenerated;
            });
        } catch (UniqueConstraintViolationException) {
            return SecurityDeviceLotCorrection::SerialNumbersTaken;
        } finally {
            $lot->announcement->refresh();
        }
    }

    /**
     * Get the serial numbers of the corrected announcement that already exist for its type in another lot.
     *
     * @return list<string>
     */
    public function takenSerialNumbers(SecurityDeviceAnnouncement $announcement, SecurityDeviceLot $lot): array
    {
        $type = SecurityDeviceType::withTrashed()->findOrFail($announcement->security_device_type_id);

        return $this->receiveLot->takenSerialNumbers($announcement->serialNumbers(), $type, $lot);
    }
}
