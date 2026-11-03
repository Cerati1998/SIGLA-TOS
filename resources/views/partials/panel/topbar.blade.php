<header class="fixed inset-x-0 top-0 z-30 flex h-header bg-white max-md:items-center max-md:gap-3 max-md:px-5.25">
    <a
        href="{{ route('intranet.dashboard') }}"
        wire:navigate
        class="flex flex-none items-center justify-center transition-[width] duration-300 ease-in-out max-md:order-2 md:w-sidebar md:group-data-collapsed/panel:w-sidebar-mini"
    >
        <img src="{{ asset('images/sigla-logo2.png') }}" alt="SIGLA, Powered by Macromar" class="h-auto max-md:h-10 max-md:w-auto md:w-50 md:group-data-collapsed/panel:hidden">
        <img src="{{ asset('images/sigla-icon.png') }}" alt="SIGLA" class="hidden size-10 md:group-data-collapsed/panel:block">
    </a>

    <div class="flex flex-1 items-center justify-between gap-3 px-5.25 max-md:contents">
        <flux:button
            variant="subtle"
            :square="true"
            class="size-11.25! rounded-card! bg-brand/10! text-brand! max-md:order-1"
            aria-label="Abrir o cerrar el menú"
            x-on:click="toggleMenu()"
        >
            <x-feather name="align-left" class="size-6" />
        </flux:button>

        <flux:dropdown position="bottom" align="end" class="max-md:order-3 max-md:ms-auto">
            <button type="button" aria-label="Menú de usuario" class="flex size-11 cursor-pointer items-center justify-center">
                <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="" class="size-10 rounded-card bg-brand/10 object-cover">
            </button>

            <flux:menu>
                <div class="px-3.75 py-2.5 text-sm font-medium text-ink">{{ auth()->user()->name }}</div>

                <flux:menu.separator />

                <flux:menu.item :href="route('profile.edit')" wire:navigate>
                    <x-feather name="user" class="me-2 size-4" />
                    Perfil
                </flux:menu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" class="w-full cursor-pointer" data-test="logout-button">
                        <x-feather name="lock" class="me-2 size-4" />
                        Cerrar sesión
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </div>
</header>
