<?php

namespace App\Http\Controllers;

use App\Actions\KitDispatches\BuildKitPdfPage;
use App\Models\Kit;
use App\Models\KitDispatch;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KitDispatchController extends Controller
{
    /**
     * Show the PDF of a dispatch: one page per kit, each one like the PDF of the kit (H15, criterion 14).
     */
    public function pdf(Request $request, KitDispatch $kitDispatch, BuildKitPdfPage $buildKitPdfPage): Response
    {
        /** @var User $user */
        $user = $request->user();
        $kitDispatch->load(['receiver', 'creator', 'kits']);

        return Pdf::loadView('pdf.kits.dispatch', [
            'dispatch' => $kitDispatch,
            'pages' => $kitDispatch->kits->map(fn (Kit $kit): array => $buildKitPdfPage->handle($kit))->all(),
            'generatedBy' => $user->name,
        ])
            ->setPaper('a4')
            ->stream(sprintf('Despacho %s.pdf', $kitDispatch->number()));
    }
}
