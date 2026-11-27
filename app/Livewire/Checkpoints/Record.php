<?php

namespace App\Livewire\Checkpoints;

use App\Actions\Checkpoints\DescribeCheckpoint;
use App\Models\Checkpoint;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The detail of a checkpoint, only to read (H36, criterion 3): its status, the unit and its transport, each
 * reinspection, each step with its photos or with its series marked, and the observations with the extra photos.
 * Once closed, it goes at the end of the PDF of the certification (T94).
 */
class Record extends Component
{
    #[Locked]
    public Checkpoint $checkpoint;

    /**
     * Render the component.
     */
    public function render(DescribeCheckpoint $describeCheckpoint): View
    {
        return view('pages.checkpoints.record', $describeCheckpoint->handle($this->checkpoint));
    }
}
