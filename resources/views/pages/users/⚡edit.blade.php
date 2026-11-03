<?php

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Editar usuario')] class extends Component {
    public User $user;

    public UserForm $form;

    /**
     * Pass the breadcrumbs to the layout.
     */
    public function rendering(View $view): void
    {
        $view->layoutData(['breadcrumbs' => [
            ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
            ['name' => 'Usuarios', 'route' => route('intranet.users.index')],
            ['name' => 'Editar'],
        ]]);
    }

    /**
     * Mount the component.
     */
    public function mount(User $user): void
    {
        $this->user = $user;
        $this->form->setUser($user);
    }

    /**
     * Save the changes and go back to the list.
     */
    public function save(): void
    {
        $this->form->update($this->user);

        Flux::toast(variant: 'success', text: 'Usuario actualizado.');

        $this->redirectRoute('intranet.users.index', navigate: true);
    }
}; ?>

<form wire:submit="save">
    <x-panel.form-card>
        <x-pages::users.fields />

        <x-slot:actions>
            <flux:button :href="route('intranet.users.index')" wire:navigate>Cancelar</flux:button>
            <flux:button variant="primary" type="submit">Guardar</flux:button>
        </x-slot:actions>
    </x-panel.form-card>
</form>
