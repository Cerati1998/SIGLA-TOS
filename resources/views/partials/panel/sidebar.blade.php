@php
    $links = [
        [
            'name' => 'Inicio',
            'icon' => 'home',
            'route' => route('intranet.dashboard'),
            'active' => request()->routeIs('intranet.dashboard'),
        ],
        ['header' => 'Administración'],
        [
            'name' => 'Usuarios',
            'icon' => 'users',
            'route' => route('intranet.users.index'),
            'active' => request()->routeIs('intranet.users.*'),
        ],
        [
            'name' => 'Sucursales',
            'icon' => 'map-pin',
            'route' => route('intranet.branches.index'),
            'active' => request()->routeIs('intranet.branches.*'),
        ],
        ['header' => 'Maestros'],
        [
            'name' => 'Líneas navieras',
            'icon' => 'anchor',
            'route' => route('intranet.shipping-lines.index'),
            'active' => request()->routeIs('intranet.shipping-lines.*'),
        ],
        [
            'name' => 'Proveedores',
            'icon' => 'truck',
            'route' => route('intranet.providers.index'),
            'active' => request()->routeIs('intranet.providers.*'),
        ],
        [
            'name' => 'Tipos de dispositivo',
            'icon' => 'shield',
            'route' => route('intranet.security-device-types.index'),
            'active' => request()->routeIs('intranet.security-device-types.*'),
        ],
        ['header' => 'Configuración'],
        [
            'name' => 'Empresa',
            'icon' => 'briefcase',
            'route' => route('intranet.company.edit'),
            'active' => request()->routeIs('intranet.company.*'),
        ],
    ];
@endphp

<aside
    :data-open="menuOpen"
    aria-label="Menú principal"
    class="fixed top-header bottom-0 left-0 z-20 w-sidebar overflow-y-auto bg-brand transition-[width,translate] duration-300 ease-in-out md:group-data-collapsed/panel:w-sidebar-mini max-md:not-data-open:-translate-x-full"
>
    <nav class="flex flex-col gap-1.25 py-1.25">
        <x-panel.menu :links="$links" />
    </nav>
</aside>
