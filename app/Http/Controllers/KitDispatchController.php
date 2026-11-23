<?php

namespace App\Http\Controllers;

use App\Actions\Kits\BuildKitContents;
use App\Models\Kit;
use App\Models\KitDispatch;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KitDispatchController extends Controller
{
    /**
     * Show the PDF of a dispatch: its data once, the kits it delivered and the contents of each kit by its pouch (H15,
     * criterion 14).
     */
    public function pdf(Request $request, KitDispatch $kitDispatch, BuildKitContents $buildKitContents): Response
    {
        /** @var User $user */
        $user = $request->user();
        $kitDispatch->load(['receiver', 'creator', 'kits']);

        return Pdf::loadView('pdf.kits.dispatch', [
            'dispatch' => $kitDispatch,
            'kits' => $kitDispatch->kits->map(fn (Kit $kit): array => $buildKitContents->handle($kit))->all(),
            'generatedBy' => $user->name,
        ])
            ->setPaper('a4')
            ->stream(sprintf('Despacho %s.pdf', $kitDispatch->number()));
    }
}
