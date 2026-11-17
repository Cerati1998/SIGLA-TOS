<?php

namespace App\Http\Controllers;

use App\Actions\KitDispatches\BuildKitPdfPage;
use App\Models\Kit;
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
    public function pdf(Request $request, Kit $kit, BuildKitPdfPage $buildKitPdfPage): Response
    {
        /** @var User $user */
        $user = $request->user();
        $kit->load(['dispatches.receiver', 'dispatches.creator']);
        $dispatch = $kit->dispatch();

        abort_if($dispatch === null, 404);

        return Pdf::loadView('pdf.kits.dispatch', [
            'dispatch' => $dispatch,
            'pages' => [$buildKitPdfPage->handle($kit)],
            'generatedBy' => $user->name,
        ])
            ->setPaper('a4')
            ->stream(sprintf('Despacho %s - %s.pdf', $dispatch->number(), $kit->pouch_code));
    }
}
