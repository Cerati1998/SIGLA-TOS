<?php

namespace App\Actions\SecurityDevices;

use App\Models\SecurityDeviceLot;
use Illuminate\Support\Facades\DB;

class RemoveSecurityDeviceLot
{
    /**
     * Remove a lot from its announcement with all its devices, all or nothing (H37, criterion 12). It only goes when none
     * of its devices changed status and the announcement keeps at least one other lot. Return whether it was removed.
     */
    public function handle(SecurityDeviceLot $lot): bool
    {
        return DB::transaction(function () use ($lot): bool {
            $lotsOfAnnouncement = SecurityDeviceLot::query()
                ->where('security_device_announcement_id', $lot->security_device_announcement_id)
                ->lockForUpdate()
                ->count();

            if ($lotsOfAnnouncement < 2 || ! $lot->seriesCanBeCorrected()) {
                return false;
            }

            $lot->devices()->delete();
            $lot->delete();

            return true;
        });
    }
}
