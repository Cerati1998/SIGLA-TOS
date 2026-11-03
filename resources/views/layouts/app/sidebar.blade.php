<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-svh bg-surface font-sans text-ink antialiased">
        <div
            x-data="{
                menuOpen: false,
                menuCollapsed: $persist(false).as('sidebar-collapsed'),
                openTree: null,
                toggleMenu() {
                    if (window.matchMedia('(min-width: 768px)').matches) {
                        this.menuCollapsed = ! this.menuCollapsed;
                    } else {
                        this.menuOpen = ! this.menuOpen;
                    }
                },
            }"
            :data-collapsed="menuCollapsed"
            class="group/panel"
        >
            @include('partials.panel.topbar')
            @include('partials.panel.sidebar')

            <div
                x-on:click="menuOpen = false"
                class="min-w-0 pt-header transition-[padding] duration-300 ease-in-out md:ps-sidebar md:group-data-collapsed/panel:ps-sidebar-mini"
            >
                {{ $slot }}
            </div>
        </div>

        @persist('toast')
            <flux:toast.group position="top end">
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @wireUiScripts
        @fluxScripts
    </body>
</html>
