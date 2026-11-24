<?php

namespace App\Http\Controllers;

use App\Models\SecurityDeviceAnnouncement;
use Illuminate\Contracts\View\View;

class SecurityDeviceAnnouncementController extends Controller
{
    /**
     * Show the detail of an announcement of the entry of lots: its header and its lots (H37, criterion 10).
     */
    public function show(SecurityDeviceAnnouncement $securityDeviceAnnouncement): View
    {
        $securityDeviceAnnouncement->load(['shippingLine', 'provider', 'creator'])->loadCount('lots')->loadSum('lots', 'quantity');

        return view('pages.security-device-announcements.show', ['announcement' => $securityDeviceAnnouncement]);
    }

    /**
     * Show the correction of the header of an announcement (H37, criterion 12).
     */
    public function edit(SecurityDeviceAnnouncement $securityDeviceAnnouncement): View
    {
        return view('pages.security-device-announcements.edit', ['announcement' => $securityDeviceAnnouncement]);
    }

    /**
     * Show the entry of new lots in an announcement already saved (H37, criterion 10).
     */
    public function createLots(SecurityDeviceAnnouncement $securityDeviceAnnouncement): View
    {
        $securityDeviceAnnouncement->load(['shippingLine', 'provider']);

        return view('pages.security-device-announcements.create-lots', ['announcement' => $securityDeviceAnnouncement]);
    }
}
