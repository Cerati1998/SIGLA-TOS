<?php

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Usuarios')] class extends Component {
    /**
     * Pass the breadcrumbs to the layout.
     */
    public function rendering(View $view): void
    {
        $view->layoutData(['breadcrumbs' => [
            ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
            ['name' => 'Usuarios'],
        ]]);
    }
}; ?>

<div>
    <x-panel.list-card>
        <livewire:tables.users-table />
    </x-panel.list-card>

    <livewire:pages::users.form-modal />
</div>
