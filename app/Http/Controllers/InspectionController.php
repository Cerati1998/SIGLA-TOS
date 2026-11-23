<?php

namespace App\Http\Controllers;

use App\Enums\CertificationSectionType;
use App\Models\Certification;
use Illuminate\Contracts\View\View;

class InspectionController extends Controller
{
    /**
     * Show the assigned units whose internal or external inspection is pending, to choose one (H18, criterion 2).
     */
    public function index(CertificationSectionType $type): View
    {
        return view('pages.inspections.index', ['type' => $type]);
    }

    /**
     * Show the steps of the internal or the external inspection of a unit (H18).
     */
    public function show(CertificationSectionType $type, Certification $certification): View
    {
        abort_unless($certification->steps()->where('type', $type)->exists(), 404);

        return view('pages.inspections.show', ['certification' => $certification, 'type' => $type]);
    }
}
