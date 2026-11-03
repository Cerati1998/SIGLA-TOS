<?php

use App\Livewire\Forms\UserForm;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Nuevo usuario')] class extends Component {
    public UserForm $form;

    #[Locked]
    public ?string $generatedPassword = null;

    /**
     * Pass the breadcrumbs to the layout.
     */
    public function rendering(View $view): void
    {
        $view->layoutData(['breadcrumbs' => [
            ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
            ['name' => 'Usuarios', 'route' => route('intranet.users.index')],
            ['name' => 'Nuevo'],
        ]]);
    }

    /**
     * Create the user and keep its password on screen so it can be copied.
     */
    public function save(): void
    {
        $this->generatedPassword = $this->form->store();
    }
}; ?>

<div>
    @if ($generatedPassword === null)
        <form wire:submit="save">
            <x-panel.form-card>
                <x-pages::users.fields />

                <x-slot:actions>
                    <flux:button :href="route('intranet.users.index')" wire:navigate>Cancelar</flux:button>
                    <flux:button variant="primary" type="submit">Guardar</flux:button>
                </x-slot:actions>
            </x-panel.form-card>
        </form>
    @else
        <x-panel.form-card>
            <x-slot:heading>Usuario creado</x-slot:heading>

            <p class="col-span-full m-0">{{ $form->name }} · {{ $form->email }}</p>

            <div
                x-data="{
                    copied: false,
                    copy() {
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(this.$refs.password.value);
                        } else {
                            this.$refs.password.select();
                            document.execCommand('copy');
                        }

                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    },
                }"
                class="col-span-full grid gap-1.75"
            >
                <label for="generated-password" class="text-sm leading-tight font-medium text-ink">Contraseña generada</label>

                <div class="flex flex-wrap items-center gap-3">
                    <input
                        id="generated-password"
                        x-ref="password"
                        type="text"
                        readonly
                        value="{{ $generatedPassword }}"
                        class="h-10.25 min-w-0 flex-1 basis-60 rounded-control border border-control bg-surface px-[10.5px] font-mono text-base max-md:h-11"
                    >

                    <flux:button type="button" x-on:click="copy()">
                        <span class="flex items-center gap-2" x-show="! copied">
                            <x-feather name="copy" class="size-4" />
                            Copiar
                        </span>
                        <span class="flex items-center gap-2" x-show="copied" x-cloak>
                            <x-feather name="check" class="size-4" />
                            Copiada
                        </span>
                    </flux:button>
                </div>
            </div>

            <p class="col-span-full m-0 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
                Copia esta contraseña y entrégasela al usuario. No se volverá a mostrar.
            </p>

            <x-slot:actions>
                <flux:button :href="route('intranet.users.create')" wire:navigate>Crear otro</flux:button>
                <flux:button variant="primary" :href="route('intranet.users.index')" wire:navigate>Ir a la lista</flux:button>
            </x-slot:actions>
        </x-panel.form-card>
    @endif
</div>
