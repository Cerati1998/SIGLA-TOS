<?php

namespace App\Http\Controllers;

use App\Models\SecurityDeviceLot;
use Illuminate\Contracts\View\View;

class SecurityDeviceLotController extends Controller
{
    /**
     * Show the detail of a lot: its announcement, its devices by status, the list of its devices and its approvals log
     * (H08, H29, H37).
     */
    public function show(SecurityDeviceLot $securityDeviceLot): View
    {
        $securityDeviceLot->load(['announcement.shippingLine', 'announcement.provider', 'announcement.creator', 'securityDeviceType', 'approvals.approver']);

        return view('pages.security-device-lots.show', [
            'lot' => $securityDeviceLot,
            'deviceCounts' => $securityDeviceLot->deviceCountsByStatus(),
        ]);
    }

    /**
     * Show the correction of a lot (H28, H37).
     */
    public function edit(SecurityDeviceLot $securityDeviceLot): View
    {
        $securityDeviceLot->load('announcement');

        return view('pages.security-device-lots.edit', ['lot' => $securityDeviceLot]);
    }
}
