<header class="sticky top-0 z-30 flex h-16 flex-none items-center justify-between gap-3 border-b border-line bg-white px-4">
    <flux:button
        icon="bars-3"
        class="w-11! text-brand! md:hidden"
        aria-label="Abrir o cerrar el menú"
        x-on:click="menuOpen = ! menuOpen"
        x-bind:aria-expanded="menuOpen"
    />

    <span class="flex-1"></span>

    <strong class="text-sm max-md:hidden">{{ auth()->user()->name }}</strong>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <flux:button type="submit" class="border-brand! text-brand!" data-test="logout-button">Cerrar sesión</flux:button>
    </form>
</header>
