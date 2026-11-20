<?php

namespace App\Http\Controllers;

use App\Actions\Certifications\ListPendingInspections;
use App\Enums\CertificationStatus;
use App\Models\Certification;
use Illuminate\Contracts\View\View;

class CertificationController extends Controller
{
    /**
     * Show the panel of the certifications: the assignment, the internal and external inspections, with how many
     * units each inspection has pending (T58), and the list of assignments, with how many are ready to close.
     */
    public function index(ListPendingInspections $listPendingInspections): View
    {
        return view('pages.certifications.index', [
            'pending' => $listPendingInspections->counts(),
            'readyToClose' => Certification::query()->whereStatus(CertificationStatus::ReadyToClose)->count(),
        ]);
    }

    /**
     * Show a certification already started: what it keeps and the data of its request in SIGLA 1.0 (H17).
     */
    public function show(Certification $certification): View
    {
        return view('pages.certifications.show', ['certification' => $certification]);
    }
}
