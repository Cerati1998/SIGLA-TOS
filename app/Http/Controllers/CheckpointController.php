<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use Illuminate\View\View;

class CheckpointController extends Controller
{
    /**
     * Show the steps of the checkpoint of a unit (H34). A closed checkpoint is only read (criterion 11).
     */
    public function show(Checkpoint $checkpoint): View
    {
        return view('pages.checkpoints.show', ['checkpoint' => $checkpoint->load('certification')]);
    }

    /**
     * Show the detail of a checkpoint, only to read (H36, criterion 3).
     */
    public function details(Checkpoint $checkpoint): View
    {
        return view('pages.checkpoints.details', ['checkpoint' => $checkpoint->load('certification')]);
    }
}
