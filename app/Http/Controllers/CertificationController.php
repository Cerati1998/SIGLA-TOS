<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use Illuminate\Contracts\View\View;

class CertificationController extends Controller
{
    /**
     * Show a certification already started: what it keeps and the data of its request in SIGLA 1.0 (H17).
     */
    public function show(Certification $certification): View
    {
        return view('pages.certifications.show', ['certification' => $certification]);
    }
}
