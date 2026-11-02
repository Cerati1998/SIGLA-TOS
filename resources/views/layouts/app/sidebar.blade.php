<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-svh bg-surface font-sans text-ink antialiased">
        <div x-data="{ menuOpen: false }" class="flex min-h-svh max-md:flex-col">
            @include('partials.panel.sidebar')

            <div class="flex min-w-0 flex-1 flex-col">
                @include('partials.panel.topbar')

                {{ $slot }}
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
