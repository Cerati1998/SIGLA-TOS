<?php

namespace App\Http\Controllers;

use App\Models\Kit;
use Illuminate\Contracts\View\View;

class KitController extends Controller
{
    /**
     * Show a kit: its data and the scanning of its devices (H14).
     */
    public function show(Kit $kit): View
    {
        $kit->load(['kitType', 'shippingLine']);

        return view('pages.kits.show', ['kit' => $kit]);
    }
}
