<aside
    :data-open="menuOpen"
    aria-label="Menú principal"
    class="flex w-65 flex-none flex-col bg-brand text-white md:sticky md:top-0 md:h-svh md:overflow-y-auto max-md:fixed max-md:inset-x-0 max-md:top-16 max-md:bottom-0 max-md:z-20 max-md:w-full max-md:overflow-y-auto max-md:not-data-open:hidden"
>
    <a href="{{ route('dashboard') }}" wire:navigate class="flex h-16 flex-none items-center border-b border-line bg-white px-5 max-md:hidden">
        <img src="{{ asset('images/sigla-logo.png') }}" alt="SIGLA, Powered by Macromar" class="h-10 w-auto">
    </a>

    <nav class="flex flex-col pb-6">
        <x-panel.menu-item :href="route('dashboard')" :active="request()->routeIs('dashboard')">Inicio</x-panel.menu-item>
    </nav>
</aside>
