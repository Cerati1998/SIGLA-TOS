<?php

namespace App\Http\Controllers;

use App\Models\SecurityDeviceLot;
use Illuminate\Contracts\View\View;

class SecurityDeviceLotController extends Controller
{
    /**
     * Show the detail of a lot: its origin, who entered it, its devices by status and the list of its devices.
     */
    public function show(SecurityDeviceLot $securityDeviceLot): View
    {
        $securityDeviceLot->load(['announcement.shippingLine', 'announcement.provider', 'announcement.securityDeviceType', 'creator']);

        return view('pages.security-device-lots.show', [
            'lot' => $securityDeviceLot,
            'deviceCounts' => $securityDeviceLot->deviceCountsByStatus(),
        ]);
    }

    /**
     * Show the correction of a lot (H28).
     */
    public function edit(SecurityDeviceLot $securityDeviceLot): View
    {
        $securityDeviceLot->load('announcement');

        return view('pages.security-device-lots.edit', ['lot' => $securityDeviceLot]);
    }
}
