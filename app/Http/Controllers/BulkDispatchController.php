<?php

namespace App\Http\Controllers;

use App\Actions\BulkDispatches\GroupSecurityDevices;
use App\Models\BulkDispatch;
use Illuminate\Contracts\View\View;

class BulkDispatchController extends Controller
{
    /**
     * Show the detail of a bulk dispatch: its data and, for each type of device, its ranges, its quantity and its
     * serial numbers (H30, criterion 11).
     */
    public function show(BulkDispatch $bulkDispatch, GroupSecurityDevices $groupSecurityDevices): View
    {
        $bulkDispatch->load(['receiver', 'creator', 'client', 'kit.kitType', 'yardZone']);

        return view('pages.bulk-dispatches.show', [
            'bulkDispatch' => $bulkDispatch,
            'types' => $groupSecurityDevices->byType($bulkDispatch->deviceIds()),
        ]);
    }
}
