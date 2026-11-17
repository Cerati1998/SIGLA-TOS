<?php

namespace App\Http\Controllers;

use App\Enums\KitAssignment;
use App\Models\Kit;
use App\Models\SecurityDevice;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KitController extends Controller
{
    /**
     * Show a kit: its data and the scanning of its devices (H14).
     */
    public function show(Kit $kit): View
    {
        $kit->load(['kitType', 'shippingLine', 'dispatches.receiver']);

        return view('pages.kits.show', ['kit' => $kit]);
    }

    /**
     * Show the PDF of a dispatched kit: its contents, when it was dispatched, who delivered it and to whom (H15,
     * criterion 14). It opens in the browser, to download or print it.
     */
    public function pdf(Request $request, Kit $kit): Response
    {
        /** @var User $user */
        $user = $request->user();

        $kit->load(['kitType', 'shippingLine', 'dispatches.receiver', 'dispatches.creator', 'devices.lot.announcement.securityDeviceType']);
        $dispatch = $kit->dispatch();

        abort_if($dispatch === null, 404);

        $devices = $kit->devices->map(function (SecurityDevice $device): array {
            /** @var object{assignment: string|null} $pivot */
            $pivot = $device->getRelationValue('pivot');

            return [
                'serialNumber' => $device->serial_number,
                'type' => $device->lot->announcement->securityDeviceType->name,
                'assignment' => KitAssignment::tryFrom((string) $pivot->assignment)?->label() ?? '-',
            ];
        });

        return Pdf::loadView('pdf.kits.dispatch', [
            'kit' => $kit,
            'dispatch' => $dispatch,
            'devices' => $devices,
            'slots' => $kit->slots(),
            'generatedAt' => now('America/Lima'),
            'generatedBy' => $user->name,
            'logo' => 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('images/sigla-logo2.png'))),
        ])
            ->setPaper('a4')
            ->stream(sprintf('Despacho %s - %s.pdf', $dispatch->number(), $kit->pouch_code));
    }
}
