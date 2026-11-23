<?php

namespace App\Http\Controllers;

use App\Actions\Kits\BuildKitContents;
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
        $kit->load(['kitType', 'shippingLine', 'dispatches.receiver', 'dispatches.creator']);

        return view('pages.kits.show', ['kit' => $kit]);
    }

    /**
     * Show the PDF of a complete kit: only its contents, without the data of its dispatch (H15, criterion 14). It
     * opens in the browser, to download or print it.
     */
    public function pdf(Request $request, Kit $kit, BuildKitContents $buildKitContents): Response
    {
        abort_unless($kit->status->hasContentsPdf(), 404);

        /** @var User $user */
        $user = $request->user();

        return Pdf::loadView('pdf.kits.contents', [
            ...$buildKitContents->handle($kit),
            'generatedBy' => $user->name,
        ])
            ->setPaper('a4')
            ->stream(sprintf('Kit %s.pdf', $kit->pouch_code));
    }
}
