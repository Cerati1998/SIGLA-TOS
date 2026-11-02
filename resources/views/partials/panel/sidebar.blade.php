<aside
    :data-open="menuOpen"
    aria-label="Menú principal"
    class="fixed top-header-mobile bottom-0 left-0 z-20 w-sidebar overflow-y-auto bg-brand transition-[width,translate] duration-300 ease-in-out md:top-header md:group-data-collapsed/panel:w-sidebar-mini max-md:not-data-open:-translate-x-full"
>
    <nav class="flex flex-col gap-1.25 py-1.25">
        <x-panel.menu-item icon="home" :href="route('dashboard')" :active="request()->routeIs('dashboard')">Inicio</x-panel.menu-item>
    </nav>
</aside>
