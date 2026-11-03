<?php

namespace App\Livewire\Users;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Usuarios')]
class Index extends Component
{
    /**
     * Render the component and pass the breadcrumbs to the layout.
     */
    public function render(): View
    {
        return view('pages.users.index')->layoutData(['breadcrumbs' => [
            ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
            ['name' => 'Usuarios'],
        ]]);
    }
}
