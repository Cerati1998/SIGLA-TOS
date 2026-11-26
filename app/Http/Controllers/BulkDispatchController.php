<?php

namespace App\Http\Controllers;

use App\Actions\BulkDispatches\DescribeBulkDispatch;
use App\Models\BulkDispatch;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BulkDispatchController extends Controller
{
    /**
     * Show the detail of a bulk dispatch: its data, the assignment its kit is linked to when it is for the operation
     * (T87) and, for each type of device, its ranges, its quantity and its serial numbers (H30, criterion 11).
     */
    public function show(BulkDispatch $bulkDispatch, DescribeBulkDispatch $describeBulkDispatch): View
    {
        return view('pages.bulk-dispatches.show', $describeBulkDispatch->handle($bulkDispatch));
    }

    /**
     * Show the PDF of a bulk dispatch, as the PDF of a dispatch of kits: its data, the assignment of its kit, its
     * devices by type with their ranges and their serial numbers, and who delivered and received them (T87).
     */
    public function pdf(Request $request, BulkDispatch $bulkDispatch, DescribeBulkDispatch $describeBulkDispatch): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Pdf::loadView('pdf.bulk-dispatches.dispatch', [
            ...$describeBulkDispatch->handle($bulkDispatch),
            'generatedBy' => $user->name,
        ])
            ->setPaper('a4')
            ->stream(sprintf('Despacho %s.pdf', $bulkDispatch->number()));
    }
}
